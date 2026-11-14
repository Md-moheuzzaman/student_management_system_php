<?php

include '../database/dbconfig.php';



if($_SERVER['REQUEST_METHOD'] === "POST"){
    $name = $_POST['username'];
    $password = sha1($_POST['password']);
    $role = "student";
    $email = $_POST['email'];
    

    $cheakEmail = "SELECT * FROM users WHERE email = '$email'";

    $result = $conn->query($cheakEmail);

    if($result->num_rows> 0){
        header("location: registration.php?error=email_exists");
        exit;
    }
    else{

    $sql = "INSERT INTO users(id, username, password, role, email)
    values(NULL, '$name', '$password', '$role', '$email')";

    $conn->query($sql);

    if($conn->affected_rows){
           header("Location: registration.php?success=registered");
            exit;
        }
   

    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Customer Registration</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f9fafc, #f3f1f5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            padding: 25px 15px;
        }

        .registration-container {
            width: 100%;
            max-width: 950px;
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 15px 45px rgba(0,0,0,0.2);
        }

        .left-panel {
            background: linear-gradient(145deg, #4e5fe9, #7745a7);
            color: white;
            padding: 55px 35px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 550px;
        }

        .left-panel h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 18px;
        }

        .left-panel p {
            color: #eeeeff;
            line-height: 1.8;
        }

        .left-icon {
            font-size: 75px;
            margin-bottom: 25px;
        }

        .right-panel {
            padding: 45px 40px;
        }

        .form-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }

        .form-subtitle {
            color: #888;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 600;
            color: #444;
            margin-bottom: 8px;
        }

        .input-group-text {
            background: #f5f5ff;
            border-right: none;
            color: #667eea;
        }

        .form-control {
            padding: 12px;
            border-left: none;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }

        .input-group:focus-within {
            box-shadow: 0 0 0 3px rgba(102,126,234,0.15);
            border-radius: 7px;
        }

        .register-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 13px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            transition: 0.3s;
        }

        .register-btn:hover {
            color: white;
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .login-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link:hover {
            text-decoration: underline;
        }

        @media(max-width: 767px) {
            .left-panel {
                min-height: auto;
                padding: 30px 25px;
                text-align: center;
            }

            .left-icon {
                font-size: 45px;
                margin-bottom: 10px;
            }

            .left-panel h2 {
                font-size: 25px;
            }

            .right-panel {
                padding: 35px 25px;
            }
        }
    </style>
</head>

<body>

<div class="registration-container">

    <div class="row g-0">

        <!-- Left Side -->
        <div class="col-md-5">

            <div class="left-panel">

                <div class="left-icon">
                    <i class="bi bi-person-plus-fill"></i>
                </div>

                <h2>Welcome!</h2>

                <p>
                    Access your academic information, manage your profile, track your progress and stay connected with your institution.
                </p>

                <div class="mt-3">
                    <i class="bi bi-shield-check me-2"></i>
                    Secure Registration
                </div>

                <div class="mt-2">
                    <i class="bi bi-person-check me-2"></i>
                    Easy Account Access
                </div>

            </div>

        </div>

        <!-- Right Side -->
        <div class="col-md-7">

            <div class="right-panel">

                <h3 class="form-title">Create Account</h3>

                <p class="form-subtitle">
                    Please fill in your information to register.
                </p>

                <!-- Success Message -->
                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        Registration successful! Please login.
                    </div>
                <?php endif; ?>

                <!-- Error Message -->
                <?php if(isset($_GET['error']) && $_GET['error'] == 'email_exists'): ?>
                    <div class="alert alert-danger">
                        This email is already registered!
                    </div>
                <?php endif; ?>

                <!-- Registration Form -->
                <form action="registration.php" method="POST">

                    <!-- Username -->
                    <div class="mb-3">

                        <label class="form-label">Full Name</label>

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person"></i>
                            </span>

                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                placeholder="Enter your full name"
                                required
                            >
                        </div>

                    </div>

                    <!-- Email -->
                    <div class="mb-3">

                        <label class="form-label">Email Address</label>

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                placeholder="Enter your email"
                                required
                            >
                        </div>

                    </div>

                    <!-- Password -->
                    <div class="mb-4">

                        <label class="form-label">Password</label>

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Enter your password"
                                required
                            >
                        </div>

                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn register-btn w-100">
                        Create Account
                        <i class="bi bi-arrow-right ms-2"></i>
                    </button>

                </form>

                <div class="text-center mt-4">
                    Already have an account?
                    <a href="index.php" class="login-link">Login</a>
                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>
