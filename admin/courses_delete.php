<?php
include '../database/dbconfig.php';

if (isset($_POST['delete_course'])) {
    $id = (int)($_POST['id'] ?? 0);
    $exists = $conn->prepare("SELECT id FROM courses WHERE id = ?");
    if (!$exists) {
        header("Location: courses.php?msg=error");
        exit;
    }
    $exists->bind_param("i", $id);
    if (!$exists->execute()) {
        header("Location: courses.php?msg=error");
        exit;
    }
    if (!$exists->get_result()->fetch_assoc()) {
        header("Location: courses.php?msg=not_found");
        exit;
    }

    $deleteSubjects = $conn->prepare("DELETE FROM subjects WHERE course_id = ?");
    $deleteCourse = $conn->prepare("DELETE FROM courses WHERE id = ?");
    if (!$deleteSubjects || !$deleteCourse) {
        header("Location: courses.php?msg=error");
        exit;
    }

    $conn->begin_transaction();
    $deleteSubjects->bind_param("i", $id);
    $deleteCourse->bind_param("i", $id);
    if ($deleteSubjects->execute() && $deleteCourse->execute() && $deleteCourse->affected_rows > 0) {
        $conn->commit();
        header("Location: courses.php?msg=deleted");
        exit;
    }

    $conn->rollback();
    header("Location: courses.php?msg=in_use");
    exit;
}
?>