<?php
/**
 * ConfigController — Module de configuration
 * Accès : admin / procureur uniquement
 * PDO nommé UNIQUEMENT, PHP 8+
 */
class ConfigController extends Controller
{
    // ─── Vérification d'accès centralisée ────────────────────────────────────
    private function requireConfig(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'procureur']);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // DASHBOARD
    // ═══════════════════════════════════════════════════════════════════════════
    public function index(): void
    {
        $this->requireConfig();
        $flash = $this->getFlash();
        $user  = Auth::currentUser();

        $stats = [
            'cabinets'          => (int)$this->db->query("SELECT COUNT(*) FROM cabinets_instruction")->fetchColumn(),
            'primo_intervenants'=> (int)$this->db->query("SELECT COUNT(*) FROM primo_intervenants")->fetchColumn(),
            'unites_enquete'    => (int)$this->db->query("SELECT COUNT(*) FROM unites_enquete")->fetchColumn(),
            'substituts'        => (int)$this->db->query("SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id=r.id WHERE r.code='substitut_procureur' AND u.actif=1")->fetchColumn(),
            'infractions'       => (int)$this->db->query("SELECT COUNT(*) FROM infractions")->fetchColumn(),
            'maisons_arret'     => (int)$this->db->query("SELECT COUNT(*) FROM maisons_arret")->fetchColumn(),
            'salles_audience'   => (int)$this->db->query("SELECT COUNT(*) FROM salles_audience")->fetchColumn(),
            'membres_audience'  => (int)$this->db->query("SELECT COUNT(DISTINCT CONCAT(role_audience)) FROM membres_audience")->fetchColumn(),
        ];

        $pageTitle = 'Configuration';
        $this->view('config/index', compact('flash', 'user', 'stats', 'pageTitle'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 1. CABINETS D'INSTRUCTION
    // ═══════════════════════════════════════════════════════════════════════════
    public function cabinets(): void
    {
        $this->requireConfig();
        $flash = $this->getFlash();
        $user  = Auth::currentUser();
        $page  = max(1, (int)($_GET['page'] ?? 1));

        $stmt = $this->db->prepare(
            "SELECT c.*, CONCAT(u.prenom,' ',u.nom) AS juge_nom
             FROM cabinets_instruction c
             LEFT JOIN users u ON u.id = c.juge_id
             ORDER BY c.numero
             LIMIT :limit OFFSET :offset"
        );
        $perPage = 20;
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();
        $cabinets = $stmt->fetchAll();

        $total = (int)$this->db->query("SELECT COUNT(*) FROM cabinets_instruction")->fetchColumn();
        $totalPages = (int)ceil($total / $perPage);

        $juges = $this->db->query(
            "SELECT u.id, CONCAT(u.prenom,' ',u.nom) AS nom
             FROM users u JOIN roles r ON u.role_id=r.id
             WHERE r.code IN ('juge_instruction','president') AND u.actif=1
             ORDER BY u.nom"
        )->fetchAll();

        $pageTitle = 'Cabinets d\'instruction';
        $this->view('config/cabinets', compact('flash','user','cabinets','juges','page','totalPages','pageTitle'));
    }

    public function cabinetStore(): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare(
            "INSERT INTO cabinets_instruction (numero, libelle, juge_id, actif)
             VALUES (:numero, :libelle, :juge_id, 1)"
        )->execute([
            ':numero'  => trim($_POST['numero'] ?? ''),
            ':libelle' => trim($_POST['libelle'] ?? ''),
            ':juge_id' => !empty($_POST['juge_id']) ? (int)$_POST['juge_id'] : null,
        ]);

        $this->flash('success', 'Cabinet créé avec succès.');
        $this->redirect('/config/cabinets');
    }

    public function cabinetUpdate(string $id): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare(
            "UPDATE cabinets_instruction
             SET numero=:numero, libelle=:libelle, juge_id=:juge_id
             WHERE id=:id"
        )->execute([
            ':numero'  => trim($_POST['numero'] ?? ''),
            ':libelle' => trim($_POST['libelle'] ?? ''),
            ':juge_id' => !empty($_POST['juge_id']) ? (int)$_POST['juge_id'] : null,
            ':id'      => (int)$id,
        ]);

        $this->flash('success', 'Cabinet mis à jour.');
        $this->redirect('/config/cabinets');
    }

    public function cabinetDelete(string $id): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare("DELETE FROM cabinets_instruction WHERE id=:id")
                 ->execute([':id' => (int)$id]);

        $this->flash('success', 'Cabinet supprimé.');
        $this->redirect('/config/cabinets');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 2. PRIMO INTERVENANTS
    // ═══════════════════════════════════════════════════════════════════════════
    public function primoIntervenants(): void
    {
        $this->requireConfig();
        $flash = $this->getFlash();
        $user  = Auth::currentUser();
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        $stmt = $this->db->prepare(
            "SELECT * FROM primo_intervenants ORDER BY nom LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();
        $primoIntervenants = $stmt->fetchAll();

        $total = (int)$this->db->query("SELECT COUNT(*) FROM primo_intervenants")->fetchColumn();
        $totalPages = (int)ceil($total / $perPage);

        $pageTitle = 'Primo intervenants';
        $this->view('config/primo_intervenants', compact('flash','user','primoIntervenants','page','totalPages','pageTitle'));
    }

    public function primoIntervenantStore(): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare(
            "INSERT INTO primo_intervenants (nom, type, description, actif)
             VALUES (:nom, :type, :description, 1)"
        )->execute([
            ':nom'         => trim($_POST['nom'] ?? ''),
            ':type'        => trim($_POST['type'] ?? ''),
            ':description' => trim($_POST['description'] ?? ''),
        ]);

        $this->flash('success', 'Primo intervenant créé.');
        $this->redirect('/config/primo-intervenants');
    }

    public function primoIntervenantUpdate(string $id): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare(
            "UPDATE primo_intervenants SET nom=:nom, type=:type, description=:description WHERE id=:id"
        )->execute([
            ':nom'         => trim($_POST['nom'] ?? ''),
            ':type'        => trim($_POST['type'] ?? ''),
            ':description' => trim($_POST['description'] ?? ''),
            ':id'          => (int)$id,
        ]);

        $this->flash('success', 'Primo intervenant mis à jour.');
        $this->redirect('/config/primo-intervenants');
    }

    public function primoIntervenantDelete(string $id): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare("DELETE FROM primo_intervenants WHERE id=:id")
                 ->execute([':id' => (int)$id]);

        $this->flash('success', 'Primo intervenant supprimé.');
        $this->redirect('/config/primo-intervenants');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 3. UNITÉS D'ENQUÊTE
    // ═══════════════════════════════════════════════════════════════════════════
    public function unitesEnquete(): void
    {
        $this->requireConfig();
        $flash = $this->getFlash();
        $user  = Auth::currentUser();
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        $stmt = $this->db->prepare(
            "SELECT * FROM unites_enquete ORDER BY nom LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();
        $unitesEnquete = $stmt->fetchAll();

        $total = (int)$this->db->query("SELECT COUNT(*) FROM unites_enquete")->fetchColumn();
        $totalPages = (int)ceil($total / $perPage);

        $pageTitle = 'Unités d\'enquête';
        $this->view('config/unites_enquete', compact('flash','user','unitesEnquete','page','totalPages','pageTitle'));
    }

    public function uniteEnqueteStore(): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare(
            "INSERT INTO unites_enquete (nom, type, telephone, actif)
             VALUES (:nom, :type, :telephone, 1)"
        )->execute([
            ':nom'       => trim($_POST['nom'] ?? ''),
            ':type'      => trim($_POST['type'] ?? ''),
            ':telephone' => trim($_POST['contact'] ?? ''),
        ]);

        $this->flash('success', 'Unité d\'enquête créée.');
        $this->redirect('/config/unites-enquete');
    }

    public function uniteEnqueteUpdate(string $id): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare(
            "UPDATE unites_enquete SET nom=:nom, type=:type, telephone=:telephone WHERE id=:id"
        )->execute([
            ':nom'       => trim($_POST['nom'] ?? ''),
            ':type'      => trim($_POST['type'] ?? ''),
            ':telephone' => trim($_POST['contact'] ?? ''),
            ':id'        => (int)$id,
        ]);

        $this->flash('success', 'Unité d\'enquête mise à jour.');
        $this->redirect('/config/unites-enquete');
    }

    public function uniteEnqueteDelete(string $id): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare("DELETE FROM unites_enquete WHERE id=:id")
                 ->execute([':id' => (int)$id]);

        $this->flash('success', 'Unité d\'enquête supprimée.');
        $this->redirect('/config/unites-enquete');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 4. SUBSTITUTS DU PROCUREUR (users filtrés role=substitut_procureur)
    // ═══════════════════════════════════════════════════════════════════════════
    public function substituts(): void
    {
        $this->requireConfig();
        $flash = $this->getFlash();
        $user  = Auth::currentUser();
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        $stmt = $this->db->prepare(
            "SELECT u.*, r.libelle AS role_lib
             FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE r.code = 'substitut_procureur'
             ORDER BY u.nom
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();
        $substituts = $stmt->fetchAll();

        $total = (int)$this->db->query(
            "SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id=r.id WHERE r.code='substitut_procureur'"
        )->fetchColumn();
        $totalPages = (int)ceil($total / $perPage);

        // Rôle substitut pour le select du formulaire
        $roleSubstitut = $this->db->query(
            "SELECT id FROM roles WHERE code='substitut_procureur' LIMIT 1"
        )->fetch();

        $pageTitle = 'Substituts du procureur';
        $this->view('config/substituts', compact('flash','user','substituts','page','totalPages','roleSubstitut','pageTitle'));
    }

    public function substitutStore(): void
    {
        $this->requireConfig();
        CSRF::check();

        $roleSubstitut = $this->db->query(
            "SELECT id FROM roles WHERE code='substitut_procureur' LIMIT 1"
        )->fetch();

        if (!$roleSubstitut) {
            $this->flash('error', 'Rôle substitut_procureur introuvable.');
            $this->redirect('/config/substituts');
        }

        $email = trim($_POST['email'] ?? '');
        $check = $this->db->prepare("SELECT id FROM users WHERE email=:email");
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            $this->flash('error', 'Cet email est déjà utilisé.');
            $this->redirect('/config/substituts');
        }

        $pwd = password_hash($_POST['password'] ?? 'changeme123', PASSWORD_BCRYPT, ['cost' => 12]);
        $this->db->prepare(
            "INSERT INTO users (role_id, nom, prenom, email, password, telephone, matricule, actif)
             VALUES (:role_id, :nom, :prenom, :email, :password, :telephone, :matricule, 1)"
        )->execute([
            ':role_id'   => (int)$roleSubstitut['id'],
            ':nom'       => trim($_POST['nom'] ?? ''),
            ':prenom'    => trim($_POST['prenom'] ?? ''),
            ':email'     => $email,
            ':password'  => $pwd,
            ':telephone' => trim($_POST['telephone'] ?? ''),
            ':matricule' => trim($_POST['matricule'] ?? ''),
        ]);

        $this->flash('success', 'Substitut créé avec succès.');
        $this->redirect('/config/substituts');
    }

    public function substitutUpdate(string $id): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare(
            "UPDATE users SET nom=:nom, prenom=:prenom, telephone=:telephone, matricule=:matricule WHERE id=:id"
        )->execute([
            ':nom'       => trim($_POST['nom'] ?? ''),
            ':prenom'    => trim($_POST['prenom'] ?? ''),
            ':telephone' => trim($_POST['telephone'] ?? ''),
            ':matricule' => trim($_POST['matricule'] ?? ''),
            ':id'        => (int)$id,
        ]);

        if (!empty($_POST['password'])) {
            $pwd = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $this->db->prepare("UPDATE users SET password=:pwd WHERE id=:id")
                     ->execute([':pwd' => $pwd, ':id' => (int)$id]);
        }

        $this->flash('success', 'Substitut mis à jour.');
        $this->redirect('/config/substituts');
    }

    public function substitutDelete(string $id): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare("DELETE FROM users WHERE id=:id")->execute([':id' => (int)$id]);
        $this->flash('success', 'Substitut supprimé.');
        $this->redirect('/config/substituts');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 5. INFRACTIONS
    // ═══════════════════════════════════════════════════════════════════════════
    public function infractions(): void
    {
        $this->requireConfig();
        $flash = $this->getFlash();
        $user  = Auth::currentUser();
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        $stmt = $this->db->prepare(
            "SELECT * FROM infractions ORDER BY categorie, code LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();
        $infractions = $stmt->fetchAll();

        $total = (int)$this->db->query("SELECT COUNT(*) FROM infractions")->fetchColumn();
        $totalPages = (int)ceil($total / $perPage);

        $pageTitle = 'Infractions';
        $this->view('config/infractions', compact('flash','user','infractions','page','totalPages','pageTitle'));
    }

    public function infractionStore(): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare(
            "INSERT INTO infractions (code, libelle, categorie, peine_min_mois, peine_max_mois)
             VALUES (:code, :libelle, :categorie, :peine_min, :peine_max)"
        )->execute([
            ':code'      => strtoupper(trim($_POST['code'] ?? '')),
            ':libelle'   => trim($_POST['libelle'] ?? ''),
            ':categorie' => $_POST['categorie'] ?? 'correctionnelle',
            ':peine_min' => !empty($_POST['peine_min_mois']) ? (int)$_POST['peine_min_mois'] : null,
            ':peine_max' => !empty($_POST['peine_max_mois']) ? (int)$_POST['peine_max_mois'] : null,
        ]);

        $this->flash('success', 'Infraction créée.');
        $this->redirect('/config/infractions');
    }

    public function infractionUpdate(string $id): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare(
            "UPDATE infractions SET code=:code, libelle=:libelle, categorie=:categorie,
             peine_min_mois=:peine_min, peine_max_mois=:peine_max WHERE id=:id"
        )->execute([
            ':code'      => strtoupper(trim($_POST['code'] ?? '')),
            ':libelle'   => trim($_POST['libelle'] ?? ''),
            ':categorie' => $_POST['categorie'] ?? 'correctionnelle',
            ':peine_min' => !empty($_POST['peine_min_mois']) ? (int)$_POST['peine_min_mois'] : null,
            ':peine_max' => !empty($_POST['peine_max_mois']) ? (int)$_POST['peine_max_mois'] : null,
            ':id'        => (int)$id,
        ]);

        $this->flash('success', 'Infraction mise à jour.');
        $this->redirect('/config/infractions');
    }

    public function infractionDelete(string $id): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare("DELETE FROM infractions WHERE id=:id")->execute([':id' => (int)$id]);
        $this->flash('success', 'Infraction supprimée.');
        $this->redirect('/config/infractions');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 6. MAISONS D'ARRÊT
    // ═══════════════════════════════════════════════════════════════════════════
    public function maisonsArret(): void
    {
        $this->requireConfig();
        $flash = $this->getFlash();
        $user  = Auth::currentUser();
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        $stmt = $this->db->prepare(
            "SELECT m.*, r.nom AS region_nom
             FROM maisons_arret m
             LEFT JOIN regions r ON r.id = m.region_id
             ORDER BY m.nom LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();
        $maisonsArret = $stmt->fetchAll();

        $total = (int)$this->db->query("SELECT COUNT(*) FROM maisons_arret")->fetchColumn();
        $totalPages = (int)ceil($total / $perPage);

        $regions = $this->db->query("SELECT id, nom FROM regions ORDER BY nom")->fetchAll();

        $pageTitle = 'Maisons d\'arrêt';
        $this->view('config/maisons_arret', compact('flash','user','maisonsArret','regions','page','totalPages','pageTitle'));
    }

    public function maisonArretStore(): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare(
            "INSERT INTO maisons_arret (nom, ville, region_id, capacite, population_actuelle, directeur, telephone, adresse, actif)
             VALUES (:nom, :ville, :region_id, :capacite, :pop, :directeur, :telephone, :adresse, 1)"
        )->execute([
            ':nom'       => trim($_POST['nom'] ?? ''),
            ':ville'     => trim($_POST['ville'] ?? ''),
            ':region_id' => !empty($_POST['region_id']) ? (int)$_POST['region_id'] : null,
            ':capacite'  => (int)($_POST['capacite'] ?? 0),
            ':pop'       => (int)($_POST['population_actuelle'] ?? 0),
            ':directeur' => trim($_POST['directeur'] ?? ''),
            ':telephone' => trim($_POST['telephone'] ?? ''),
            ':adresse'   => trim($_POST['adresse'] ?? ''),
        ]);

        $this->flash('success', 'Maison d\'arrêt créée.');
        $this->redirect('/config/maisons-arret');
    }

    public function maisonArretUpdate(string $id): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare(
            "UPDATE maisons_arret SET nom=:nom, ville=:ville, region_id=:region_id, capacite=:capacite,
             population_actuelle=:pop, directeur=:directeur, telephone=:telephone, adresse=:adresse WHERE id=:id"
        )->execute([
            ':nom'       => trim($_POST['nom'] ?? ''),
            ':ville'     => trim($_POST['ville'] ?? ''),
            ':region_id' => !empty($_POST['region_id']) ? (int)$_POST['region_id'] : null,
            ':capacite'  => (int)($_POST['capacite'] ?? 0),
            ':pop'       => (int)($_POST['population_actuelle'] ?? 0),
            ':directeur' => trim($_POST['directeur'] ?? ''),
            ':telephone' => trim($_POST['telephone'] ?? ''),
            ':adresse'   => trim($_POST['adresse'] ?? ''),
            ':id'        => (int)$id,
        ]);

        $this->flash('success', 'Maison d\'arrêt mise à jour.');
        $this->redirect('/config/maisons-arret');
    }

    public function maisonArretDelete(string $id): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare("DELETE FROM maisons_arret WHERE id=:id")->execute([':id' => (int)$id]);
        $this->flash('success', 'Maison d\'arrêt supprimée.');
        $this->redirect('/config/maisons-arret');
    }

    // ─── STATISTIQUES POPULATION CARCÉRALE PAR SEXE ─────────────────────────
    public function maisonArretStats(string $id): void
    {
        $this->requireConfig();
        $id = (int)$id;

        $maison = $this->db->prepare("SELECT * FROM maisons_arret WHERE id=?")->execute([$id])
            ? $this->db->prepare("SELECT * FROM maisons_arret WHERE id=?")
            : null;
        $stmtM = $this->db->prepare("SELECT * FROM maisons_arret WHERE id=?");
        $stmtM->execute([$id]);
        $maison = $stmtM->fetch();
        if (!$maison) {
            $this->flash('error', 'Maison d\'arrêt introuvable.');
            $this->redirect('/config/maisons-arret');
        }

        // Stats par sexe
        $stmtS = $this->db->prepare(
            "SELECT sexe, COUNT(*) AS nb
             FROM detenus
             WHERE maison_arret_id=? AND statut='incarcere'
             GROUP BY sexe"
        );
        $stmtS->execute([$id]);
        $statsParSexe = $stmtS->fetchAll();

        // Stats par type de détention et sexe
        $stmtT = $this->db->prepare(
            "SELECT type_detention, sexe, COUNT(*) AS nb
             FROM detenus
             WHERE maison_arret_id=? AND statut='incarcere'
             GROUP BY type_detention, sexe
             ORDER BY type_detention, sexe"
        );
        $stmtT->execute([$id]);
        $statsParType = $stmtT->fetchAll();

        $total = array_sum(array_column($statsParSexe, 'nb'));

        $flash = $this->getFlash();
        $user  = Auth::currentUser();
        $pageTitle = 'Population — ' . $maison['nom'];
        $this->view('config/maison_arret_stats',
            compact('maison','statsParSexe','statsParType','total','flash','user','pageTitle'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 7. SALLES D'AUDIENCE
    // ═══════════════════════════════════════════════════════════════════════════
    public function sallesAudience(): void
    {
        $this->requireConfig();
        $flash = $this->getFlash();
        $user  = Auth::currentUser();
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        $stmt = $this->db->prepare(
            "SELECT * FROM salles_audience ORDER BY nom LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();
        $salles = $stmt->fetchAll();

        $total = (int)$this->db->query("SELECT COUNT(*) FROM salles_audience")->fetchColumn();
        $totalPages = (int)ceil($total / $perPage);

        $pageTitle = 'Salles d\'audience';
        $this->view('config/salles_audience', compact('flash','user','salles','page','totalPages','pageTitle'));
    }

    public function salleAudienceStore(): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare(
            "INSERT INTO salles_audience (nom, capacite, description, actif)
             VALUES (:nom, :capacite, :description, 1)"
        )->execute([
            ':nom'         => trim($_POST['nom'] ?? ''),
            ':capacite'    => (int)($_POST['capacite'] ?? 50),
            ':description' => trim($_POST['equipements'] ?? ''),
        ]);

        $this->flash('success', 'Salle d\'audience créée.');
        $this->redirect('/config/salles-audience');
    }

    public function salleAudienceUpdate(string $id): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare(
            "UPDATE salles_audience SET nom=:nom, capacite=:capacite, description=:description WHERE id=:id"
        )->execute([
            ':nom'         => trim($_POST['nom'] ?? ''),
            ':capacite'    => (int)($_POST['capacite'] ?? 50),
            ':description' => trim($_POST['equipements'] ?? ''),
            ':id'          => (int)$id,
        ]);

        $this->flash('success', 'Salle d\'audience mise à jour.');
        $this->redirect('/config/salles-audience');
    }

    public function salleAudienceDelete(string $id): void
    {
        $this->requireConfig();
        CSRF::check();

        $this->db->prepare("DELETE FROM salles_audience WHERE id=:id")->execute([':id' => (int)$id]);
        $this->flash('success', 'Salle d\'audience supprimée.');
        $this->redirect('/config/salles-audience');
    }

    // ─── CRUD Membres d'audience par défaut ─────────────────────────────────
    // (liste des rôles types disponibles pour configurer une audience)

    public function membresAudience(): void {
        $this->requireConfig();
        $roles = [
            'president'           => 'Président',
            'greffier'            => 'Greffier',
            'assesseur_1'         => 'Assesseur N°1',
            'assesseur_2'         => 'Assesseur N°2',
            'jure_1'              => 'Juré N°1',
            'jure_2'              => 'Juré N°2',
            'procureur'           => 'Représentant du Parquet',
            'substitut'           => 'Substitut',
            'juge_assesseur'      => 'Juge assesseur',
            'avocat_defense'      => 'Avocat de la défense',
            'avocat_partie_civile'=> 'Avocat de la partie civile',
            'greffier_adjoint'    => 'Greffier adjoint',
            'autre'               => 'Autre membre',
        ];

        // Membres fréquents (utilisateurs avec rôles judiciaires)
        $juges     = $this->db->query("SELECT u.*, r.code as role_code FROM users u JOIN roles r ON u.role_id=r.id WHERE r.code IN ('president','juge_siege','vice_president') AND u.actif=1 ORDER BY u.nom")->fetchAll();
        $greffiers = $this->db->query("SELECT u.* FROM users u JOIN roles r ON u.role_id=r.id WHERE r.code='greffier' AND u.actif=1 ORDER BY u.nom")->fetchAll();
        $parquet   = $this->db->query("SELECT u.*, r.code as role_code FROM users u JOIN roles r ON u.role_id=r.id WHERE r.code IN ('procureur','substitut_procureur') AND u.actif=1 ORDER BY r.id, u.prenom")->fetchAll();

        // Stats : audiences récentes avec composition
        $stats = $this->db->query("
            SELECT role_audience, COUNT(*) as nb
            FROM membres_audience
            GROUP BY role_audience ORDER BY nb DESC
        ")->fetchAll();

        $flash = $this->getFlash();
        $user  = Auth::currentUser();
        $this->view('config/membres-audience', compact('roles','juges','greffiers','parquet','stats','flash','user'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // DOSSIERS & PV ASSIGNÉS PAR CABINET
    // ═══════════════════════════════════════════════════════════════════════════
    public function cabinetDossiers(string $id): void
    {
        $this->requireConfig();
        $id = (int)$id;

        $stmtC = $this->db->prepare("SELECT c.*, CONCAT(u.prenom,' ',u.nom) AS juge_nom FROM cabinets_instruction c LEFT JOIN users u ON u.id=c.juge_id WHERE c.id=?");
        $stmtC->execute([$id]);
        $cabinet = $stmtC->fetch();
        if (!$cabinet) { $this->flash('error','Cabinet introuvable.'); $this->redirect('/config/cabinets'); }

        $statut = $_GET['statut'] ?? '';
        $where  = "d.cabinet_id = ?";
        $params = [$id];
        if ($statut) { $where .= " AND d.statut = ?"; $params[] = $statut; }

        $dossiers = $this->db->prepare(
            "SELECT d.*, us.nom AS sub_nom, us.prenom AS sub_prenom
             FROM dossiers d
             LEFT JOIN users us ON d.substitut_id = us.id
             WHERE $where
             ORDER BY d.created_at DESC"
        );
        $dossiers->execute($params);
        $dossiers = $dossiers->fetchAll();

        // PVs transférés vers ce cabinet (via les dossiers)
        $pvs = $this->db->prepare(
            "SELECT p.*, ue.nom AS unite_nom
             FROM pv p
             JOIN dossiers d ON p.id = d.pv_id
             LEFT JOIN unites_enquete ue ON p.unite_enquete_id = ue.id
             WHERE d.cabinet_id = ?
             ORDER BY p.date_reception DESC"
        );
        $pvs->execute([$id]);
        $pvs = $pvs->fetchAll();

        $flash = $this->getFlash();
        $user  = Auth::currentUser();
        $pageTitle = 'Dossiers — Cabinet ' . $cabinet['numero'];
        $this->view('config/cabinet_dossiers', compact('cabinet','dossiers','pvs','flash','user','pageTitle','statut'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // DOSSIERS & PV ASSIGNÉS PAR SUBSTITUT
    // ═══════════════════════════════════════════════════════════════════════════
    public function substitutDossiers(string $id): void
    {
        $this->requireConfig();
        $id = (int)$id;

        $stmtS = $this->db->prepare("SELECT u.*, r.libelle AS role_lib FROM users u JOIN roles r ON u.role_id=r.id WHERE u.id=? AND r.code='substitut_procureur'");
        $stmtS->execute([$id]);
        $substitut = $stmtS->fetch();
        if (!$substitut) { $this->flash('error','Substitut introuvable.'); $this->redirect('/config/substituts'); }

        $dossiers = $this->db->prepare(
            "SELECT d.*, ci.numero AS cabinet_num, ci.libelle AS cabinet_lib
             FROM dossiers d
             LEFT JOIN cabinets_instruction ci ON d.cabinet_id = ci.id
             WHERE d.substitut_id = ?
             ORDER BY d.created_at DESC"
        );
        $dossiers->execute([$id]);
        $dossiers = $dossiers->fetchAll();

        $pvs = $this->db->prepare(
            "SELECT p.*, ue.nom AS unite_nom
             FROM pv p
             LEFT JOIN unites_enquete ue ON p.unite_enquete_id = ue.id
             WHERE p.substitut_id = ?
             ORDER BY p.date_reception DESC"
        );
        $pvs->execute([$id]);
        $pvs = $pvs->fetchAll();

        $flash = $this->getFlash();
        $user  = Auth::currentUser();
        $pageTitle = 'Dossiers — ' . $substitut['prenom'] . ' ' . $substitut['nom'];
        $this->view('config/substitut_dossiers', compact('substitut','dossiers','pvs','flash','user','pageTitle'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // API : CABINETS TRIÉS PAR CHARGE (pour transfert intelligent)
    // ═══════════════════════════════════════════════════════════════════════════
    public function apiCabinetsCharge(): void
    {
        Auth::requireLogin();
        $cabinets = $this->db->query(
            "SELECT c.id, c.numero, c.libelle,
                    CONCAT(IFNULL(u.prenom,''),' ',IFNULL(u.nom,'')) AS juge_nom,
                    (SELECT COUNT(*) FROM dossiers d WHERE d.cabinet_id=c.id AND d.statut NOT IN ('juge','classe')) AS nb_dossiers,
                    (SELECT COUNT(*) FROM pv p JOIN dossiers d2 ON p.id=d2.pv_id WHERE d2.cabinet_id=c.id AND p.statut='transfere_instruction') AS nb_pvs
             FROM cabinets_instruction c
             LEFT JOIN users u ON u.id = c.juge_id
             WHERE c.actif = 1
             ORDER BY nb_dossiers ASC, nb_pvs ASC"
        )->fetchAll();
        $this->json(['success' => true, 'data' => $cabinets]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // API : SUBSTITUTS TRIÉS PAR CHARGE
    // ═══════════════════════════════════════════════════════════════════════════
    public function apiSubstitutsCharge(): void
    {
        Auth::requireLogin();
        $substituts = $this->db->query(
            "SELECT u.id, u.nom, u.prenom,
                    (SELECT COUNT(*) FROM dossiers d WHERE d.substitut_id=u.id AND d.statut NOT IN ('juge','classe')) AS nb_dossiers,
                    (SELECT COUNT(*) FROM pv p WHERE p.substitut_id=u.id AND p.statut='en_traitement') AS nb_pvs
             FROM users u
             JOIN roles r ON u.role_id=r.id
             WHERE r.code='substitut_procureur' AND u.actif=1
             ORDER BY nb_dossiers ASC, nb_pvs ASC"
        )->fetchAll();
        $this->json(['success' => true, 'data' => $substituts]);
    }

}
