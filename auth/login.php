<?php
// Debugging: show all errors (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';

    generateCSRFToken();

$errors = [];
$identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $errors['general'] = 'Invalid request. Please try again.';
    } else {
        $identifier = trim($_POST['identifier']);
        $password = $_POST['password'];

        if (empty($identifier)) {
            $errors['identifier'] = 'Email or Matric Number is required';
        }
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }

        if (empty($errors)) {
            $user = null;

            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                // Login by email
                $stmt = $conn->prepare("SELECT id, fullname, email, matric_number, password, role 
                                        FROM users WHERE email = ?");
                $stmt->bind_param("s", $identifier);
            } else {
                // Login by matric number (students only)
                $stmt = $conn->prepare("SELECT id, fullname, email, matric_number, password, role 
                                        FROM users WHERE matric_number = ? AND role = 'student'");
                $stmt->bind_param("s", $identifier);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
            }
            $stmt->close();

            // if ($user && password_verify($password, $user['password'])) {
            //     session_regenerate_id(true);

            //     $_SESSION['user_id'] = $user['id'];
            //     $_SESSION['username'] = $user['fullname'];
            //     $_SESSION['role'] = $user['role'];
            //     $_SESSION['email'] = $user['email'];
            //     $_SESSION['matric_number'] = $user['matric_number'];

            //     generateCSRFToken();

            //     // Redirect by role
            //     if ($user['role'] === 'admin') {
            //         header('Location: ../admin/dashboard.php');
            //     } elseif ($user['role'] === 'lecturer') {
            //         header('Location: ../lecturer/dashboard.php');
            //     } else {
            //         header('Location: ../student/dashboard.php');
            //     }
            //     exit;
            // } else {
            //     $errors['general'] = 'Invalid email/matric number or password';
            // }
            if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['fullname'] = $user['fullname']; // ✅ fixed
    $_SESSION['role'] = $user['role'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['matric_number'] = $user['matric_number'];

    generateCSRFToken();

    // Redirect by role
    if ($user['role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
    } elseif ($user['role'] === 'lecturer') {
        header('Location: ../lecturer/dashboard.php');
    } else {
        header('Location: ../student/dashboard.php');
    }
    exit;
}

        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QuizMaster Pro</title>
    <link rel="stylesheet" href="../src/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-r from-indigo-500 to-purple-600 font-['Inter']">

    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Login</h1>
            <p class="text-gray-600">Welcome back! Please log in.</p>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="bg-red-100 text-red-700 p-3 mb-4 rounded-lg text-sm">
                <?= htmlspecialchars($errors['general']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <!-- Email / Matric -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Email or Matric Number</label>
                <input type="text" name="identifier" value="<?= htmlspecialchars($identifier) ?>"
                       class="w-full px-4 py-3 rounded-lg border focus:ring-2 focus:ring-indigo-400 focus:outline-none <?= isset($errors['identifier']) ? 'border-red-500' : 'border-gray-300' ?>">
                <?php if (!empty($errors['identifier'])): ?>
                    <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['identifier']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Password -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Password</label>
                <input type="password" name="password"
                       class="w-full px-4 py-3 rounded-lg border focus:ring-2 focus:ring-indigo-400 focus:outline-none <?= isset($errors['password']) ? 'border-red-500' : 'border-gray-300' ?>">
                <?php if (!empty($errors['password'])): ?>
                    <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['password']) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-lg font-semibold hover:from-indigo-700 hover:to-purple-700 transition transform hover:scale-105">
                <i class="fas fa-sign-in-alt mr-2"></i> Login
            </button>
        </form>

        <div class="text-center mt-6">
            <p class="text-gray-600">Don't have an account? 
                <a href="register.php" class="text-indigo-600 font-semibold hover:underline">Register</a>
            </p>
        </div>
    </div>

</body>
</html>
