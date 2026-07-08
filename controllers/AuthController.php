<?php
require_once BASE_PATH . 'models/User.php';

class AuthController {

    public function login() {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($password)) {
                $error = 'Inserisci username e password.';
            } else {
                $userModel = new User();
                $user = $userModel->findByUsername($username);

                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id']       = $user['id'];
                    $_SESSION['user_nome']     = $user['nome'] . ' ' . $user['cognome'];
                    $_SESSION['user_username'] = $user['username'];
                    $_SESSION['user_ruolo']    = $user['ruolo']; // admin, docente, studente
                    header('Location: ' . BASE_URL . 'switch');
                    exit;
                } else {
                    $error = 'Credenziali non valide.';
                }
            }
        }

        require BASE_PATH . 'views/auth/login.php';
    }

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . 'auth/login');
        exit;
    }
}
