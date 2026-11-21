<?php
session_start();
include '../database/dbconfig.php';

// Student Authentication
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function rows($conn, $sql, $types = '', $params = []) {
    $st = $conn->prepare($sql);
    if (!$st) return [];
    if ($types !== '') $st->bind_param($types, ...$params);
    $st->execute();
    $res = $st->get_result();
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function gradeOf($m) {
    if ($m >= 80) return ['A+', 'ap'];
    if ($m >= 70) return ['A',  'a'];
    if ($m >= 60) return ['B',  'b'];
    if ($m >= 50) return ['C',  'c'];
    if ($m >= 40) return ['D',  'd'];
    return ['F', 'f'];
}

/* ---------- who is this student? ----------
   $_SESSION['id'] is the USERS id. The students table has its own id, linked by students.user_id.
   Using the session id (not an id from the URL) means a student can only ever see their own marks. */
$me = rows($conn,
    "SELECT s.id, s.name,
            GROUP_CONCAT(DISTINCT CONCAT(c.class_name, ' - ', c.section) ORDER BY c.class_name, c.section SEPARATOR ', ') AS class_names
       FROM students s
       LEFT JOIN student_classes sc ON sc.student_id = s.id
       LEFT JOIN classes c ON c.id = sc.class_id
      WHERE s.user_id = ?
      GROUP BY s.id, s.name", "i", [(int)$_SESSION['id']])[0] ?? null;

$examTypes = ['Quiz', 'Assignment', 'Midterm', 'Final'];
$examType = $_GET['exam_type'] ?? '';
if (!in_array($examType, $examTypes, true)) $examType = '';      // '' = all exams

$marks = [];
$att = ['total' => 0, 'ok' => 0];
if ($me) {
    $marks = rows($conn,
        "SELECT m.exam_type, m.marks, m.exam_date, sub.subject_name
           FROM marks m
           JOIN subjects sub ON sub.id = m.subject_id
          WHERE m.id = (
                SELECT MAX(m2.id) FROM marks m2
                 WHERE m2.student_id = m.student_id
                   AND m2.subject_id = m.subject_id
                   AND m2.exam_type = m.exam_type
          )
            AND m.student_id = ? AND (? = '' OR m.exam_type = ?)
          ORDER BY m.exam_date DESC, sub.subject_name",
        "iss", [(int)$me['id'], $examType, $examType]);

    $a = rows($conn,
        "SELECT COUNT(*) AS total, COALESCE(SUM(status IN ('P','L')), 0) AS ok FROM attendance WHERE student_id = ?",
        "i", [(int)$me['id']])[0] ?? null;
    if ($a) $att = ['total' => (int)$a['total'], 'ok' => (int)$a['ok']];
}

$values = array_map(function ($r) { return (float)$r['marks']; }, $marks);
$avg  = $values ? round(array_sum($values) / count($values), 1) : 0;
$high = $values ? max($values) : 0;
$attPct = $att['total'] ? round(100 * $att['ok'] / $att['total']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Marks | Ustudent</title>
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

  <?php include '../includes/sidebar_student.php'; ?>

  <div class="main">
    <?php include '../includes/topbar.php'; ?>

    <main class="content">

      <div class="head">
        <div>
          <h1>My Marks</h1>
          <p><?php echo $me ? h($me['name'] . ' · ' . $me['class_names']) : 'Your results appear here.'; ?></p>
        </div>
      </div>

      <?php if (!$me): ?>
        <div class="alert alert-warning">Your student profile has not been created yet. Please ask the admin to add you in the Students page.</div>
      <?php else: ?>

        <section class="stats" aria-label="Summary">
          <div class="stat"><span class="ico b"><svg viewBox="0 0 24 24"><path d="M9 11l3 3 8-8"/><path d="M20 12v7a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2h9"/></svg></span><div><b><?php echo count($marks); ?></b><span>Results</span></div></div>
          <div class="stat"><span class="ico g"><svg viewBox="0 0 24 24"><path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/></svg></span><div><b><?php echo $avg; ?></b><span>Average marks</span></div></div>
          <div class="stat"><span class="ico y"><svg viewBox="0 0 24 24"><path d="M12 3l3 6 6 1-4.5 4.5L18 21l-6-3-6 3 1.5-6.5L3 10l6-1z"/></svg></span><div><b><?php echo $high; ?></b><span>Highest mark</span></div></div>
          <div class="stat"><span class="ico r"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/></svg></span><div><b><?php echo $attPct; ?>%</b><span>Attendance</span></div></div>
        </section>

        <section class="card">
          <h2>Results</h2>
          <form class="tool-bar" method="get" action="student_mark.php">
            <select class="form-select" name="exam_type" aria-label="Filter by exam" onchange="this.form.submit()">
              <option value="">All exams</option>
              <?php foreach ($examTypes as $t): ?>
                <option <?php echo $examType === $t ? 'selected' : ''; ?>><?php echo h($t); ?></option>
              <?php endforeach; ?>
            </select>
          </form>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Subject</th><th>Exam</th><th>Date</th><th>Marks</th><th>Grade</th></tr></thead>
              <tbody>
              <?php if (!$marks): ?>
                <tr><td colspan="5" class="muted" style="padding:32px;text-align:center">No marks have been published yet.</td></tr>
              <?php endif; ?>
              <?php foreach ($marks as $r): $g = gradeOf((float)$r['marks']); ?>
                <tr>
                  <td class="strong"><?php echo h($r['subject_name']); ?></td>
                  <td><?php echo h($r['exam_type']); ?></td>
                  <td><?php echo h(date('d M Y', strtotime($r['exam_date']))); ?></td>
                  <td><b><?php echo h($r['marks'] + 0); ?></b> <span class="muted">/ 100</span></td>
                  <td><span class="grade <?php echo $g[1]; ?>"><?php echo $g[0]; ?></span></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="card-foot">
            <div class="scale"><span><b>A+</b> 80+</span><span><b>A</b> 70+</span><span><b>B</b> 60+</span><span><b>C</b> 50+</span><span><b>D</b> 40+</span><span><b>F</b> below 40</span></div>
          </div>
        </section>

      <?php endif; ?>
    </main>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
<script>
// Highlight "Marks" in the sidebar (your student sidebar links are still "#")
document.querySelectorAll('aside nav a').forEach(function (a) {
  a.classList.toggle('on', a.textContent.trim() === 'Marks');
});
</script>
</body>
</html>