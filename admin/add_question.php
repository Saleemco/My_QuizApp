<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("INSERT INTO questions 
        (question_text, option_a, option_b, option_c, option_d, correct_option, category, difficulty) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", 
        $_POST['question_text'], $_POST['option_a'], $_POST['option_b'], 
        $_POST['option_c'], $_POST['option_d'], $_POST['correct_option'], 
        $_POST['category'], $_POST['difficulty']
    );
    $stmt->execute();
}
