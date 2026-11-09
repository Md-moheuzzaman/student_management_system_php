<?php 

if (isset($_POST['update_course'])) {
    $id = $_POST['id'];

    $name = ($_POST['course_name']);
    $description = ($_POST['description']);

    $conn->query("UPDATE courses SET course_name='$name', description='$description' WHERE id=$id");
    header("Location: courses.php?msg=updated");
    exit;
}
?>