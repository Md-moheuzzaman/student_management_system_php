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

$pageTitle = 'Students';
$activePage = 'students';
$searchInput = $_GET['q'] ?? '';
$classInput = $_GET['class_id'] ?? 0;
$search = is_string($searchInput) ? substr(trim($searchInput), 0, 100) : '';
$classId = is_scalar($classInput) ? max(0, (int) $classInput) : 0;
$classes = [];
$students = [];
$studentCount = 0;
$databaseError = false;

try {
    $db = db_connection();
    $studentCount = (int) db_scalar($db, 'SELECT COUNT(*) FROM students');
    $classes = db_rows($db, 'SELECT id, class_name, section FROM classes ORDER BY class_name, section');
    $pattern = '%' . $search . '%';
    $stmt = $db->prepare('SELECT s.id, s.name, s.email, s.phone, s.created_at, c.class_name, c.section FROM students s LEFT JOIN classes c ON c.id = s.class_id WHERE (? = \'\' OR s.name LIKE ? OR s.email LIKE ?) AND (? = 0 OR c.id = ?) ORDER BY s.name LIMIT 100');
    $stmt->bind_param('sssii', $search, $pattern, $pattern, $classId, $classId);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $exception) {
    $databaseError = true;
}
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
      <div class="welcome-row"><div><p class="eyebrow">STUDENT DIRECTORY</p><h2>Student records</h2><p><?= number_format($studentCount) ?> registered students</p></div></div>
      <?php if ($databaseError): ?><div class="alert alert-error" role="alert">Student records are unavailable. Check the database connection and imported schema.</div><?php endif; ?>
      <section class="panel record-panel">
        <form class="record-filters" method="get" action="students.php">
          <label class="filter-search"><span>Search students</span><input type="search" name="q" value="<?= e($search) ?>" placeholder="Name or email"></label>
          <label><span>Class</span><select name="class_id"><option value="0">All classes</option><?php foreach ($classes as $class): ?><option value="<?= (int) $class['id'] ?>"<?= $classId === (int) $class['id'] ? ' selected' : '' ?>><?= e($class['class_name'] . ' ' . $class['section']) ?></option><?php endforeach; ?></select></label>
          <button class="button button-green filter-submit" type="submit">Apply filters</button>
          <?php if ($search !== '' || $classId > 0): ?><a class="filter-reset" href="students.php">Reset</a><?php endif; ?>
        </form>
        <?php if (!$students): ?><div class="empty-state"><span class="empty-mark" aria-hidden="true">S</span><div><strong><?= $databaseError ? 'No records to display' : 'No matching students' ?></strong><p><?= $databaseError ? 'Connect to your student database to load this directory.' : 'Try a different search or class filter.' ?></p></div></div>
        <?php else: ?>
          <div class="record-table-wrap"><table class="record-table"><thead><tr><th>STUDENT</th><th>CLASS</th><th>PHONE</th><th>ENROLLED</th></tr></thead><tbody>
          <?php foreach ($students as $student): ?><tr><td><span class="student-initial"><?= e(strtoupper(substr($student['name'], 0, 1))) ?></span><span class="student-name"><?= e($student['name']) ?><small><?= e($student['email']) ?></small></span></td><td><?= e(trim(($student['class_name'] ?? 'Unassigned') . ' ' . ($student['section'] ?? ''))) ?></td><td><?= e($student['phone'] ?: '—') ?></td><td><?= e(date('M j, Y', strtotime($student['created_at']))) ?></td></tr><?php endforeach; ?>
          </tbody></table></div><p class="table-footnote">Showing <?= count($students) ?> of up to 100 matching records</p>
        <?php endif; ?>
      </section>
    </main>
    <?php require __DIR__ . '/../includes/footer.php'; ?>
  </div>
</div>
<script src="../assets/js/admin-theme.js"></script>
</body>
</html>