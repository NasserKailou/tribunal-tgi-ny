<?php
/**
 * CasierJudiciairController — Casier judiciaire (schéma v013)
 * Tables: casier_judiciaire_personnes, casier_judiciaire_condamnations
 */
class CasierJudiciairController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $user   = Auth::currentUser();
        $search = trim($_GET['q'] ?? '');
        $total  = 0;
        $personnes = [];

        if ($search) {
            $stmt = $this->db->prepare(
                "SELECT p.*,
                    (SELECT COUNT(*) FROM casier_judiciaire_condamnations c WHERE c.personne_id=p.id) AS nb_condamnations
                 FROM casier_judiciaire_personnes p
                 WHERE p.nom LIKE :q OR p.prenom LIKE :q OR p.nin LIKE :q
                 ORDER BY p.nom, p.prenom LIMIT 50"
            );
            $stmt->execute([':q' => "%{$search}%"]);
            $personnes = $stmt->fetchAll();
            $total     = count($personnes);
        }

        $flash = $this->getFlash();
        $this->view('casier_judiciaire/index', compact('personnes', 'total', 'search', 'flash', 'user'));
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $stmt = $this->db->prepare(
            "SELECT p.*,
                (SELECT COUNT(*) FROM casier_judiciaire_condamnations c WHERE c.personne_id=p.id) AS nb_condamnations
             FROM casier_judiciaire_personnes p WHERE p.id=?"
        );
        $stmt->execute([(int)$id]);
        $personne = $stmt->fetch();
        if (!$personne) { $this->redirect('/casier-judiciaire'); }

        $stmtC = $this->db->prepare(
            "SELECT c.*, d.numero_rg
             FROM casier_judiciaire_condamnations c
             LEFT JOIN dossiers d ON d.id=c.dossier_id
             WHERE c.personne_id=?
             ORDER BY c.date_condamnation DESC"
        );
        $stmtC->execute([(int)$id]);
        $condamnations = $stmtC->fetchAll();

        $user  = Auth::currentUser();
        $flash = $this->getFlash();
        $this->view('casier_judiciaire/show', compact('personne', 'condamnations', 'flash', 'user'));
    }

    public function ajouter(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'president']);
        CSRF::check();

        $nom    = $this->sanitize($_POST['nom'] ?? '');
        $prenom = $this->sanitize($_POST['prenom'] ?? '');
        $nin    = $this->sanitize($_POST['nin'] ?? '');

        if (empty($nom)) {
            $this->flash('error', 'Le nom est requis.');
            $this->redirect('/casier-judiciaire');
            return;
        }

        // Chercher ou créer la personne
        $stmt = $this->db->prepare("SELECT id FROM casier_judiciaire_personnes WHERE nom=? AND prenom=? LIMIT 1");
        $stmt->execute([$nom, $prenom]);
        $personneId = $stmt->fetchColumn();

        if (!$personneId) {
            $ins = $this->db->prepare(
                "INSERT INTO casier_judiciaire_personnes (nin,nom,prenom,date_naissance,lieu_naissance,nationalite,sexe)
                 VALUES (:nin,:nom,:prenom,:dn,:ln,:nat,:sexe)"
            );
            $ins->execute([
                ':nin'    => $nin ?: null,
                ':nom'    => $nom,
                ':prenom' => $prenom,
                ':dn'     => $_POST['date_naissance'] ?: null,
                ':ln'     => $this->sanitize($_POST['lieu_naissance'] ?? ''),
                ':nat'    => $this->sanitize($_POST['nationalite'] ?? 'Nigérienne'),
                ':sexe'   => $_POST['sexe'] ?? 'M',
            ]);
            $personneId = (int)$this->db->lastInsertId();
        }

        // Ajouter condamnation
        $insCond = $this->db->prepare(
            "INSERT INTO casier_judiciaire_condamnations
                (personne_id, dossier_id, jugement_id, date_condamnation,
                 juridiction, infraction, peine, observations, created_by)
             VALUES (:pid,:did,:jid,:date,:jur,:infr,:peine,:obs,:by)"
        );
        $insCond->execute([
            ':pid'   => $personneId,
            ':did'   => (int)($_POST['dossier_id'] ?? 0) ?: null,
            ':jid'   => (int)($_POST['jugement_id'] ?? 0) ?: null,
            ':date'  => $_POST['date_condamnation'] ?? date('Y-m-d'),
            ':jur'   => $this->sanitize($_POST['juridiction'] ?? 'TGI-HC Niamey'),
            ':infr'  => $this->sanitize($_POST['infraction'] ?? ''),
            ':peine' => $this->sanitize($_POST['peine'] ?? ''),
            ':obs'   => $this->sanitize($_POST['observations'] ?? ''),
            ':by'    => Auth::userId(),
        ]);

        $this->flash('success', 'Condamnation enregistrée au casier.');
        $this->redirect('/casier-judiciaire/show/' . $personneId);
    }
}
