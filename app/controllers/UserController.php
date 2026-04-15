<?php
class UserController extends Controller {
    public function index(): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','president']);
        $flash = $this->getFlash();
        $user  = Auth::currentUser();
        $users = $this->db->query(
            "SELECT u.*, r.libelle as role_lib FROM users u JOIN roles r ON u.role_id=r.id ORDER BY u.nom"
        )->fetchAll();
        $this->view('users/index', compact('users','flash','user'));
    }

    public function create(): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','president']);
        $user  = Auth::currentUser();
        $roles = $this->db->query("SELECT * FROM roles ORDER BY libelle")->fetchAll();
        $this->view('users/create', compact('roles','user'));
    }

    public function store(): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','president']);

        $email = trim($_POST['email'] ?? '');
        $check = $this->db->prepare("SELECT id FROM users WHERE email=?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $this->flash('error', 'Cet email est déjà utilisé.');
            $this->redirect('/users/create');
        }

        $pwd = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost'=>12]);
        $this->db->prepare(
            "INSERT INTO users (role_id,nom,prenom,email,password,telephone,matricule) VALUES (?,?,?,?,?,?,?)"
        )->execute([
            (int)$_POST['role_id'],
            $this->sanitize($_POST['nom']),
            $this->sanitize($_POST['prenom']),
            $email,
            $pwd,
            $this->sanitize($_POST['telephone']??''),
            $this->sanitize($_POST['matricule']??''),
        ]);
        $this->flash('success', 'Utilisateur créé avec succès.');
        $this->redirect('/users');
    }

    public function edit(string $id): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','president']);
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([(int)$id]);
        $editUser = $stmt->fetch();
        if (!$editUser) { $this->redirect('/users'); }
        $user  = Auth::currentUser();
        $roles = $this->db->query("SELECT * FROM roles ORDER BY libelle")->fetchAll();
        $this->view('users/edit', compact('editUser','roles','user'));
    }

    public function update(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','president']);
        $this->db->prepare("UPDATE users SET role_id=?,nom=?,prenom=?,telephone=?,matricule=? WHERE id=?")
            ->execute([
                (int)$_POST['role_id'],
                $this->sanitize($_POST['nom']),
                $this->sanitize($_POST['prenom']),
                $this->sanitize($_POST['telephone']??''),
                $this->sanitize($_POST['matricule']??''),
                (int)$id,
            ]);
        if (!empty($_POST['password'])) {
            $pwd = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost'=>12]);
            $this->db->prepare("UPDATE users SET password=? WHERE id=?")->execute([$pwd,(int)$id]);
        }
        $this->flash('success', 'Utilisateur mis à jour.');
        $this->redirect('/users');
    }

    public function toggle(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin']);
        $this->db->prepare("UPDATE users SET actif = !actif WHERE id=?")->execute([(int)$id]);
        $this->flash('success', 'Statut utilisateur modifié.');
        $this->redirect('/users');
    }
}
