<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Require lecturer access
requireLecturer();

$errors = [];
$success = '';
$current_user = getCurrentUser();
$lecturer_id = $current_user['id'];

if (isset($_POST['add_question'])) {
    $question = trim($_POST['question']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct_option = $_POST['correct_option'];

    // Validation
    if (empty($question)) $errors[] = 'Question is required';
    if (empty($option_a)) $errors[] = 'Option A is required';
    if (empty($option_b)) $errors[] = 'Option B is required';
    if (empty($correct_option)) $errors[] = 'Correct option is required';

    if (empty($errors)) {
        // Check current question count for the lecturer
        $count_stmt = $conn->prepare("SELECT COUNT(*) FROM questions WHERE lecturer_id = ?");
        $count_stmt->bind_param("i", $lecturer_id);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result()->fetch_row();
        $current_question_count = $count_result[0];
        $count_stmt->close();

        if ($current_question_count >= 50) {
            $errors[] = 'You have reached the maximum limit of 50 questions.';
        } else {
            $stmt = $conn->prepare("INSERT INTO questions (question, option_a, option_b, option_c, option_d, correct_option, lecturer_id) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssi", $question, $option_a, $option_b, $option_c, $option_d, $correct_option, $lecturer_id);
        
            if ($stmt->execute()) {
                $success = 'Question added successfully!';
            } else {
                $errors[] = 'Failed to add question: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}

if (isset($_POST['delete_question'])) {
    $id = $_POST['question_id'];
    $stmt = $conn->prepare("DELETE FROM questions WHERE id = ? AND lecturer_id = ?");
    $stmt->bind_param("ii", $id, $lecturer_id);
    $stmt->execute();
    $stmt->close();
    $success = 'Question deleted successfully!';
}

// Get questions for the current lecturer
$questions = [];
$sql = "SELECT * FROM questions WHERE lecturer_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();


if ($result) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
}

// Fetch quiz settings for the current lecturer
$quiz_settings = ['min_questions' => 10, 'max_questions' => 30]; // Default values
$stmt = $conn->prepare("SELECT min_questions, max_questions FROM quiz_settings WHERE lecturer_id = ?");
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $quiz_settings = $result->fetch_assoc();
}
$stmt->close();

// Handle quiz settings update
if (isset($_POST['update_quiz_settings'])) {
    $min_questions = (int)$_POST['min_questions'];
    $max_questions = (int)$_POST['max_questions'];

    // Basic validation for quiz settings
    if ($min_questions < 10 || $min_questions > 30 || $max_questions < 10 || $max_questions > 30 || $min_questions > $max_questions) {
        $errors[] = 'Minimum and maximum questions must be between 10 and 30, and minimum cannot exceed maximum.';
    } else {
        $stmt = $conn->prepare("INSERT INTO quiz_settings (lecturer_id, min_questions, max_questions) VALUES (?, ?, ?)
                                ON DUPLICATE KEY UPDATE min_questions = ?, max_questions = ?");
        $stmt->bind_param("iiiii", $lecturer_id, $min_questions, $max_questions, $min_questions, $max_questions);
        if ($stmt->execute()) {
            $success = 'Quiz settings updated successfully!';
            $quiz_settings['min_questions'] = $min_questions;
            $quiz_settings['max_questions'] = $max_questions;
        } else {
            $errors[] = 'Failed to update quiz settings: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions - Quiz App</title>
    <link rel="stylesheet" href="../src/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-input {
            transition: all 0.3s ease;
        }
        .form-input:focus {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        .action-btn {
            transition: all 0.2s ease;
        }
        .action-btn:hover {
            transform: translateY(-1px);
        }
        .question-row:hover {
            background-color: #f9fafb;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-['Inter']">
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
                    <a href="students.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-user-graduate"></i>
                        <span>Students</span>
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

    <!-- Display flash messages -->
    <?php flash('success'); ?>
    <?php flash('error'); ?>

    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 py-16 mb-8">
        <div class="container mx-auto px-4 text-center">
            <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-question-circle text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-white mb-4">Question Management</h1>
            <p class="text-xl text-white/90">Create and manage quiz questions for your students</p>
        </div>
    </div>

    <div class="container mx-auto px-4 max-w-6xl pb-16">

            <!-- Content -->
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Add Question Form -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h2 class="text-lg font-semibold text-gray-800">Add New Question</h2>
                        </div>
                        <div class="p-6">
                            <?php if (!empty($errors)): ?>
                                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
                                    <?php foreach ($errors as $error): ?>
                                        <p class="flex items-center"><i class="fas fa-exclamation-circle mr-2"></i> <?= $error ?></p>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-600 text-sm">
                                    <p class="flex items-center"><i class="fas fa-check-circle mr-2"></i> <?= $success ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <form action="questions.php" method="POST">
                                <div class="mb-4">
                                    <label for="question" class="block text-sm font-medium text-gray-700 mb-2">Question Text</label>
                                    <textarea id="question" name="question" rows="3" class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required></textarea>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="option_a" class="block text-sm font-medium text-gray-700 mb-2">Option A</label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">A.</span>
                                            <input type="text" id="option_a" name="option_a" class="form-input pl-8 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="option_b" class="block text-sm font-medium text-gray-700 mb-2">Option B</label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">B.</span>
                                            <input type="text" id="option_b" name="option_b" class="form-input pl-8 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="option_c" class="block text-sm font-medium text-gray-700 mb-2">Option C</label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">C.</span>
                                            <input type="text" id="option_c" name="option_c" class="form-input pl-8 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="option_d" class="block text-sm font-medium text-gray-700 mb-2">Option D</label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">D.</span>
                                            <input type="text" id="option_d" name="option_d" class="form-input pl-8 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Correct Answer</label>
                                    <div class="flex space-x-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="correct_option" value="a" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" required>
                                            <span class="ml-2 text-gray-700">Option A</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="correct_option" value="b" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                            <span class="ml-2 text-gray-700">Option B</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="correct_option" value="c" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                            <span class="ml-2 text-gray-700">Option C</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="correct_option" value="d" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                            <span class="ml-2 text-gray-700">Option D</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <button type="submit" name="add_question" class="action-btn w-full bg-indigo-600 text-white py-2.5 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                                    <i class="fas fa-plus-circle mr-2"></i> Add Question
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Question List -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-800">Existing Questions</h2>
                                <span class="text-sm bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full"><?= count($questions) ?> total</span>
                            </div>
                        </div>
                        <div class="overflow-hidden">
                            <?php if (empty($questions)): ?>
                                <div class="p-6 text-center text-gray-500">
                                    <i class="fas fa-inbox text-3xl mb-2 text-gray-300"></i>
                                    <p>No questions found</p>
                                </div>
                            <?php else: ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Question</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correct</th>
                                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($questions as $q): ?>
                                                <tr class="question-row">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $q['id'] ?></td>
                                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="<?= htmlspecialchars($q['question']) ?>">
                                                        <?= htmlspecialchars(substr($q['question'], 0, 50) . (strlen($q['question']) > 50 ? '...' : '')) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            <?= strtoupper($q['correct_option']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                        <form action="questions.php" method="POST" class="inline-block">
                                                            <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                                                            <button type="submit" name="delete_question" class="action-btn text-red-600 hover:text-red-900 focus:outline-none">
                                                                <i class="fas fa-trash-alt mr-1"></i> Delete
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-800">Quiz Settings for Your Students</h2>
                    </div>
                    <div class="p-6">
                        <form action="questions.php" method="POST">
                            <input type="hidden" name="update_quiz_settings" value="1">
                            <div class="mb-4">
                                <label for="min_questions" class="block text-sm font-medium text-gray-700 mb-2">Minimum Questions for Quiz (10-30)</label>
                                <input type="number" id="min_questions" name="min_questions" min="10" max="30" value="<?= htmlspecialchars($quiz_settings['min_questions']) ?>" class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                            </div>
                            <div class="mb-6">
                                <label for="max_questions" class="block text-sm font-medium text-gray-700 mb-2">Maximum Questions for Quiz (10-30)</label>
                                <input type="number" id="max_questions" name="max_questions" min="10" max="30" value="<?= htmlspecialchars($quiz_settings['max_questions']) ?>" class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                            </div>
                            <button type="submit" class="action-btn w-full bg-indigo-600 text-white py-2.5 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                                Update Quiz Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
