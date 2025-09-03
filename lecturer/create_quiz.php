<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

requireLecturer();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $lecturer_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO quizzes (lecturer_id, title) VALUES (?, ?)");
    $stmt->bind_param("is", $lecturer_id, $title);
    $stmt->execute();
    $quiz_id = $stmt->insert_id;

    // Add selected questions
    if (!empty($_POST['question_ids'])) {
        foreach ($_POST['question_ids'] as $qid) {
            $stmt = $conn->prepare("INSERT INTO quiz_questions (quiz_id, question_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $quiz_id, $qid);
            $stmt->execute();
        }
    }

    $_SESSION['flash_success'] = "Quiz created successfully!";
    header("Location: dashboard.php");
    exit;
}

// Fetch all available questions from the global bank
$result = $conn->query("SELECT * FROM questions ORDER BY created_at DESC");
$questions = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Quiz</title>
    <link rel="stylesheet" href="../src/output.css">
</head>
<body>
    <h1>Create Quiz</h1>
    <form method="post">
        <label>Quiz Title:</label>
        <input type="text" name="title" required><br><br>

        <h3>Select Questions:</h3>
        <?php foreach ($questions as $q): ?>
            <div>
                <input type="checkbox" name="question_ids[]" value="<?= $q['id'] ?>">
                <?= htmlspecialchars($q['question_text']) ?>
            </div>
        <?php endforeach; ?>

        <br>
        <button type="submit">Create Quiz</button>
    </form>
</body>
</html>
