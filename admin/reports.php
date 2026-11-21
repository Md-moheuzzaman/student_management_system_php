<?php
session_start();
include '../database/dbconfig.php';

// Admin Authentication
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
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

/* ---------- filters ---------- */
$classId = (int)($_GET['class_id'] ?? 0);               // 0 = all classes
$from = $_GET['from'] ?? date('Y-m-01');                // default: this month
$to   = $_GET['to']   ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = date('Y-m-d');

/* ---------- CSV export (one line per student) ---------- */
// A class filter matches any course/class the student is enrolled in.
$studentSql =
    "SELECT s.name, CONCAT(c.class_name, ' - ', c.section) AS class_name,
            (SELECT ROUND(100 * SUM(a.status IN ('P','L')) / COUNT(*), 1)
               FROM attendance a
               JOIN subjects sub ON sub.id = a.subject_id
               JOIN student_classes sc ON sc.student_id = s.id
               JOIN classes ec ON ec.id = sc.class_id AND ec.course_id = sub.course_id
              WHERE a.student_id = s.id AND a.date BETWEEN ? AND ?
                AND (? = 0 OR sc.class_id = ?)) AS att_pct,
            (SELECT ROUND(AVG(m.marks), 1)
               FROM marks m
               JOIN subjects sub ON sub.id = m.subject_id
               JOIN student_classes sc ON sc.student_id = s.id
               JOIN classes ec ON ec.id = sc.class_id AND ec.course_id = sub.course_id
              WHERE m.student_id = s.id AND m.exam_date BETWEEN ? AND ?
                AND (? = 0 OR sc.class_id = ?)) AS avg_marks,
            (SELECT COALESCE(SUM(f.amount), 0) FROM fees f
              WHERE f.student_id = s.id AND f.status IN ('Paid','Partial')) AS fees_paid
       FROM students s JOIN classes c ON c.id = s.class_id
      WHERE (? = 0 OR EXISTS (
          SELECT 1 FROM student_classes sc
           WHERE sc.student_id = s.id AND sc.class_id = ?
      ))
      ORDER BY c.class_name, c.section, s.name";

if (isset($_GET['export'])) {
    $list = rows($conn, $studentSql, "ssiissiiii", [$from, $to, $classId, $classId, $from, $to, $classId, $classId, $classId, $classId]);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="student_report_' . $from . '_to_' . $to . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Student', 'Class', 'Attendance %', 'Average marks', 'Fees paid']);
    foreach ($list as $r) fputcsv($out, [$r['name'], $r['class_name'], $r['att_pct'], $r['avg_marks'], $r['fees_paid']]);
    fclose($out);
    exit;
}

/* ---------- numbers for the page ---------- */
$classes = rows($conn, "SELECT id, class_name, section FROM classes ORDER BY class_name, section");

$totalStudents = (int)(rows($conn,
    "SELECT COUNT(*) AS n FROM students s
      WHERE (? = 0 OR EXISTS (
          SELECT 1 FROM student_classes sc
           WHERE sc.student_id = s.id AND sc.class_id = ?
      ))", "ii", [$classId, $classId])[0]['n'] ?? 0);

// attendance: Present and Late both count as "attended"
$attByClass = rows($conn,
    "SELECT CONCAT(c.class_name, ' - ', c.section) AS class_name, COUNT(*) AS total,
            SUM(a.status = 'P') AS p, SUM(a.status = 'A') AS a, SUM(a.status = 'L') AS l
       FROM attendance a
       JOIN students s ON s.id = a.student_id
       JOIN student_classes sc ON sc.student_id = s.id
       JOIN classes c ON c.id = sc.class_id
       JOIN subjects sub ON sub.id = a.subject_id AND sub.course_id = c.course_id
      WHERE a.date BETWEEN ? AND ? AND (? = 0 OR sc.class_id = ?)
      GROUP BY c.id ORDER BY c.class_name, c.section",
    "ssii", [$from, $to, $classId, $classId]);
$attTotal = 0; $attOk = 0;
foreach ($attByClass as $r) { $attTotal += (int)$r['total']; $attOk += (int)$r['p'] + (int)$r['l']; }
$attPct = $attTotal ? round(100 * $attOk / $attTotal) : 0;

$marksBySubject = rows($conn,
    "SELECT sub.subject_name, COUNT(*) AS n, ROUND(AVG(m.marks), 1) AS avg_m, MAX(m.marks) AS hi, MIN(m.marks) AS lo
       FROM marks m
       JOIN subjects sub ON sub.id = m.subject_id
       JOIN students s ON s.id = m.student_id
      WHERE m.exam_date BETWEEN ? AND ? AND (? = 0 OR EXISTS (
          SELECT 1 FROM student_classes sc
           JOIN classes c ON c.id = sc.class_id
           WHERE sc.student_id = s.id AND sc.class_id = ? AND c.course_id = sub.course_id
      ))
      GROUP BY sub.id ORDER BY sub.subject_name",
    "ssii", [$from, $to, $classId, $classId]);

$avgMarks = (float)(rows($conn,
    "SELECT ROUND(AVG(m.marks), 1) AS a
       FROM marks m
       JOIN subjects sub ON sub.id = m.subject_id
       JOIN students s ON s.id = m.student_id
      WHERE m.exam_date BETWEEN ? AND ? AND (? = 0 OR EXISTS (
          SELECT 1 FROM student_classes sc
           JOIN classes c ON c.id = sc.class_id
           WHERE sc.student_id = s.id AND sc.class_id = ? AND c.course_id = sub.course_id
      ))",
    "ssii", [$from, $to, $classId, $classId])[0]['a'] ?? 0);

$topStudents = rows($conn,
    "SELECT s.name, ROUND(AVG(m.marks), 1) AS avg_m
       FROM marks m
       JOIN subjects sub ON sub.id = m.subject_id
       JOIN students s ON s.id = m.student_id
      WHERE m.exam_date BETWEEN ? AND ? AND (? = 0 OR EXISTS (
          SELECT 1 FROM student_classes sc
           JOIN classes c ON c.id = sc.class_id
           WHERE sc.student_id = s.id AND sc.class_id = ? AND c.course_id = sub.course_id
      ))
      GROUP BY s.id ORDER BY avg_m DESC, s.name LIMIT 5",
    "ssii", [$from, $to, $classId, $classId]);

// fees: a record counts in the period by its payment date (or the day it was created if unpaid)
$feeRows = rows($conn,
    "SELECT f.status, COUNT(*) AS n, COALESCE(SUM(f.amount), 0) AS total
       FROM fees f JOIN students s ON s.id = f.student_id
      WHERE COALESCE(f.payment_date, DATE(f.created_at)) BETWEEN ? AND ? AND (? = 0 OR EXISTS (
          SELECT 1 FROM student_classes sc
           WHERE sc.student_id = s.id AND sc.class_id = ?
      ))
      GROUP BY f.status",
    "ssii", [$from, $to, $classId, $classId]);
$fee = ['Paid' => ['n' => 0, 'total' => 0], 'Partial' => ['n' => 0, 'total' => 0], 'Unpaid' => ['n' => 0, 'total' => 0]];
foreach ($feeRows as $r) $fee[$r['status']] = ['n' => (int)$r['n'], 'total' => (float)$r['total']];
$feeCount = $fee['Paid']['n'] + $fee['Partial']['n'] + $fee['Unpaid']['n'];
$collected = $fee['Paid']['total'] + $fee['Partial']['total'];
function pct($n, $all) { return $all ? round(100 * $n / $all) : 0; }

$exportUrl = 'reports.php?' . http_build_query(['class_id' => $classId, 'from' => $from, 'to' => $to, 'export' => 1]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reports | Ustudent</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<link rel="stylesheet" href="../assets/css/academic_common.css">
<link rel="stylesheet" href="../assets/css/mark.css">
<link rel="stylesheet" href="../assets/css/reports.css">
<style>@media print { .side, .top, .site-footer, .no-print { display: none !important; } }</style>
</head>
<body>
<div class="app">

  <?php include '../includes/sidebar.php'; ?>

  <div class="main">
    <?php include '../includes/topbar.php'; ?>

    <main class="content">

      <div class="head">
        <div>
          <h1>Reports</h1>
          <p>Attendance, marks and fee summaries for <?php echo h(date('d M Y', strtotime($from))); ?> to <?php echo h(date('d M Y', strtotime($to))); ?>.</p>
        </div>
        <div class="head-actions no-print">
          <button class="btn ghost" type="button" onclick="window.print()">Print</button>
          <a class="btn" href="<?php echo h($exportUrl); ?>">Export CSV</a>
        </div>
      </div>

      <!-- Filters -->
      <section class="card mb-4 no-print">
        <h2>Report filters</h2>
        <div class="card-body">
          <form class="row g-3 align-items-end" method="get" action="reports.php">
            <div class="col-md-4">
              <label class="form-label" for="cls">Class</label>
              <select class="form-select" id="cls" name="class_id">
                <option value="0">All classes</option>
                <?php foreach ($classes as $c): ?>
                  <option value="<?php echo (int)$c['id']; ?>" <?php echo $classId === (int)$c['id'] ? 'selected' : ''; ?>>
                    <?php echo h($c['class_name'] . ' - ' . $c['section']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3"><label class="form-label" for="from">From</label>
              <input class="form-control" type="date" id="from" name="from" value="<?php echo h($from); ?>"></div>
            <div class="col-md-3"><label class="form-label" for="to">To</label>
              <input class="form-control" type="date" id="to" name="to" value="<?php echo h($to); ?>"></div>
            <div class="col-md-2 d-grid"><button class="btn" type="submit">Generate</button></div>
          </form>
        </div>
      </section>

      <!-- Summary cards -->
      <section class="stats" aria-label="Summary">
        <div class="stat"><span class="ico b"><svg viewBox="0 0 24 24"><path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.5 6-3v-5"/></svg></span><div><b><?php echo number_format($totalStudents); ?></b><span>Total students</span></div></div>
        <div class="stat"><span class="ico g"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/></svg></span><div><b><?php echo $attPct; ?>%</b><span>Average attendance</span></div></div>
        <div class="stat"><span class="ico y"><svg viewBox="0 0 24 24"><path d="M9 11l3 3 8-8"/><path d="M20 12v7a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2h9"/></svg></span><div><b><?php echo $avgMarks; ?></b><span>Average marks</span></div></div>
        <div class="stat"><span class="ico r"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></span><div><b>$<?php echo number_format($collected, 2); ?></b><span>Fees collected</span></div></div>
      </section>

      <div class="row2">
        <!-- Attendance by class -->
        <section class="card">
          <h2>Attendance by class</h2>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Class</th><th>Present</th><th>Late</th><th>Absent</th><th>Rate</th></tr></thead>
              <tbody>
              <?php if (!$attByClass): ?><tr><td colspan="5" class="muted" style="padding:28px;text-align:center">No attendance recorded in this period.</td></tr><?php endif; ?>
              <?php foreach ($attByClass as $r): $rate = pct((int)$r['p'] + (int)$r['l'], (int)$r['total']); ?>
                <tr>
                  <td class="strong"><?php echo h($r['class_name']); ?></td>
                  <td><?php echo (int)$r['p']; ?></td><td><?php echo (int)$r['l']; ?></td><td><?php echo (int)$r['a']; ?></td>
                  <td><span class="pill <?php echo $rate >= 85 ? 'paid' : ($rate >= 70 ? 'partial' : 'unpaid'); ?>"><?php echo $rate; ?>%</span></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Fee status -->
        <section class="card">
          <h2>Fee status</h2>
          <div class="breakdown">
            <?php foreach (['Paid' => 'paid', 'Partial' => 'partial', 'Unpaid' => 'unpaid'] as $label => $cls): $p = pct($fee[$label]['n'], $feeCount); ?>
              <div>
                <div class="line-head"><span><?php echo $label; ?></span><span><?php echo $fee[$label]['n']; ?> records &middot; <?php echo $p; ?>%</span></div>
                <div class="progress"><div class="progress-bar <?php echo $cls; ?>" style="width:<?php echo $p; ?>%"></div></div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="breakdown-note">Based on <?php echo $feeCount; ?> fee record(s) in this period.</div>
        </section>
      </div>

      <div class="row2">
        <!-- Marks by subject -->
        <section class="card">
          <h2>Marks by subject</h2>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Subject</th><th>Results</th><th>Average</th><th>Highest</th><th>Lowest</th></tr></thead>
              <tbody>
              <?php if (!$marksBySubject): ?><tr><td colspan="5" class="muted" style="padding:28px;text-align:center">No marks entered in this period.</td></tr><?php endif; ?>
              <?php foreach ($marksBySubject as $r): ?>
                <tr><td class="strong"><?php echo h($r['subject_name']); ?></td><td><?php echo (int)$r['n']; ?></td>
                  <td><?php echo h($r['avg_m']); ?></td><td><?php echo h($r['hi'] + 0); ?></td><td><?php echo h($r['lo'] + 0); ?></td></tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Top students -->
        <section class="card">
          <h2>Top students</h2>
          <div class="table-wrap">
            <table>
              <tbody>
              <?php if (!$topStudents): ?><tr><td class="muted" style="padding:28px;text-align:center">No marks entered in this period.</td></tr><?php endif; ?>
              <?php foreach ($topStudents as $i => $r): ?>
                <tr>
                  <td><div class="who"><span class="rank <?php echo $i === 0 ? 'first' : ''; ?>"><?php echo $i + 1; ?></span><b><?php echo h($r['name']); ?></b></div></td>
                  <td class="num strong"><?php echo h($r['avg_m']); ?> avg</td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      </div>

    </main>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
<script>
// Highlight "Reports" in the sidebar
document.querySelectorAll('aside nav a').forEach(function (a) {
  a.classList.toggle('on', a.getAttribute('href') === 'reports.php');
});
</script>
</body>
</html>