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

$student = rows($conn,
    "SELECT s.id, s.name,
            GROUP_CONCAT(DISTINCT CONCAT(c.class_name, ' - ', c.section) ORDER BY c.class_name, c.section SEPARATOR ', ') AS class_names,
            GROUP_CONCAT(DISTINCT co.course_name ORDER BY co.course_name SEPARATOR ', ') AS course_names
       FROM students s
       LEFT JOIN student_classes sc ON sc.student_id = s.id
       LEFT JOIN classes c ON c.id = sc.class_id
       JOIN courses co ON co.id = c.course_id
      WHERE s.user_id = ?
      GROUP BY s.id, s.name",
    "i", [(int)$_SESSION['id']])[0] ?? null;

$subjects = $student ? rows($conn,
    "SELECT DISTINCT sub.id, sub.subject_name, GROUP_CONCAT(DISTINCT t.name ORDER BY t.name SEPARATOR ', ') AS teachers
       FROM teacher_subjects ts
       JOIN student_classes sc ON sc.class_id = ts.class_id AND sc.student_id = ?
       JOIN subjects sub ON sub.id = ts.subject_id
       LEFT JOIN teachers t ON t.id = ts.teacher_id
      GROUP BY sub.id, sub.subject_name
      ORDER BY sub.subject_name",
    "i", [(int)$student['id']]) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Courses | Ustudent</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<link rel="stylesheet" href="../assets/css/academic_common.css">
</head>
<body>
<div class="app">
  <?php include '../includes/sidebar_student.php'; ?>
  <div class="main">
    <?php include '../includes/topbar.php'; ?>
    <main class="content">
      <div class="head">
        <div>
          <h1>My Courses</h1>
          <p><?php echo $student ? h($student['name'] . ' · ' . $student['class_names']) : 'Your enrolled courses and subjects.'; ?></p>
        </div>
      </div>

      <?php if (!$student): ?>
        <div class="alert alert-warning">Your student profile has not been created yet. Please ask the admin to add you in the Students page.</div>
      <?php else: ?>
        <section class="stats" aria-label="Summary">
          <div class="stat"><span class="ico b"><svg viewBox="0 0 24 24"><path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.5 6-3v-5"/></svg></span><div><b><?php echo h($student['class_names']); ?></b><span>My department</span></div></div>
          <div class="stat"><span class="ico g"><svg viewBox="0 0 24 24"><path d="M4 5a2 2 0 012-2h13v16H6a2 2 0 00-2 2z"/><path d="M4 19V5"/></svg></span><div><b><?php echo count($subjects); ?></b><span>Subjects</span></div></div>
          <div class="stat"><span class="ico y"><svg viewBox="0 0 24 24"><path d="M3 5h18M3 12h18M3 19h18"/></svg></span><div><b><?php echo h($student['course_names']); ?></b><span>My courses</span></div></div>
        </section>

        <section class="card">
          <h2>Subjects in <?php echo h($student['class_names']); ?></h2>
          <div class="table-wrap">
            <table>
              <thead><tr><th>#</th><th>Subject</th><th>Teacher</th></tr></thead>
              <tbody>
              <?php if (!$subjects): ?>
                <tr><td colspan="3" class="muted" style="padding:28px;text-align:center">No subjects have been assigned to your class yet.</td></tr>
              <?php endif; ?>
              <?php foreach ($subjects as $i => $subject): ?>
                <tr>
                  <td class="roll"><?php echo $i + 1; ?></td>
                  <td><b><?php echo h($subject['subject_name']); ?></b></td>
                  <td><?php echo h($subject['teachers'] ?? ''); ?></td>
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
  a.classList.toggle('on', a.textContent.trim() === 'Courses');
});
</script>
</body>
</html>