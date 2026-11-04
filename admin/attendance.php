<?php
require __DIR__ . '/../includes/functions.php';

if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Administrator access is required.');
}

$pageTitle = 'Attendance';
$activePage = 'attendance';
$dateInput = $_GET['date'] ?? date('Y-m-d');
$date = is_string($dateInput) ? $dateInput : date('Y-m-d');
$dateObject = DateTime::createFromFormat('!Y-m-d', $date);
if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
    $date = date('Y-m-d');
}
$records = [];
$summary = ['total' => 0, 'present' => 0, 'absent' => 0, 'late' => 0];
$databaseError = false;

try {
    $db = db_connection();
    $stmt = $db->prepare("SELECT COUNT(*) AS total, COALESCE(SUM(status = 'P'), 0) AS present, COALESCE(SUM(status = 'A'), 0) AS absent, COALESCE(SUM(status = 'L'), 0) AS late FROM attendance WHERE date = ?");
    $stmt->bind_param('s', $date);
    $stmt->execute();
    $summary = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $db->prepare('SELECT a.status, s.name, s.email, c.class_name, c.section, sub.subject_name FROM attendance a JOIN students s ON s.id = a.student_id JOIN classes c ON c.id = s.class_id JOIN subjects sub ON sub.id = a.subject_id WHERE a.date = ? ORDER BY c.class_name, c.section, s.name, sub.subject_name LIMIT 200');
    $stmt->bind_param('s', $date);
    $stmt->execute();
    $records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $exception) {
    $databaseError = true;
}

$presentPercent = $summary['total'] > 0 ? (int) round($summary['present'] / $summary['total'] * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | Ustudent</title>
<script>try { document.documentElement.dataset.theme = localStorage.getItem('admin-theme') === 'dark' ? 'dark' : 'light'; } catch (error) { document.documentElement.dataset.theme = 'light'; }</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-shell">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>
  <div class="admin-main">
    <?php require __DIR__ . '/../includes/navbar.php'; ?>
    <main class="dashboard-content">
      <div class="welcome-row"><div><p class="eyebrow">DAILY REGISTER</p><h2>Attendance</h2><p>Review class attendance by date.</p></div></div>
      <?php if ($databaseError): ?><div class="alert alert-error" role="alert">Attendance data is unavailable. Check the database connection and imported schema.</div><?php endif; ?>
      <section class="stats-grid attendance-stats" aria-label="Attendance totals">
        <article class="stat-card stat-card-green"><div class="stat-card-top"><span>RECORDS</span></div><strong><?= number_format((int) $summary['total']) ?></strong><p>Attendance entries</p></article>
        <article class="stat-card stat-card-white"><div class="stat-card-top"><span>PRESENT</span></div><strong><?= number_format((int) $summary['present']) ?></strong><p><?= $presentPercent ?>% of entries</p></article>
        <article class="stat-card stat-card-gold"><div class="stat-card-top"><span>ABSENT</span></div><strong><?= number_format((int) $summary['absent']) ?></strong><p>Marked absent</p></article>
        <article class="stat-card stat-card-white"><div class="stat-card-top"><span>LATE</span></div><strong><?= number_format((int) $summary['late']) ?></strong><p>Marked late</p></article>
      </section>
      <section class="panel record-panel">
        <div class="panel-heading"><div><p class="eyebrow">ATTENDANCE RECORDS</p><h3><?= e(date('l, j F Y', strtotime($date))) ?></h3></div>
          <form class="date-filter" method="get" action="attendance.php"><label for="attendance-date">Choose date</label><input id="attendance-date" type="date" name="date" value="<?= e($date) ?>"><button class="button button-outline" type="submit">View</button></form>
        </div>
        <?php if (!$records): ?><div class="empty-state"><span class="empty-mark" aria-hidden="true">A</span><div><strong>No attendance for this date</strong><p>Choose another date to view recorded attendance.</p></div></div>
        <?php else: ?><div class="record-table-wrap"><table class="record-table"><thead><tr><th>STUDENT</th><th>CLASS</th><th>SUBJECT</th><th>STATUS</th></tr></thead><tbody>
          <?php foreach ($records as $record): ?><tr><td><span class="student-name"><?= e($record['name']) ?><small><?= e($record['email']) ?></small></span></td><td><?= e($record['class_name'] . ' ' . $record['section']) ?></td><td><?= e($record['subject_name']) ?></td><td><span class="status-pill attendance-<?= e(strtolower($record['status'])) ?>"><?= $record['status'] === 'P' ? 'Present' : ($record['status'] === 'A' ? 'Absent' : 'Late') ?></span></td></tr><?php endforeach; ?>
        </tbody></table></div><p class="table-footnote">Showing <?= count($records) ?> records<?= count($records) === 200 ? ' (maximum reached)' : '' ?>.</p><?php endif; ?>
      </section>
    </main>
    <?php require __DIR__ . '/../includes/footer.php'; ?>
  </div>
</div>
<script src="../assets/js/admin-theme.js"></script>
</body>
</html>