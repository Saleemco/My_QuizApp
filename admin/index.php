<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Require admin access
requireAdmin();

// Get statistics (existing logic remains unchanged)
$user_count = 0;
$question_count = 0;
$quiz_count = 0;

$result = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    $user_count = $row['count'];
}

$result = $conn->query("SELECT COUNT(*) as count FROM questions");
if ($result) {
    $row = $result->fetch_assoc();
    $question_count = $row['count'];
}

$result = $conn->query("SELECT COUNT(DISTINCT quiz_id) as count FROM quiz_questions");
if ($result) {
    $row = $result->fetch_assoc();
    $quiz_count = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Quiz App</title>
    <link rel="stylesheet" href="../src/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .stat-card {
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border-left-color: currentColor;
        }
        .stat-value {
            background: linear-gradient(135deg, #4f46e5, #8b5cf6);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-['Inter']">
    <!-- Display flash messages -->
    <?php flash('success'); ?>
    <?php flash('error'); ?>

    <div class="flex h-screen overflow-hidden">

        <!-- Main Content -->
        <div class="flex-1 overflow-auto p-6">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Admin Dashboard</h1>
                <p class="text-gray-500 text-sm">System statistics and activity</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
                <div class="stat-card bg-white p-5 rounded-lg shadow-sm border border-gray-100 text-indigo-600">
                    <h2 class="text-base font-semibold text-gray-600 mb-1">Users</h2>
                    <p class="text-3xl font-bold stat-value mb-1"><?= $user_count ?></p>
                    <p class="text-xs text-gray-500">Registered users</p>
                </div>
                
                <div class="stat-card bg-white p-5 rounded-lg shadow-sm border border-gray-100 text-blue-600">
                    <h2 class="text-base font-semibold text-gray-600 mb-1">Questions</h2>
                    <p class="text-3xl font-bold stat-value mb-1"><?= $question_count ?></p>
                    <p class="text-xs text-gray-500">Questions in database</p>
                </div>
                
                <div class="stat-card bg-white p-5 rounded-lg shadow-sm border border-gray-100 text-purple-600">
                    <h2 class="text-base font-semibold text-gray-600 mb-1">Quizzes</h2>
                    <p class="text-3xl font-bold stat-value mb-1"><?= $quiz_count ?></p>
                    <p class="text-xs text-gray-500">Active quizzes</p>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800">Recent Activity</h2>
                </div>
                <div class="p-5">
                    <p class="text-gray-600 text-sm">No recent activity yet</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
