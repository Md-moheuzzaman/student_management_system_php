<?php
if (isset($_POST['add_course'])) {
    $name = trim((string)($_POST['course_name'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $subjectName = $description !== '' ? $description : $name;

    $courseStatement = $conn->prepare("INSERT INTO courses (course_name, description) VALUES (?, ?)");
    $subjectStatement = $conn->prepare("INSERT INTO subjects (subject_name, course_id) VALUES (LEFT(?, 100), ?)");
    if (!$courseStatement || !$subjectStatement) {
        header("Location: courses.php?msg=error");
        exit;
    }

    $conn->begin_transaction();
    $courseStatement->bind_param("ss", $name, $description);
    if ($courseStatement->execute()) {
        $courseId = (int)$conn->insert_id;
        $subjectStatement->bind_param("si", $subjectName, $courseId);
        if ($subjectStatement->execute()) {
            $conn->commit();
            header("Location: courses.php?msg=added");
            exit;
        }
    }

    $conn->rollback();
    header("Location: courses.php?msg=error");
    exit;
}
?>