<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';

// Get top scores with better query
$leaderboard = [];
$sql = "SELECT u.name, u.role, MAX(qa.percentage) as best_percentage,
               MAX(qa.score) as top_score, qa.total_questions,
               COUNT(qa.id) as total_attempts,
               AVG(qa.percentage) as avg_percentage
        FROM quiz_attempts qa
        JOIN users u ON qa.user_id = u.id
        WHERE u.role = 'student'
        GROUP BY u.id, u.name, u.role
        ORDER BY best_percentage DESC, total_attempts DESC
        LIMIT 20";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $leaderboard[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - QuizMaster Pro</title>
    <link rel="stylesheet" href="src/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .leaderboard-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .podium-1 {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            transform: scale(1.1) translateY(-10px);
            z-index: 3;
        }
        .podium-2 {
            background: linear-gradient(135deg, #C0C0C0, #A9A9A9);
            transform: scale(1.05);
            z-index: 2;
        }
        .podium-3 {
            background: linear-gradient(135deg, #CD7F32, #A0522D);
            z-index: 1;
        }
        .podium-item {
            transition: all 0.3s ease;
            color: white;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25);
        }
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-['Inter'] antialiased">
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
                    <a href="index.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isStudent()): ?>
                            <a href="student/quiz.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                                <i class="fas fa-play-circle"></i>
                                <span>Take Quiz</span>
                            </a>
                            <a href="student/dashboard.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard</span>
                            </a>
                        <?php elseif (isLecturer()): ?>
                            <a href="lecturer/dashboard.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <span>Dashboard</span>
                            </a>
                        <?php elseif (isAdmin()): ?>
                            <a href="admin/dashboard.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                                <i class="fas fa-cog"></i>
                                <span>Admin</span>
                            </a>
                        <?php endif; ?>
                        <a href="auth/logout.php" class="bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-2 rounded-full hover:from-red-600 hover:to-red-700 transition duration-200">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    <?php else: ?>
                        <a href="auth/login.php" class="text-gray-700 hover:text-indigo-600 transition duration-200">Login</a>
                        <a href="auth/register.php" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-2 rounded-full hover:from-indigo-700 hover:to-purple-700 transition duration-200">
                            Get Started
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="leaderboard-gradient py-16 mb-8">
        <div class="container mx-auto px-4 text-center">
            <div class="max-w-4xl mx-auto">
                <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-trophy text-white text-4xl"></i>
                </div>
                <h1 class="text-5xl font-bold text-white mb-4">Global Leaderboard</h1>
                <p class="text-xl text-white/90 mb-8">Celebrating our top performers and their achievements</p>

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-2xl mx-auto">
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-white">
                        <div class="text-3xl font-bold"><?= count($leaderboard) ?></div>
                        <div class="text-sm opacity-80">Active Students</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-white">
                        <div class="text-3xl font-bold"><?= !empty($leaderboard) ? round($leaderboard[0]['best_percentage']) : 0 ?>%</div>
                        <div class="text-sm opacity-80">Top Score</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-white">
                        <div class="text-3xl font-bold"><?= !empty($leaderboard) ? round(array_sum(array_column($leaderboard, 'avg_percentage')) / count($leaderboard)) : 0 ?>%</div>
                        <div class="text-sm opacity-80">Average Score</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <main class="container mx-auto px-4 pb-16">
        <div class="max-w-6xl mx-auto">
            
            <?php if (empty($leaderboard)): ?>
                <div class="bg-white rounded-3xl shadow-xl p-16 text-center">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-trophy text-gray-400 text-4xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">No Rankings Yet</h2>
                    <p class="text-gray-600 text-xl mb-8">Be the first to top the leaderboard!</p>
                    <?php if (isLoggedIn() && isStudent()): ?>
                        <a href="student/quiz.php" class="inline-flex items-center bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-8 py-4 rounded-2xl text-lg font-semibold hover:from-indigo-700 hover:to-purple-700 transition duration-200 transform hover:scale-105">
                            <i class="fas fa-play mr-3"></i>
                            Take Your First Quiz
                        </a>
                    <?php else: ?>
                        <a href="auth/register.php" class="inline-flex items-center bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-8 py-4 rounded-2xl text-lg font-semibold hover:from-indigo-700 hover:to-purple-700 transition duration-200 transform hover:scale-105">
                            <i class="fas fa-user-plus mr-3"></i>
                            Join Now
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Podium for top 3 -->
                <div class="flex justify-center items-end mb-16 space-x-4">
                    <?php
                    // Reorder for podium display: 2nd, 1st, 3rd
                    $podium_order = [1, 0, 2]; // indices for 2nd, 1st, 3rd place
                    $podium_positions = [2, 1, 3]; // actual positions

                    foreach ($podium_order as $index => $pos):
                        if (isset($leaderboard[$pos])):
                            $user = $leaderboard[$pos];
                            $percentage = round($user['best_percentage']);
                            $position = $podium_positions[$index];
                    ?>
                        <div class="podium-item podium-<?= $position ?> w-48 p-6 text-center">
                            <div class="mb-4">
                                <?php if ($position === 1): ?>
                                    <i class="fas fa-crown text-4xl mb-2"></i>
                                <?php elseif ($position === 2): ?>
                                    <i class="fas fa-medal text-4xl mb-2"></i>
                                <?php else: ?>
                                    <i class="fas fa-award text-4xl mb-2"></i>
                                <?php endif; ?>
                            </div>
                            <div class="text-5xl font-bold mb-2">#<?= $position ?></div>
                            <div class="font-bold text-xl mb-3"><?= htmlspecialchars($user['name']) ?></div>
                            <div class="text-3xl font-bold mb-2"><?= $percentage ?>%</div>
                            <div class="text-sm opacity-90"><?= $user['total_attempts'] ?> attempts</div>
                        </div>
                    <?php endif; endforeach; ?>
                </div>
                
                <!-- Full leaderboard -->
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6">
                        <h2 class="text-2xl font-bold text-white flex items-center">
                            <i class="fas fa-list-ol mr-3"></i>
                            Complete Rankings
                        </h2>
                    </div>

                    <div class="divide-y divide-gray-200">
                        <?php foreach ($leaderboard as $index => $user):
                            $rank = $index + 1;
                            $percentage = round($user['best_percentage']);
                            $avg_percentage = round($user['avg_percentage']);
                        ?>
                            <div class="p-6 hover:bg-gray-50 transition duration-200 card-hover">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-6">
                                        <!-- Rank Badge -->
                                        <div class="w-16 h-16 <?= $rank <= 3 ? 'bg-gradient-to-r from-yellow-400 to-yellow-600' : 'bg-gradient-to-r from-gray-400 to-gray-600' ?> rounded-full flex items-center justify-center text-white font-bold text-xl">
                                            <?php if ($rank === 1): ?>
                                                <i class="fas fa-crown"></i>
                                            <?php elseif ($rank === 2): ?>
                                                <i class="fas fa-medal"></i>
                                            <?php elseif ($rank === 3): ?>
                                                <i class="fas fa-award"></i>
                                            <?php else: ?>
                                                <?= $rank ?>
                                            <?php endif; ?>
                                        </div>

                                        <!-- User Info -->
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($user['name']) ?></h3>
                                            <p class="text-gray-600">
                                                <?= $user['total_attempts'] ?> attempts •
                                                Avg: <?= $avg_percentage ?>%
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Score Info -->
                                    <div class="text-right">
                                        <div class="text-3xl font-bold text-gray-800 mb-2"><?= $percentage ?>%</div>
                                        <div class="w-32 bg-gray-200 rounded-full h-3">
                                            <div class="<?= $percentage >= 80 ? 'bg-gradient-to-r from-green-400 to-green-600' : ($percentage >= 60 ? 'bg-gradient-to-r from-yellow-400 to-yellow-600' : 'bg-gradient-to-r from-red-400 to-red-600') ?> h-3 rounded-full transition-all duration-500" style="width: <?= $percentage ?>%"></div>
                                        </div>
                                        <div class="text-sm text-gray-500 mt-1">Best Score</div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modern Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4 text-center">
            <div class="flex items-center justify-center space-x-3 mb-4">
                <div class="w-8 h-8 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-white"></i>
                </div>
                <h3 class="text-xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
                    QuizMaster Pro
                </h3>
            </div>
            <p class="text-gray-400">
                &copy; 2025 QuizMaster Pro. All rights reserved. Empowering learners worldwide.
            </p>
        </div>
    </footer>
</body>
</html>
