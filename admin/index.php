
<?php
session_start();
require_once '../database/dbconfig.php';
?>


<?php
if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";

    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result)== 1){
    $user = mysqli_fetch_assoc($result);

    if(sha1($password) === $user['password']){
            if($user['role'] === "admin"){
                 $_SESSION['id'] = $user['id'];
                $_SESSION['name'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                header("Location:dashboard_admin.php");
                exit;

            }else if ($user['role'] == 'student') {
                $_SESSION['id'] = $user['id'];
                $_SESSION['name'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
    $_SESSION['url'] = 'dashboard_student.php';
    header("Location: dashboard_student.php");
    exit();

}else if ($user['role'] == 'teacher') {
    $_SESSION['id'] = $user['id'];
                $_SESSION['name'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
    $_SESSION['url'] = 'dashboard_teacher.php';
    header("Location: dashboard_teacher.php");
    exit();
}
    
            }else {
                $error = "Access Denied!";
            }
            }else{
            $error = "Wrong Password!";
        }


     }else{
        $error = "Email Not Found!";
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

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: #f3f6fc;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 1050px;
            min-height: 620px;
            background: #fff;
            border-radius: 22px;
            overflow: hidden;
            display: flex;
            box-shadow: 0 15px 50px rgba(30, 50, 90, 0.12);
        }

        /* Left section */

        .login-left {
            width: 50%;
            padding: 65px 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .brand {
            color: #2563eb;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 45px;
        }

        .brand i {
            margin-right: 10px;
        }

        .login-title {
            font-size: 32px;
            font-weight: 750;
            color: #172554;
            margin-bottom: 10px;
        }

        .login-subtitle {
            color: #7b8496;
            margin-bottom: 35px;
            font-size: 14px;
        }

        .form-label {
            color: #344054;
            font-weight: 600;
            font-size: 14px;
        }

        .form-control {
            height: 52px;
            border: 1px solid #e0e5ef;
            border-radius: 9px;
            padding-left: 15px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
        }

        .input-group .form-control {
            border-right: 0;
        }

        .input-group-text {
            background: #fff;
            border: 1px solid #e0e5ef;
            border-left: 0;
            cursor: pointer;
            border-radius: 0 9px 9px 0;
        }

        .forgot-link {
            color: #2563eb;
            font-size: 13px;
            text-decoration: none;
            font-weight: 600;
        }

        .login-btn {
            height: 52px;
            border: none;
            border-radius: 9px;
            background: #2563eb;
            color: white;
            font-weight: 600;
            width: 100%;
            transition: .3s;
        }

        .login-btn:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        .login-footer {
            margin-top: 35px;
            text-align: center;
            color: #98a2b3;
            font-size: 12px;
        }

        /* Right section */

        .login-right {
            width: 50%;
            background: linear-gradient(145deg, #1e40af, #2563eb, #60a5fa);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 45px;
            position: relative;
        }

        .login-right img {
            width: 100%;
            max-width: 360px;
            height: auto;
            object-fit: contain;
            margin-bottom: 30px;
        }

        .login-right h2 {
            font-size: 29px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .login-right p {
            color: #e0eaff;
            font-size: 14px;
            line-height: 1.8;
            max-width: 350px;
        }

        .error-message {
            color: #dc3545;
            background: #fff0f0;
            padding: 10px 12px;
            border-radius: 7px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        /* Responsive */

        @media(max-width: 768px) {
            .login-wrapper {
                max-width: 480px;
            }

            .login-left {
                width: 100%;
                padding: 45px 30px;
            }

            .login-right {
                display: none;
            }

            .login-title {
                font-size: 27px;
            }
        }
    </style>
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

            <?php
            //if (isset($error)) {
                //echo '<div class="error-message">' . htmlspecialchars($error) . '</div>';
           // }
            ?>


            <?php 
                if (isset($error)) {
                    echo $error;
                        }
                
                ?>

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

        <img
            src="assets/images/12145614_Wavy_Edu-05_Single-07.jpg"
            alt="">

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
