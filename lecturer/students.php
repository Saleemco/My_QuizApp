<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Require lecturer access
requireLecturer();

$lecturer_id = $_SESSION['user_id'];

// Get students for the current lecturer
$students = [];
$stmt = $conn->prepare("SELECT id, fullname, email, matric_number FROM users WHERE lecturer_id = ? AND role = 'student' ORDER BY fullname");
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Students - Quiz App</title>
    <link rel="stylesheet" href="../src/output.css">
</head>
<body class="bg-gray-100 min-h-screen font-['Inter']">
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
                    <a href="questions.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-question-circle"></i>
                        <span>Questions</span>
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
                <i class="fas fa-user-graduate text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-white mb-4">My Students</h1>
            <p class="text-xl text-white/90">Monitor and manage your students' progress</p>
        </div>
    </div>

    <div class="container mx-auto px-4 max-w-6xl pb-16">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matric Number</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($student['fullname']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($student['email'] ?? '') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($student['matric_number'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
