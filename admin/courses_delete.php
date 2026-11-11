<?php
include '../database/dbconfig.php';

if (isset($_POST['delete_course'])) {
    $id = $_POST['id'];

    if ($conn->affected_rows) {
        header("Location: courses.php");
        exit;
    }

    $conn->query("DELETE FROM courses WHERE id=$id");
    header("Location: courses.php?msg=deleted");
    exit;
}
?>