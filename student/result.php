<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Require student access
requireStudent();

// Get quiz results from session
if (!isset($_SESSION['quiz_score']) || !isset($_SESSION['quiz_total'])) {
    header('Location: quiz.php');
    exit;
}

$score = $_SESSION['quiz_score'];
$total = $_SESSION['quiz_total'];
$percentage = round(($score / $total) * 100);
$questions = $_SESSION['quiz_questions'];
$user_answers = $_SESSION['quiz_answers'];

// Clear quiz session data
unset($_SESSION['quiz_score']);
unset($_SESSION['quiz_total']);
unset($_SESSION['quiz_questions']);
unset($_SESSION['quiz_answers']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - QuizMaster Pro</title>
    <link rel="stylesheet" href="../src/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .result-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .celebration-animation {
            animation: celebrate 2s ease-in-out;
        }
        @keyframes celebrate {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .score-circle {
            background: conic-gradient(from 0deg, #10b981 0deg, #10b981 <?= $percentage * 3.6 ?>deg, #e5e7eb <?= $percentage * 3.6 ?>deg, #e5e7eb 360deg);
        }
        .floating-animation {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .review-section {
            display: none;
            animation: fadeIn 0.5s ease-in-out;
        }
        .review-section.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-['Inter'] antialiased">
    <!-- Hero Results Section -->
    <div class="result-gradient py-16 mb-8">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <div class="bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl p-12 celebration-animation">
                    <!-- Result Icon -->
                    <div class="mb-8">
                        <?php if ($percentage >= 80): ?>
                            <div class="w-24 h-24 bg-gradient-to-r from-green-400 to-green-600 rounded-full flex items-center justify-center mx-auto floating-animation">
                                <i class="fas fa-trophy text-white text-4xl"></i>
                            </div>
                        <?php elseif ($percentage >= 60): ?>
                            <div class="w-24 h-24 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full flex items-center justify-center mx-auto floating-animation">
                                <i class="fas fa-medal text-white text-4xl"></i>
                            </div>
                        <?php else: ?>
                            <div class="w-24 h-24 bg-gradient-to-r from-orange-400 to-red-500 rounded-full flex items-center justify-center mx-auto floating-animation">
                                <i class="fas fa-redo text-white text-4xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Result Title -->
                    <h1 class="text-5xl font-bold text-gray-800 mb-4">
                        <?php if ($percentage >= 80): ?>
                            Excellent Work!
                        <?php elseif ($percentage >= 60): ?>
                            Good Job!
                        <?php else: ?>
                            Keep Trying!
                        <?php endif; ?>
                    </h1>

                    <p class="text-xl text-gray-600 mb-8">
                        You scored <span class="font-bold text-indigo-600"><?= $score ?></span> out of <span class="font-bold text-indigo-600"><?= $total ?></span> questions correctly
                    </p>

                    <!-- Circular Progress -->
                    <div class="flex justify-center mb-8">
                        <div class="relative w-48 h-48">
                            <div class="score-circle w-full h-full rounded-full flex items-center justify-center">
                                <div class="w-36 h-36 bg-white rounded-full flex items-center justify-center shadow-inner">
                                    <div class="text-center">
                                        <div class="text-4xl font-bold text-gray-800"><?= $percentage ?>%</div>
                                        <div class="text-sm text-gray-600">Score</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Message -->
                    <div class="bg-gray-50 rounded-2xl p-6">
                        <p class="text-lg text-gray-700">
                            <?php if ($percentage >= 90): ?>
                                🎉 Outstanding performance! You've mastered this topic.
                            <?php elseif ($percentage >= 80): ?>
                                🌟 Great job! You have a solid understanding of the material.
                            <?php elseif ($percentage >= 70): ?>
                                👍 Good work! A few more practice sessions and you'll be an expert.
                            <?php elseif ($percentage >= 60): ?>
                                📚 Not bad! Review the material and try again to improve your score.
                            <?php else: ?>
                                💪 Don't give up! Practice makes perfect. Review and try again.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 max-w-4xl">

        <!-- Navigation Tabs -->
        <div class="flex justify-center mb-8">
            <div class="bg-white rounded-2xl p-2 shadow-lg">
                <button id="summary-btn" class="px-8 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold transition duration-200">
                    <i class="fas fa-chart-pie mr-2"></i>Summary
                </button>
                <button id="review-btn" class="px-8 py-3 rounded-xl text-gray-600 hover:text-gray-800 font-semibold transition duration-200">
                    <i class="fas fa-list-check mr-2"></i>Review Answers
                </button>
            </div>
        </div>

        <!-- Summary Section -->
        <div id="summary" class="review-section active">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Performance Stats -->
                <div class="bg-white rounded-3xl shadow-xl p-8 text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-green-400 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2"><?= $score ?></h3>
                    <p class="text-gray-600 font-medium">Correct Answers</p>
                </div>

                <div class="bg-white rounded-3xl shadow-xl p-8 text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-red-400 to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-times text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2"><?= $total - $score ?></h3>
                    <p class="text-gray-600 font-medium">Incorrect Answers</p>
                </div>

                <div class="bg-white rounded-3xl shadow-xl p-8 text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-percentage text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2"><?= $percentage ?>%</h3>
                    <p class="text-gray-600 font-medium">Accuracy Rate</p>
                </div>
            </div>

            <!-- Performance Analysis -->
            <div class="bg-white rounded-3xl shadow-xl p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-analytics mr-3 text-indigo-600"></i>
                    Performance Analysis
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6">
                        <h3 class="font-semibold text-gray-800 mb-3">Strengths</h3>
                        <ul class="space-y-2 text-gray-600">
                            <?php if ($percentage >= 80): ?>
                                <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i>Excellent comprehension</li>
                                <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i>Strong knowledge base</li>
                            <?php elseif ($percentage >= 60): ?>
                                <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i>Good understanding</li>
                                <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i>Solid foundation</li>
                            <?php else: ?>
                                <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i>Room for improvement</li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-2xl p-6">
                        <h3 class="font-semibold text-gray-800 mb-3">Areas to Focus</h3>
                        <ul class="space-y-2 text-gray-600">
                            <?php if ($percentage < 60): ?>
                                <li class="flex items-center"><i class="fas fa-exclamation-circle text-orange-500 mr-2"></i>Review core concepts</li>
                                <li class="flex items-center"><i class="fas fa-exclamation-circle text-orange-500 mr-2"></i>Practice more questions</li>
                            <?php elseif ($percentage < 80): ?>
                                <li class="flex items-center"><i class="fas fa-exclamation-circle text-orange-500 mr-2"></i>Fine-tune understanding</li>
                                <li class="flex items-center"><i class="fas fa-exclamation-circle text-orange-500 mr-2"></i>Review incorrect answers</li>
                            <?php else: ?>
                                <li class="flex items-center"><i class="fas fa-star text-yellow-500 mr-2"></i>Maintain excellence</li>
                                <li class="flex items-center"><i class="fas fa-star text-yellow-500 mr-2"></i>Challenge yourself further</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Section -->
        <div id="review" class="review-section">
            <?php foreach ($questions as $index => $question):
                $qid = $question['id'];
                $user_answer = $user_answers['question_'.$qid] ?? '';
                $is_correct = $user_answer === $question['correct_option'];
            ?>
                <div class="bg-white rounded-3xl shadow-xl p-8 mb-8 border-l-8 <?= $is_correct ? 'border-green-500' : 'border-red-500' ?>">
                    <!-- Question Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 <?= $is_correct ? 'bg-gradient-to-r from-green-500 to-green-600' : 'bg-gradient-to-r from-red-500 to-red-600' ?> rounded-full flex items-center justify-center text-white font-bold text-lg">
                                <?= $index + 1 ?>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Question <?= $index+1 ?></h2>
                                <p class="text-gray-500">Review your answer</p>
                            </div>
                        </div>

                        <!-- Result Badge -->
                        <div class="<?= $is_correct ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> px-4 py-2 rounded-full text-sm font-semibold flex items-center">
                            <i class="fas <?= $is_correct ? 'fa-check' : 'fa-times' ?> mr-2"></i>
                            <?= $is_correct ? 'Correct' : 'Incorrect' ?>
                        </div>
                    </div>

                    <!-- Question Text -->
                    <div class="bg-gray-50 rounded-2xl p-6 mb-8">
                        <p class="text-xl text-gray-800 leading-relaxed"><?= htmlspecialchars($question['question']) ?></p>
                    </div>

                    <!-- Answer Options -->
                    <div class="grid gap-4 mb-8">
                        <?php
                        $options = ['a', 'b', 'c', 'd'];
                        $option_colors = ['blue', 'green', 'yellow', 'red'];
                        foreach ($options as $i => $option):
                            $is_user_answer = $user_answer === $option;
                            $is_correct_answer = $question['correct_option'] === $option;
                            $option_text = $question['option_' . $option];

                            if ($is_correct_answer) {
                                $bg_class = 'bg-gradient-to-r from-green-100 to-green-200 border-green-500';
                                $text_class = 'text-green-800';
                                $icon = 'fa-check-circle text-green-600';
                            } elseif ($is_user_answer && !$is_correct_answer) {
                                $bg_class = 'bg-gradient-to-r from-red-100 to-red-200 border-red-500';
                                $text_class = 'text-red-800';
                                $icon = 'fa-times-circle text-red-600';
                            } else {
                                $bg_class = 'bg-white border-gray-200';
                                $text_class = 'text-gray-800';
                                $icon = '';
                            }
                        ?>
                            <div class="<?= $bg_class ?> border-2 rounded-2xl p-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-8 h-8 bg-gradient-to-r from-<?= $option_colors[$i] ?>-500 to-<?= $option_colors[$i] ?>-600 rounded-full flex items-center justify-center text-white font-bold">
                                            <?= strtoupper($option) ?>
                                        </div>
                                        <span class="text-lg <?= $text_class ?>"><?= htmlspecialchars($option_text) ?></span>
                                    </div>
                                    <?php if ($icon): ?>
                                        <i class="fas <?= $icon ?> text-2xl"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Answer Summary -->
                    <div class="bg-gray-50 rounded-2xl p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-600 mb-1">Your Answer</p>
                                <p class="text-lg font-bold <?= $is_correct ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= $user_answer ? strtoupper($user_answer) . '. ' . htmlspecialchars($question['option_' . $user_answer]) : 'No answer selected' ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-600 mb-1">Correct Answer</p>
                                <p class="text-lg font-bold text-green-600">
                                    <?= strtoupper($question['correct_option']) ?>. <?= htmlspecialchars($question['option_' . $question['correct_option']]) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Action Buttons -->
        <div class="text-center mt-12">
            <div class="bg-white rounded-3xl shadow-xl p-8">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">What's Next?</h3>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="quiz.php" class="group flex items-center justify-center px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl hover:from-indigo-700 hover:to-purple-700 transition duration-200 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-redo mr-3 group-hover:animate-spin"></i>
                        Take Another Quiz
                    </a>
                    <a href="history.php" class="group flex items-center justify-center px-8 py-4 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-2xl hover:from-gray-700 hover:to-gray-800 transition duration-200 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-history mr-3 group-hover:animate-pulse"></i>
                        View History
                    </a>
                    <a href="../leaderboard.php" class="group flex items-center justify-center px-8 py-4 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-2xl hover:from-yellow-600 hover:to-orange-600 transition duration-200 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-trophy mr-3 group-hover:animate-bounce"></i>
                        Leaderboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const summaryBtn = document.getElementById('summary-btn');
        const reviewBtn = document.getElementById('review-btn');
        const summarySection = document.getElementById('summary');
        const reviewSection = document.getElementById('review');

        function showSummary() {
            summarySection.classList.add('active');
            reviewSection.classList.remove('active');

            // Update button styles
            summaryBtn.className = 'px-8 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold transition duration-200';
            reviewBtn.className = 'px-8 py-3 rounded-xl text-gray-600 hover:text-gray-800 font-semibold transition duration-200';
        }

        function showReview() {
            reviewSection.classList.add('active');
            summarySection.classList.remove('active');

            // Update button styles
            reviewBtn.className = 'px-8 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold transition duration-200';
            summaryBtn.className = 'px-8 py-3 rounded-xl text-gray-600 hover:text-gray-800 font-semibold transition duration-200';
        }

        summaryBtn.addEventListener('click', showSummary);
        reviewBtn.addEventListener('click', showReview);

        // Add celebration animation on load for high scores
        <?php if ($percentage >= 80): ?>
        document.addEventListener('DOMContentLoaded', () => {
            // Create confetti effect
            const colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#feca57'];
            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    createConfetti();
                }, i * 100);
            }
        });

        function createConfetti() {
            const confetti = document.createElement('div');
            confetti.style.position = 'fixed';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.top = '-10px';
            confetti.style.width = '10px';
            confetti.style.height = '10px';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.borderRadius = '50%';
            confetti.style.pointerEvents = 'none';
            confetti.style.zIndex = '1000';
            confetti.style.animation = 'fall 3s linear forwards';

            document.body.appendChild(confetti);

            setTimeout(() => {
                confetti.remove();
            }, 3000);
        }

        // Add CSS for confetti animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fall {
                to {
                    transform: translateY(100vh) rotate(360deg);
                }
            }
        `;
        document.head.appendChild(style);
        <?php endif; ?>
    </script>
</body>
</html>
