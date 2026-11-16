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

// Add Student
if (isset($_POST['add_student'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $class_id = (int)$_POST['class_id'];
    $address = $conn->real_escape_string($_POST['address']);
    $password = sha1($_POST['password']);

    $checkEmail = $conn->query("SELECT id FROM users WHERE email='$email'");

    if (!$checkEmail) {
        header("Location: student.php?msg=error");
        exit;
    }

    if ($checkEmail->num_rows > 0) {
        header("Location: student.php?msg=email_exists");
        exit;
    }

    $dobValue = ($dob === '') ? 'NULL' : "'$dob'";

    $conn->begin_transaction();

    if ($conn->query("INSERT INTO users (username, password, role, email) VALUES ('$username', '$password', 'student', '$email')")) {
        $user_id = $conn->insert_id;
        $sql = "INSERT INTO students (user_id, name, email, phone, dob, class_id, address)
                VALUES ($user_id, '$name', '$email', '$phone', $dobValue, $class_id, '$address')";

        if ($conn->query($sql)) {
            $conn->commit();
            header("Location: student.php?msg=added");
            exit;
        }
    }

    $conn->rollback();
    header("Location: student.php?msg=error");
    exit;
}

// Update Student
if (isset($_POST['update_student'])) {
    $student_id = (int)$_POST['id'];
    $username = $conn->real_escape_string($_POST['username']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $class_id = (int)$_POST['class_id'];
    $address = $conn->real_escape_string($_POST['address']);
    $password = $_POST['password'];

    $studentResult = $conn->query("SELECT user_id FROM students WHERE id=$student_id");

    if (!$studentResult) {
        header("Location: student.php?msg=error");
        exit;
    }

    if ($studentResult->num_rows === 0) {
        header("Location: student.php?msg=not_found");
        exit;
    }

    $studentRow = $studentResult->fetch_assoc();
    $user_id = (int)$studentRow['user_id'];
    $checkEmail = $conn->query("SELECT id FROM users WHERE email='$email' AND id!=$user_id");

    if (!$checkEmail) {
        header("Location: student.php?msg=error");
        exit;
    }

    if ($checkEmail->num_rows > 0) {
        header("Location: student.php?msg=email_exists");
        exit;
    }

    $dobValue = ($dob === '') ? 'NULL' : "'$dob'";
    $conn->begin_transaction();

    $userSql = "UPDATE users SET username='$username', email='$email'";
    if ($password !== '') {
        $hashedPassword = sha1($password);
        $userSql .= ", password='$hashedPassword'";
    }
    $userSql .= " WHERE id=$user_id AND role='student'";

    $studentSql = "UPDATE students SET name='$name', email='$email', phone='$phone',
                   dob=$dobValue, class_id=$class_id, address='$address' WHERE id=$student_id";

    if ($conn->query($userSql) && $conn->query($studentSql)) {
        $conn->commit();
        header("Location: student.php?msg=updated");
        exit;
    }

    $conn->rollback();
    header("Location: student.php?msg=error");
    exit;
}

// Delete Student
if (isset($_POST['delete_student'])) {
    $student_id = (int)$_POST['id'];
    $studentResult = $conn->query("SELECT user_id FROM students WHERE id=$student_id");

    if (!$studentResult) {
        header("Location: student.php?msg=error");
        exit;
    }

    if ($studentResult->num_rows === 0) {
        header("Location: student.php?msg=not_found");
        exit;
    }

    $studentRow = $studentResult->fetch_assoc();
    $user_id = (int)$studentRow['user_id'];
    $conn->begin_transaction();

    if ($conn->query("DELETE FROM students WHERE id=$student_id") &&
        $conn->query("DELETE FROM users WHERE id=$user_id AND role='student'")) {
        $conn->commit();
        header("Location: student.php?msg=deleted");
        exit;
    }

    $conn->rollback();
    header("Location: student.php?msg=error");
    exit;
}

// Get all students and classes
$result = $conn->query("SELECT students.*, classes.class_name, classes.section
                        FROM students
                        LEFT JOIN classes ON students.class_id=classes.id
                        ORDER BY students.id ASC");
$classes = $conn->query("SELECT classes.id, classes.class_name, classes.section, courses.course_name
                         FROM classes
                         INNER JOIN courses ON classes.course_id=courses.id
                         ORDER BY classes.id ASC");

if (!$result || !$classes) {
    $msg = 'error';
}

$student_name = '';
$username = '';
$email = '';
$phone = '';
$dob = '';
$class_id = '';
$address = '';

// Existing student data for Edit
if ($action === 'edit' && $id > 0) {
    $editResult = $conn->query("SELECT students.*, users.username
                                FROM students
                                INNER JOIN users ON students.user_id=users.id
                                WHERE students.id=$id AND users.role='student'");

    if (!$editResult) {
        header('Location: student.php?msg=error');
        exit;
    }

    if ($editResult->num_rows > 0) {
        $row = $editResult->fetch_assoc();
        $student_name = $row['name'];
        $username = $row['username'];
        $email = $row['email'];
        $phone = $row['phone'];
        $dob = $row['dob'];
        $class_id = $row['class_id'];
        $address = $row['address'];
    } else {
        header('Location: student.php?msg=not_found');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard | Students</title>

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
                    <h1>Students</h1>
                    <p>Add, edit and delete the students here</p>
                </div>

                <a href="student.php?action=add" class="btn">+ Add student</a>
            </div>

            <?php if ($msg === 'added') { ?>
                <div class="alert ok">Student added successfully.</div>
            <?php } elseif ($msg === 'updated') { ?>
                <div class="alert ok">Student updated successfully.</div>
            <?php } elseif ($msg === 'deleted') { ?>
                <div class="alert ok">Student deleted successfully.</div>
            <?php } elseif ($msg === 'email_exists') { ?>
                <div class="alert bad">That email is already in use.</div>
            <?php } elseif ($msg === 'not_found') { ?>
                <div class="alert bad">The student could not be found.</div>
            <?php } elseif ($msg === 'error') { ?>
                <div class="alert bad">The request could not be completed. Check the information and related database records.</div>
            <?php } ?>

            <section class="card">
                <h2>All students</h2>

                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Class</th>
                            <th>Phone</th>
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
                                <td>
                                    <?php if ($row['class_name'] !== null) { ?>
                                        <span class="class-tag"><?php echo htmlspecialchars($row['class_name'] . ' ' . $row['section'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php } else { ?>
                                        <span class="muted">No class</span>
                                    <?php } ?>
                                </td>
                                <td class="phone"><?php echo htmlspecialchars($row['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <div class="row-actions">
                                        <a class="small-btn" href="student.php?action=edit&id=<?php echo $row['id']; ?>">Edit</a>
                                        <form method="post" onsubmit="return confirm('Delete this student?');">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete_student" class="small-btn danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6" class="empty">No students found.</td>
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
            <h2><?php echo ($action === 'add') ? 'Add student' : 'Edit student'; ?></h2>
            <a href="student.php" class="close">&times;</a>
        </div>

        <form method="post">
            <?php if ($action === 'edit') { ?>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
            <?php } ?>

            <div class="modal-body">
                <div class="field">
                    <label for="name">Full name</label>
                    <input type="text" name="name" id="name" maxlength="100" value="<?php echo htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8'); ?>" required>
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
                    <input type="text" name="phone" id="phone" maxlength="15" value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="field">
                    <label for="dob">Date of birth</label>
                    <input type="date" name="dob" id="dob" value="<?php echo htmlspecialchars($dob ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="field">
                    <label for="class_id">Class</label>
                    <?php if ($classes && $classes->num_rows === 0) { ?>
                        <span class="muted">No classes are available. Add a class record before registering a student.</span>
                    <?php } ?>
                    <select name="class_id" id="class_id" required>
                        <option value="">Select a class</option>
                        <?php if ($classes) { ?>
                            <?php while ($class = $classes->fetch_assoc()) { ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo ((string)$class_id === (string)$class['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['section'] . ' - ' . $class['course_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>

                <div class="field">
                    <label for="address">Address</label>
                    <textarea name="address" id="address" maxlength="255"><?php echo htmlspecialchars($address ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
            </div>

            <div class="modal-foot">
                <a href="student.php" class="btn ghost">Cancel</a>
                <button type="submit" class="btn" name="<?php echo ($action === 'add') ? 'add_student' : 'update_student'; ?>">
                    <?php echo ($action === 'add') ? 'Save student' : 'Update student'; ?>
                </button>
            </div>
        </form>
    </div>
</div>
<?php } ?>

<?php include '../includes/footer.php'; ?>
</body>
</html>