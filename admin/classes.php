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

// Add Class
if (isset($_POST['add_class'])) {
    $class_name = $conn->real_escape_string($_POST['class_name']);
    $section = $conn->real_escape_string($_POST['section']);
    $course_id = (int)$_POST['course_id'];

    $courseResult = $conn->query("SELECT id FROM courses WHERE id=$course_id");

    if (!$courseResult) {
        header("Location: classes.php?msg=error");
        exit;
    }

    if ($courseResult->num_rows === 0) {
        header("Location: classes.php?msg=invalid_course");
        exit;
    }

    if ($conn->query("INSERT INTO classes (class_name, section, course_id) VALUES ('$class_name', '$section', $course_id)")) {
        header("Location: classes.php?msg=added");
        exit;
    }

    header("Location: classes.php?msg=error");
    exit;
}

// Update Class
if (isset($_POST['update_class'])) {
    $class_id = (int)$_POST['id'];
    $class_name = $conn->real_escape_string($_POST['class_name']);
    $section = $conn->real_escape_string($_POST['section']);
    $course_id = (int)$_POST['course_id'];

    $classResult = $conn->query("SELECT id FROM classes WHERE id=$class_id");
    $courseResult = $conn->query("SELECT id FROM courses WHERE id=$course_id");

    if (!$classResult || !$courseResult) {
        header("Location: classes.php?msg=error");
        exit;
    }

    if ($classResult->num_rows === 0) {
        header("Location: classes.php?msg=not_found");
        exit;
    }

    if ($courseResult->num_rows === 0) {
        header("Location: classes.php?msg=invalid_course");
        exit;
    }

    if ($conn->query("UPDATE classes SET class_name='$class_name', section='$section', course_id=$course_id WHERE id=$class_id")) {
        header("Location: classes.php?msg=updated");
        exit;
    }

    header("Location: classes.php?msg=error");
    exit;
}

// Delete Class
if (isset($_POST['delete_class'])) {
    $class_id = (int)$_POST['id'];
    $classResult = $conn->query("SELECT id FROM classes WHERE id=$class_id");

    if (!$classResult) {
        header("Location: classes.php?msg=error");
        exit;
    }

    if ($classResult->num_rows === 0) {
        header("Location: classes.php?msg=not_found");
        exit;
    }

    if ($conn->query("DELETE FROM classes WHERE id=$class_id")) {
        header("Location: classes.php?msg=deleted");
        exit;
    }

    header("Location: classes.php?msg=in_use");
    exit;
}

// Get all classes and courses
$result = $conn->query("SELECT classes.*, courses.course_name
                        FROM classes
                        INNER JOIN courses ON classes.course_id=courses.id
                        ORDER BY classes.id ASC");
$courses = $conn->query("SELECT * FROM courses ORDER BY id ASC");

if (!$result || !$courses) {
    $msg = 'error';
}

$class_name = '';
$section = '';
$course_id = '';

// Existing class data for Edit
if ($action === 'edit' && $id > 0) {
    $editResult = $conn->query("SELECT * FROM classes WHERE id=$id");

    if (!$editResult) {
        header('Location: classes.php?msg=error');
        exit;
    }

    if ($editResult->num_rows > 0) {
        $row = $editResult->fetch_assoc();
        $class_name = $row['class_name'];
        $section = $row['section'];
        $course_id = $row['course_id'];
    } else {
        header('Location: classes.php?msg=not_found');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard | Classes</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">

<?php include '../includes/admin_dashboard_css.php'; ?>
<link rel="stylesheet" href="../assets/css/courses.css">
</head>

<body>
<div class="app">

    <?php include '../includes/sidebar.php'; ?>

    <div class="main">
        <?php include '../includes/topbar.php'; ?>

        <main class="content">
            <div class="head">
                <div>
                    <h1>Classes</h1>
                    <p>Add, edit and delete the classes here</p>
                </div>

                <a href="classes.php?action=add" class="btn">+ Add class</a>
            </div>

            <?php if ($msg === 'added') { ?>
                <div class="alert ok">Class added successfully.</div>
            <?php } elseif ($msg === 'updated') { ?>
                <div class="alert ok">Class updated successfully.</div>
            <?php } elseif ($msg === 'deleted') { ?>
                <div class="alert ok">Class deleted successfully.</div>
            <?php } elseif ($msg === 'in_use') { ?>
                <div class="alert bad">This class cannot be deleted while students or teacher assignments use it.</div>
            <?php } elseif ($msg === 'invalid_course') { ?>
                <div class="alert bad">Select a valid course for this class.</div>
            <?php } elseif ($msg === 'not_found') { ?>
                <div class="alert bad">The class could not be found.</div>
            <?php } elseif ($msg === 'error') { ?>
                <div class="alert bad">The request could not be completed. Check the information and database connection.</div>
            <?php } ?>

            <section class="card">
                <h2>All classes</h2>

                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Class name</th>
                            <th>Section</th>
                            <th>Course</th>
                            <th class="num">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($result && $result->num_rows > 0) { ?>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><b><?php echo htmlspecialchars($row['class_name'], ENT_QUOTES, 'UTF-8'); ?></b></td>
                                <td><?php echo htmlspecialchars($row['section'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['course_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <div class="row-actions">
                                        <a class="small-btn" href="classes.php?action=edit&id=<?php echo $row['id']; ?>">Edit</a>
                                        <form method="post" onsubmit="return confirm('Delete this class?');">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete_class" class="small-btn danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="5" class="empty">No classes found.</td>
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
    <div class="modal">
        <div class="modal-head">
            <h2><?php echo ($action === 'add') ? 'Add class' : 'Edit class'; ?></h2>
            <a href="classes.php" class="close">&times;</a>
        </div>

        <form method="post">
            <?php if ($action === 'edit') { ?>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
            <?php } ?>

            <div class="modal-body">
                <div class="field">
                    <label for="class_name">Class name</label>
                    <input type="text" name="class_name" id="class_name" maxlength="50" value="<?php echo htmlspecialchars($class_name, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="field">
                    <label for="section">Section</label>
                    <input type="text" name="section" id="section" maxlength="10" value="<?php echo htmlspecialchars($section, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="field">
                    <label for="course_id">Course</label>
                    <select name="course_id" id="course_id" required>
                        <option value="">Select a course</option>
                        <?php if ($courses) { ?>
                            <?php while ($course = $courses->fetch_assoc()) { ?>
                                <option value="<?php echo $course['id']; ?>" <?php echo ((string)$course_id === (string)$course['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="modal-foot">
                <a href="classes.php" class="btn ghost">Cancel</a>
                <button type="submit" class="btn" name="<?php echo ($action === 'add') ? 'add_class' : 'update_class'; ?>">
                    <?php echo ($action === 'add') ? 'Save class' : 'Update class'; ?>
                </button>
            </div>
        </form>
    </div>
</div>
<?php } ?>

<?php include '../includes/footer.php'; ?>
</body>
</html>