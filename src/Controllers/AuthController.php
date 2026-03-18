<?php
/**
 * CineWeeknd - Auth Controller
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/UserModel.php';

class AuthController {

    public static function showLogin(): void {
        require __DIR__ . '/../Views/pages/login.php';
    }

    public static function showRegister(): void {
        require __DIR__ . '/../Views/pages/register.php';
    }

    public static function login(): void {
        if (!verifyCsrf($_POST[CSRF_TOKEN_NAME] ?? '')) {
            setFlash('error', 'Token de segurança inválido. Tente novamente.');
            redirect('/login');
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            setFlash('error', 'E-mail e senha são obrigatórios.');
            redirect('/login');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('error', 'Formato de e-mail inválido.');
            redirect('/login');
        }

        $user = UserModel::findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            setFlash('error', 'E-mail ou senha incorretos.');
            redirect('/login');
        }

        if (!$user['is_active']) {
            setFlash('error', 'Sua conta foi desativada. Contate o suporte.');
            redirect('/login');
        }

        // Regenerate session on login
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['is_admin']   = (bool) $user['is_admin'];

        setFlash('success', 'Bem-vindo de volta, ' . $user['name'] . '! 🎬');
        redirect($user['is_admin'] ? '/admin' : '/');
    }

    public static function register(): void {
        if (!verifyCsrf($_POST[CSRF_TOKEN_NAME] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            redirect('/register');
        }

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';
        $errors   = [];

        if (strlen($name) < 2 || strlen($name) > 100)
            $errors[] = 'O nome deve ter entre 2 e 100 caracteres.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors[] = 'Endereço de e-mail inválido.';
        if (strlen($password) < MIN_PASSWORD_LENGTH)
            $errors[] = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' caracteres.';
        if ($password !== $confirm)
            $errors[] = 'As senhas não conferem.';

        if (!empty($errors)) {
            setFlash('error', implode('<br>', $errors));
            redirect('/register');
        }

        if (UserModel::findByEmail($email)) {
            setFlash('error', 'Este e-mail já está cadastrado.');
            redirect('/register');
        }

        UserModel::create($name, $email, $password);
        setFlash('success', 'Conta criada com sucesso! Faça seu login. 🎉');
        redirect('/login');
    }

    public static function logout(): void {
        session_destroy();
        header("Location: " . APP_URL . "/login");
        exit;
    }
}
