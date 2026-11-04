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

$pageTitle = 'Academics';
$activePage = 'academics';
$summary = ['courses' => 0, 'classes' => 0, 'subjects' => 0, 'assignments' => 0];
$courses = [];
$classes = [];
$subjects = [];
$assignments = [];
$databaseError = false;

try {
    $db = db_connection();
    $summary['courses'] = (int) db_scalar($db, 'SELECT COUNT(*) FROM courses');
    $summary['classes'] = (int) db_scalar($db, 'SELECT COUNT(*) FROM classes');
    $summary['subjects'] = (int) db_scalar($db, 'SELECT COUNT(*) FROM subjects');
    $summary['assignments'] = (int) db_scalar($db, 'SELECT COUNT(*) FROM teacher_subjects');

    $courses = db_rows($db, 'SELECT co.course_name, co.description, COUNT(DISTINCT cl.id) AS class_count, COUNT(DISTINCT sub.id) AS subject_count, COUNT(DISTINCT s.id) AS student_count FROM courses co LEFT JOIN classes cl ON cl.course_id = co.id LEFT JOIN subjects sub ON sub.course_id = co.id LEFT JOIN students s ON s.class_id = cl.id GROUP BY co.id, co.course_name, co.description ORDER BY co.course_name LIMIT 100');
    $classes = db_rows($db, 'SELECT cl.class_name, cl.section, co.course_name, COUNT(DISTINCT s.id) AS student_count FROM classes cl JOIN courses co ON co.id = cl.course_id LEFT JOIN students s ON s.class_id = cl.id GROUP BY cl.id, cl.class_name, cl.section, co.course_name ORDER BY co.course_name, cl.class_name, cl.section LIMIT 100');
    $subjects = db_rows($db, 'SELECT sub.subject_name, co.course_name, COUNT(DISTINCT ts.class_id) AS class_count, COUNT(DISTINCT ts.teacher_id) AS teacher_count FROM subjects sub JOIN courses co ON co.id = sub.course_id LEFT JOIN teacher_subjects ts ON ts.subject_id = sub.id GROUP BY sub.id, sub.subject_name, co.course_name ORDER BY co.course_name, sub.subject_name LIMIT 100');
    $assignments = db_rows($db, 'SELECT t.name AS teacher_name, sub.subject_name, cl.class_name, cl.section, co.course_name FROM teacher_subjects ts JOIN teachers t ON t.id = ts.teacher_id JOIN subjects sub ON sub.id = ts.subject_id JOIN classes cl ON cl.id = ts.class_id JOIN courses co ON co.id = cl.course_id ORDER BY t.name, co.course_name, cl.class_name, sub.subject_name LIMIT 100');
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
      <div class="welcome-row"><div><p class="eyebrow">COURSES &amp; CURRICULUM</p><h2>Academic structure</h2><p>Courses, classes, subjects, and teaching assignments.</p></div></div>
      <?php if ($databaseError): ?><div class="alert alert-error" role="alert">Academic data is unavailable. Check the database connection and imported schema.</div><?php endif; ?>

      <section class="stats-grid" aria-label="Academic summary">
        <article class="stat-card stat-card-green"><div class="stat-card-top"><span>COURSES</span></div><strong><?= number_format($summary['courses']) ?></strong><p>Academic programs</p></article>
        <article class="stat-card stat-card-white"><div class="stat-card-top"><span>CLASSES</span></div><strong><?= number_format($summary['classes']) ?></strong><p>Class sections</p></article>
        <article class="stat-card stat-card-gold"><div class="stat-card-top"><span>SUBJECTS</span></div><strong><?= number_format($summary['subjects']) ?></strong><p>Across all courses</p></article>
        <article class="stat-card stat-card-white"><div class="stat-card-top"><span>TEACHING ASSIGNMENTS</span></div><strong><?= number_format($summary['assignments']) ?></strong><p>Subject and class links</p></article>
      </section>

      <div class="academic-page-grid">
        <section class="panel">
          <div class="panel-heading"><div><p class="eyebrow">PROGRAMS</p><h3>Courses</h3></div><span class="panel-count"><?= count($courses) ?> shown</span></div>
          <?php if (!$courses): ?><div class="empty-state compact-empty"><span class="empty-mark" aria-hidden="true">C</span><div><strong>No courses yet</strong><p>Course records will appear here.</p></div></div>
          <?php else: ?><div class="record-table-wrap"><table class="record-table"><thead><tr><th>COURSE</th><th>CLASSES</th><th>SUBJECTS</th><th>STUDENTS</th></tr></thead><tbody><?php foreach ($courses as $course): ?><tr><td><span class="student-name"><?= e($course['course_name']) ?><?php if (!empty($course['description'])): ?><small><?= e($course['description']) ?></small><?php endif; ?></span></td><td><?= number_format((int) $course['class_count']) ?></td><td><?= number_format((int) $course['subject_count']) ?></td><td><?= number_format((int) $course['student_count']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
        </section>

        <section class="panel">
          <div class="panel-heading"><div><p class="eyebrow">CLASS GROUPS</p><h3>Classes</h3></div><span class="panel-count"><?= count($classes) ?> shown</span></div>
          <?php if (!$classes): ?><div class="empty-state compact-empty"><span class="empty-mark" aria-hidden="true">K</span><div><strong>No classes yet</strong><p>Class and section records will appear here.</p></div></div>
          <?php else: ?><div class="record-table-wrap"><table class="record-table"><thead><tr><th>CLASS</th><th>COURSE</th><th>STUDENTS</th></tr></thead><tbody><?php foreach ($classes as $class): ?><tr><td><?= e($class['class_name'] . ' ' . $class['section']) ?></td><td><?= e($class['course_name']) ?></td><td><?= number_format((int) $class['student_count']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
        </section>

        <section class="panel">
          <div class="panel-heading"><div><p class="eyebrow">CURRICULUM</p><h3>Subjects</h3></div><span class="panel-count"><?= count($subjects) ?> shown</span></div>
          <?php if (!$subjects): ?><div class="empty-state compact-empty"><span class="empty-mark" aria-hidden="true">S</span><div><strong>No subjects yet</strong><p>Subject records will appear here.</p></div></div>
          <?php else: ?><div class="record-table-wrap"><table class="record-table"><thead><tr><th>SUBJECT</th><th>COURSE</th><th>CLASSES</th><th>TEACHERS</th></tr></thead><tbody><?php foreach ($subjects as $subject): ?><tr><td><?= e($subject['subject_name']) ?></td><td><?= e($subject['course_name']) ?></td><td><?= number_format((int) $subject['class_count']) ?></td><td><?= number_format((int) $subject['teacher_count']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
        </section>

        <section class="panel academic-assignments">
          <div class="panel-heading"><div><p class="eyebrow">STAFF &amp; CLASS LINKS</p><h3>Teaching assignments</h3></div><span class="panel-count"><?= count($assignments) ?> shown</span></div>
          <?php if (!$assignments): ?><div class="empty-state compact-empty"><span class="empty-mark" aria-hidden="true">T</span><div><strong>No teaching assignments yet</strong><p>Teacher, subject, and class links will appear here.</p></div></div>
          <?php else: ?><div class="record-table-wrap"><table class="record-table"><thead><tr><th>TEACHER</th><th>SUBJECT</th><th>CLASS</th><th>COURSE</th></tr></thead><tbody><?php foreach ($assignments as $assignment): ?><tr><td><?= e($assignment['teacher_name']) ?></td><td><?= e($assignment['subject_name']) ?></td><td><?= e($assignment['class_name'] . ' ' . $assignment['section']) ?></td><td><?= e($assignment['course_name']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
        </section>
      </div>
    </main>
    <?php require __DIR__ . '/../includes/footer.php'; ?>
  </div>
</div>
<script src="../assets/js/admin-theme.js"></script>
</body>
</html>