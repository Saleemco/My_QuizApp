<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Require student access
requireStudent();

$current_user = getCurrentUser();
$user_id = $current_user['id'];

// Get quiz attempts with enhanced data
$attempts = [];
$stmt = $conn->prepare("
    SELECT id, score, total_questions, percentage, started_at, completed_at,
           TIMESTAMPDIFF(SECOND, started_at, completed_at) as time_taken
    FROM quiz_attempts
    WHERE user_id = ?
    ORDER BY started_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $attempts[] = $row;
    }
}
$stmt->close();

// Calculate statistics
$total_attempts = count($attempts);
$best_score = $total_attempts > 0 ? max(array_column($attempts, 'percentage')) : 0;
$avg_score = $total_attempts > 0 ? array_sum(array_column($attempts, 'percentage')) / $total_attempts : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz History - QuizMaster Pro</title>
    <link rel="stylesheet" href="../src/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .history-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    <!-- Navigation -->
    <nav class="bg-white/95 backdrop-blur-sm shadow-lg sticky top-0 z-50 mb-8">
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
                    <a href="dashboard.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="quiz.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-play-circle"></i>
                        <span>Take Quiz</span>
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

    <!-- Hero Section -->
    <div class="history-gradient py-12 mb-8">
        <div class="container mx-auto px-4 text-center">
            <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-history text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-white mb-4">Quiz History</h1>
            <p class="text-xl text-white/90 mb-8">Track your progress and review past performances</p>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-2xl mx-auto">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-white">
                    <div class="text-3xl font-bold"><?= $total_attempts ?></div>
                    <div class="text-sm opacity-80">Total Attempts</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-white">
                    <div class="text-3xl font-bold"><?= round($best_score) ?>%</div>
                    <div class="text-sm opacity-80">Best Score</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-white">
                    <div class="text-3xl font-bold"><?= round($avg_score) ?>%</div>
                    <div class="text-sm opacity-80">Average Score</div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 max-w-6xl pb-16">
        
        <?php if (empty($attempts)): ?>
            <div class="bg-white rounded-3xl shadow-xl p-16 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-clipboard-list text-gray-400 text-4xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-4">No Quiz History Yet</h2>
                <p class="text-gray-600 text-xl mb-8">Start your learning journey by taking your first quiz!</p>
                <a href="quiz.php" class="inline-flex items-center bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-8 py-4 rounded-2xl text-lg font-semibold hover:from-indigo-700 hover:to-purple-700 transition duration-200 transform hover:scale-105">
                    <i class="fas fa-play mr-3"></i>
                    Take Your First Quiz
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($attempts as $index => $attempt):
                    $percentage = round($attempt['percentage']);
                    $time_taken = $attempt['time_taken'] ? gmdate("H:i:s", $attempt['time_taken']) : 'N/A';
                ?>
                    <div class="bg-white rounded-3xl shadow-xl p-8 card-hover">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 <?= $percentage >= 80 ? 'bg-gradient-to-r from-green-500 to-green-600' : ($percentage >= 60 ? 'bg-gradient-to-r from-yellow-500 to-yellow-600' : 'bg-gradient-to-r from-red-500 to-red-600') ?> rounded-full flex items-center justify-center text-white font-bold text-xl">
                                    #<?= $index + 1 ?>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-800">Quiz Attempt #<?= $index + 1 ?></h3>
                                    <p class="text-gray-600"><?= date('F j, Y \a\t g:i A', strtotime($attempt['started_at'])) ?></p>
                                </div>
                            </div>

                            <!-- Performance Badge -->
                            <div class="text-right">
                                <div class="text-4xl font-bold <?= $percentage >= 80 ? 'text-green-600' : ($percentage >= 60 ? 'text-yellow-600' : 'text-red-600') ?> mb-2">
                                    <?= $percentage ?>%
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php if ($percentage >= 90): ?>
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full font-semibold">Excellent</span>
                                    <?php elseif ($percentage >= 80): ?>
                                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-semibold">Very Good</span>
                                    <?php elseif ($percentage >= 70): ?>
                                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full font-semibold">Good</span>
                                    <?php elseif ($percentage >= 60): ?>
                                        <span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full font-semibold">Fair</span>
                                    <?php else: ?>
                                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full font-semibold">Needs Work</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Detailed Stats -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="bg-gray-50 rounded-2xl p-4 text-center">
                                <div class="text-2xl font-bold text-gray-800"><?= $attempt['score'] ?></div>
                                <div class="text-sm text-gray-600">Correct Answers</div>
                            </div>
                            <div class="bg-gray-50 rounded-2xl p-4 text-center">
                                <div class="text-2xl font-bold text-gray-800"><?= $attempt['total_questions'] ?></div>
                                <div class="text-sm text-gray-600">Total Questions</div>
                            </div>
                            <div class="bg-gray-50 rounded-2xl p-4 text-center">
                                <div class="text-2xl font-bold text-gray-800"><?= $time_taken ?></div>
                                <div class="text-sm text-gray-600">Time Taken</div>
                            </div>
                            <div class="bg-gray-50 rounded-2xl p-4 text-center">
                                <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                                    <div class="<?= $percentage >= 80 ? 'bg-gradient-to-r from-green-400 to-green-600' : ($percentage >= 60 ? 'bg-gradient-to-r from-yellow-400 to-yellow-600' : 'bg-gradient-to-r from-red-400 to-red-600') ?> h-3 rounded-full transition-all duration-500" style="width: <?= $percentage ?>%"></div>
                                </div>
                                <div class="text-sm text-gray-600">Progress</div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="mt-12 text-center">
            <div class="bg-white rounded-3xl shadow-xl p-8">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Ready for More?</h3>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="quiz.php" class="group flex items-center justify-center px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl hover:from-indigo-700 hover:to-purple-700 transition duration-200 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-redo mr-3 group-hover:animate-spin"></i>
                        Take Another Quiz
                    </a>
                    <a href="../leaderboard.php" class="group flex items-center justify-center px-8 py-4 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-2xl hover:from-yellow-600 hover:to-orange-600 transition duration-200 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-trophy mr-3 group-hover:animate-bounce"></i>
                        View Leaderboard
                    </a>
                    <a href="dashboard.php" class="group flex items-center justify-center px-8 py-4 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-2xl hover:from-gray-700 hover:to-gray-800 transition duration-200 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-tachometer-alt mr-3 group-hover:animate-pulse"></i>
                        Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
