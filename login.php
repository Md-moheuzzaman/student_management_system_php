
<?php
session_start();
require_once 'database/dbconfig.php';

$error = null;
$emailValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $emailValue = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $statement = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE email = ? LIMIT 1");

    if (!$statement) {
        $error = 'Unable to process your login right now. Please try again.';
    } else {
        $statement->bind_param("s", $emailValue);
        if (!$statement->execute()) {
            $error = 'Unable to process your login right now. Please try again.';
        } else {
            $user = $statement->get_result()->fetch_assoc();
            if (!$user) {
                $error = 'Email Not Found!';
            } elseif (sha1($password) !== $user['password']) {
                $error = 'Wrong Password!';
            } else {
                $redirects = [
                    'admin' => 'admin/dashboard_admin.php',
                    'student' => 'student/dashboard_student.php',
                    'teacher' => 'teacher/dashboard_teacher.php',
                ];

                if (!isset($redirects[$user['role']])) {
                    $error = 'Access Denied!';
                } else {
                    $_SESSION['id'] = $user['id'];
                    $_SESSION['name'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    if ($user['role'] === 'student') {
                        $_SESSION['url'] = 'dashboard_student.php';
                    } elseif ($user['role'] === 'teacher') {
                        $_SESSION['url'] = 'dashboard_teacher.php';
                    }

                    header("Location: " . $redirects[$user['role']]);
                    exit;
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

    <title>Student Portal | Login</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css">


</head>

<body>



<div class="login-wrapper">

    <!-- Login Form -->

    <div class="login-left">

        <div class="brand">
            <i class="fa-solid fa-graduation-cap"></i>
            Ustudent
        </div>

        <h1 class="login-title">Welcome Back!</h1>

        <p class="login-subtitle">
            Login to your account and continue your learning journey.
        </p>

        <form method="post">

            <div class="mb-4">
                <label class="form-label">Email Address</label>

                <div class="input-group">
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter your email" 
                        value="admin@gmail.com"
                        required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>

                <div class="input-group">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        placeholder="Enter your password"
                        value="admin"
                        required>

                    <span class="input-group-text" onclick="togglePassword()">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </span>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-4">
                <a href="#" class="forgot-link">
                    Forgot Password?
                </a>
            </div>

            <?php if ($error !== null): ?>
                <div class="error-message" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <button type="submit" name="login" class="login-btn">
                Sign In <i class="fa-solid fa-arrow-right ms-2"></i>
            </button>

        </form>

        <div class="login-footer">
            &copy; Ustudent. 2026. All Rights Reserved.
        </div>

    </div>

    <!-- Right Illustration Section -->

    <div class="login-right">

        <!-- <img
            src="assets/images/12145614_Wavy_Edu-05_Single-07.jpg"
            alt=""> -->

        <h2>Manage Your Learning</h2>

        <p>
            Access your academic information, manage your profile,
            track your progress and stay connected with your institution.
        </p>

        <div class="mt-4">
            <i class="fa-solid fa-book-open me-3"></i>
            <i class="fa-solid fa-graduation-cap me-3"></i>
            <i class="fa-solid fa-user-graduate"></i>
        </div>

    </div>

</div>

<script>
    function togglePassword() {
        const password = document.getElementById("password");
        const eyeIcon = document.getElementById("eyeIcon");

        if (password.type === "password") {
            password.type = "text";
            eyeIcon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            password.type = "password";
            eyeIcon.classList.replace("fa-eye-slash", "fa-eye");
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
