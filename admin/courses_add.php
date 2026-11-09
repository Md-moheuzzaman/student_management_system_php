<?php
if (isset($_POST['add_course'])) {

    $name = ($_POST['course_name']);
    $description = ($_POST['description']);

    $conn->query("INSERT INTO courses (course_name, description) VALUES ('$name', '$description')");
    header("Location: courses.php");
    exit;
}
?>