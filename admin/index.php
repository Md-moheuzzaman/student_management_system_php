<?php
require __DIR__ . '/config.php';

// Already signed in: go straight to the dashboard.
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error    = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrf_valid($_POST['csrf'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } elseif ($username === '' || $password === '') {
        $error = 'Enter your username and password.';
    } else {
        $stmt = db_execute($db, 'SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1', [$username]);
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            header('Location: dashboard.php');
            exit;
        }
        // Same message for unknown user and wrong password.
        $error = 'Incorrect username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Log in | Student Management System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login">
  <aside class="login-side">
    <div class="brand"><span class="mark" aria-hidden="true"></span>Student Management System</div>
    <div>
      <h1>Every student record, in one place</h1>
      <p>Attendance, marks, fees and reports for admins, teachers, students and parents.</p>
    </div>
    <small>IsDB-BISEW project, PHP &amp; MySQL</small>
  </aside>

  <main class="login-main">
    <form class="login-form" method="post" action="index.php" novalidate>
      <h2>Log in</h2>
      <p class="sub">Use the account your administrator gave you.</p>

      <?php if ($error !== ''): ?>
        <div class="alert error" role="alert"><?= e($error) ?></div>
      <?php endif; ?>

      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <label for="username">Username</label>
      <input type="text" id="username" name="username" value="<?= e($username) ?>" autocomplete="username" required autofocus>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" autocomplete="current-password" required>

      <button class="btn block" type="submit">Log in</button>
    </form>
  </main>
</div>
</body>
</html>
