$quiz_id = $_GET['quiz_id'];

$stmt = $conn->prepare("
    SELECT q.* 
    FROM questions q
    INNER JOIN quiz_questions qq ON q.id = qq.question_id
    WHERE qq.quiz_id = ?
");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
$questions = $result->fetch_all(MYSQLI_ASSOC);
