<?php
// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    // Configure session settings for localhost development
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 0 for localhost (no HTTPS)
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Regenerate session ID to prevent fixation (only once per session)
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Flash message function with improved styling
function flash($name = '', $message = '', $class = 'bg-green-100 border border-green-400 text-green-700') {
    if (!empty($name)) {
        if (!empty($message) && empty($_SESSION[$name])) {
            $_SESSION[$name] = $message;
            $_SESSION[$name.'_class'] = $class;
        } elseif (empty($message) && !empty($_SESSION[$name])) {
            $class = !empty($_SESSION[$name.'_class']) ? $_SESSION[$name.'_class'] : '';
            echo '<div class="'.$class.' px-4 py-3 rounded relative mb-4" role="alert">'.$_SESSION[$name].'</div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name.'_class']);
        }
    }
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check user role - Fixed inconsistency
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Check user role - Fixed inconsistency
function isLecturer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'lecturer';
}

// Check if user is student
function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

// Get current user info
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['username'] ?? '',
        'role' => $_SESSION['role'] ?? 'student'
    ];
}

// Redirect if not logged in
function requireLogin($redirect_path = '../auth/login.php') {
    if (!isLoggedIn()) {
        $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . $redirect_path);
        exit;
    }
}

// Redirect if not admin
function requireAdmin($redirect_path = '../index.php') {
    requireLogin();
    if (!isAdmin()) {
        flash('error', 'Access denied. Admin privileges required.', 'bg-red-100 border border-red-400 text-red-700');
        header('Location: ' . $redirect_path);
        exit;
    }
}

// Redirect if not lecturer or admin
function requireLecturerOrAdmin($redirect_path = '../index.php') {
    requireLogin();
    if (!isLecturer() && !isAdmin()) {
        flash('error', 'Access denied. Lecturer or Admin privileges required.', 'bg-red-100 border border-red-400 text-red-700');
        header('Location: ' . $redirect_path);
        exit;
    }
}

// Redirect if not lecturer
function requireLecturer($redirect_path = '../index.php') {
    requireLogin();
    if (!isLecturer()) {
        flash('error', 'Access denied. Lecturer privileges required.', 'bg-red-100 border border-red-400 text-red-700');
        header('Location: ' . $redirect_path);
        exit;
    }
}

// Redirect if not student
function requireStudent($redirect_path = '../index.php') {
    requireLogin();
    if (!isStudent()) {
        flash('error', 'Access denied. Student privileges required.', 'bg-red-100 border border-red-400 text-red-700');
        header('Location: ' . $redirect_path);
        exit;
    }
}

// Logout function
function logout() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
?>
