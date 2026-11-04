<?php
require __DIR__ . '/../includes/functions.php';

if (!empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'admin') {
		header('Location: dashboard.php');
		exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$username = trim($_POST['username'] ?? '');
		$password = $_POST['password'] ?? '';

		if (!csrf_valid($_POST['csrf'] ?? '')) {
				$error = 'Your sign-in session expired. Please try again.';
		} elseif ($username === '' || $password === '') {
				$error = 'Enter your username and password.';
		} else {
				try {
						$db = db_connection();
						$stmt = $db->prepare('SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1');
						$stmt->bind_param('s', $username);
						$stmt->execute();
						$user = $stmt->get_result()->fetch_assoc();
						$stmt->close();

						if ($user && $user['role'] === 'admin' && password_verify($password, $user['password'])) {
								session_regenerate_id(true);
								$_SESSION['user_id'] = (int) $user['id'];
								$_SESSION['username'] = $user['username'];
								$_SESSION['role'] = $user['role'];
								header('Location: dashboard.php');
								exit;
						}

						$error = 'Incorrect username or password, or this account does not have administrator access.';
				} catch (mysqli_sql_exception $exception) {
						$error = 'The database is not available. Start MySQL and import student_management_system.sql.';
				}
		}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Administrator sign in | Ustudent</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-body">
<main class="auth-layout">
	<section class="auth-aside">
		<a class="home-brand" href="../index.php"><span class="brand-symbol" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M4 11.5 16 5l12 6.5L16 18 4 11.5Z"/><path d="M9 14.5v7c4.5 3.2 9.5 3.2 14 0v-7M28 12v8"/></svg></span><span>Ustudent<span class="brand-subtitle">STUDENT SERVICES</span></span></a>
		<div class="auth-aside-copy"><p class="eyebrow">ADMINISTRATOR ACCESS</p><h1>Good work starts with a clear picture.</h1><p>Sign in to see the people, progress and practical details that make your school run.</p></div>
		<span class="auth-aside-foot">STUDENT MANAGEMENT SYSTEM <span><?= e(date('Y')) ?></span></span>
	</section>
	<section class="auth-main">
		<form class="auth-form" method="post" action="index.php">
			<a class="back-link" href="../index.php"><span aria-hidden="true">&larr;</span> Back to home</a>
			<p class="eyebrow">WELCOME BACK</p>
			<h2>Administrator sign in</h2>
			<p class="auth-intro">Use your administrator account to continue.</p>
			<?php if ($error !== ''): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
			<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
			<label for="username">Username</label>
			<input id="username" name="username" type="text" value="<?= e($username) ?>" autocomplete="username" required autofocus>
			<label for="password">Password</label>
			<input id="password" name="password" type="password" autocomplete="current-password" required>
			<button class="button button-green auth-submit" type="submit">Sign in <span aria-hidden="true">&rarr;</span></button>
			<p class="auth-note">Access is limited to administrator accounts.</p>
		</form>
	</section>
</main>
</body>
</html>
