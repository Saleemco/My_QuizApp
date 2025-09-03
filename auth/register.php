<?php
require_once __DIR__ . '/../includes/session.php'; // must come first (defines generateCSRFToken)
require_once __DIR__ . '/../includes/db.php';

// Ensure CSRF token exists
$csrf_token = generateCSRFToken();

// Redirect if already logged in
if (isLoggedIn()) {
    $role = $_SESSION['role'];
    if ($role === 'admin') {
        header('Location: ../admin/dashboard.php');
    } elseif ($role === 'lecturer') {
        header('Location: ../lecturer/dashboard.php');
    } elseif ($role === 'student') {
        header('Location: ../student/dashboard.php');
    }
    exit;
}

$message = "";

// Handle registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ Verify CSRF token first
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = "❌ Invalid CSRF token. Please refresh the page.";
    } else {
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        // $email    = mysqli_real_escape_string($conn, $_POST['email']);
        $email = isset($_POST['email']) && !empty(trim($_POST['email'])) 
    ? mysqli_real_escape_string($conn, $_POST['email']) 
    : null;

        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role     = mysqli_real_escape_string($conn, $_POST['role']);
        $matric   = isset($_POST['matric_number']) ? mysqli_real_escape_string($conn, $_POST['matric_number']) : null;
        $lecturer_id = ($role === 'student' && !empty($_POST['lecturer_id'])) 
                        ? (int) $_POST['lecturer_id'] 
                        : "NULL";

        // Validation
        if ($role === 'student' && empty($matric)) {
            $message = "⚠️ Matric Number is required for students.";
        } elseif ($role === 'student' && $lecturer_id === "NULL") {
            $message = "⚠️ Please select a lecturer.";
        } else {
            // Prevent duplicate email
            $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
            if (mysqli_num_rows($check) > 0) {
                $message = "⚠️ Email already exists!";
            } else {
                // Insert user
                $sql = "INSERT INTO users (fullname, email, password, role, matric_number, lecturer_id) 
                        VALUES ('$fullname', '$email', '$password', '$role', " . 
                        ($matric ? "'$matric'" : "NULL") . ", " . 
                        ($lecturer_id !== "NULL" ? $lecturer_id : "NULL") . ")";
                if (mysqli_query($conn, $sql)) {
                    $message = "✅ Registration successful! You can now log in.";
                } else {
                    $message = "❌ Error: " . mysqli_error($conn);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - QuizMaster Pro</title>
    <link rel="stylesheet" href="../src/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .register-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
    <script>
        function toggleMatricField() {
            const role = document.getElementById("role").value;
            document.getElementById("matric-field").style.display   = (role === "student") ? "block" : "none";
            document.getElementById("lecturer-field").style.display = (role === "student") ? "block" : "none";
        }
    </script>
</head>
<body class="register-gradient min-h-screen flex items-center justify-center p-4 font-['Inter'] antialiased">
    <div class="glass-effect p-10 rounded-3xl shadow-2xl w-full max-w-lg relative z-10">
        <div class="text-center mb-6">
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-user-plus text-white text-2xl"></i>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Create Account</h1>
            <p class="text-gray-600">Sign up as a Lecturer or Student</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 text-center text-red-600 font-semibold">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="" method="post" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div>
                <label class="block text-gray-700 font-medium">Full Name</label>
                <input type="text" name="fullname" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-gray-700 font-medium">Email</label>
                <input type="email" name="email" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-gray-700 font-medium">Password</label>
                <input type="password" name="password" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-gray-700 font-medium">Register As</label>
                <select id="role" name="role" onchange="toggleMatricField()" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500">
                    <option value="student">Student</option>
                    <option value="lecturer">Lecturer</option>
                </select>
            </div>

            <div id="matric-field" style="display:none;">
                <label class="block text-gray-700 font-medium">Matric Number</label>
                <input type="text" name="matric_number" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div id="lecturer-field" style="display:none;">
                <label class="block text-gray-700 font-medium">Select Lecturer</label>
                <select name="lecturer_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Choose Lecturer --</option>
                    <?php
                    $lecturers = mysqli_query($conn, "SELECT id, fullname FROM users WHERE role='lecturer'");
                    while ($lec = mysqli_fetch_assoc($lecturers)) {
                        echo "<option value='{$lec['id']}'>{$lec['fullname']}</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-xl font-semibold hover:from-indigo-700 hover:to-purple-700 transition">
                <i class="fas fa-user-plus mr-2"></i> Register
            </button>
        </form>

        <p class="mt-6 text-center text-gray-600">
            Already have an account?
            <a href="login.php" class="text-indigo-600 font-semibold hover:underline">Login here</a>
        </p>
    </div>

    <script>
        toggleMatricField();
    </script>
</body>
</html>
