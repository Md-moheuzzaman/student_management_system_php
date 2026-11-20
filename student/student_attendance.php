<?php
session_start();
include '../database/dbconfig.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
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

$student = rows($conn,
    "SELECT s.id, s.name, c.class_name, c.section
       FROM students s
       JOIN classes c ON c.id = s.class_id
      WHERE s.user_id = ?",
    "i", [(int)$_SESSION['id']])[0] ?? null;

$subjects = $student ? rows($conn,
    "SELECT DISTINCT sub.id, sub.subject_name
       FROM attendance a
       JOIN subjects sub ON sub.id = a.subject_id
      WHERE a.student_id = ?
      ORDER BY sub.subject_name",
    "i", [(int)$student['id']]) : [];

$subjectId = (int)($_GET['subject_id'] ?? 0);
$fromDate = (string)($_GET['from_date'] ?? '');
$toDate = (string)($_GET['to_date'] ?? '');
$filterError = '';
if ($fromDate !== '' && !validDate($fromDate)) {
    $fromDate = '';
    $filterError = 'The start date is invalid.';
}
if ($toDate !== '' && !validDate($toDate)) {
    $toDate = '';
    $filterError = 'The end date is invalid.';
}
if ($fromDate !== '' && $toDate !== '' && $fromDate > $toDate) {
    $filterError = 'The start date must be on or before the end date.';
    $fromDate = '';
    $toDate = '';
}

$validSubjectIds = array_map('intval', array_column($subjects, 'id'));
if (!in_array($subjectId, $validSubjectIds, true)) $subjectId = 0;

$attendance = [];
if ($student) {
    $sql = "SELECT a.date, a.status, sub.subject_name
              FROM attendance a
              JOIN subjects sub ON sub.id = a.subject_id
             WHERE a.student_id = ?";
    $types = 'i';
    $params = [(int)$student['id']];
    if ($subjectId > 0) {
        $sql .= " AND a.subject_id = ?";
        $types .= 'i';
        $params[] = $subjectId;
    }
    if ($fromDate !== '') {
        $sql .= " AND a.date >= ?";
        $types .= 's';
        $params[] = $fromDate;
    }
    if ($toDate !== '') {
        $sql .= " AND a.date <= ?";
        $types .= 's';
        $params[] = $toDate;
    }
    $attendance = rows($conn, $sql . " ORDER BY a.date DESC, sub.subject_name", $types, $params);
}

$present = 0;
$absent = 0;
$late = 0;
foreach ($attendance as $record) {
    if ($record['status'] === 'P') $present++;
    elseif ($record['status'] === 'A') $absent++;
    elseif ($record['status'] === 'L') $late++;
}
$attendanceRate = $attendance ? round(100 * ($present + $late) / count($attendance)) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Attendance | Ustudent</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<link rel="stylesheet" href="../assets/css/academic_common.css">
<link rel="stylesheet" href="../assets/css/attendance.css">
<link rel="stylesheet" href="../assets/css/mark.css">
</head>
<body>
<div class="app">
  <?php include '../includes/sidebar_student.php'; ?>
  <div class="main">
    <?php include '../includes/topbar.php'; ?>
    <main class="content">
      <div class="head">
        <div>
          <h1>My Attendance</h1>
          <p><?php echo $student ? h($student['name'] . ' · ' . $student['class_name'] . ' - ' . $student['section']) : 'Your individual attendance records.'; ?></p>
        </div>
      </div>

      <?php if (!$student): ?>
        <div class="alert alert-warning">Your student profile has not been created yet. Please ask the admin to add you in the Students page.</div>
      <?php else: ?>
        <?php if ($filterError !== ''): ?><div class="alert alert-warning"><?php echo h($filterError); ?></div><?php endif; ?>
        <section class="stats" aria-label="Attendance summary">
          <div class="stat"><span class="ico b"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/></svg></span><div><b><?php echo count($attendance); ?></b><span>Attendance records</span></div></div>
          <div class="stat"><span class="ico g"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span><div><b><?php echo $present; ?></b><span>Present</span></div></div>
          <div class="stat"><span class="ico r"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg></span><div><b><?php echo $absent; ?></b><span>Absent</span></div></div>
          <div class="stat"><span class="ico y"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><div><b><?php echo $attendanceRate; ?>%</b><span>Attendance rate</span></div></div>
        </section>

        <section class="card mb-4">
          <h2>Filter attendance</h2>
          <div class="card-body">
            <form class="row g-3 align-items-end" method="get" action="student_attendance.php">
              <div class="col-md-4">
                <label class="form-label" for="subjectSel">Subject</label>
                <select class="form-select" id="subjectSel" name="subject_id">
                  <option value="0">All subjects</option>
                  <?php foreach ($subjects as $subject): ?>
                    <option value="<?php echo (int)$subject['id']; ?>" <?php echo $subjectId === (int)$subject['id'] ? 'selected' : ''; ?>><?php echo h($subject['subject_name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label" for="fromDate">From date</label>
                <input class="form-control" type="date" id="fromDate" name="from_date" value="<?php echo h($fromDate); ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label" for="toDate">To date</label>
                <input class="form-control" type="date" id="toDate" name="to_date" value="<?php echo h($toDate); ?>">
              </div>
              <div class="col-md-2 d-grid"><button class="btn" type="submit">Apply filters</button></div>
            </form>
          </div>
        </section>

        <section class="card">
          <h2>Individual attendance records</h2>
          <div class="table-wrap">
            <table>
              <thead><tr><th>#</th><th>Date</th><th>Subject</th><th>Status</th></tr></thead>
              <tbody>
              <?php if (!$attendance): ?>
                <tr><td colspan="4" class="muted" style="padding:28px;text-align:center">No attendance records match your filters.</td></tr>
              <?php endif; ?>
              <?php foreach ($attendance as $i => $record): ?>
                <tr>
                  <td class="roll"><?php echo $i + 1; ?></td>
                  <td><?php echo h(date('d M Y', strtotime($record['date']))); ?></td>
                  <td><b><?php echo h($record['subject_name']); ?></b></td>
                  <td><span class="grade <?php echo $record['status'] === 'P' ? 'a' : ($record['status'] === 'A' ? 'f' : 'c'); ?>">
                    <?php echo $record['status'] === 'P' ? 'Present' : ($record['status'] === 'A' ? 'Absent' : 'Late'); ?>
                  </span></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      <?php endif; ?>
    </main>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
<script>
document.querySelectorAll('aside nav a').forEach(function (a) {
  a.classList.toggle('on', a.textContent.trim() === 'Attendance');
});
</script>
</body>
</html>