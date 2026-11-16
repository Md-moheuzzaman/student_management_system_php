<?php
session_start();

include '../database/dbconfig.php';

// Admin Authentication
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$msg = $_GET['msg'] ?? '';
$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

// Add Teacher
if (isset($_POST['add_teacher'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $qualification = $conn->real_escape_string($_POST['qualification']);
    $password = sha1($_POST['password']);

    $checkEmail = $conn->query("SELECT id FROM users WHERE email='$email'");

    if (!$checkEmail) {
        header("Location: teacher.php?msg=error");
        exit;
    }

    if ($checkEmail->num_rows > 0) {
        header("Location: teacher.php?msg=email_exists");
        exit;
    }

    $conn->begin_transaction();

    if ($conn->query("INSERT INTO users (username, password, role, email) VALUES ('$username', '$password', 'teacher', '$email')")) {
        $user_id = $conn->insert_id;
        $sql = "INSERT INTO teachers (user_id, name, email, phone, qualification)
                VALUES ($user_id, '$name', '$email', '$phone', '$qualification')";

        if ($conn->query($sql)) {
            $conn->commit();
            header("Location: teacher.php?msg=added");
            exit;
        }
    }

    $conn->rollback();
    header("Location: teacher.php?msg=error");
    exit;
}

// Update Teacher
if (isset($_POST['update_teacher'])) {
    $teacher_id = (int)$_POST['id'];
    $username = $conn->real_escape_string($_POST['username']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $qualification = $conn->real_escape_string($_POST['qualification']);
    $password = $_POST['password'];

    $teacherResult = $conn->query("SELECT user_id FROM teachers WHERE id=$teacher_id");

    if (!$teacherResult) {
        header("Location: teacher.php?msg=error");
        exit;
    }

    if ($teacherResult->num_rows === 0) {
        header("Location: teacher.php?msg=not_found");
        exit;
    }

    $teacherRow = $teacherResult->fetch_assoc();
    $user_id = (int)$teacherRow['user_id'];
    $checkEmail = $conn->query("SELECT id FROM users WHERE email='$email' AND id!=$user_id");

    if (!$checkEmail) {
        header("Location: teacher.php?msg=error");
        exit;
    }

    if ($checkEmail->num_rows > 0) {
        header("Location: teacher.php?msg=email_exists");
        exit;
    }

    $conn->begin_transaction();

    $userSql = "UPDATE users SET username='$username', email='$email'";
    if ($password !== '') {
        $hashedPassword = sha1($password);
        $userSql .= ", password='$hashedPassword'";
    }
    $userSql .= " WHERE id=$user_id AND role='teacher'";

    $teacherSql = "UPDATE teachers SET name='$name', email='$email', phone='$phone',
                   qualification='$qualification' WHERE id=$teacher_id";

    if ($conn->query($userSql) && $conn->query($teacherSql)) {
        $conn->commit();
        header("Location: teacher.php?msg=updated");
        exit;
    }

    $conn->rollback();
    header("Location: teacher.php?msg=error");
    exit;
}

// Delete Teacher
if (isset($_POST['delete_teacher'])) {
    $teacher_id = (int)$_POST['id'];
    $teacherResult = $conn->query("SELECT user_id FROM teachers WHERE id=$teacher_id");

    if (!$teacherResult) {
        header("Location: teacher.php?msg=error");
        exit;
    }

    if ($teacherResult->num_rows === 0) {
        header("Location: teacher.php?msg=not_found");
        exit;
    }

    $teacherRow = $teacherResult->fetch_assoc();
    $user_id = (int)$teacherRow['user_id'];
    $conn->begin_transaction();

    if ($conn->query("DELETE FROM teachers WHERE id=$teacher_id") &&
        $conn->query("DELETE FROM users WHERE id=$user_id AND role='teacher'")) {
        $conn->commit();
        header("Location: teacher.php?msg=deleted");
        exit;
    }

    $conn->rollback();
    header("Location: teacher.php?msg=error");
    exit;
}

// Get all teachers
$result = $conn->query("SELECT * FROM teachers ORDER BY id ASC");
if (!$result) {
    $msg = 'error';
}

$teacher_name = '';
$username = '';
$email = '';
$phone = '';
$qualification = '';

// Existing teacher data for Edit
if ($action === 'edit' && $id > 0) {
    $editResult = $conn->query("SELECT teachers.*, users.username
                                FROM teachers
                                INNER JOIN users ON teachers.user_id=users.id
                                WHERE teachers.id=$id AND users.role='teacher'");

    if (!$editResult) {
        header('Location: teacher.php?msg=error');
        exit;
    }

    if ($editResult->num_rows > 0) {
        $row = $editResult->fetch_assoc();
        $teacher_name = $row['name'];
        $username = $row['username'];
        $email = $row['email'];
        $phone = $row['phone'];
        $qualification = $row['qualification'];
    } else {
        header('Location: teacher.php?msg=not_found');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard | Teachers</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">

<?php include '../includes/admin_dashboard_css.php'; ?>
<link rel="stylesheet" href="../assets/css/courses.css">
<link rel="stylesheet" href="../assets/css/admin_student.css">
</head>

<body>
<div class="app">

    <?php include '../includes/sidebar.php'; ?>

    <div class="main">
        <?php include '../includes/topbar.php'; ?>

        <main class="content">
            <div class="head">
                <div>
                    <h1>Teachers</h1>
                    <p>Add, edit and delete the teachers here</p>
                </div>

                <a href="teacher.php?action=add" class="btn">+ Add teacher</a>
            </div>

            <?php if ($msg === 'added') { ?>
                <div class="alert ok">Teacher added successfully.</div>
            <?php } elseif ($msg === 'updated') { ?>
                <div class="alert ok">Teacher updated successfully.</div>
            <?php } elseif ($msg === 'deleted') { ?>
                <div class="alert ok">Teacher deleted successfully.</div>
            <?php } elseif ($msg === 'email_exists') { ?>
                <div class="alert bad">That email is already in use.</div>
            <?php } elseif ($msg === 'not_found') { ?>
                <div class="alert bad">The teacher could not be found.</div>
            <?php } elseif ($msg === 'error') { ?>
                <div class="alert bad">The request could not be completed. Check the information and related database records.</div>
            <?php } ?>

            <section class="card">
                <h2>All teachers</h2>

                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Qualification</th>
                            <th class="num">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($result && $result->num_rows > 0) { ?>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><b><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></b></td>
                                <td><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="phone"><?php echo htmlspecialchars($row['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['qualification'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <div class="row-actions">
                                        <a class="small-btn" href="teacher.php?action=edit&id=<?php echo $row['id']; ?>">Edit</a>
                                        <form method="post" onsubmit="return confirm('Delete this teacher?');">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete_teacher" class="small-btn danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6" class="empty">No teachers found.</td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</div>

<?php if ($action === 'add' || $action === 'edit') { ?>
<div class="overlay open">
    <div class="modal people-modal">
        <div class="modal-head">
            <h2><?php echo ($action === 'add') ? 'Add teacher' : 'Edit teacher'; ?></h2>
            <a href="teacher.php" class="close">&times;</a>
        </div>

        <form method="post">
            <?php if ($action === 'edit') { ?>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
            <?php } ?>

            <div class="modal-body">
                <div class="field">
                    <label for="name">Full name</label>
                    <input type="text" name="name" id="name" maxlength="100" value="<?php echo htmlspecialchars($teacher_name, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="field">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" maxlength="50" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" maxlength="100" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="field">
                    <label for="password">Password <?php echo ($action === 'edit') ? '(leave blank to keep current password)' : ''; ?></label>
                    <input type="password" name="password" id="password" <?php echo ($action === 'add') ? 'required' : ''; ?>>
                </div>

                <div class="field">
                    <label for="phone">Phone</label>
                    <input type="text" name="phone" id="phone" maxlength="15" value="<?php echo htmlspecialchars($phone ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="field">
                    <label for="qualification">Qualification</label>
                    <input type="text" name="qualification" id="qualification" maxlength="150" value="<?php echo htmlspecialchars($qualification ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            </div>

            <div class="modal-foot">
                <a href="teacher.php" class="btn ghost">Cancel</a>
                <button type="submit" class="btn" name="<?php echo ($action === 'add') ? 'add_teacher' : 'update_teacher'; ?>">
                    <?php echo ($action === 'add') ? 'Save teacher' : 'Update teacher'; ?>
                </button>
            </div>
        </form>
    </div>
</div>
<?php } ?>

<?php include '../includes/footer.php'; ?>
</body>
</html>