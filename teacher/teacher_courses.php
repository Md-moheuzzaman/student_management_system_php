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

$teacher = rows($conn, "SELECT id, name FROM teachers WHERE user_id = ?", "i", [(int)$_SESSION['id']])[0] ?? null;
$courses = $teacher ? rows($conn,
    "SELECT ts.class_id, ts.subject_id, c.class_name, c.section, sub.subject_name,
            co.course_name
       FROM teacher_subjects ts
       JOIN classes c ON c.id = ts.class_id
       JOIN subjects sub ON sub.id = ts.subject_id
       JOIN courses co ON co.id = sub.course_id
      WHERE ts.teacher_id = ?
      ORDER BY c.class_name, c.section, sub.subject_name",
    "i", [(int)$teacher['id']]) : [];
$classCount = count(array_unique(array_column($courses, 'class_id')));
$subjectCount = count(array_unique(array_column($courses, 'subject_id')));
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
  <?php include '../includes/sidebar_teacher.php'; ?>
  <div class="main">
    <?php include '../includes/topbar.php'; ?>
    <main class="content">
      <div class="head">
        <div>
          <h1>My Courses</h1>
          <p><?php echo $teacher ? 'Classes and subjects assigned to ' . h($teacher['name']) . '.' : 'Your assigned classes and subjects.'; ?></p>
        </div>
      </div>

      <?php if (!$teacher): ?>
        <div class="alert alert-warning">Your teacher profile has not been created yet. Please ask the admin to add you in the Teachers page.</div>
      <?php else: ?>
        <section class="stats" aria-label="Summary">
          <div class="stat"><span class="ico b"><svg viewBox="0 0 24 24"><path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.5 6-3v-5"/></svg></span><div><b><?php echo $classCount; ?></b><span>Classes</span></div></div>
          <div class="stat"><span class="ico g"><svg viewBox="0 0 24 24"><path d="M4 5a2 2 0 012-2h13v16H6a2 2 0 00-2 2z"/><path d="M4 19V5"/></svg></span><div><b><?php echo $subjectCount; ?></b><span>Subjects</span></div></div>
          <div class="stat"><span class="ico y"><svg viewBox="0 0 24 24"><path d="M3 5h18M3 12h18M3 19h18"/></svg></span><div><b><?php echo count($courses); ?></b><span>Assignments</span></div></div>
        </section>

        <section class="card">
          <h2>Assigned classes and subjects</h2>
          <div class="table-wrap">
            <table>
              <thead><tr><th>#</th><th>Class</th><th>Subject</th><th>Course</th></tr></thead>
              <tbody>
              <?php if (!$courses): ?>
                <tr><td colspan="4" class="muted" style="padding:28px;text-align:center">No classes or subjects are assigned to you yet.</td></tr>
              <?php endif; ?>
              <?php foreach ($courses as $i => $course): ?>
                <tr>
                  <td class="roll"><?php echo $i + 1; ?></td>
                  <td><b><?php echo h($course['class_name'] . ' - ' . $course['section']); ?></b></td>
                  <td><?php echo h($course['subject_name']); ?></td>
                  <td><?php echo h($course['course_name']); ?></td>
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