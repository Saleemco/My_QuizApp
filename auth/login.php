<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';

$errors = [];
$identifier = ''; // Changed from $email to $identifier
// Fetch default admin and a sample lecturer for display
$admin_user = null;
$lecturer_user = null;

$stmt = $conn->prepare("SELECT email, password FROM users WHERE role = 'admin' LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $admin_user = $result->fetch_assoc();
}
$stmt->close();

$stmt = $conn->prepare("SELECT email, matric_number FROM users WHERE role = 'lecturer' LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $lecturer_user = $result->fetch_assoc();
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $errors['general'] = 'Invalid request. Please try again.';
    } else {
        $identifier = trim($_POST['identifier']); // Can be email or matric number
        $password = $_POST['password'];

        // Validation
        if (empty($identifier)) {
            $errors['identifier'] = 'Email or Matric Number is required';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }
    }

    if (empty($errors)) {
        $user = null;
        // Try logging in with email
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare("SELECT id, name, email, matric_number, password, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $identifier);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
            }
            $stmt->close();
        }
        
        // If not found by email or identifier is not an email, try with matric number
        if (!$user) {
            $stmt = $conn->prepare("SELECT id, name, email, matric_number, password, role FROM users WHERE matric_number = ?");
            $stmt->bind_param("s", $identifier);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
            }
            $stmt->close();
        }
        
        if ($user) {
            if (password_verify($password, $user['password'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);

                // Set session variables (consistent naming)
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['matric_number'] = $user['matric_number'];

                // Generate CSRF token
                generateCSRFToken();

                // Set success message
                flash('success', 'Login successful! Welcome back, ' . $user['name'], 'bg-green-100 border border-green-400 text-green-700');

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: ../admin/dashboard.php');
                } elseif ($user['role'] === 'lecturer') {
                    header('Location: ../lecturer/dashboard.php');
                } else {
                    header('Location: ../student/dashboard.php');
                }
                exit;
            } else {
                $errors['general'] = 'Invalid identifier or password';
            }
        } else {
            $errors['general'] = 'Invalid identifier or password';
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 70%;
            right: 10%;
            animation-delay: -2s;
        }
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            top: 40%;
            left: 80%;
            animation-delay: -4s;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>
<body class="login-gradient min-h-screen flex items-center justify-center p-4 font-['Inter'] antialiased relative">
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    <div class="glass-effect p-10 rounded-3xl shadow-2xl w-full max-w-lg relative z-10">
        <div class="text-center mb-8">
            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-graduation-cap text-white text-2xl"></i>
                </div>
            </div>

            <h1 class="text-4xl font-bold text-gray-800 mb-3">Welcome Back</h1>
            <p class="text-gray-600 text-lg">Sign in to continue your learning journey</p>
        </div>
            
        <?php if (!empty($errors['general'])): ?>
            <div class="bg-red-50 border border-red-200 p-4 mb-6 rounded-2xl">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-500 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 font-medium"><?= $errors['general'] ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
            
        <form action="login.php" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

            <div>
                <label for="identifier" class="block text-sm font-semibold text-gray-700 mb-2">Email or Matric Number</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input type="text" id="identifier" name="identifier" value="<?= htmlspecialchars($identifier) ?>"
                        class="block w-full pl-12 pr-4 py-4 border border-gray-300 rounded-2xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 text-lg <?= isset($errors['identifier']) ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : '' ?>"
                        placeholder="you@example.com or H/CS/23/1154">
                </div>
                <?php if (isset($errors['identifier'])): ?>
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        <?= $errors['identifier'] ?>
                    </p>
                <?php endif; ?>
            </div>
                
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                    <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 transition duration-200">
                        Forgot password?
                    </a>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" id="password" name="password"
                        class="block w-full pl-12 pr-4 py-4 border border-gray-300 rounded-2xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 text-lg <?= isset($errors['password']) ? 'border-red-300' : '' ?>"
                        placeholder="••••••••">
                </div>
                <?php if (isset($errors['password'])): ?>
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        <?= $errors['password'] ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox"
                        class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded-lg">
                    <label for="remember-me" class="ml-3 block text-sm text-gray-700 font-medium">
                        Remember me
                    </label>
                </div>
            </div>

            <button type="submit" class="group w-full flex justify-center items-center py-4 px-6 border border-transparent rounded-2xl shadow-lg text-lg font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200 transform hover:scale-105">
                <i class="fas fa-sign-in-alt mr-3 group-hover:animate-pulse"></i>
                Sign In
            </button>
        </form>

        <!-- Test Accounts Section -->
        <?php if ($admin_user || $lecturer_user): ?>
            <div class="mt-8">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-gray-500 font-medium">Quick Access</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($admin_user || $lecturer_user): ?>
            <div class="mt-6">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-2xl border border-blue-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-key mr-2 text-indigo-600"></i>
                        Demo Accounts
                    </h3>
                    <div class="space-y-3">
                        <?php if ($admin_user): ?>
                            <div class="bg-white p-4 rounded-xl border border-indigo-200 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-indigo-600 flex items-center">
                                            <i class="fas fa-crown mr-2"></i>Admin Account
                                        </p>
                                        <p class="text-sm text-gray-600 mt-1">
                                            Email: <span class="font-mono bg-gray-100 px-2 py-1 rounded">admin@example.com</span>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Password: <span class="font-mono bg-gray-100 px-2 py-1 rounded">password</span>
                                        </p>
                                    </div>
                                    <i class="fas fa-cog text-indigo-400 text-2xl"></i>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($lecturer_user): ?>
                            <div class="bg-white p-4 rounded-xl border border-blue-200 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-blue-600 flex items-center">
                                            <i class="fas fa-chalkboard-teacher mr-2"></i>Lecturer Account
                                        </p>
                                        <p class="text-sm text-gray-600 mt-1">
                                            Email: <span class="font-mono bg-gray-100 px-2 py-1 rounded"><?= htmlspecialchars($lecturer_user['email']) ?></span>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Password: <span class="font-mono bg-gray-100 px-2 py-1 rounded">password</span>
                                        </p>
                                    </div>
                                    <i class="fas fa-graduation-cap text-blue-400 text-2xl"></i>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-8 text-center">
            <p class="text-gray-600">
                Don't have an account?
                <a href="register.php" class="font-semibold text-indigo-600 hover:text-indigo-500 transition duration-200">
                    Create one now
                </a>
            </p>
        </div>
    </div>
</body>
</html>
