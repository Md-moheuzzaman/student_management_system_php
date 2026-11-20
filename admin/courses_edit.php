<?php 
if (isset($_POST['update_course'])) {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim((string)($_POST['course_name'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $subjectName = $description !== '' ? $description : $name;

    $courseStatement = $conn->prepare("SELECT course_name, description FROM courses WHERE id = ?");
    if (!$courseStatement) {
        header("Location: courses.php?msg=error");
        exit;
    }
    $courseStatement->bind_param("i", $id);
    if (!$courseStatement->execute()) {
        header("Location: courses.php?msg=error");
        exit;
    }
    $oldCourse = $courseStatement->get_result()->fetch_assoc();
    if (!$oldCourse) {
        header("Location: courses.php?msg=error");
        exit;
    }
    $oldSubjectName = trim((string)$oldCourse['description']);
    if ($oldSubjectName === '') {
        $oldSubjectName = $oldCourse['course_name'];
    }

    $updateCourse = $conn->prepare("UPDATE courses SET course_name = ?, description = ? WHERE id = ?");
    $renameSubject = $conn->prepare("UPDATE subjects SET subject_name = LEFT(?, 100) WHERE course_id = ? AND subject_name = LEFT(?, 100)");
    $findSubject = $conn->prepare("SELECT id FROM subjects WHERE course_id = ? LIMIT 1");
    $insertSubject = $conn->prepare("INSERT INTO subjects (subject_name, course_id) VALUES (LEFT(?, 100), ?)");
    if (!$updateCourse || !$renameSubject || !$findSubject || !$insertSubject) {
        header("Location: courses.php?msg=error");
        exit;
    }

    $conn->begin_transaction();
    $updateCourse->bind_param("ssi", $name, $description, $id);
    if ($updateCourse->execute()) {
        $renameSubject->bind_param("sis", $subjectName, $id, $oldSubjectName);
        if ($renameSubject->execute()) {
            $findSubject->bind_param("i", $id);
            if ($findSubject->execute()) {
                $hasSubject = $findSubject->get_result()->fetch_assoc();
                if ($hasSubject || $insertSubject->bind_param("si", $subjectName, $id) && $insertSubject->execute()) {
                    $conn->commit();
                    header("Location: courses.php?msg=updated");
                    exit;
                }
            }
        }
    }

    $conn->rollback();
    header("Location: courses.php?msg=error");
    exit;
}
?>