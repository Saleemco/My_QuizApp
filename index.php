<?php
require_once __DIR__ . '/includes/session.php';

// Redirect logged-in users to their appropriate dashboard
if (isLoggedIn() && isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    if ($role === 'admin') {
        header('Location: admin/dashboard.php');
        exit;
    } elseif ($role === 'lecturer') {
        header('Location: lecturer/dashboard.php');
        exit;
    } elseif ($role === 'student') {
        header('Location: student/dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuizMaster Pro - Advanced Quiz Management System</title>
    <link rel="stylesheet" href="src/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
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

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <?php if (isLoggedIn()): ?>
                        <a href="student/quiz.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                            <i class="fas fa-play-circle"></i>
                            <span>Take Quiz</span>
                        </a>
                        <a href="student/history.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                            <i class="fas fa-history"></i>
                            <span>History</span>
                        </a>
                        <a href="leaderboard.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                            <i class="fas fa-trophy"></i>
                            <span>Leaderboard</span>
                        </a>
                        <?php if (isAdmin()): ?>
                            <a href="admin/dashboard.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                                <i class="fas fa-cog"></i>
                                <span>Admin</span>
                            </a>
                        <?php elseif (isLecturer()): ?>
                            <a href="lecturer/dashboard.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <span>Dashboard</span>
                            </a>
                        <?php endif; ?>
                        <a href="auth/logout.php" class="bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-2 rounded-full hover:from-red-600 hover:to-red-700 transition duration-200 transform hover:scale-105">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    <?php else: ?>
                        <a href="leaderboard.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200">
                            <i class="fas fa-trophy"></i>
                            <span>Leaderboard</span>
                        </a>
                        <a href="auth/login.php" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-2 rounded-full hover:from-indigo-700 hover:to-purple-700 transition duration-200 transform hover:scale-105">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <button class="md:hidden text-gray-700 hover:text-indigo-600" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <?php if (isLoggedIn()): ?>
                        <a href="student/quiz.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200 py-2">
                            <i class="fas fa-play-circle"></i>
                            <span>Take Quiz</span>
                        </a>
                        <a href="student/history.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200 py-2">
                            <i class="fas fa-history"></i>
                            <span>History</span>
                        </a>
                        <a href="leaderboard.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200 py-2">
                            <i class="fas fa-trophy"></i>
                            <span>Leaderboard</span>
                        </a>
                    <?php else: ?>
                        <a href="leaderboard.php" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600 transition duration-200 py-2">
                            <i class="fas fa-trophy"></i>
                            <span>Leaderboard</span>
                        </a>
                        <a href="auth/login.php" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-2 rounded-full text-center">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient relative overflow-hidden">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="absolute inset-0">
            <div class="absolute top-20 left-10 w-72 h-72 bg-white/10 rounded-full blur-3xl floating-animation"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-purple-300/20 rounded-full blur-3xl floating-animation" style="animation-delay: -3s;"></div>
        </div>

        <div class="relative container mx-auto px-4 py-24 lg:py-32">
            <div class="text-center text-white">
                <h1 class="text-5xl lg:text-7xl font-black mb-6 leading-tight">
                    Master Your
                    <span class="block bg-gradient-to-r from-yellow-300 to-orange-300 bg-clip-text text-transparent">
                        Knowledge
                    </span>
                </h1>
                <p class="text-xl lg:text-2xl text-white/90 max-w-4xl mx-auto mb-12 leading-relaxed">
                    Experience the future of learning with our advanced quiz platform.
                    Challenge yourself, track progress, and compete with peers in an engaging environment.
                </p>

                <?php if (isLoggedIn()): ?>
                    <div class="flex flex-col sm:flex-row justify-center gap-6 mb-16">
                        <a href="student/quiz.php" class="group bg-white text-indigo-600 px-10 py-4 rounded-2xl text-lg font-semibold hover:bg-gray-50 transition duration-300 transform hover:scale-105 shadow-2xl">
                            <i class="fas fa-play-circle mr-3 group-hover:animate-pulse"></i>
                            Start Quiz Now
                        </a>
                        <a href="leaderboard.php" class="group bg-gradient-to-r from-yellow-400 to-orange-400 text-gray-900 px-10 py-4 rounded-2xl text-lg font-semibold hover:from-yellow-300 hover:to-orange-300 transition duration-300 transform hover:scale-105 shadow-2xl">
                            <i class="fas fa-trophy mr-3 group-hover:animate-bounce"></i>
                            View Leaderboard
                        </a>
                    </div>
                <?php else: ?>
                    <div class="flex flex-col sm:flex-row justify-center gap-6 mb-16">
                        <a href="auth/login.php" class="group bg-white text-indigo-600 px-10 py-4 rounded-2xl text-lg font-semibold hover:bg-gray-50 transition duration-300 transform hover:scale-105 shadow-2xl">
                            <i class="fas fa-sign-in-alt mr-3 group-hover:animate-pulse"></i>
                            Login to Continue
                        </a>
                        <a href="leaderboard.php" class="group bg-white/20 backdrop-blur-sm text-white border-2 border-white/30 px-10 py-4 rounded-2xl text-lg font-semibold hover:bg-white/30 transition duration-300 transform hover:scale-105">
                            <i class="fas fa-trophy mr-3"></i>
                            View Leaderboard
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Stats Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                    <div class="text-center">
                        <div class="text-4xl font-bold mb-2">10K+</div>
                        <div class="text-white/80">Questions Available</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold mb-2">5K+</div>
                        <div class="text-white/80">Active Students</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold mb-2">98%</div>
                        <div class="text-white/80">Success Rate</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <main class="container mx-auto px-4 py-16">

        <!-- Features Section -->
        <section class="mb-20">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                    Why Choose QuizMaster Pro?
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Discover the advanced features that make learning engaging and effective
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="group bg-white p-8 rounded-3xl shadow-lg card-hover border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition duration-300">
                        <i class="fas fa-brain text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Smart Learning</h3>
                    <p class="text-gray-600 leading-relaxed">
                        AI-powered question selection adapts to your learning pace and identifies knowledge gaps for targeted improvement.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="group bg-white p-8 rounded-3xl shadow-lg card-hover border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition duration-300">
                        <i class="fas fa-stopwatch text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Timed Challenges</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Dynamic timer system with auto-submission, pause functionality, and time management analytics to enhance performance.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="group bg-white p-8 rounded-3xl shadow-lg card-hover border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-teal-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition duration-300">
                        <i class="fas fa-trophy text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Global Rankings</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Compete with learners worldwide, track your progress, and climb the leaderboard with detailed performance metrics.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="group bg-white p-8 rounded-3xl shadow-lg card-hover border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-red-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition duration-300">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Analytics Dashboard</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Comprehensive insights into your learning journey with detailed statistics, progress tracking, and performance trends.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="group bg-white p-8 rounded-3xl shadow-lg card-hover border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-r from-cyan-500 to-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition duration-300">
                        <i class="fas fa-users text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Collaborative Learning</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Join study groups, participate in team challenges, and learn together with peers in an interactive environment.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="group bg-white p-8 rounded-3xl shadow-lg card-hover border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-r from-violet-500 to-purple-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition duration-300">
                        <i class="fas fa-mobile-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Mobile Optimized</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Seamless experience across all devices with responsive design, offline capabilities, and touch-optimized interface.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <!-- Modern Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="container mx-auto px-4 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand Section -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-white text-lg"></i>
                        </div>
                        <h3 class="text-2xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
                            QuizMaster Pro
                        </h3>
                    </div>
                    <p class="text-gray-400 mb-6 max-w-md">
                        Empowering learners worldwide with advanced quiz technology.
                        Join thousands of students and educators in the future of interactive learning.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-indigo-600 transition duration-200">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-indigo-600 transition duration-200">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-indigo-600 transition duration-200">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-indigo-600 transition duration-200">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-semibold mb-6">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="leaderboard.php" class="text-gray-400 hover:text-white transition duration-200">Leaderboard</a></li>
                        <li><a href="auth/login.php" class="text-gray-400 hover:text-white transition duration-200">Login</a></li>
                        <li><a href="auth/register.php" class="text-gray-400 hover:text-white transition duration-200">Registration Info</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-200">Help Center</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h4 class="text-lg font-semibold mb-6">Support</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-200">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-200">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-200">Terms of Service</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-200">FAQ</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-12 pt-8 text-center">
                <p class="text-gray-400">
                    &copy; 2025 QuizMaster Pro. All rights reserved. Made with ❤️ for learners worldwide.
                </p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const mobileMenu = document.getElementById('mobile-menu');
            const menuButton = event.target.closest('button');

            if (!menuButton && !mobileMenu.contains(event.target)) {
                mobileMenu.classList.add('hidden');
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
