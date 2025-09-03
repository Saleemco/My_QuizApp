<?php
// 🚨 IMPORTANT: Make sure no whitespace or HTML comes before this line

// Check if headers were already sent (debug helper)
if (headers_sent($file, $line)) {
    die("⚠️ Headers already sent in $file on line $line");
}

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if you switch to HTTPS
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Regenerate session ID once per session to prevent fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

/**
 * CSRF Helpers
 */
function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Auth Helpers
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isLecturer(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'lecturer';
}

function isStudent(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'       => $_SESSION['user_id'],
        'fullname' => $_SESSION['fullname'] ?? '',
        'role'     => $_SESSION['role'] ?? ''
    ];
}

/**
 * Flash messaging
 */
function flash(string $name, string $message = '', string $class = 'bg-green-100 border border-green-400 text-green-700') {
    if (!empty($name)) {
        if (!empty($message) && empty($_SESSION[$name])) {
            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class;
        } elseif (empty($message) && !empty($_SESSION[$name])) {
            $class = $_SESSION[$name . '_class'] ?? '';
            echo '<div class="' . $class . ' px-4 py-3 rounded relative mb-4" role="alert">' . $_SESSION[$name] . '</div>';
            unset($_SESSION[$name], $_SESSION[$name . '_class']);
        }
    }
}

/**
 * Route Guards
 */
function requireLogin(string $redirect_path = '../auth/login.php') {
    if (!isLoggedIn()) {
        $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'] ?? '';
        header('Location: ' . $redirect_path);
        exit;
    }
}

function requireAdmin(string $redirect_path = '../index.php') {
    requireLogin();
    if (!isAdmin()) {
        flash('error', 'Access denied. Admin privileges required.', 'bg-red-100 border border-red-400 text-red-700');
        header('Location: ' . $redirect_path);
        exit;
    }
}

function requireLecturerOrAdmin(string $redirect_path = '../index.php') {
    requireLogin();
    if (!isLecturer() && !isAdmin()) {
        flash('error', 'Access denied. Lecturer or Admin privileges required.', 'bg-red-100 border border-red-400 text-red-700');
        header('Location: ' . $redirect_path);
        exit;
    }
}

function requireLecturer(string $redirect_path = '../index.php') {
    requireLogin();
    if (!isLecturer()) {
        flash('error', 'Access denied. Lecturer privileges required.', 'bg-red-100 border border-red-400 text-red-700');
        header('Location: ' . $redirect_path);
        exit;
    }
}

function requireStudent(string $redirect_path = '../index.php') {
    requireLogin();
    if (!isStudent()) {
        flash('error', 'Access denied. Student privileges required.', 'bg-red-100 border border-red-400 text-red-700');
        header('Location: ' . $redirect_path);
        exit;
    }
}

/**
 * Logout
 */
function logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
