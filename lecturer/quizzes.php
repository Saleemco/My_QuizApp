<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Require lecturer access
requireLecturer();

$errors = [];
$success = '';
$lecturer_id = $_SESSION['user_id'];

// Handle form submissions
if (isset($_POST['create_quiz'])) {
    $title = trim($_POST['title']);
    $time_limit = (int)$_POST['time_limit'];
    $question_ids = $_POST['questions'] ?? [];

    // Validation
    if (empty($title)) $errors[] = 'Quiz title is required';
    if ($time_limit < 1) $errors[] = 'Time limit must be at least 1 minute';
    if (count($question_ids) < 1) $errors[] = 'Select at least one question';

    if (empty($errors)) {
        $created_by = $lecturer_id;
        $stmt = $conn->prepare("INSERT INTO quizzes (title, time_limit, created_by) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $title, $time_limit, $created_by);
        
        if ($stmt->execute()) {
            $quiz_id = $stmt->insert_id;
            
            // Add questions to quiz
            foreach ($question_ids as $question_id) {
                $stmt2 = $conn->prepare("INSERT INTO quiz_questions (quiz_id, question_id) VALUES (?, ?)");
                $stmt2->bind_param("ii", $quiz_id, $question_id);
                $stmt2->execute();
                $stmt2->close();
            }
            
            $success = 'Quiz created successfully!';
        } else {
            $errors[] = 'Failed to create quiz: ' . $conn->error;
        }
        $stmt->close();
    }
}

if (isset($_POST['delete_quiz'])) {
    $id = $_POST['quiz_id'];
    
    // Delete quiz questions first
    $stmt = $conn->prepare("DELETE FROM quiz_questions WHERE quiz_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    
    // Delete quiz
    $stmt = $conn->prepare("DELETE FROM quizzes WHERE id = ? AND created_by = ?");
    $stmt->bind_param("ii", $id, $lecturer_id);
    $stmt->execute();
    $stmt->close();
    
    $success = 'Quiz deleted successfully!';
}

// Get questions created by the lecturer
$questions = [];
$stmt = $conn->prepare("SELECT * FROM questions WHERE lecturer_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
}
$stmt->close();

// Get quizzes created by the lecturer
$quizzes = [];
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE created_by = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $quizzes[] = $row;
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quizzes - Quiz App</title>
    <link rel="stylesheet" href="../src/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen font-['Inter']">
    <!-- Display flash messages -->
    <?php flash('success'); ?>
    <?php flash('error'); ?>

    <div class="flex">

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Quizzes</h1>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Create Quiz Form -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Create New Quiz</h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <?php foreach ($errors as $error): ?>
                                <p><?= $error ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            <?= $success ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="quizzes.php" method="POST">
                        <div class="mb-4">
                            <label for="title" class="block text-gray-700 font-medium mb-2">Quiz Title</label>
                            <input type="text" id="title" name="title" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="time_limit" class="block text-gray-700 font-medium mb-2">Time Limit (minutes)</label>
                            <input type="number" id="time_limit" name="time_limit" min="1" value="15" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 font-medium mb-2">Select Questions</label>
                            <div class="border rounded-lg p-4 max-h-96 overflow-y-auto">
                                <?php foreach ($questions as $question): ?>
                                    <div class="mb-2">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="questions[]" value="<?= $question['id'] ?>" class="form-checkbox text-indigo-600">
                                            <span class="ml-2"><?= substr($question['question'], 0, 80) . (strlen($question['question']) > 80 ? '...' : '') ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <button type="submit" name="create_quiz" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition duration-200">
                            Create Quiz
                        </button>
                    </form>
                </div>
                
                <!-- Quiz List -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Existing Quizzes</h2>
                    
                    <?php if (empty($quizzes)): ?>
                        <p class="text-gray-600">No quizzes found.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="py-2 px-4 border-b text-left">ID</th>
                                        <th class="py-2 px-4 border-b text-left">Title</th>
                                        <th class="py-2 px-4 border-b text-left">Time Limit</th>
                                        <th class="py-2 px-4 border-b">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($quizzes as $quiz): ?>
                                        <tr>
                                            <td class="py-2 px-4 border-b"><?= $quiz['id'] ?></td>
                                            <td class="py-2 px-4 border-b"><?= htmlspecialchars($quiz['title']) ?></td>
                                            <td class="py-2 px-4 border-b"><?= $quiz['time_limit'] ?> minutes</td>
                                            <td class="py-2 px-4 border-b text-center">
                                                <form action="quizzes.php" method="POST" class="inline-block">
                                                    <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                                                    <button type="submit" name="delete_quiz" class="text-red-600 hover:text-red-800">
                                                        Delete
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
    </div>
</body>
</html>
