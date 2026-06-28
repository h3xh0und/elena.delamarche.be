<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function isLoggedIn(): bool {
    // 'kind' was the old session key before the rename to 'user'
    if (!empty($_SESSION['kind']) && empty($_SESSION['user'])) {
        $_SESSION['user'] = $_SESSION['kind'];
        unset($_SESSION['kind']);
    }
    return !empty($_SESSION['user']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        header('Location: ' . $base . '/index.php');
        exit;
    }
}

function currentUser(): string {
    return $_SESSION['user'] ?? '';
}

function login(string $name): void {
    session_regenerate_id(true);
    $_SESSION['user'] = $name;
}

function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function csrfToken(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function checkCsrf(): bool {
    $token = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}
