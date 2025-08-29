<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Require student access
requireStudent();

// Set quiz duration in seconds (15 minutes)
$quiz_duration = 15 * 60;

// Get current user
$current_user = getCurrentUser();
$student_id = $current_user['id'];
$lecturer_id = null;
$stmt = $conn->prepare("SELECT lecturer_id FROM users WHERE id = ? AND role = 'student'");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $lecturer_id = $result->fetch_assoc()['lecturer_id'];
}
$stmt->close();

$quiz_available = false;
$message = '';
$questions = [];

if ($lecturer_id) {
    // Get lecturer's question count
    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM questions WHERE lecturer_id = ?");
    $count_stmt->bind_param("i", $lecturer_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result()->fetch_row();
    $lecturer_question_count = $count_result[0];
    $count_stmt->close();

    // Get quiz settings for the lecturer
    $min_questions = 10; // Default
    $max_questions = 30; // Default
    $settings_stmt = $conn->prepare("SELECT min_questions, max_questions FROM quiz_settings WHERE lecturer_id = ?");
    $settings_stmt->bind_param("i", $lecturer_id);
    $settings_stmt->execute();
    $settings_result = $settings_stmt->get_result();
    if ($settings_result->num_rows > 0) {
        $settings = $settings_result->fetch_assoc();
        $min_questions = $settings['min_questions'];
        $max_questions = $settings['max_questions'];
    }
    $settings_stmt->close();

    if ($lecturer_question_count < 15) {
        $message = "Your lecturer has not added enough questions for a quiz. Minimum 15 questions required. Current: " . $lecturer_question_count;
    } else {
        $quiz_available = true;
        
        // Determine number of questions for the quiz
        $num_questions_for_quiz = min($max_questions, max($min_questions, $lecturer_question_count));
        
        // Get questions from the lecturer, shuffled and limited
        $stmt = $conn->prepare("SELECT * FROM questions WHERE lecturer_id = ? ORDER BY RAND() LIMIT ?");
        $stmt->bind_param("ii", $lecturer_id, $num_questions_for_quiz);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $questions[] = $row;
            }
        }
        $stmt->close();

        // If for some reason fewer questions were fetched than intended (e.g., due to limit), update total
        $num_questions_for_quiz = count($questions);
        if ($num_questions_for_quiz == 0) {
            $quiz_available = false;
            $message = "No questions available for your quiz from your lecturer.";
        }
    }
} else {
    $message = "You are not assigned to a lecturer, or your role is not student.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $score = 0;
    $total_questions = count($questions);
    
    foreach ($questions as $question) {
        $qid = $question['id'];
        $selected = $_POST['question_'.$qid] ?? '';
        $correct = $question['correct_option'];
        
        if ($selected === $correct) {
            $score++;
        }
    }
    
    // Save attempt to database with enhanced structure
    $user_id = $current_user['id'];
    $started_at = date('Y-m-d H:i:s');
    $completed_at = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO quiz_attempts (user_id, score, total_questions, started_at, completed_at, is_completed) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("iiiss", $user_id, $score, $total_questions, $started_at, $completed_at);
    $stmt->execute();
    $attempt_id = $conn->insert_id;
    $stmt->close();
    
    // Redirect to result page
    $_SESSION['quiz_score'] = $score;
    $_SESSION['quiz_total'] = $total_questions;
    $_SESSION['quiz_questions'] = $questions;
    $_SESSION['quiz_answers'] = $_POST;
    
    header('Location: result.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz - QuizMaster Pro</title>
    <link rel="stylesheet" href="../src/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .question-container {
            display: none;
            animation: fadeIn 0.5s ease-in-out;
        }
        .question-container.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .option-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        .option-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .option-card.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.4);
        }
        .option-input {
            display: none;
        }
        .timer-warning {
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .progress-bar {
            transition: width 0.3s ease-in-out;
        }
        .quiz-gradient {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
                    <a href="../index.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                    <a href="dashboard.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
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

    <!-- Modern Quiz Header -->
    <div class="quiz-gradient py-16 mb-8">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl p-8">
                    <div class="flex flex-col lg:flex-row justify-between items-center gap-6">
                        <div class="text-center lg:text-left">
                            <h1 class="text-4xl font-bold text-gray-800 mb-2">Quiz Challenge</h1>
                            <p class="text-gray-600 text-lg">Test your knowledge and compete for the top spot!</p>
                        </div>

                        <!-- Timer and Progress -->
                        <div class="flex flex-col items-center space-y-4">
                            <div id="timer" class="bg-gradient-to-r from-red-500 to-pink-600 text-white px-8 py-4 rounded-2xl shadow-lg">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-clock text-2xl"></i>
                                    <div class="text-center">
                                        <div class="text-3xl font-bold" id="timer-display">15:00</div>
                                        <div class="text-sm opacity-90">Time Remaining</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-64 bg-gray-200 rounded-full h-3 shadow-inner">
                                <div id="progress-bar" class="progress-bar bg-gradient-to-r from-green-400 to-blue-500 h-3 rounded-full" style="width: 0%"></div>
                            </div>
                            <div class="text-sm text-gray-600">
                                <span id="current-question">0</span> of <span id="total-questions">0</span> questions
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 max-w-4xl">
        
        <?php if (!$quiz_available): ?>
            <div class="bg-white rounded-3xl shadow-xl p-12 text-center">
                <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-exclamation-triangle text-red-500 text-3xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Quiz Not Available</h2>
                <p class="text-gray-600 text-lg mb-8"><?= $message ?></p>
                <a href="dashboard.php" class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl hover:from-indigo-700 hover:to-purple-700 transition duration-200 transform hover:scale-105">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Dashboard
                </a>
            </div>
        <?php else: ?>
            <form id="quiz-form" action="quiz.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="question-container <?= $index === 0 ? 'active' : '' ?>" id="question-<?= $index+1 ?>">
                        <div class="bg-white rounded-3xl shadow-xl p-8 mb-8">
                            <!-- Question Header -->
                            <div class="flex items-center justify-between mb-8">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                        <?= $index + 1 ?>
                                    </div>
                                    <div>
                                        <h2 class="text-2xl font-bold text-gray-800">Question <?= $index+1 ?></h2>
                                        <p class="text-gray-500">Choose the best answer</p>
                                    </div>
                                </div>

                                <!-- Question Type Badge -->
                                <div class="bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-semibold">
                                    <i class="fas fa-list-ul mr-2"></i>Multiple Choice
                                </div>
                            </div>

                            <!-- Question Text -->
                            <div class="bg-gray-50 rounded-2xl p-6 mb-8">
                                <p class="text-xl text-gray-800 leading-relaxed"><?= htmlspecialchars($question['question']) ?></p>
                            </div>

                            <!-- Answer Options -->
                            <div class="grid gap-4">
                                <label class="option-card bg-white border-2 border-gray-200 rounded-2xl p-6 hover:border-indigo-300">
                                    <input type="radio" class="option-input" name="question_<?= $question['id'] ?>" value="a">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                            A
                                        </div>
                                        <span class="text-lg text-gray-800"><?= htmlspecialchars($question['option_a']) ?></span>
                                    </div>
                                </label>

                                <label class="option-card bg-white border-2 border-gray-200 rounded-2xl p-6 hover:border-indigo-300">
                                    <input type="radio" class="option-input" name="question_<?= $question['id'] ?>" value="b">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center text-white font-bold">
                                            B
                                        </div>
                                        <span class="text-lg text-gray-800"><?= htmlspecialchars($question['option_b']) ?></span>
                                    </div>
                                </label>

                                <label class="option-card bg-white border-2 border-gray-200 rounded-2xl p-6 hover:border-indigo-300">
                                    <input type="radio" class="option-input" name="question_<?= $question['id'] ?>" value="c">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-8 h-8 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-full flex items-center justify-center text-white font-bold">
                                            C
                                        </div>
                                        <span class="text-lg text-gray-800"><?= htmlspecialchars($question['option_c']) ?></span>
                                    </div>
                                </label>

                                <label class="option-card bg-white border-2 border-gray-200 rounded-2xl p-6 hover:border-indigo-300">
                                    <input type="radio" class="option-input" name="question_<?= $question['id'] ?>" value="d">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-8 h-8 bg-gradient-to-r from-red-500 to-red-600 rounded-full flex items-center justify-center text-white font-bold">
                                            D
                                        </div>
                                        <span class="text-lg text-gray-800"><?= htmlspecialchars($question['option_d']) ?></span>
                                    </div>
                                </label>
                            </div>

                            <!-- Navigation Buttons -->
                            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                                <button type="button" class="prev-btn flex items-center px-6 py-3 bg-gray-100 text-gray-700 rounded-2xl hover:bg-gray-200 transition duration-200 <?= $index === 0 ? 'invisible' : '' ?>">
                                    <i class="fas fa-chevron-left mr-2"></i>
                                    Previous
                                </button>

                                <div class="flex items-center space-x-4">
                                    <!-- Question Indicator -->
                                    <div class="flex space-x-2">
                                        <?php for ($i = 0; $i < count($questions); $i++): ?>
                                            <div class="w-3 h-3 rounded-full <?= $i === $index ? 'bg-indigo-600' : 'bg-gray-300' ?>"></div>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <?php if ($index === count($questions) - 1): ?>
                                    <button type="submit" class="flex items-center px-8 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-2xl hover:from-green-700 hover:to-green-800 transition duration-200 transform hover:scale-105 shadow-lg">
                                        <i class="fas fa-check mr-2"></i>
                                        Submit Quiz
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="next-btn flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl hover:from-indigo-700 hover:to-purple-700 transition duration-200 transform hover:scale-105">
                                        Next
                                        <i class="fas fa-chevron-right ml-2"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </form>
        <?php endif; ?>
    </div>

    <script>
        // Only initialize timer and question navigation if quiz is available
        <?php if ($quiz_available): ?>
            // Enhanced Timer functionality
            const timerDisplay = document.getElementById('timer-display');
            const timerContainer = document.getElementById('timer');
            const progressBar = document.getElementById('progress-bar');
            const currentQuestionSpan = document.getElementById('current-question');
            const totalQuestionsSpan = document.getElementById('total-questions');

            let timeLeft = <?= $quiz_duration ?>;
            const totalTime = <?= $quiz_duration ?>;
            const totalQuestions = <?= count($questions) ?>;
            let currentQuestionIndex = 0;

            // Initialize display
            totalQuestionsSpan.textContent = totalQuestions;
            updateQuestionProgress();

            function updateTimer() {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                // Update timer color based on time remaining
                if (timeLeft <= 60) {
                    timerContainer.className = 'bg-gradient-to-r from-red-600 to-red-700 text-white px-8 py-4 rounded-2xl shadow-lg timer-warning';
                } else if (timeLeft <= 300) {
                    timerContainer.className = 'bg-gradient-to-r from-orange-500 to-red-600 text-white px-8 py-4 rounded-2xl shadow-lg';
                }

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    showTimeUpModal();
                    setTimeout(() => {
                        document.getElementById('quiz-form').submit();
                    }, 3000);
                }

                timeLeft--;
            }

            function showTimeUpModal() {
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                modal.innerHTML = `
                    <div class="bg-white rounded-3xl p-8 text-center max-w-md mx-4 shadow-2xl">
                        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-clock text-red-500 text-3xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">Time's Up!</h2>
                        <p class="text-gray-600 mb-4">Your quiz will be submitted automatically.</p>
                        <div class="animate-spin w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full mx-auto"></div>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            function updateQuestionProgress() {
                currentQuestionSpan.textContent = currentQuestionIndex + 1;
                const progressPercentage = ((currentQuestionIndex + 1) / totalQuestions) * 100;
                progressBar.style.width = progressPercentage + '%';
            }

            const timerInterval = setInterval(updateTimer, 1000);
            updateTimer();

            // Enhanced Question navigation
            const questions = document.querySelectorAll('.question-container');

            function showQuestion(index) {
                questions.forEach(q => q.classList.remove('active'));
                questions[index].classList.add('active');
                currentQuestionIndex = index;
                updateQuestionProgress();

                // Scroll to top smoothly
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            document.querySelectorAll('.next-btn').forEach((button, index) => {
                button.addEventListener('click', () => {
                    if (currentQuestionIndex < totalQuestions - 1) {
                        showQuestion(currentQuestionIndex + 1);
                    }
                });
            });

            document.querySelectorAll('.prev-btn').forEach((button, index) => {
                button.addEventListener('click', () => {
                    if (currentQuestionIndex > 0) {
                        showQuestion(currentQuestionIndex - 1);
                    }
                });
            });

            // Enhanced Option selection with animations
            document.querySelectorAll('.option-card').forEach(card => {
                card.addEventListener('click', () => {
                    // Remove selection from other options in this question
                    const questionContainer = card.closest('.question-container');
                    questionContainer.querySelectorAll('.option-card').forEach(opt => {
                        opt.classList.remove('selected');
                    });

                    // Select clicked option with animation
                    card.classList.add('selected');

                    // Check the radio button
                    const radio = card.querySelector('input[type="radio"]');
                    radio.checked = true;

                    // Add a subtle success animation
                    card.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        card.style.transform = '';
                    }, 150);
                });
            });

            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowRight' && currentQuestionIndex < totalQuestions - 1) {
                    showQuestion(currentQuestionIndex + 1);
                } else if (e.key === 'ArrowLeft' && currentQuestionIndex > 0) {
                    showQuestion(currentQuestionIndex - 1);
                } else if (e.key >= '1' && e.key <= '4') {
                    const optionIndex = parseInt(e.key) - 1;
                    const currentQuestionElement = questions[currentQuestionIndex];
                    const options = currentQuestionElement.querySelectorAll('.option-card');
                    if (options[optionIndex]) {
                        options[optionIndex].click();
                    }
                }
            });

            // Auto-save functionality (optional)
            let autoSaveInterval = setInterval(() => {
                const formData = new FormData(document.getElementById('quiz-form'));
                // You can implement auto-save to localStorage here
                console.log('Auto-saving progress...');
            }, 30000); // Save every 30 seconds

            // Warn before leaving page
            window.addEventListener('beforeunload', (e) => {
                e.preventDefault();
                e.returnValue = 'Are you sure you want to leave? Your progress will be lost.';
            });

            // Clear warning when submitting
            document.getElementById('quiz-form').addEventListener('submit', () => {
                clearInterval(autoSaveInterval);
                window.removeEventListener('beforeunload', () => {});
            });

        <?php endif; ?>
    </script>
</body>
</html>
