<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Require student access
requireStudent();

$current_user = getCurrentUser();
$student_id = $current_user['id'];

// Get student statistics
$stats = [];

// Total quiz attempts
$result = $conn->prepare("SELECT COUNT(*) as count FROM quiz_attempts WHERE user_id = ?");
$result->bind_param("i", $student_id);
$result->execute();
$stats['total_attempts'] = $result->get_result()->fetch_assoc()['count'];

// Best score
$result = $conn->prepare("SELECT MAX(percentage) as best_score FROM quiz_attempts WHERE user_id = ?");
$result->bind_param("i", $student_id);
$result->execute();
$stats['best_score'] = round($result->get_result()->fetch_assoc()['best_score'] ?? 0, 1);

// Average score
$result = $conn->prepare("SELECT AVG(percentage) as avg_score FROM quiz_attempts WHERE user_id = ?");
$result->bind_param("i", $student_id);
$result->execute();
$stats['avg_score'] = round($result->get_result()->fetch_assoc()['avg_score'] ?? 0, 1);

// Current rank
$result = $conn->query("
    SELECT user_id, MAX(percentage) as best_score,
           ROW_NUMBER() OVER (ORDER BY MAX(percentage) DESC) as rank
    FROM quiz_attempts
    GROUP BY user_id
");
$rank = 0;
while ($row = $result->fetch_assoc()) {
    if ($row['user_id'] == $student_id) {
        $rank = $row['rank'];
        break;
    }
}
$stats['current_rank'] = $rank;

// Recent quiz attempts
$recent_attempts = [];
$result = $conn->prepare("
    SELECT score, total_questions, percentage, started_at, completed_at
    FROM quiz_attempts
    WHERE user_id = ?
    ORDER BY started_at DESC
    LIMIT 5
");
$result->bind_param("i", $student_id);
$result->execute();
$result_data = $result->get_result();
while ($row = $result_data->fetch_assoc()) {
    $recent_attempts[] = $row;
}

// Performance trend (last 10 attempts)
$performance_trend = [];
$result = $conn->prepare("
    SELECT percentage, started_at
    FROM quiz_attempts
    WHERE user_id = ?
    ORDER BY started_at DESC
    LIMIT 10
");
$result->bind_param("i", $student_id);
$result->execute();
$result_data = $result->get_result();
while ($row = $result_data->fetch_assoc()) {
    $performance_trend[] = $row;
}
$performance_trend = array_reverse($performance_trend); // Oldest first for chart

// Get available questions count (from lecturer)
$available_questions = 0;
$result = $conn->prepare("
    SELECT COUNT(*) as count
    FROM questions q
    JOIN users u ON q.lecturer_id = u.id
    WHERE u.id = (SELECT lecturer_id FROM users WHERE id = ?)
");
$result->bind_param("i", $student_id);
$result->execute();
$available_questions = $result->get_result()->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - QuizMaster Pro</title>
    <link href="../src/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .student-gradient {
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
        .progress-ring {
            transform: rotate(-90deg);
        }
        .progress-ring-circle {
            transition: stroke-dasharray 0.35s;
            transform-origin: 50% 50%;
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
                    <a href="quiz.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-play-circle"></i>
                        <span>Take Quiz</span>
                    </a>
                    <a href="history.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-history"></i>
                        <span>History</span>
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

    <!-- Modern Student Header -->
    <div class="student-gradient py-16 mb-8">
        <div class="container mx-auto px-6">
            <div class="flex flex-col lg:flex-row justify-between items-center">
                <div class="text-white mb-6 lg:mb-0">
                    <h1 class="text-4xl font-bold mb-2">Welcome back, <?= htmlspecialchars($current_user['fullname']) ?>!</h1>
                    <p class="text-xl opacity-90">Ready to challenge yourself today?</p>
                    <p class="opacity-75">Track your progress and compete with peers</p>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="stat-card text-white p-4 rounded-2xl text-center">
                        <div class="text-2xl font-bold"><?= $stats['total_attempts'] ?></div>
                        <div class="text-sm opacity-80">Attempts</div>
                    </div>
                    <div class="stat-card text-white p-4 rounded-2xl text-center">
                        <div class="text-2xl font-bold"><?= $stats['best_score'] ?>%</div>
                        <div class="text-sm opacity-80">Best Score</div>
                    </div>
                    <div class="stat-card text-white p-4 rounded-2xl text-center">
                        <div class="text-2xl font-bold"><?= $stats['avg_score'] ?>%</div>
                        <div class="text-sm opacity-80">Average</div>
                    </div>
                    <div class="stat-card text-white p-4 rounded-2xl text-center">
                        <div class="text-2xl font-bold">#<?= $stats['current_rank'] ?: 'N/A' ?></div>
                        <div class="text-sm opacity-80">Rank</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6">
        <!-- Main Action Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <!-- Take Quiz Card -->
            <div class="bg-white rounded-3xl shadow-xl p-8 card-hover border-l-8 border-indigo-500">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-play text-white text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Available</div>
                        <div class="text-2xl font-bold text-gray-800"><?= $available_questions ?></div>
                        <div class="text-sm text-gray-500">Questions</div>
                    </div>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-3">Take Quiz</h2>
                <p class="text-gray-600 mb-6">Challenge yourself with a new quiz and improve your knowledge</p>
                <a href="quiz.php" class="group w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-4 px-6 rounded-2xl font-semibold text-center block hover:from-indigo-700 hover:to-purple-700 transition duration-200 transform hover:scale-105">
                    <i class="fas fa-rocket mr-2 group-hover:animate-pulse"></i>
                    Start Quiz Now
                </a>
            </div>

            <!-- Performance Card -->
            <div class="bg-white rounded-3xl shadow-xl p-8 card-hover border-l-8 border-green-500">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-green-600 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                    <!-- Circular Progress for Best Score -->
                    <div class="relative w-16 h-16">
                        <svg class="progress-ring w-16 h-16" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="54" fill="transparent" stroke="#e5e7eb" stroke-width="12"/>
                            <circle cx="60" cy="60" r="54" fill="transparent" stroke="#10b981" stroke-width="12"
                                    stroke-dasharray="<?= 2 * pi() * 54 ?>"
                                    stroke-dashoffset="<?= 2 * pi() * 54 * (1 - $stats['best_score'] / 100) ?>"
                                    class="progress-ring-circle"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-sm font-bold text-gray-800"><?= $stats['best_score'] ?>%</span>
                        </div>
                    </div>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-3">Performance</h2>
                <p class="text-gray-600 mb-6">Track your progress and see detailed analytics</p>
                <a href="history.php" class="group w-full bg-gradient-to-r from-green-600 to-green-700 text-white py-4 px-6 rounded-2xl font-semibold text-center block hover:from-green-700 hover:to-green-800 transition duration-200 transform hover:scale-105">
                    <i class="fas fa-chart-bar mr-2 group-hover:animate-pulse"></i>
                    View History
                </a>
            </div>

            <!-- Leaderboard Card -->
            <div class="bg-white rounded-3xl shadow-xl p-8 card-hover border-l-8 border-yellow-500">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-16 h-16 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-trophy text-white text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Your Rank</div>
                        <div class="text-2xl font-bold text-gray-800">#<?= $stats['current_rank'] ?: 'N/A' ?></div>
                        <div class="text-sm text-gray-500">Global</div>
                    </div>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-3">Leaderboard</h2>
                <p class="text-gray-600 mb-6">See how you rank against other students worldwide</p>
                <a href="../leaderboard.php" class="group w-full bg-gradient-to-r from-yellow-500 to-orange-500 text-white py-4 px-6 rounded-2xl font-semibold text-center block hover:from-yellow-600 hover:to-orange-600 transition duration-200 transform hover:scale-105">
                    <i class="fas fa-crown mr-2 group-hover:animate-bounce"></i>
                    View Rankings
                </a>
            </div>
        </div>
        
        <!-- Recent Activities and Performance -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Recent Quiz Attempts -->
            <div class="bg-white rounded-3xl shadow-xl p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-history mr-3 text-indigo-600"></i>
                        Recent Attempts
                    </h2>
                    <a href="history.php" class="text-indigo-600 hover:text-indigo-700 font-semibold">View All</a>
                </div>

                <div class="space-y-4">
                    <?php if (empty($recent_attempts)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-clipboard-list text-4xl mb-4"></i>
                            <p>No quiz attempts yet</p>
                            <a href="quiz.php" class="inline-block mt-4 bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition duration-200">
                                Take Your First Quiz
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_attempts as $attempt): ?>
                            <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-2xl">
                                <div class="w-12 h-12 <?= $attempt['percentage'] >= 80 ? 'bg-gradient-to-r from-green-500 to-green-600' : ($attempt['percentage'] >= 60 ? 'bg-gradient-to-r from-yellow-500 to-yellow-600' : 'bg-gradient-to-r from-red-500 to-red-600') ?> rounded-full flex items-center justify-center">
                                    <i class="fas <?= $attempt['percentage'] >= 80 ? 'fa-trophy' : ($attempt['percentage'] >= 60 ? 'fa-medal' : 'fa-redo') ?> text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800">
                                        Score: <?= $attempt['score'] ?>/<?= $attempt['total_questions'] ?>
                                        (<?= round($attempt['percentage']) ?>%)
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <?= date('M j, Y g:i A', strtotime($attempt['started_at'])) ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <?php if ($attempt['percentage'] >= 80): ?>
                                        <div class="text-green-600 font-semibold">Excellent</div>
                                    <?php elseif ($attempt['percentage'] >= 60): ?>
                                        <div class="text-yellow-600 font-semibold">Good</div>
                                    <?php else: ?>
                                        <div class="text-red-600 font-semibold">Needs Work</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Performance Chart -->
            <div class="bg-white rounded-3xl shadow-xl p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-chart-area mr-3 text-purple-600"></i>
                        Performance Trend
                    </h2>
                </div>

                <?php if (empty($performance_trend)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-chart-line text-4xl mb-4"></i>
                        <p>No performance data yet</p>
                        <p class="text-sm">Take more quizzes to see your progress</p>
                    </div>
                <?php else: ?>
                    <div class="relative h-64">
                        <canvas id="performanceChart"></canvas>
                    </div>

                    <!-- Performance Insights -->
                    <div class="mt-6 grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-4 rounded-2xl text-center">
                            <div class="text-2xl font-bold text-blue-600"><?= $stats['avg_score'] ?>%</div>
                            <div class="text-sm text-blue-800">Average Score</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-2xl text-center">
                            <div class="text-2xl font-bold text-green-600"><?= $stats['best_score'] ?>%</div>
                            <div class="text-sm text-green-800">Best Score</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Study Tips and Motivation -->
        <div class="bg-white rounded-3xl shadow-xl p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-lightbulb mr-3 text-yellow-500"></i>
                Study Tips & Motivation
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-2xl border border-blue-200">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-target text-white"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">Set Goals</h3>
                    <p class="text-gray-600 text-sm">Aim for consistent improvement. Try to beat your previous score each time!</p>
                </div>

                <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-2xl border border-green-200">
                    <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">Practice Regularly</h3>
                    <p class="text-gray-600 text-sm">Regular practice is key to improvement. Take quizzes frequently to stay sharp.</p>
                </div>

                <div class="bg-gradient-to-r from-purple-50 to-violet-50 p-6 rounded-2xl border border-purple-200">
                    <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">Learn from Others</h3>
                    <p class="text-gray-600 text-sm">Check the leaderboard to see how others are performing and get motivated!</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Performance Chart
        <?php if (!empty($performance_trend)): ?>
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [<?php foreach ($performance_trend as $index => $trend): ?>'Attempt <?= $index + 1 ?>'<?= $index < count($performance_trend) - 1 ? ',' : '' ?><?php endforeach; ?>],
                datasets: [{
                    label: 'Score (%)',
                    data: [<?php foreach ($performance_trend as $index => $trend): ?><?= $trend['percentage'] ?><?= $index < count($performance_trend) - 1 ? ',' : '' ?><?php endforeach; ?>],
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgb(99, 102, 241)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                },
                elements: {
                    point: {
                        hoverBackgroundColor: 'rgb(99, 102, 241)'
                    }
                }
            }
        });
        <?php endif; ?>

        // Animate counters on page load
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('.text-2xl.font-bold');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent);
                if (isNaN(target)) return;

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
