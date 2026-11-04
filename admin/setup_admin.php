<?php
/**
 * One-time helper: creates the first administrator account.
 * It stops working as soon as an admin exists.
 * DELETE THIS FILE after you have created your admin.
 */
require __DIR__ . '/config.php';

$result = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
$adminExists = (int) $result->fetch_row()[0] > 0;
$result->free();
$message = '';
$error   = '';
$done    = false;

if (!$adminExists && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrf_valid($_POST['csrf'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } elseif ($username === '' || strlen($username) > 50) {
        $error = 'Enter a username of up to 50 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Use a password of at least 8 characters.';
    } else {
        $stmt = db_execute($db, 'INSERT INTO users (username, password, role, email) VALUES (?, ?, ?, ?)', [
            $username,
            password_hash($password, PASSWORD_DEFAULT),
            'admin',
            $email,
        ]);
        $stmt->close();
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create admin | Student Management System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-main" style="min-height:100vh">
  <div class="login-form">
    <h2>Create the first admin</h2>

    <?php if ($done): ?>
      <p class="sub">The admin account is ready.</p>
      <div class="alert ok">Delete <b>setup_admin.php</b> from the project folder now.</div>
      <a class="btn block" href="index.php" style="text-align:center">Go to log in</a>

    <?php elseif ($adminExists): ?>
      <p class="sub">An admin account already exists.</p>
      <div class="alert info">Delete <b>setup_admin.php</b> from the project folder.</div>
      <a class="btn block" href="index.php" style="text-align:center">Go to log in</a>

    <?php else: ?>
      <p class="sub">This page works once, while no admin exists.</p>
      <?php if ($error !== ''): ?><div class="alert error" role="alert"><?= e($error) ?></div><?php endif; ?>
      <form method="post" action="setup_admin.php" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= e($_POST['username'] ?? '') ?>" required autofocus>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
        <label for="password">Password (8 characters or more)</label>
        <input type="password" id="password" name="password" autocomplete="new-password" required>
        <button class="btn block" type="submit">Create admin</button>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
