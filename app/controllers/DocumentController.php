<?php
/**
 * DocumentController — Pièces jointes par dossier
 *
 * Routes :
 *   POST /documents/upload/{dossierId}  → upload()
 *   POST /documents/delete/{id}         → delete()
 *   GET  /documents/view/{id}           → serve()
 *   GET  /documents/list/{dossierId}    → list()
 *
 * Contraintes :
 *  - PHP 8 pur, PDO nommé
 *  - Auth::requireLogin() sur toutes les méthodes
 *  - CSRF sur les POST
 *  - Fichiers uploadés dans public/uploads/documents/dossier_{id}/
 *  - Nom stocké : {sha256_hash}_{nom_original}
 *  - Types autorisés : pdf, doc, docx, jpg, jpeg, png, xlsx, xls, odt
 *  - Taille max : 10 Mo
 */
class DocumentController extends Controller
{
    // ----------------------------------------------------------------
    // Types de fichiers autorisés
    // ----------------------------------------------------------------
    private const TYPES_AUTORISES = [
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xls'  => 'application/vnd.ms-excel',
        'odt'  => 'application/vnd.oasis.opendocument.text',
    ];

    // Types que l'on affiche inline dans le popup (PDF et images)
    private const TYPES_INLINE = ['application/pdf', 'image/jpeg', 'image/png'];

    private const TAILLE_MAX = 10 * 1024 * 1024; // 10 Mo

    // ----------------------------------------------------------------
    // POST /documents/upload/{dossierId}
    // ----------------------------------------------------------------
    public function upload(string $dossierId): void
    {
        Auth::requireLogin();
        CSRF::check();

        $dossierId = (int) $dossierId;
        if ($dossierId <= 0) {
            $this->json(['success' => false, 'message' => 'Dossier invalide.'], 400);
        }

        // Vérifier que le dossier existe
        $stmt = $this->db->prepare('SELECT id FROM dossiers WHERE id = :id');
        $stmt->execute([':id' => $dossierId]);
        if (!$stmt->fetch()) {
            $this->json(['success' => false, 'message' => 'Dossier introuvable.'], 404);
        }

        // Vérifier présence du fichier
        if (empty($_FILES['fichier']) || $_FILES['fichier']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->json(['success' => false, 'message' => 'Aucun fichier reçu.'], 400);
        }

        $file  = $_FILES['fichier'];
        $error = $file['error'];

        if ($error !== UPLOAD_ERR_OK) {
            $messages = [
                UPLOAD_ERR_INI_SIZE   => 'Le fichier dépasse la limite du serveur.',
                UPLOAD_ERR_FORM_SIZE  => 'Le fichier est trop lourd.',
                UPLOAD_ERR_PARTIAL    => 'Envoi partiel — réessayez.',
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant.',
                UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire sur le disque.',
            ];
            $msg = $messages[$error] ?? 'Erreur d\'upload inconnue (code ' . $error . ').';
            $this->json(['success' => false, 'message' => $msg], 400);
        }

        // Taille
        if ($file['size'] > self::TAILLE_MAX) {
            $this->json(['success' => false, 'message' => 'Le fichier dépasse 10 Mo.'], 400);
        }

        // Extension
        $nomOriginal = $file['name'];
        $ext = strtolower(pathinfo($nomOriginal, PATHINFO_EXTENSION));
        if (!array_key_exists($ext, self::TYPES_AUTORISES)) {
            $this->json([
                'success'  => false,
                'message'  => 'Type de fichier non autorisé. Formats acceptés : ' . implode(', ', array_keys(self::TYPES_AUTORISES)) . '.',
            ], 400);
        }

        // Détermination du MIME réel (finfo si disponible)
        $mimeReel = $this->detectMime($file['tmp_name'], $ext);

        // Création du dossier de destination
        $uploadDir = $this->getUploadDir($dossierId);
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $this->json(['success' => false, 'message' => 'Impossible de créer le dossier de stockage.'], 500);
            }
        }

        // Nom unique : sha256 du contenu + nom original nettoyé
        $hash        = substr(hash_file('sha256', $file['tmp_name']), 0, 16);
        $nomNettoye  = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nomOriginal);
        $nomStockage = $hash . '_' . $nomNettoye;
        $cheminAbs   = $uploadDir . $nomStockage;

        if (!move_uploaded_file($file['tmp_name'], $cheminAbs)) {
            $this->json(['success' => false, 'message' => 'Échec du déplacement du fichier.'], 500);
        }

        // Chemin relatif (depuis public/)
        $cheminRelatif = 'uploads/documents/dossier_' . $dossierId . '/' . $nomStockage;

        // Description optionnelle
        $description = trim($_POST['description'] ?? '');

        // Insertion en base — construction dynamique pour compatibilité schéma 001 / 005
        // (colonne nom_original OU nom_fichier, mime_type OU type_mime selon version)
        $colonnes = $this->db->query("SHOW COLUMNS FROM documents")->fetchAll(\PDO::FETCH_COLUMN);
        $cols     = ['dossier_id', 'nom_stockage', 'chemin_fichier', 'type_document', 'taille_octets', 'description', 'uploaded_by', 'created_at'];
        $vals     = [':dossier_id', ':nom_stockage', ':chemin_fichier', ':type_document', ':taille_octets', ':description', ':uploaded_by', 'NOW()'];
        $params   = [
            ':dossier_id'    => $dossierId,
            ':nom_stockage'  => $nomStockage,
            ':chemin_fichier'=> $cheminRelatif,
            ':type_document' => 'piece_jointe',
            ':taille_octets' => $file['size'],
            ':description'   => $description ?: null,
            ':uploaded_by'   => Auth::userId(),
        ];

        if (in_array('nom_original', $colonnes)) {
            $cols[] = 'nom_original'; $vals[] = ':nom_original';
            $params[':nom_original'] = $nomOriginal;
        }
        if (in_array('nom_fichier', $colonnes)) {
            $cols[] = 'nom_fichier'; $vals[] = ':nom_fichier';
            $params[':nom_fichier'] = $nomOriginal;
        }
        if (in_array('mime_type', $colonnes)) {
            $cols[] = 'mime_type'; $vals[] = ':mime_type';
            $params[':mime_type'] = $mimeReel;
        }
        if (in_array('type_mime', $colonnes)) {
            $cols[] = 'type_mime'; $vals[] = ':type_mime';
            $params[':type_mime'] = $mimeReel;
        }

        $sql  = 'INSERT INTO documents (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $vals) . ')';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $newId = (int) $this->db->lastInsertId();

        $this->json([
            'success'  => true,
            'message'  => 'Fichier uploadé avec succès.',
            'document' => [
                'id'     => $newId,
                'nom'    => $nomOriginal,
                'type'   => $mimeReel,
                'taille' => $this->formatTaille($file['size']),
                'date'   => date('d/m/Y H:i'),
                'url'    => BASE_URL . '/documents/view/' . $newId,
            ],
        ]);
    }

    // ----------------------------------------------------------------
    // POST /documents/delete/{id}
    // ----------------------------------------------------------------
    public function delete(string $id): void
    {
        Auth::requireLogin();
        CSRF::check();

        $id = (int) $id;
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID invalide.'], 400);
        }

        $stmt = $this->db->prepare('SELECT * FROM documents WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$doc) {
            $this->json(['success' => false, 'message' => 'Document introuvable.'], 404);
        }

        // Suppression du fichier physique
        $cheminAbs = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $doc['chemin_fichier']);

        if (file_exists($cheminAbs)) {
            unlink($cheminAbs);
        }

        // Suppression en base
        $stmt = $this->db->prepare('DELETE FROM documents WHERE id = :id');
        $stmt->execute([':id' => $id]);

        $this->json(['success' => true, 'message' => 'Document supprimé.']);
    }

    // ----------------------------------------------------------------
    // GET /documents/view/{id}
    // ----------------------------------------------------------------
    public function serve(string $id): void
    {
        Auth::requireLogin();

        $id = (int) $id;
        if ($id <= 0) {
            http_response_code(400);
            exit('ID invalide.');
        }

        $stmt = $this->db->prepare('SELECT * FROM documents WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$doc) {
            http_response_code(404);
            exit('Document introuvable.');
        }

        // Normaliser les colonnes (compatibilité migration 001 vs 005)
        $doc['nom_original'] = $doc['nom_original'] ?? $doc['nom_fichier'] ?? $doc['nom_stockage'] ?? 'document';
        $doc['mime_type']    = $doc['mime_type']    ?? $doc['type_mime']  ?? 'application/octet-stream';

        // Construire le chemin absolu — chemin_fichier stocké en relatif (ex: uploads/documents/dossier_1/xxx.pdf)
        $cheminRelatif = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $doc['chemin_fichier']);
        $cheminAbs     = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $cheminRelatif;

        if (!file_exists($cheminAbs)) {
            http_response_code(404);
            exit('Fichier introuvable sur le serveur. Chemin: ' . htmlspecialchars($cheminAbs));
        }

        $mime = $doc['mime_type'] ?: 'application/octet-stream';

        // Inline pour PDF et images, attachment pour le reste
        $disposition = in_array($mime, self::TYPES_INLINE, true) ? 'inline' : 'attachment';

        // IMPORTANT : vider tous les buffers de sortie avant l'envoi binaire
        // (ob_start() est actif dans public/index.php)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Nom de fichier sécurisé pour l'en-tête Content-Disposition (RFC 6266)
        $nomSafe  = preg_replace('/[^\w\.\-]/', '_', $doc['nom_original']);
        $nomUtf8  = rawurlencode($doc['nom_original']);

        $filesize = filesize($cheminAbs);

        // Envoyer le fichier
        header('Content-Type: ' . $mime);
        header('Content-Disposition: ' . $disposition
            . '; filename="' . $nomSafe . '"'
            . '; filename*=UTF-8\'\'' . $nomUtf8);
        if ($filesize !== false) {
            header('Content-Length: ' . $filesize);
        }
        header('Cache-Control: private, max-age=3600');
        header('X-Content-Type-Options: nosniff');
        // Autoriser l'affichage dans une iframe (même origine)
        header('X-Frame-Options: SAMEORIGIN');
        // Supprimer tout en-tête Content-Security-Policy susceptible de bloquer les iframes
        header_remove('Content-Security-Policy');

        readfile($cheminAbs);
        exit;
    }

    // ----------------------------------------------------------------
    // GET /documents/list/{dossierId}
    // ----------------------------------------------------------------
    public function list(string $dossierId): void
    {
        Auth::requireLogin();

        $dossierId = (int) $dossierId;
        if ($dossierId <= 0) {
            $this->json(['success' => false, 'message' => 'Dossier invalide.'], 400);
        }

        $stmt = $this->db->prepare(
            'SELECT d.id,
                    COALESCE(d.nom_original, d.nom_fichier, d.nom_stockage) AS nom_original,
                    COALESCE(d.mime_type, d.type_mime, \'application/octet-stream\') AS mime_type,
                    d.taille_octets, d.description, d.created_at,
                    CONCAT(u.prenom, \' \', u.nom) AS uploaded_by_nom
             FROM documents d
             LEFT JOIN users u ON u.id = d.uploaded_by
             WHERE d.dossier_id = :dossier_id
               AND d.type_document = \'piece_jointe\'
             ORDER BY d.created_at DESC'
        );
        $stmt->execute([':dossier_id' => $dossierId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = array_map(function (array $row): array {
            return [
                'id'             => (int) $row['id'],
                'nom'            => $row['nom_original'],
                'type'           => $row['mime_type'] ?? 'application/octet-stream',
                'taille'         => $this->formatTaille((int) ($row['taille_octets'] ?? 0)),
                'taille_octets'  => (int) ($row['taille_octets'] ?? 0),
                'description'    => $row['description'],
                'date'           => $row['created_at'] ? date('d/m/Y H:i', strtotime($row['created_at'])) : '—',
                'uploaded_by'    => $row['uploaded_by_nom'],
                'url'            => BASE_URL . '/documents/view/' . $row['id'],
                'inline'         => in_array($row['mime_type'] ?? '', self::TYPES_INLINE, true),
            ];
        }, $rows);

        $this->json(['success' => true, 'data' => $result]);
    }

    // ----------------------------------------------------------------
    // Helpers privés
    // ----------------------------------------------------------------

    /**
     * Retourne le chemin absolu du dossier d'upload pour un dossier judiciaire.
     */
    private function getUploadDir(int $dossierId): string
    {
        return ROOT_PATH
            . DIRECTORY_SEPARATOR . 'public'
            . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR . 'documents'
            . DIRECTORY_SEPARATOR . 'dossier_' . $dossierId
            . DIRECTORY_SEPARATOR;
    }

    /**
     * Détecte le MIME type via finfo (si disponible) ou par extension.
     */
    private function detectMime(string $tmpPath, string $ext): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $tmpPath);
            finfo_close($finfo);
            if ($mime && $mime !== 'application/octet-stream') {
                return $mime;
            }
        }
        return self::TYPES_AUTORISES[$ext] ?? 'application/octet-stream';
    }

    /**
     * Formate une taille en octets en Ko ou Mo lisibles.
     */
    private function formatTaille(int $octets): string
    {
        if ($octets <= 0) {
            return '0 o';
        }
        if ($octets < 1024) {
            return $octets . ' o';
        }
        if ($octets < 1024 * 1024) {
            return round($octets / 1024, 1) . ' Ko';
        }
        return round($octets / (1024 * 1024), 2) . ' Mo';
    }
}
