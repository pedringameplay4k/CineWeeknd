<?php
/**
 * CineWeeknd - Application Configuration
 */

define('APP_NAME', 'CineWeeknd');
define('APP_VERSION', '2.0.0');
define('APP_URL', 'http://localhost/CineWeeknd/public');
define('APP_ROOT', dirname(__DIR__));
define('UPLOAD_DIR', APP_ROOT . '/public/assets/images/posters/');
define('UPLOAD_URL', APP_URL . '/assets/images/posters/');
define('DEFAULT_POSTER', APP_URL . '/assets/images/default-poster.jpg');

// Session config
define('SESSION_LIFETIME', 3600 * 24); // 24 hours
define('CSRF_TOKEN_NAME', '_csrf_token');

// Pagination
define('MOVIES_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 10);

// Password requirements
define('MIN_PASSWORD_LENGTH', 8);

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Start session securely
function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
        // Regenerate session ID periodically
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } elseif (time() - $_SESSION['created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}

// Generate or verify CSRF token
function csrfToken(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCsrf(string $token): bool {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function csrfField(): string {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars(csrfToken()) . '">';
}

// XSS prevention
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Redirect helper
function redirect(string $url): void {
    header("Location: " . APP_URL . $url);
    exit;
}

// Flash messages
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Auth check
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        // Se for AJAX, retorna JSON em vez de redirecionar
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
        if ($isAjax) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'login_required', 'message' => 'Faça login para continuar.', 'redirect' => APP_URL . '/login']);
            exit;
        }
        setFlash('warning', 'É necessário fazer login.');
        redirect('/login');
    }
}

function requireAdmin(): void {
    requireLogin();
    if (empty($_SESSION['is_admin'])) {
        setFlash('error', 'Acesso negado.');
        redirect('/');
    }
}

// TMDB API
define('TMDB_API_KEY', '0a66e89e356fb9c0adf89ef04469c66e');
