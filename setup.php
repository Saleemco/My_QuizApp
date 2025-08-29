<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";

// Create connection without specifying database
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS quiz_app";
if ($conn->query($sql)) {
    echo "Database created successfully or already exists.<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db("quiz_app");

// Create users table with enhanced structure
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    matric_number VARCHAR(50) UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'lecturer', 'student') DEFAULT 'student',
    lecturer_id INT,
    profile_image VARCHAR(255),
    phone VARCHAR(20),
    department VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lecturer_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_matric (matric_number),
    INDEX idx_role (role),
    INDEX idx_lecturer (lecturer_id)
)";
if ($conn->query($sql)) {
    echo "Users table created successfully.<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Create categories table
$sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    color VARCHAR(7) DEFAULT '#3B82F6',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
)";
if ($conn->query($sql)) {
    echo "Categories table created successfully.<br>";
} else {
    echo "Error creating categories table: " . $conn->error . "<br>";
}

// Create questions table with enhanced structure
$sql = "CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    option_a VARCHAR(500) NOT NULL,
    option_b VARCHAR(500) NOT NULL,
    option_c VARCHAR(500),
    option_d VARCHAR(500),
    correct_option ENUM('a', 'b', 'c', 'd') NOT NULL,
    explanation TEXT,
    difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    question_type ENUM('multiple_choice', 'true_false', 'fill_blank') DEFAULT 'multiple_choice',
    points INT DEFAULT 1,
    category_id INT,
    lecturer_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lecturer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_lecturer (lecturer_id),
    INDEX idx_category (category_id),
    INDEX idx_difficulty (difficulty),
    INDEX idx_active (is_active)
)";
if ($conn->query($sql)) {
    echo "Questions table created successfully.<br>";
} else {
    echo "Error creating questions table: " . $conn->error . "<br>";
}

// Create quizzes table with enhanced structure
$sql = "CREATE TABLE IF NOT EXISTS quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    time_limit INT NOT NULL DEFAULT 900,
    max_attempts INT DEFAULT 1,
    passing_score INT DEFAULT 60,
    show_results BOOLEAN DEFAULT TRUE,
    randomize_questions BOOLEAN DEFAULT TRUE,
    category_id INT,
    difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    is_active BOOLEAN DEFAULT TRUE,
    start_date DATETIME,
    end_date DATETIME,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_created_by (created_by),
    INDEX idx_category (category_id),
    INDEX idx_active (is_active),
    INDEX idx_dates (start_date, end_date)
)";
if ($conn->query($sql)) {
    echo "Quizzes table created successfully.<br>";
} else {
    echo "Error creating quizzes table: " . $conn->error . "<br>";
}

// Create quiz_questions table
$sql = "CREATE TABLE IF NOT EXISTS quiz_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_id INT NOT NULL,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
)";
if ($conn->query($sql)) {
    echo "Quiz questions table created successfully.<br>";
} else {
    echo "Error creating quiz questions table: " . $conn->error . "<br>";
}

// Create quiz_attempts table with enhanced structure
$sql = "CREATE TABLE IF NOT EXISTS quiz_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quiz_id INT,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    percentage DECIMAL(5,2) GENERATED ALWAYS AS (ROUND((score / total_questions) * 100, 2)) STORED,
    time_taken INT, -- in seconds
    ip_address VARCHAR(45),
    user_agent TEXT,
    is_completed BOOLEAN DEFAULT TRUE,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_quiz (quiz_id),
    INDEX idx_score (score),
    INDEX idx_completed (is_completed)
)";
if ($conn->query($sql)) {
    echo "Quiz attempts table created successfully.<br>";
} else {
    echo "Error creating quiz attempts table: " . $conn->error . "<br>";
}

// Create quiz_responses table for detailed answer tracking
$sql = "CREATE TABLE IF NOT EXISTS quiz_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option ENUM('a', 'b', 'c', 'd'),
    is_correct BOOLEAN,
    time_spent INT, -- in seconds
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    INDEX idx_attempt (attempt_id),
    INDEX idx_question (question_id)
)";
if ($conn->query($sql)) {
    echo "Quiz responses table created successfully.<br>";
} else {
    echo "Error creating quiz responses table: " . $conn->error . "<br>";
}

// Create quiz_settings table for lecturers
$sql = "CREATE TABLE IF NOT EXISTS quiz_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lecturer_id INT NOT NULL UNIQUE,
    min_questions INT DEFAULT 10,
    max_questions INT DEFAULT 30,
    default_time_limit INT DEFAULT 900,
    allow_retakes BOOLEAN DEFAULT TRUE,
    show_correct_answers BOOLEAN DEFAULT TRUE,
    randomize_options BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lecturer_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql)) {
    echo "Quiz settings table created successfully.<br>";
} else {
    echo "Error creating quiz settings table: " . $conn->error . "<br>";
}

// Create notifications table
$sql = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read)
)";
if ($conn->query($sql)) {
    echo "Notifications table created successfully.<br>";
} else {
    echo "Error creating notifications table: " . $conn->error . "<br>";
}

// Create system_settings table
$sql = "CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_key (setting_key)
)";
if ($conn->query($sql)) {
    echo "System settings table created successfully.<br>";
} else {
    echo "Error creating system settings table: " . $conn->error . "<br>";
}

// Insert default admin user if not exists
$adminEmail = 'admin@example.com';
$adminPassword = password_hash('password', PASSWORD_DEFAULT); // Hash the password
$checkAdminSql = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($checkAdminSql);
$stmt->bind_param("s", $adminEmail);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    $insertAdminSql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')";
    $stmtInsert = $conn->prepare($insertAdminSql);
    $adminName = 'Admin User';
    $stmtInsert->bind_param("sss", $adminName, $adminEmail, $adminPassword);
    if ($stmtInsert->execute()) {
        echo "Default admin user created successfully (Email: admin@example.com, Password: password).<br>";
    } else {
        echo "Error creating default admin user: " . $stmtInsert->error . "<br>";
    }
    $stmtInsert->close();
} else {
    echo "Admin user already exists.<br>";
}
$stmt->close();

// Insert default categories
$default_categories = [
    ['Mathematics', 'Mathematical concepts and problem solving', '#EF4444'],
    ['Science', 'General science topics', '#10B981'],
    ['Computer Science', 'Programming and computer concepts', '#3B82F6'],
    ['English', 'Language and literature', '#8B5CF6'],
    ['History', 'Historical events and figures', '#F59E0B'],
    ['General Knowledge', 'Mixed topics and trivia', '#6B7280']
];

foreach ($default_categories as $category) {
    $checkCategorySql = "SELECT id FROM categories WHERE name = ?";
    $stmt = $conn->prepare($checkCategorySql);
    $stmt->bind_param("s", $category[0]);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        $insertCategorySql = "INSERT INTO categories (name, description, color) VALUES (?, ?, ?)";
        $stmtInsert = $conn->prepare($insertCategorySql);
        $stmtInsert->bind_param("sss", $category[0], $category[1], $category[2]);
        if ($stmtInsert->execute()) {
            echo "Category '{$category[0]}' created successfully.<br>";
        }
        $stmtInsert->close();
    }
    $stmt->close();
}

// Insert default system settings
$default_settings = [
    ['site_name', 'QuizMaster Pro', 'Name of the quiz application'],
    ['site_description', 'Advanced Quiz Management System', 'Description of the site'],
    ['max_quiz_attempts', '3', 'Maximum number of quiz attempts per user'],
    ['default_quiz_time', '900', 'Default quiz time in seconds (15 minutes)'],
    ['enable_notifications', '1', 'Enable system notifications'],
    ['maintenance_mode', '0', 'Enable maintenance mode']
];

foreach ($default_settings as $setting) {
    $checkSettingSql = "SELECT id FROM system_settings WHERE setting_key = ?";
    $stmt = $conn->prepare($checkSettingSql);
    $stmt->bind_param("s", $setting[0]);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        $insertSettingSql = "INSERT INTO system_settings (setting_key, setting_value, description) VALUES (?, ?, ?)";
        $stmtInsert = $conn->prepare($insertSettingSql);
        $stmtInsert->bind_param("sss", $setting[0], $setting[1], $setting[2]);
        if ($stmtInsert->execute()) {
            echo "Setting '{$setting[0]}' created successfully.<br>";
        }
        $stmtInsert->close();
    }
    $stmt->close();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Quiz App</title>
    <link rel="stylesheet" href="assets/css/output.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md text-center">
        <h1 class="text-2xl font-bold text-indigo-600 mb-4">Database Setup Complete</h1>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            Database tables have been successfully created!
        </div>
        <p class="mb-6">You can now use the Quiz Application.</p>
        <a href="index.php" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 inline-block">
            Go to Home Page
        </a>
    </div>
</body>
</html>