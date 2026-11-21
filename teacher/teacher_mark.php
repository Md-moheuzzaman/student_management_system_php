<?php
session_start();
include '../database/dbconfig.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }

function rows($conn, $sql, $types = '', $params = []) {
    $statement = $conn->prepare($sql);
    if (!$statement) {
        throw new RuntimeException('Could not prepare the database query.');
    }
    if ($types !== '' && !$statement->bind_param($types, ...$params)) {
        throw new RuntimeException('Could not bind the database query parameters.');
    }
    if (!$statement->execute()) {
        throw new RuntimeException('Could not load the requested data.');
    }
    $result = $statement->get_result();
    if (!$result) {
        throw new RuntimeException('Could not read the requested data.');
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

function validDate($value) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return false;
    $parts = explode('-', $value);
    return checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]);
}

$teacher = rows($conn, "SELECT id, name FROM teachers WHERE user_id = ?", "i", [(int)$_SESSION['id']])[0] ?? null;
$assignments = $teacher ? rows($conn,
    "SELECT ts.id, ts.class_id, ts.subject_id, c.class_name, c.section, sub.subject_name
       FROM teacher_subjects ts
       JOIN classes c ON c.id = ts.class_id
       JOIN subjects sub ON sub.id = ts.subject_id
      WHERE ts.teacher_id = ?
      ORDER BY c.class_name, c.section, sub.subject_name",
    "i", [(int)$teacher['id']]) : [];

$assignId = (int)($_REQUEST['assign'] ?? 0);
$examTypes = ['Quiz', 'Assignment', 'Midterm', 'Final'];
$examType = (string)($_REQUEST['exam_type'] ?? 'Midterm');
if (!in_array($examType, $examTypes, true)) $examType = 'Midterm';
$examDate = (string)($_REQUEST['exam_date'] ?? date('Y-m-d'));
if (!validDate($examDate) || $examDate > date('Y-m-d')) $examDate = date('Y-m-d');

$current = null;
foreach ($assignments as $assignment) {
    if ((int)$assignment['id'] === $assignId) {
        $current = $assignment;
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_marks']) && $current) {
    $classStudents = array_map('intval', array_column(
        rows($conn, "SELECT student_id AS id FROM student_classes WHERE class_id = ?", "i", [(int)$current['class_id']]), 'id'));
    $find = $conn->prepare("SELECT id FROM marks WHERE student_id = ? AND subject_id = ? AND exam_type = ? ORDER BY id DESC LIMIT 1");
    $update = $conn->prepare("UPDATE marks SET marks = ?, exam_date = ? WHERE id = ?");
    $insert = $conn->prepare("INSERT INTO marks (student_id, subject_id, exam_type, marks, exam_date) VALUES (?, ?, ?, ?, ?)");
    if (!$find || !$update || !$insert) {
        throw new RuntimeException('Could not prepare mark saving queries.');
    }

    $saved = 0;
    $skipped = 0;
    $conn->begin_transaction();
    try {
        foreach (($_POST['marks'] ?? []) as $studentId => $value) {
            $studentId = (int)$studentId;
            $value = trim((string)$value);
            if ($value === '') continue;
            if (!in_array($studentId, $classStudents, true) || !is_numeric($value) || (float)$value < 0 || (float)$value > 100) {
                $skipped++;
                continue;
            }

            $mark = (float)$value;
            $subjectId = (int)$current['subject_id'];
            $find->bind_param("iis", $studentId, $subjectId, $examType);
            if (!$find->execute()) throw new RuntimeException('Could not check existing marks.');
            $existing = $find->get_result()->fetch_assoc();

            if ($existing) {
                $markId = (int)$existing['id'];
                $update->bind_param("dsi", $mark, $examDate, $markId);
                if (!$update->execute()) throw new RuntimeException('Could not update marks.');
            } else {
                $insert->bind_param("iisds", $studentId, $subjectId, $examType, $mark, $examDate);
                if (!$insert->execute()) throw new RuntimeException('Could not save marks.');
            }
            $saved++;
        }
        $conn->commit();
    } catch (Throwable $error) {
        $conn->rollback();
        throw $error;
    }
    header("Location: teacher_mark.php?assign=$assignId&exam_type=" . urlencode($examType) .
        "&exam_date=" . urlencode($examDate) . "&saved=$saved&skipped=$skipped");
    exit;
}

$students = $current ? rows($conn,
    "SELECT s.id, s.name, s.email, m.marks
       FROM students s
       LEFT JOIN marks m ON m.id = (
           SELECT MAX(m2.id) FROM marks m2
            WHERE m2.student_id = s.id AND m2.subject_id = ? AND m2.exam_type = ?
       )
      WHERE EXISTS (
          SELECT 1 FROM student_classes sc
           WHERE sc.student_id = s.id AND sc.class_id = ?
      )
      ORDER BY s.name",
    "isi", [(int)$current['subject_id'], $examType, (int)$current['class_id']]) : [];
$enteredMarks = [];
foreach ($students as $student) {
    if ($student['marks'] !== null) $enteredMarks[] = (float)$student['marks'];
}
$average = $enteredMarks ? round(array_sum($enteredMarks) / count($enteredMarks), 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Enter Marks | Ustudent</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<link rel="stylesheet" href="../assets/css/academic_common.css">
<link rel="stylesheet" href="../assets/css/mark.css">
</head>
<body>
<div class="app">
  <?php include '../includes/sidebar_teacher.php'; ?>
  <div class="main">
    <?php include '../includes/topbar.php'; ?>
    <main class="content">
      <div class="head"><div><h1>Enter Marks</h1><p>Select one of your assigned subjects and record student marks out of 100.</p></div></div>

      <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">
          Saved marks for <?php echo (int)$_GET['saved']; ?> student(s).
          <?php if ((int)($_GET['skipped'] ?? 0) > 0): ?><?php echo (int)$_GET['skipped']; ?> invalid row(s) were skipped.<?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if (!$teacher): ?>
        <div class="alert alert-warning">Your teacher profile has not been created yet. Please ask the admin to add you in the Teachers page.</div>
      <?php elseif (!$assignments): ?>
        <div class="alert alert-warning">No class or subject is assigned to you yet.</div>
      <?php else: ?>
        <section class="card mb-4">
          <h2>Select subject and exam</h2>
          <div class="card-body">
            <form class="row g-3 align-items-end" method="get" action="teacher_mark.php">
              <div class="col-md-5">
                <label class="form-label" for="assignSel">Class and subject</label>
                <select class="form-select" id="assignSel" name="assign" required>
                  <?php foreach ($assignments as $assignment): ?>
                    <option value="<?php echo (int)$assignment['id']; ?>" <?php echo $assignId === (int)$assignment['id'] ? 'selected' : ''; ?>>
                      <?php echo h($assignment['class_name'] . ' - ' . $assignment['section'] . ' | ' . $assignment['subject_name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label" for="examType">Exam</label>
                <select class="form-select" id="examType" name="exam_type">
                  <?php foreach ($examTypes as $type): ?>
                    <option value="<?php echo h($type); ?>" <?php echo $examType === $type ? 'selected' : ''; ?>><?php echo h($type); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label" for="examDate">Exam date</label>
                <input class="form-control" type="date" id="examDate" name="exam_date" value="<?php echo h($examDate); ?>" max="<?php echo date('Y-m-d'); ?>" required>
              </div>
              <div class="col-md-2 d-grid"><button class="btn" type="submit">Load students</button></div>
            </form>
          </div>
        </section>

        <?php if ($current): ?>
          <section class="stats" aria-label="Summary">
            <div class="stat"><span class="ico b"><svg viewBox="0 0 24 24"><path d="M2 9l10-5 10 5-10 5z"/></svg></span><div><b><?php echo count($students); ?></b><span>Students</span></div></div>
            <div class="stat"><span class="ico g"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span><div><b><?php echo count($enteredMarks); ?></b><span>Marks entered</span></div></div>
            <div class="stat"><span class="ico y"><svg viewBox="0 0 24 24"><path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/></svg></span><div><b><?php echo $average; ?></b><span>Average marks</span></div></div>
          </section>

          <form method="post" action="teacher_mark.php">
            <input type="hidden" name="assign" value="<?php echo $assignId; ?>">
            <input type="hidden" name="exam_type" value="<?php echo h($examType); ?>">
            <input type="hidden" name="exam_date" value="<?php echo h($examDate); ?>">
            <section class="card">
              <h2><?php echo h($current['subject_name'] . ' - ' . $examType); ?></h2>
              <div class="table-wrap">
                <table>
                  <thead><tr><th>#</th><th>Student</th><th>Marks (0-100)</th></tr></thead>
                  <tbody>
                  <?php if (!$students): ?>
                    <tr><td colspan="3" class="muted" style="padding:28px;text-align:center">No students are enrolled in this class yet.</td></tr>
                  <?php endif; ?>
                  <?php foreach ($students as $i => $student): ?>
                    <tr>
                      <td class="roll"><?php echo $i + 1; ?></td>
                      <td><div class="who"><span class="dot"><?php echo h(strtoupper(substr($student['name'], 0, 2))); ?></span><div><b><?php echo h($student['name']); ?></b><small><?php echo h($student['email']); ?></small></div></div></td>
                      <td><input class="form-control mark-input" type="number" name="marks[<?php echo (int)$student['id']; ?>]" min="0" max="100" step="0.01" value="<?php echo $student['marks'] !== null ? h($student['marks'] + 0) : ''; ?>"></td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <div class="card-foot">
                <div class="scale"><span><b>0-100</b> marks range</span><span>Blank rows are not changed</span></div>
                <?php if ($students): ?><button class="btn" type="submit" name="save_marks" value="1">Save marks</button><?php endif; ?>
              </div>
            </section>
          </form>
        <?php elseif ($assignId > 0): ?>
          <div class="alert alert-warning">That class and subject assignment is not available to your account.</div>
        <?php endif; ?>
      <?php endif; ?>
    </main>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
<script>
document.querySelectorAll('aside nav a').forEach(function (a) {
  a.classList.toggle('on', a.textContent.trim() === 'Marks');
});
</script>
</body>
</html>