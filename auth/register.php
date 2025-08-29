<?php
require_once __DIR__ . '/../includes/session.php';

// Redirect logged-in users
if (isLoggedIn()) {
    $role = $_SESSION['role'];
    if ($role === 'admin') {
        header('Location: ../admin/dashboard.php');
        exit;
    } elseif ($role === 'lecturer') {
        header('Location: ../lecturer/dashboard.php');
        exit;
    } elseif ($role === 'student') {
        header('Location: ../student/dashboard.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - QuizMaster Pro</title>
    <link rel="stylesheet" href="../src/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .register-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="register-gradient min-h-screen flex items-center justify-center p-4 font-['Inter'] antialiased">
    <div class="glass-effect p-10 rounded-3xl shadow-2xl w-full max-w-lg relative z-10">
        <div class="text-center mb-8">
            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-graduation-cap text-white text-2xl"></i>
                </div>
            </div>

            <h1 class="text-4xl font-bold text-gray-800 mb-3">Registration Closed</h1>
            <p class="text-gray-600 text-lg">Student accounts are managed by lecturers</p>
        </div>

        <div class="bg-blue-50 border border-blue-200 p-6 rounded-2xl mb-8">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">How to Get Access</h3>
            </div>
            <div class="space-y-3 text-gray-700">
                <p class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                    <span>Contact your lecturer or course instructor</span>
                </p>
                <p class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                    <span>They will create your student account</span>
                </p>
                <p class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                    <span>You'll receive login credentials via email</span>
                </p>
            </div>
        </div>

        <div class="text-center space-y-4">
            <a href="login.php" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-4 px-6 rounded-2xl font-semibold text-center block hover:from-indigo-700 hover:to-purple-700 transition duration-200 transform hover:scale-105">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Go to Login
            </a>
            <a href="../index.php" class="w-full bg-gray-100 text-gray-700 py-4 px-6 rounded-2xl font-semibold text-center block hover:bg-gray-200 transition duration-200">
                <i class="fas fa-home mr-2"></i>
                Back to Home
            </a>
        </div>
    </div>
</body>
</html>
