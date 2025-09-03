<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Require admin access
requireAdmin();

$errors = [];
$success = '';

// Handle form submissions
if (isset($_POST['create_user'])) {
    $fullname = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $matric_number = $_POST['matric_number'] ?? null;
    $lecturer_id = !empty($_POST['lecturer_id']) ? $_POST['lecturer_id'] : null;

    // Validation
    if (empty($fullname)) $errors[] = 'Full Name is required';
    if (empty($password)) $errors[] = 'Password is required';
    
    if ($role === 'student') {
        if (empty($matric_number)) $errors[] = 'Matric number is required for students';
        if (empty($lecturer_id)) $errors[] = 'Please select a lecturer for the student';
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
    } else {
        if (empty($email)) $errors[] = 'Email is required';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
    }

    // Check if email or matric number exists
    if (!empty($email)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email already registered';
        }
        $stmt->close();
    }
    if (!empty($matric_number)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE matric_number = ?");
        $stmt->bind_param("s", $matric_number);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Matric number already registered';
        }
        $stmt->close();
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        if ($role === 'lecturer') {
            $matric_number = NULL;
        }
        if ($role === 'student' && empty($email)) {
            $email = NULL;
        }

        $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role, matric_number, lecturer_id) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $fullname, $email, $hashed_password, $role, $matric_number, $lecturer_id);

        if ($stmt->execute()) {
            $success = 'User created successfully!';
        } else {
            $errors[] = 'Error creating user: ' . $conn->error;
        }
        $stmt->close();
    }
}

if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];
    $matric_number = $_POST['matric_number'] ?? null;
    $lecturer_id = !empty($_POST['lecturer_id']) ? $_POST['lecturer_id'] : null;

    $stmt = $conn->prepare("UPDATE users SET role = ?, matric_number = ?, lecturer_id = ? WHERE id = ?");
    $stmt->bind_param("ssii", $role, $matric_number, $lecturer_id, $user_id);
    
    if ($stmt->execute()) {
        $success = 'User updated successfully!';
    } else {
        $errors[] = 'Error updating user: ' . $conn->error;
    }
    $stmt->close();
}

if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $success = 'User deleted successfully!';
    } else {
        $errors[] = 'Error deleting user: ' . $conn->error;
    }
    $stmt->close();
}

// Get all users with lecturer names
$users = [];
$result = $conn->query("
    SELECT u.*, l.fullname AS lecturer_name
    FROM users u 
    LEFT JOIN users l ON u.lecturer_id = l.id 
    ORDER BY u.role, u.fullname
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Get all lecturers for dropdown
$lecturers = [];
$result = $conn->query("SELECT id, fullname FROM users WHERE role = 'lecturer' ORDER BY fullname");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $lecturers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - QuizMaster Pro</title>
    <link rel="stylesheet" href="../src/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen font-['Inter'] antialiased">

    <!-- Display flash messages -->
    <?php flash('success'); ?>
    <?php flash('error'); ?>

    <div class="container mx-auto px-4 max-w-7xl pb-16">
        <!-- Users Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($users as $user): ?>
                <div class="bg-white rounded-3xl shadow-xl p-6 card-hover">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 <?= $user['role'] === 'admin' ? 'bg-gradient-to-r from-red-500 to-red-600' : ($user['role'] === 'lecturer' ? 'bg-gradient-to-r from-blue-500 to-blue-600' : 'bg-gradient-to-r from-green-500 to-green-600') ?> rounded-full flex items-center justify-center text-white font-bold">
                                <i class="fas <?= $user['role'] === 'admin' ? 'fa-crown' : ($user['role'] === 'lecturer' ? 'fa-chalkboard-teacher' : 'fa-user-graduate') ?>"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($user['fullname']) ?></h3>
                                <span class="text-sm <?= $user['role'] === 'admin' ? 'text-red-600 bg-red-100' : ($user['role'] === 'lecturer' ? 'text-blue-600 bg-blue-100' : 'text-green-600 bg-green-100') ?> px-2 py-1 rounded-full font-semibold">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Actions Dropdown -->
                        <div class="relative">
                            <button onclick="toggleDropdown(<?= $user['id'] ?>)" class="text-gray-400 hover:text-gray-600 transition duration-200">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div id="dropdown-<?= $user['id'] ?>" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-xl z-10 border">
                                <button onclick="openEditModal(<?= $user['id'] ?>, '<?= $user['role'] ?>', <?= $user['lecturer_id'] ?? 'null' ?>, '<?= htmlspecialchars($user['matric_number'] ?? '') ?>')" 
                                        class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 rounded-t-2xl">
                                    <i class="fas fa-edit mr-2"></i>Edit User
                                </button>
                                <button onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['fullname']) ?>')" 
                                        class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 rounded-b-2xl">
                                    <i class="fas fa-trash mr-2"></i>Delete User
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Details -->
                    <div class="space-y-3">
                        <?php if ($user['email']): ?>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-envelope w-4 mr-3"></i>
                                <span class="text-sm"><?= htmlspecialchars($user['email']) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($user['matric_number']): ?>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-id-card w-4 mr-3"></i>
                                <span class="text-sm"><?= htmlspecialchars($user['matric_number']) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($user['lecturer_name']): ?>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-user-tie w-4 mr-3"></i>
                                <span class="text-sm">Lecturer: <?= htmlspecialchars($user['lecturer_name']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Lecturer Dropdown Fix -->
    <select name="lecturer_id" id="createLecturerId">
        <option value="">Select Lecturer</option>
        <?php foreach ($lecturers as $lecturer): ?>
            <option value="<?= $lecturer['id'] ?>"><?= htmlspecialchars($lecturer['fullname']) ?></option>
        <?php endforeach; ?>
    </select>

    <script>
        function toggleDropdown(userId) {
            const dropdown = document.getElementById('dropdown-' + userId);
            document.querySelectorAll('[id^="dropdown-"]').forEach(d => {
                if (d.id !== dropdown.id) d.classList.add('hidden');
            });
            dropdown.classList.toggle('hidden');
        }

        function deleteUser(userId, userName) {
            if (confirm('Are you sure you want to delete user "' + userName + '"?')) {
                document.getElementById('deleteUserId').value = userId;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>
