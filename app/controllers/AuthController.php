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

        $intended = $_SESSION['intended_url'] ?? '/dashboard';
        unset($_SESSION['intended_url']);
        $this->redirect($intended);
    }

    public function logout(): void {
        Auth::logout();
        $this->redirect('/login');
    }
}
