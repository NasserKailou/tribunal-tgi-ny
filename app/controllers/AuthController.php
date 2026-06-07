<?php
class AuthController extends Controller {
    public function loginForm(): void {
        if (Auth::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        $flash = $this->getFlash();
        $this->view('auth/login', ['flash' => $flash], 'auth');
    }

    public function login(): void {
        CSRF::check();
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->flash('error', 'Veuillez remplir tous les champs.');
            $this->redirect('/login');
        }

        $stmt = $this->db->prepare(
            "SELECT u.*, r.code as role_code, r.libelle as role_lib 
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.email = :email AND u.actif = 1 
             LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $this->flash('error', 'Email ou mot de passe incorrect.');
            $this->redirect('/login');
        }

        Auth::login($user);

        // Récupérer l'URL d'intention et la nettoyer
        $intended = $_SESSION['intended_url'] ?? '';
        unset($_SESSION['intended_url']);

        // Si l'URL intended est vide ou pointe vers /login, rediriger vers /dashboard
        if (empty($intended) || strpos($intended, '/login') !== false) {
            $this->redirect('/dashboard');
            return;
        }

        // Extraire le chemin relatif depuis BASE_URL
        // Si intended contient déjà le préfixe du sous-dossier (REQUEST_URI brut),
        // on construit la redirection vers BASE_URL + chemin relatif propre
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
        if ($basePath && strpos($intended, $basePath) === 0) {
            // intended = /tribunal-tgi-ny/public/dashboard -> chemin = /dashboard
            $chemin = substr($intended, strlen($basePath)) ?: '/dashboard';
            $this->redirect($chemin);
        } else {
            // L'URL intended est déjà relative, on la redirige directement
            $this->redirect($intended ?: '/dashboard');
        }
    }

    public function logout(): void {
        Auth::logout();
        $this->redirect('/login');
    }
}
