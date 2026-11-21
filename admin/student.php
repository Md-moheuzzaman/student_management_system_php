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

function selectedClassIds($input)
{
    if (!is_array($input)) {
        return null;
    }

    $classIds = [];
    foreach ($input as $value) {
        if (!is_scalar($value) || !ctype_digit((string)$value) || (int)$value < 1) {
            return null;
        }
        $classIds[] = (int)$value;
    }

    $classIds = array_values(array_unique($classIds));
    return $classIds ? $classIds : null;
}

function fetchSelectedClasses($conn, $classIds)
{
    $placeholders = implode(',', array_fill(0, count($classIds), '?'));
    $statement = $conn->prepare("SELECT id, class_name, section, course_id FROM classes WHERE id IN ($placeholders)");
    if (!$statement) {
        return false;
    }

    $types = str_repeat('i', count($classIds));
    if (!$statement->bind_param($types, ...$classIds) || !$statement->execute()) {
        return false;
    }

    $result = $statement->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : false;
}

function saveStudentClasses($conn, $studentId, $classIds)
{
    if (!$conn->query("DELETE FROM student_classes WHERE student_id=$studentId")) {
        return false;
    }

    $statement = $conn->prepare("INSERT INTO student_classes (student_id, class_id) VALUES (?, ?)");
    if (!$statement) {
        return false;
    }

    foreach ($classIds as $classId) {
        if (!$statement->bind_param("ii", $studentId, $classId) || !$statement->execute()) {
            return false;
        }
    }

    return true;
}

// Add Student
if (isset($_POST['add_student'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $classIds = selectedClassIds($_POST['class_ids'] ?? null);
    $address = $conn->real_escape_string($_POST['address']);
    $password = sha1($_POST['password']);

    if ($classIds === null) {
        header("Location: student.php?msg=invalid_classes");
        exit;
    }

    $selectedClasses = fetchSelectedClasses($conn, $classIds);
    if ($selectedClasses === false) {
        header("Location: student.php?msg=error");
        exit;
    }
    if (count($selectedClasses) !== count($classIds) ||
        count(array_unique(array_map(function ($class) {
            return $class['class_name'] . "\0" . $class['section'];
        }, $selectedClasses))) !== 1 ||
        count(array_unique(array_column($selectedClasses, 'course_id'))) !== count($classIds)) {
        header("Location: student.php?msg=invalid_classes");
        exit;
    }
    $class_id = $classIds[0];

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
            $student_id = $conn->insert_id;
        } else {
            $student_id = 0;
        }

        if ($student_id > 0 && saveStudentClasses($conn, $student_id, $classIds)) {
            $conn->commit();
            header("Location: student.php?msg=added");
            exit;
        }
    }

    $conn->rollback();
    header("Location: student.php?msg=error");
    exit;
}

// Complete a profile for an account created through public registration
if (isset($_POST['complete_student'])) {
    $user_id = (int)$_POST['id'];
    $username = $conn->real_escape_string($_POST['username']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $classIds = selectedClassIds($_POST['class_ids'] ?? null);
    $address = $conn->real_escape_string($_POST['address']);

    if ($classIds === null) {
        header("Location: student.php?msg=invalid_classes");
        exit;
    }

    $selectedClasses = fetchSelectedClasses($conn, $classIds);
    if ($selectedClasses === false) {
        header("Location: student.php?msg=error");
        exit;
    }
    if (count($selectedClasses) !== count($classIds) ||
        count(array_unique(array_map(function ($class) {
            return $class['class_name'] . "\0" . $class['section'];
        }, $selectedClasses))) !== 1 ||
        count(array_unique(array_column($selectedClasses, 'course_id'))) !== count($classIds)) {
        header("Location: student.php?msg=invalid_classes");
        exit;
    }

    $pendingUser = $conn->query(
        "SELECT id FROM users
          WHERE id=$user_id AND role='student'
            AND NOT EXISTS (SELECT 1 FROM students WHERE user_id=$user_id)"
    );
    if (!$pendingUser) {
        header("Location: student.php?msg=error");
        exit;
    }
    if ($pendingUser->num_rows === 0) {
        header("Location: student.php?msg=not_found");
        exit;
    }

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
    $class_id = $classIds[0];
    $conn->begin_transaction();

    $userSql = "UPDATE users SET username='$username', email='$email'
                WHERE id=$user_id AND role='student'";
    $studentSql = "INSERT INTO students (user_id, name, email, phone, dob, class_id, address)
                   VALUES ($user_id, '$name', '$email', '$phone', $dobValue, $class_id, '$address')";

    if ($conn->query($userSql) && $conn->query($studentSql)) {
        $student_id = $conn->insert_id;
        if (saveStudentClasses($conn, $student_id, $classIds)) {
            $conn->commit();
            header("Location: student.php?msg=profile_completed");
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
    $classIds = selectedClassIds($_POST['class_ids'] ?? null);
    $address = $conn->real_escape_string($_POST['address']);
    $password = $_POST['password'];

    if ($classIds === null) {
        header("Location: student.php?msg=invalid_classes");
        exit;
    }

    $selectedClasses = fetchSelectedClasses($conn, $classIds);
    if ($selectedClasses === false) {
        header("Location: student.php?msg=error");
        exit;
    }
    if (count($selectedClasses) !== count($classIds) ||
        count(array_unique(array_map(function ($class) {
            return $class['class_name'] . "\0" . $class['section'];
        }, $selectedClasses))) !== 1 ||
        count(array_unique(array_column($selectedClasses, 'course_id'))) !== count($classIds)) {
        header("Location: student.php?msg=invalid_classes");
        exit;
    }
    $class_id = $classIds[0];

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

    if ($conn->query($userSql) && $conn->query($studentSql) && saveStudentClasses($conn, $student_id, $classIds)) {
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

// Include accounts created through registration even if their student profile is not complete.
$result = $conn->query("SELECT students.id AS student_id, users.id AS user_id,
                               COALESCE(students.name, users.username) AS name, users.email,
                               students.phone, classes.class_name, classes.section
                        FROM users
                        LEFT JOIN students ON students.user_id=users.id
                        LEFT JOIN classes ON students.class_id=classes.id
                        WHERE users.role='student'
                        ORDER BY users.id ASC");
$classes = $conn->query("SELECT classes.id, classes.class_name, classes.section, courses.course_name
                         FROM classes
                         INNER JOIN courses ON classes.course_id=courses.id
                         ORDER BY classes.id ASC");

if (!$result || !$classes) {
    $msg = 'error';
}

$availableClasses = $classes ? $classes->fetch_all(MYSQLI_ASSOC) : [];
$departmentGroups = [];
$classDepartmentIds = [];
foreach ($availableClasses as $class) {
    $groupKey = $class['class_name'] . "\0" . $class['section'];
    if (!isset($departmentGroups[$groupKey])) {
        $departmentGroups[$groupKey] = [
            'id' => (string)(count($departmentGroups) + 1),
            'class_name' => $class['class_name'],
            'section' => $class['section'],
        ];
    }
    $classDepartmentIds[(int)$class['id']] = $departmentGroups[$groupKey]['id'];
}

$student_name = '';
$username = '';
$email = '';
$phone = '';
$dob = '';
$class_id = '';
$class_ids = [];
$selectedDepartmentId = '';
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

        $classResult = $conn->query("SELECT class_id FROM student_classes WHERE student_id=$id ORDER BY class_id ASC");
        if (!$classResult) {
            header('Location: student.php?msg=error');
            exit;
        }
        while ($classRow = $classResult->fetch_assoc()) {
            $class_ids[] = (int)$classRow['class_id'];
        }
        if (!$class_ids) {
            $class_ids[] = (int)$class_id;
        }
        $selectedDepartmentId = $classDepartmentIds[$class_ids[0]] ?? '';
    } else {
        header('Location: student.php?msg=not_found');
        exit;
    }
}

if ($action === 'complete' && $id > 0) {
    $pendingResult = $conn->query(
        "SELECT users.username, users.email FROM users
          WHERE users.id=$id AND users.role='student'
            AND NOT EXISTS (SELECT 1 FROM students WHERE user_id=users.id)"
    );
    if (!$pendingResult) {
        header('Location: student.php?msg=error');
        exit;
    }
    if ($pendingResult->num_rows === 0) {
        header('Location: student.php?msg=not_found');
        exit;
    }

    $pendingRow = $pendingResult->fetch_assoc();
    $student_name = $pendingRow['username'];
    $username = $pendingRow['username'];
    $email = $pendingRow['email'];
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
            <?php } elseif ($msg === 'profile_completed') { ?>
                <div class="alert ok">Registered student profile completed successfully.</div>
            <?php } elseif ($msg === 'deleted') { ?>
                <div class="alert ok">Student deleted successfully.</div>
            <?php } elseif ($msg === 'email_exists') { ?>
                <div class="alert bad">That email is already in use.</div>
            <?php } elseif ($msg === 'invalid_classes') { ?>
                <div class="alert bad">Select one or more courses from the same department and section.</div>
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
                            <th>Dept</th>
                            <th>Phone</th>
                            <th class="num">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($result && $result->num_rows > 0) { ?>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['student_id'] ?? '-'; ?></td>
                                <td><b><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></b></td>
                                <td><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php if ($row['student_id'] === null) { ?>
                                        <span class="muted">Profile pending</span>
                                    <?php } elseif ($row['class_name'] !== null) { ?>
                                        <span class="class-tag"><?php echo htmlspecialchars($row['class_name'] . ' ' . $row['section'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php } else { ?>
                                        <span class="muted">No class</span>
                                    <?php } ?>
                                </td>
                                <td class="phone"><?php echo htmlspecialchars($row['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <div class="row-actions">
                                        <?php if ($row['student_id'] === null) { ?>
                                            <a class="small-btn" href="student.php?action=complete&id=<?php echo $row['user_id']; ?>">Complete profile</a>
                                        <?php } else { ?>
                                            <a class="small-btn" href="student.php?action=edit&id=<?php echo $row['student_id']; ?>">Edit</a>
                                            <form method="post" onsubmit="return confirm('Delete this student?');">
                                                <input type="hidden" name="id" value="<?php echo $row['student_id']; ?>">
                                                <button type="submit" name="delete_student" class="small-btn danger">Delete</button>
                                            </form>
                                        <?php } ?>
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

<?php if ($action === 'add' || $action === 'edit' || $action === 'complete') { ?>
<div class="overlay open">
    <div class="modal people-modal">
        <div class="modal-head">
            <h2><?php echo ($action === 'add') ? 'Add student' : (($action === 'complete') ? 'Complete student profile' : 'Edit student'); ?></h2>
            <a href="student.php" class="close">&times;</a>
        </div>

        <form method="post">
            <?php if ($action === 'edit') { ?>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
            <?php } elseif ($action === 'complete') { ?>
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

                <?php if ($action !== 'complete') { ?>
                <div class="field">
                    <label for="password">Password <?php echo ($action === 'edit') ? '(leave blank to keep current password)' : ''; ?></label>
                    <input type="password" name="password" id="password" <?php echo ($action === 'add') ? 'required' : ''; ?>>
                </div>
                <?php } ?>

                <div class="field">
                    <label for="phone">Phone</label>
                    <input type="text" name="phone" id="phone" maxlength="15" value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="field">
                    <label for="dob">Date of birth</label>
                    <input type="date" name="dob" id="dob" value="<?php echo htmlspecialchars($dob ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="field">
                    <label for="department_id">Department</label>
                    <?php if (!$availableClasses) { ?>
                        <span class="muted">No classes are available. Add a class record before registering a student.</span>
                    <?php } ?>
                    <select name="department_id" id="department_id" required>
                        <option value="">Select a department</option>
                        <?php foreach ($departmentGroups as $department) { ?>
                            <option value="<?php echo htmlspecialchars($department['id'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selectedDepartmentId === $department['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($department['class_name'] . ' ' . $department['section'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="field">
                    <label for="class_ids">Courses</label>
                    <select name="class_ids[]" id="class_ids" multiple size="5" required>
                        <?php foreach ($availableClasses as $class) { ?>
                            <option value="<?php echo (int)$class['id']; ?>" data-department="<?php echo htmlspecialchars($classDepartmentIds[(int)$class['id']], ENT_QUOTES, 'UTF-8'); ?>" <?php echo in_array((int)$class['id'], $class_ids, true) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['course_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <span class="muted">Select one or more courses in the chosen department.</span>
                </div>

                <div class="field">
                    <label for="address">Address</label>
                    <textarea name="address" id="address" maxlength="255"><?php echo htmlspecialchars($address ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
            </div>

            <div class="modal-foot">
                <a href="student.php" class="btn ghost">Cancel</a>
                <button type="submit" class="btn" name="<?php echo ($action === 'add') ? 'add_student' : (($action === 'complete') ? 'complete_student' : 'update_student'); ?>">
                    <?php echo ($action === 'add') ? 'Save student' : (($action === 'complete') ? 'Complete profile' : 'Update student'); ?>
                </button>
            </div>
        </form>
    </div>
</div>
<?php } ?>

<?php include '../includes/footer.php'; ?>
<script>
var departmentSelect = document.getElementById('department_id');
var courseSelect = document.getElementById('class_ids');
if (departmentSelect && courseSelect) {
    function filterCourses() {
        Array.prototype.forEach.call(courseSelect.options, function (option) {
            var available = option.dataset.department === departmentSelect.value;
            option.hidden = !available;
            option.disabled = !available;
            if (!available) option.selected = false;
        });
    }
    departmentSelect.addEventListener('change', filterCourses);
    filterCourses();
}
</script>
</body>
</html>