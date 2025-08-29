<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Require admin access
requireAdmin();

$current_user = getCurrentUser();

// Get dashboard statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $result->fetch_assoc()['count'];

// Total students
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'");
$stats['total_students'] = $result->fetch_assoc()['count'];

// Total lecturers
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'lecturer'");
$stats['total_lecturers'] = $result->fetch_assoc()['count'];

// Total questions
$result = $conn->query("SELECT COUNT(*) as count FROM questions");
$stats['total_questions'] = $result->fetch_assoc()['count'];

// Total quiz attempts
$result = $conn->query("SELECT COUNT(*) as count FROM quiz_attempts");
$stats['total_attempts'] = $result->fetch_assoc()['count'];

// Average score
$result = $conn->query("SELECT AVG(percentage) as avg_score FROM quiz_attempts");
$stats['avg_score'] = round($result->fetch_assoc()['avg_score'] ?? 0, 1);

// Recent activities
$recent_activities = [];
$result = $conn->query("
    SELECT u.name, qa.score, qa.total_questions, qa.started_at, 'quiz_attempt' as type
    FROM quiz_attempts qa
    JOIN users u ON qa.user_id = u.id
    ORDER BY qa.started_at DESC
    LIMIT 10
");
while ($row = $result->fetch_assoc()) {
    $recent_activities[] = $row;
}

// Top performers
$top_performers = [];
$result = $conn->query("
    SELECT u.name, u.role, MAX(qa.percentage) as best_score, COUNT(qa.id) as attempts
    FROM users u
    LEFT JOIN quiz_attempts qa ON u.id = qa.user_id
    WHERE u.role = 'student'
    GROUP BY u.id
    ORDER BY best_score DESC
    LIMIT 5
");
while ($row = $result->fetch_assoc()) {
    $top_performers[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - QuizMaster Pro</title>
    <link href="../src/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.25);
        }
        .stat-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
    </style>
</head>
<body class="bg-gray-50 font-['Inter'] antialiased">
    <!-- Modern Navigation -->
    <nav class="bg-white/95 backdrop-blur-sm shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-white text-lg"></i>
                    </div>
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        QuizMaster Pro
                    </h1>
                </div>

                <div class="flex items-center space-x-6">
                    <a href="../index.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                    <a href="users.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                    <a href="questions.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-question-circle"></i>
                        <span>Questions</span>
                    </a>
                    <a href="../leaderboard.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-trophy"></i>
                        <span>Leaderboard</span>
                    </a>
                    <a href="../auth/logout.php" class="bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-2 rounded-full hover:from-red-600 hover:to-red-700 transition duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Modern Admin Header -->
    <div class="admin-gradient py-16 mb-8">
        <div class="container mx-auto px-6">
            <div class="flex flex-col lg:flex-row justify-between items-center">
                <div class="text-white mb-6 lg:mb-0">
                    <h1 class="text-4xl font-bold mb-2">Admin Dashboard</h1>
                    <p class="text-xl opacity-90">Welcome back, <?= htmlspecialchars($current_user['name']) ?>!</p>
                    <p class="opacity-75">Manage your quiz platform with powerful tools</p>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="stat-card text-white p-4 rounded-2xl text-center">
                        <div class="text-2xl font-bold"><?= $stats['total_users'] ?></div>
                        <div class="text-sm opacity-80">Total Users</div>
                    </div>
                    <div class="stat-card text-white p-4 rounded-2xl text-center">
                        <div class="text-2xl font-bold"><?= $stats['total_questions'] ?></div>
                        <div class="text-sm opacity-80">Questions</div>
                    </div>
                    <div class="stat-card text-white p-4 rounded-2xl text-center">
                        <div class="text-2xl font-bold"><?= $stats['total_attempts'] ?></div>
                        <div class="text-sm opacity-80">Quiz Attempts</div>
                    </div>
                    <div class="stat-card text-white p-4 rounded-2xl text-center">
                        <div class="text-2xl font-bold"><?= $stats['avg_score'] ?>%</div>
                        <div class="text-sm opacity-80">Avg Score</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6">
        <!-- Main Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Users Card -->
            <div class="bg-white rounded-3xl shadow-xl p-8 card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-users text-white text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-gray-800"><?= $stats['total_users'] ?></div>
                        <div class="text-gray-500">Total Users</div>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        Students: <?= $stats['total_students'] ?> | Lecturers: <?= $stats['total_lecturers'] ?>
                    </div>
                </div>
                <a href="users.php" class="mt-4 inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold">
                    Manage Users <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

            <!-- Questions Card -->
            <div class="bg-white rounded-3xl shadow-xl p-8 card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-green-600 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-question-circle text-white text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-gray-800"><?= $stats['total_questions'] ?></div>
                        <div class="text-gray-500">Questions</div>
                    </div>
                </div>
                <div class="text-sm text-gray-600 mb-4">
                    Available in question bank
                </div>
                <a href="questions.php" class="inline-flex items-center text-green-600 hover:text-green-700 font-semibold">
                    Manage Questions <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

            <!-- Quiz Attempts Card -->
            <div class="bg-white rounded-3xl shadow-xl p-8 card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-gray-800"><?= $stats['total_attempts'] ?></div>
                        <div class="text-gray-500">Quiz Attempts</div>
                    </div>
                </div>
                <div class="text-sm text-gray-600 mb-4">
                    Total quiz submissions
                </div>
                <a href="#" class="inline-flex items-center text-purple-600 hover:text-purple-700 font-semibold">
                    View Analytics <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

            <!-- Average Score Card -->
            <div class="bg-white rounded-3xl shadow-xl p-8 card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-red-500 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-trophy text-white text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-gray-800"><?= $stats['avg_score'] ?>%</div>
                        <div class="text-gray-500">Average Score</div>
                    </div>
                </div>
                <div class="text-sm text-gray-600 mb-4">
                    Platform-wide performance
                </div>
                <a href="../leaderboard.php" class="inline-flex items-center text-orange-600 hover:text-orange-700 font-semibold">
                    View Leaderboard <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
    </div>
    
        <!-- Management Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Recent Activities -->
            <div class="bg-white rounded-3xl shadow-xl p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-clock mr-3 text-indigo-600"></i>
                        Recent Activities
                    </h2>
                    <a href="#" class="text-indigo-600 hover:text-indigo-700 font-semibold">View All</a>
                </div>

                <div class="space-y-4">
                    <?php if (empty($recent_activities)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-4"></i>
                            <p>No recent activities</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-2xl">
                                <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-graduate text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($activity['name']) ?></p>
                                    <p class="text-sm text-gray-600">
                                        Completed quiz: <?= $activity['score'] ?>/<?= $activity['total_questions'] ?>
                                        (<?= round(($activity['score'] / $activity['total_questions']) * 100) ?>%)
                                    </p>
                                    <p class="text-xs text-gray-500"><?= date('M j, Y g:i A', strtotime($activity['started_at'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Performers -->
            <div class="bg-white rounded-3xl shadow-xl p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-star mr-3 text-yellow-500"></i>
                        Top Performers
                    </h2>
                    <a href="../leaderboard.php" class="text-yellow-600 hover:text-yellow-700 font-semibold">View Leaderboard</a>
                </div>

                <div class="space-y-4">
                    <?php if (empty($top_performers)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-trophy text-4xl mb-4"></i>
                            <p>No quiz attempts yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($top_performers as $index => $performer): ?>
                            <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-2xl">
                                <div class="w-12 h-12 <?= $index === 0 ? 'bg-gradient-to-r from-yellow-400 to-yellow-600' : ($index === 1 ? 'bg-gradient-to-r from-gray-400 to-gray-600' : ($index === 2 ? 'bg-gradient-to-r from-orange-400 to-orange-600' : 'bg-gradient-to-r from-blue-400 to-blue-600')) ?> rounded-full flex items-center justify-center text-white font-bold">
                                    <?= $index + 1 ?>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($performer['name']) ?></p>
                                    <p class="text-sm text-gray-600">
                                        Best Score: <?= $performer['best_score'] ?? 0 ?>%
                                        (<?= $performer['attempts'] ?> attempts)
                                    </p>
                                </div>
                                <?php if ($index === 0): ?>
                                    <i class="fas fa-crown text-yellow-500 text-xl"></i>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-3xl shadow-xl p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-bolt mr-3 text-indigo-600"></i>
                Quick Actions
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <a href="users.php" class="group bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-2xl hover:from-blue-100 hover:to-indigo-100 transition duration-200 border border-blue-200">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition duration-200">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">Manage Users</h3>
                    <p class="text-gray-600 text-sm">Add, edit, or remove users from the system</p>
                </a>

                <a href="questions.php" class="group bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-2xl hover:from-green-100 hover:to-emerald-100 transition duration-200 border border-green-200">
                    <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition duration-200">
                        <i class="fas fa-question-circle text-white"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">Manage Questions</h3>
                    <p class="text-gray-600 text-sm">Create and organize quiz questions</p>
                </a>

                <a href="quizzes.php" class="group bg-gradient-to-r from-purple-50 to-violet-50 p-6 rounded-2xl hover:from-purple-100 hover:to-violet-100 transition duration-200 border border-purple-200">
                    <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition duration-200">
                        <i class="fas fa-clipboard-list text-white"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">Manage Quizzes</h3>
                    <p class="text-gray-600 text-sm">Create and configure quiz settings</p>
                </a>

                <a href="#" class="group bg-gradient-to-r from-orange-50 to-red-50 p-6 rounded-2xl hover:from-orange-100 hover:to-red-100 transition duration-200 border border-orange-200">
                    <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-red-500 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition duration-200">
                        <i class="fas fa-cog text-white"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">System Settings</h3>
                    <p class="text-gray-600 text-sm">Configure platform settings and preferences</p>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Add some interactive features
        document.addEventListener('DOMContentLoaded', function() {
            // Animate counters
            const counters = document.querySelectorAll('.text-3xl.font-bold');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent);
                let current = 0;
                const increment = target / 50;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.textContent = target + (counter.textContent.includes('%') ? '%' : '');
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current) + (counter.textContent.includes('%') ? '%' : '');
                    }
                }, 20);
            });
        });
    </script>
</body>
</html>
