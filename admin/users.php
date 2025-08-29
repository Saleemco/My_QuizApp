<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Require admin access
requireAdmin();

$errors = [];
$success = '';

// Handle form submissions
if (isset($_POST['create_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $matric_number = $_POST['matric_number'] ?? null;
    $lecturer_id = !empty($_POST['lecturer_id']) ? $_POST['lecturer_id'] : null;

    // Validation
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($password)) $errors[] = 'Password is required';
    
    if ($role === 'student') {
        if (empty($matric_number)) $errors[] = 'Matric number is required for students';
        if (empty($lecturer_id)) $errors[] = 'Please select a lecturer for the student';
        // Email is optional for students, they can login with matric number
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
    } else {
        // Email is required for admin/lecturer
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
    
//     if (empty($email)) {
//     $errors[] = "Email is required.";
// } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
//     $errors[] = "Invalid email format.";
// }


//     if (empty($errors)) {
//         $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
//         $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, matric_number, lecturer_id) VALUES (?, ?, ?, ?, ?, ?)");
//         $stmt->bind_param("sssssi", $name, $email, $hashed_password, $role, $matric_number, $lecturer_id);
        
//         if ($stmt->execute()) {
//             $success = 'User created successfully!';
//         } else {
//             $errors[] = 'Error creating user: ' . $conn->error;
//         }
//         $stmt->close();
//     }
//     if (empty($errors)) {
//     $hashed_password = password_hash($password, PASSWORD_DEFAULT);

//     // If lecturer, set matric_number = NULL
//     if ($role === 'lecturer') {
//         $matric_number = NULL;
//     }

//     $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, matric_number, lecturer_id) VALUES (?, ?, ?, ?, ?, ?)");
//     $stmt->bind_param("sssssi", $name, $email, $hashed_password, $role, $matric_number, $lecturer_id);

//     if ($stmt->execute()) {
//         $success = 'User created successfully!';
//     } else {
//         $errors[] = 'Error creating user: ' . $conn->error;
//     }
//     $stmt->close();
// }
if (empty($errors)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // If lecturer, set matric_number = NULL
    if ($role === 'lecturer') {
        $matric_number = NULL;
    }

    // If student and email is empty, set email to NULL
    if ($role === 'student' && empty($email)) {
        $email = NULL;
    }

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, matric_number, lecturer_id) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $name, $email, $hashed_password, $role, $matric_number, $lecturer_id);

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
    SELECT u.*, l.name as lecturer_name 
    FROM users u 
    LEFT JOIN users l ON u.lecturer_id = l.id 
    ORDER BY u.role, u.name
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Get all lecturers for dropdown
$lecturers = [];
$result = $conn->query("SELECT id, name FROM users WHERE role = 'lecturer' ORDER BY name");
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
    <style>
        .admin-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.25);
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
    <div class="admin-gradient py-12 mb-8">
        <div class="container mx-auto px-4 text-center">
            <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-users text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-white mb-4">User Management</h1>
            <p class="text-xl text-white/90">Manage students, lecturers, and administrators</p>
        </div>
    </div>

    <div class="container mx-auto px-4 max-w-7xl pb-16">
        <!-- Action Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">All Users</h2>
                <p class="text-gray-600">Manage system users and their roles</p>
            </div>
            <button onclick="openCreateModal()" class="mt-4 sm:mt-0 bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-3 rounded-2xl hover:from-indigo-700 hover:to-purple-700 transition duration-200 transform hover:scale-105 shadow-lg">
                <i class="fas fa-plus mr-2"></i>Create New User
            </button>
        </div>
        
        <!-- Error/Success Messages -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <div>
                        <?php foreach ($errors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-2xl mb-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <p><?= htmlspecialchars($success) ?></p>
                </div>
            </div>
        <?php endif; ?>

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
                                <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($user['name']) ?></h3>
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
                                <button onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>')" 
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

    <!-- Create User Modal -->
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Create New User</h2>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="users.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Full Name</label>
                    <input type="text" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Email (Optional for students)</label>
                    <input type="email" name="email" class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Role</label>
                    <select name="role" id="createRole" onchange="toggleCreateFields()" class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="student">Student</option>
                        <option value="lecturer">Lecturer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="mb-4" id="createMatricField">
                    <label class="block text-gray-700 font-semibold mb-2">Matric Number</label>
                    <input type="text" name="matric_number" id="createMatricNumber" class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="mb-6" id="createLecturerField">
                    <label class="block text-gray-700 font-semibold mb-2">Assign to Lecturer</label>
                    <select name="lecturer_id" id="createLecturerId" class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Lecturer</option>
                        <?php foreach ($lecturers as $lecturer): ?>
                            <option value="<?= $lecturer['id'] ?>"><?= htmlspecialchars($lecturer['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex space-x-4">
                    <button type="button" onclick="closeCreateModal()" class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-2xl font-semibold hover:bg-gray-300 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" name="create_user" class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-2xl font-semibold hover:from-indigo-700 hover:to-purple-700 transition duration-200">
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Edit User</h2>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="users.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="user_id" id="editUserId">

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Role</label>
                    <select name="role" id="editRole" onchange="toggleEditFields()" class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="student">Student</option>
                        <option value="lecturer">Lecturer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="mb-4" id="editMatricField">
                    <label class="block text-gray-700 font-semibold mb-2">Matric Number</label>
                    <input type="text" name="matric_number" id="editMatricNumber" class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="mb-6" id="editLecturerField">
                    <label class="block text-gray-700 font-semibold mb-2">Assign to Lecturer</label>
                    <select name="lecturer_id" id="editLecturerId" class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Lecturer</option>
                        <?php foreach ($lecturers as $lecturer): ?>
                            <option value="<?= $lecturer['id'] ?>"><?= htmlspecialchars($lecturer['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex space-x-4">
                    <button type="button" onclick="closeEditModal()" class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-2xl font-semibold hover:bg-gray-300 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" name="update_user" class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-2xl font-semibold hover:from-indigo-700 hover:to-purple-700 transition duration-200">
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Form -->
    <form id="deleteForm" action="users.php" method="POST" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        <input type="hidden" name="user_id" id="deleteUserId">
        <input type="hidden" name="delete_user" value="1">
    </form>

    <script>
        // Modal functions
        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
            toggleCreateFields();
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        function openEditModal(userId, role, lecturerId, matricNumber) {
            document.getElementById('editUserId').value = userId;
            document.getElementById('editRole').value = role;
            document.getElementById('editMatricNumber').value = matricNumber;
            document.getElementById('editLecturerId').value = lecturerId || '';
            document.getElementById('editModal').classList.remove('hidden');
            toggleEditFields();
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function toggleCreateFields() {
            const role = document.getElementById('createRole').value;
            const matricField = document.getElementById('createMatricField');
            const lecturerField = document.getElementById('createLecturerField');

            if (role === 'student') {
                matricField.style.display = 'block';
                lecturerField.style.display = 'block';
                document.getElementById('createMatricNumber').required = true;
                document.getElementById('createLecturerId').required = true;
            } else {
                matricField.style.display = 'none';
                lecturerField.style.display = 'none';
                document.getElementById('createMatricNumber').required = false;
                document.getElementById('createLecturerId').required = false;
            }
        }

        function toggleEditFields() {
            const role = document.getElementById('editRole').value;
            const matricField = document.getElementById('editMatricField');
            const lecturerField = document.getElementById('editLecturerField');

            if (role === 'student') {
                matricField.style.display = 'block';
                lecturerField.style.display = 'block';
            } else {
                matricField.style.display = 'none';
                lecturerField.style.display = 'none';
            }
        }

        function toggleDropdown(userId) {
            const dropdown = document.getElementById('dropdown-' + userId);
            const allDropdowns = document.querySelectorAll('[id^="dropdown-"]');

            // Close all other dropdowns
            allDropdowns.forEach(d => {
                if (d.id !== 'dropdown-' + userId) {
                    d.classList.add('hidden');
                }
            });

            // Toggle current dropdown
            dropdown.classList.toggle('hidden');
        }

        function deleteUser(userId, userName) {
            if (confirm('Are you sure you want to delete user "' + userName + '"? This action cannot be undone.')) {
                document.getElementById('deleteUserId').value = userId;
                document.getElementById('deleteForm').submit();
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('[onclick*="toggleDropdown"]')) {
                const allDropdowns = document.querySelectorAll('[id^="dropdown-"]');
                allDropdowns.forEach(d => d.classList.add('hidden'));
            }
        });

        // Initialize field visibility on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleCreateFields();
        });
    </script>
</body>
</html>
