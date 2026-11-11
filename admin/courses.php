
<?php
session_start();

include '../database/dbconfig.php';

// Admin Authentication
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Add Course
include 'courses_add.php';

// Update Course
include 'courses_edit.php';

// Delete Course
include 'courses_delete.php';

// Get all courses
$result = $conn->query("SELECT * FROM courses ORDER BY id ASC");

// Message
$msg = $_GET['msg'] ?? '';

// Add / Edit Form Control
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

$course_name = '';
$description = '';

// Get existing course data for Edit
if ($action === 'edit' && $id !== '') {

    $sql = "SELECT * FROM courses WHERE id='$id'";
    $editResult = $conn->query($sql);

    if ($editResult && $editResult->num_rows > 0) {

        $row = $editResult->fetch_assoc();

        $course_name = $row['course_name'];
        $description = $row['description'];

    } else {
        header('Location: courses.php');
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Admin Dashboard | Ustudent</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">

<?php include '../includes/admin_dashboard_css.php'; ?>

<!-- style css -->
 <link rel="stylesheet" href="../assets/css/courses.css">

</head>

<body>

<div class="app">

    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main -->
    <div class="main">

        <!-- Topbar -->
        <?php include '../includes/topbar.php'; ?>

        <main class="content">

            <!-- Page Header -->
            <div class="head">

                <div>
                    <h1>Courses</h1>
                    <p>Add, edit and delete the courses here</p>
                </div>

                <a href="courses.php?action=add" class="btn">
                    + Add course
                </a>

            </div>

            <!-- Message -->

            <?php if ($msg == 'added') { ?>

                <div class="alert ok">
                    Course added successfully.
                </div>

            <?php } elseif ($msg == 'updated') { ?>

                <div class="alert ok">
                    Course updated successfully.
                </div>

            <?php } elseif ($msg == 'deleted')  {?>

                <div class="alert ok">
                    Course deleted successfully.
                </div>

            <?php } ?>


            <!-- Courses Table -->

            <section class="card">

                <h2>All courses</h2>

                <table>

                    <thead>

                        <tr>
                            <th>#</th>
                            <th>Course name</th>
                            <th>Description</th>
                            <th class="num">Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php while ($row = $result->fetch_assoc()) { ?>

                        <tr>

                            <td>
                                <?php echo $row['id']; ?>
                            </td>

                            <td>
                                <b>
                                    <?php echo htmlspecialchars($row['course_name']); ?>
                                </b>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['description']); ?>
                            </td>

                            <td>

                                <div class="row-actions">

                                    <!-- Edit -->
                                    <a class="small-btn"
                                       href="courses.php?action=edit&id=<?php echo $row['id']; ?>">
                                        Edit
                                    </a>

                                    <!-- Delete -->
                                    <form method="post"
                                          onsubmit="return confirm('Delete this course?');">

                                        <input type="hidden"
                                               name="id"
                                               value="<?php echo $row['id']; ?>">

                                        <button type="submit"
                                                name="delete_course"
                                                class="small-btn danger">
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </section>

        </main>

    </div>

</div>


<!-- PHP Add / Edit Form -->

<?php if ($action === 'add' || $action === 'edit') { ?>

<div class="overlay open">

    <div class="modal">

        <div class="modal-head">

            <h2>
                <?php echo ($action === 'add') ? 'Add course' : 'Edit course'; ?>
            </h2>

            <a href="courses.php" class="close">&times;</a>

        </div>

        <form method="post">

            <?php if ($action === 'edit') { ?>

                <input type="hidden"
                       name="id"
                       value="<?php echo htmlspecialchars($id); ?>">

            <?php } ?>

            <div class="modal-body">

                <div class="field">

                    <label for="course_name">Course name</label>

                    <input type="text"
                           name="course_name"
                           id="course_name"
                           maxlength="100"
                           value="<?php echo htmlspecialchars($course_name); ?>"
                           required>

                </div>

                <div class="field">

                    <label for="description">Description (optional)</label>

                    <textarea name="description"
                              id="description"><?php echo htmlspecialchars($description); ?></textarea>

                </div>

            </div>

            <div class="modal-foot">

                <a href="courses.php" class="btn ghost">
                    Cancel
                </a>

                <button type="submit"
                        class="btn"
                        name="<?php echo ($action === 'add') ? 'add_course' : 'update_course'; ?>">

                    <?php echo ($action === 'add') ? 'Save course' : 'Update course'; ?>

                </button>

            </div>

        </form>

    </div>

</div>

<?php } ?>


<?php include '../includes/footer.php'; ?>

</body>
</html>
