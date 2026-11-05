<?php
include __DIR__ . '/../includes/dbconfig.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | Ustudent</title>
<script>
try {
	document.documentElement.dataset.theme = localStorage.getItem('admin-theme') === 'dark' ? 'dark' : 'light';
} catch (error) {
	document.documentElement.dataset.theme = 'light';
}
</script>
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
		<main class="dashboard-content" id="overview">
			<div class="welcome-row"><div><p class="eyebrow">YOUR SCHOOL, AT A GLANCE</p><h2>Good day, <?= e($_SESSION['username'] ?? 'Administrator') ?>.</h2><p><?= e(date('l, j F Y')) ?> <span class="welcome-divider">/</span> Here is the latest overview.</p></div><a class="button button-outline" href="../index.php">View home <span aria-hidden="true">↗</span></a></div>
			<?php if ($databaseError): ?><div class="alert alert-error" role="alert">Dashboard data is unavailable. Start MySQL and import <code>student_management_system.sql</code>.</div><?php endif; ?>

			<section class="stats-grid" aria-label="School statistics">
				<article class="stat-card stat-card-green"><div class="stat-card-top"><span>ENROLLED STUDENTS</span><span class="stat-icon">S</span></div><strong><?= number_format($stats['students']) ?></strong><p>Student records</p></article>
				<article class="stat-card stat-card-white"><div class="stat-card-top"><span>TEACHING STAFF</span><span class="stat-icon icon-orange">T</span></div><strong><?= number_format($stats['teachers']) ?></strong><p>Teacher profiles</p></article>
				<article class="stat-card stat-card-white"><div class="stat-card-top"><span>CLASSES</span><span class="stat-icon icon-blue">C</span></div><strong><?= number_format($stats['classes']) ?></strong><p>Across <?= number_format($stats['courses']) ?> courses</p></article>
				<article class="stat-card stat-card-gold"><div class="stat-card-top"><span>FEES COLLECTED</span><span class="stat-icon">৳</span></div><strong class="stat-currency">৳<?= number_format($stats['collected'], 2) ?></strong><p>Paid fee records</p></article>
			</section>

			<div class="dashboard-grid">
				<section class="panel students-panel" id="students">
					<div class="panel-heading"><div><p class="eyebrow">LATEST ENTRIES</p><h3>Recently added students</h3></div><span class="panel-count"><?= count($recentStudents) ?> shown</span></div>
					<?php if (!$recentStudents): ?><div class="empty-state"><span class="empty-mark" aria-hidden="true">+</span><div><strong>No student records yet</strong><p>New student profiles will appear here.</p></div></div>
					<?php else: ?>
						<div class="table-scroll"><table><thead><tr><th>STUDENT</th><th>CLASS</th><th>ADDED</th></tr></thead><tbody>
							<?php foreach ($recentStudents as $student): ?><tr><td><span class="student-initial"><?= e(strtoupper(substr($student['name'], 0, 1))) ?></span><span class="student-name"><?= e($student['name']) ?><small><?= e($student['email']) ?></small></span></td><td><?= e(trim(($student['class_name'] ?? 'Unassigned') . ' ' . ($student['section'] ?? ''))) ?></td><td><?= e(date('M j, Y', strtotime($student['created_at']))) ?></td></tr><?php endforeach; ?>
						</tbody></table></div>
					<?php endif; ?>
				</section>

				<section class="panel attendance-panel" id="attendance">
					<div class="panel-heading"><div><p class="eyebrow">DAILY CHECK-IN</p><h3>Attendance today</h3></div><span class="today-mark"><?= e(date('M j')) ?></span></div>
					<?php if ($stats['attendance_total'] === 0): ?><div class="attendance-empty"><span class="attendance-ring">—</span><p>No attendance recorded today.</p></div>
					<?php else: ?><div class="attendance-summary"><div><strong><?= $attendancePercent ?><small>%</small></strong><p>present today</p></div><span class="attendance-ring" style="--progress:<?= $attendancePercent ?>%"><span><?= number_format($stats['attendance']) ?><small> / <?= number_format($stats['attendance_total']) ?></small></span></span></div><div class="progress-track"><span style="width:<?= $attendancePercent ?>%"></span></div><p class="progress-caption"><?= number_format($stats['attendance']) ?> present of <?= number_format($stats['attendance_total']) ?> attendance records</p><?php endif; ?>
				</section>

				<section class="panel fees-panel" id="fees">
					<div class="panel-heading"><div><p class="eyebrow">PAYMENTS TO FOLLOW UP</p><h3>Pending fees</h3></div><span class="fee-total"><?= number_format($stats['pending']) ?> open</span></div>
					<?php if (!$pendingFees): ?><div class="empty-state compact-empty"><span class="empty-mark" aria-hidden="true">✓</span><div><strong>All caught up</strong><p>There are no pending fee records.</p></div></div>
					<?php else: ?><div class="table-scroll"><table><thead><tr><th>STUDENT</th><th>AMOUNT</th><th>STATUS</th></tr></thead><tbody><?php foreach ($pendingFees as $fee): ?><tr><td><?= e($fee['name']) ?></td><td>৳<?= number_format((float) $fee['amount'], 2) ?></td><td><span class="status-pill <?= e(strtolower($fee['status'])) ?>"><?= e($fee['status']) ?></span></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
				</section>

				<section class="panel academic-panel" id="academics">
					<div class="panel-heading"><div><p class="eyebrow">LEARNING STRUCTURE</p><h3>Academic overview</h3></div><span class="academic-decoration" aria-hidden="true">✳</span></div>
					<div class="academic-numbers"><div><strong><?= number_format($stats['courses']) ?></strong><span>Courses</span></div><i></i><div><strong><?= number_format($stats['classes']) ?></strong><span>Classes</span></div><i></i><div><strong><?= number_format($stats['teachers']) ?></strong><span>Teachers</span></div></div>
					<p class="academic-note">A quick count of the teaching structure currently in your records.</p>
				</section>
			</div>
		</main>
		<?php require __DIR__ . '/../includes/footer.php'; ?>
	</div>
</div>
<script src="../assets/js/admin-theme.js"></script>
</body>
</html>
