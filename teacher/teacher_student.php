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

$teacher = rows($conn, "SELECT id FROM teachers WHERE user_id = ?", "i", [(int)$_SESSION['id']])[0] ?? null;
$classes = $teacher ? rows($conn,
    "SELECT DISTINCT c.id, c.class_name, c.section
       FROM teacher_subjects ts
       JOIN classes c ON c.id = ts.class_id
      WHERE ts.teacher_id = ?
      ORDER BY c.class_name, c.section",
    "i", [(int)$teacher['id']]) : [];
$classId = (int)($_GET['class_id'] ?? 0);
$currentClass = null;
foreach ($classes as $class) {
    if ((int)$class['id'] === $classId) {
        $currentClass = $class;
        break;
    }
}
if (!$currentClass && $classes) {
    $currentClass = $classes[0];
    $classId = (int)$currentClass['id'];
}

$students = $currentClass ? rows($conn,
    "SELECT s.id, s.name, s.email, s.phone,
            GROUP_CONCAT(DISTINCT sub.subject_name ORDER BY sub.subject_name SEPARATOR ', ') AS subjects
       FROM students s
       JOIN teacher_subjects ts ON ts.class_id = s.class_id AND ts.teacher_id = ?
       JOIN subjects sub ON sub.id = ts.subject_id
      WHERE s.class_id = ?
      GROUP BY s.id, s.name, s.email, s.phone
      ORDER BY s.name",
    "ii", [(int)$teacher['id'], $classId]) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Students | Ustudent</title>
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
        <div><h1>My Students</h1><p>Students in the classes and subjects assigned to you.</p></div>
      </div>

      <?php if (!$teacher): ?>
        <div class="alert alert-warning">Your teacher profile has not been created yet. Please ask the admin to add you in the Teachers page.</div>
      <?php elseif (!$classes): ?>
        <div class="alert alert-warning">No class or subject is assigned to you yet.</div>
      <?php else: ?>
        <section class="card mb-4">
          <h2>Select class</h2>
          <div class="card-body">
            <form class="row g-3 align-items-end" method="get" action="teacher_student.php">
              <div class="col-md-8">
                <label class="form-label" for="classSel">Class</label>
                <select class="form-select" id="classSel" name="class_id" required>
                  <?php foreach ($classes as $class): ?>
                    <option value="<?php echo (int)$class['id']; ?>" <?php echo $classId === (int)$class['id'] ? 'selected' : ''; ?>>
                      <?php echo h($class['class_name'] . ' - ' . $class['section']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4 d-grid"><button class="btn" type="submit">Load students</button></div>
            </form>
          </div>
        </section>

        <section class="stats" aria-label="Summary">
          <div class="stat"><span class="ico b"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg></span><div><b><?php echo count($students); ?></b><span>Students</span></div></div>
          <div class="stat"><span class="ico g"><svg viewBox="0 0 24 24"><path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.5 6-3v-5"/></svg></span><div><b><?php echo h($currentClass['class_name'] . ' - ' . $currentClass['section']); ?></b><span>Selected class</span></div></div>
        </section>

        <section class="card">
          <h2>Students in <?php echo h($currentClass['class_name'] . ' - ' . $currentClass['section']); ?></h2>
          <div class="table-wrap">
            <table>
              <thead><tr><th>#</th><th>Student</th><th>Email</th><th>Phone</th><th>Assigned subjects</th></tr></thead>
              <tbody>
              <?php if (!$students): ?>
                <tr><td colspan="5" class="muted" style="padding:28px;text-align:center">No students are enrolled in this class yet.</td></tr>
              <?php endif; ?>
              <?php foreach ($students as $i => $student): ?>
                <tr>
                  <td class="roll"><?php echo $i + 1; ?></td>
                  <td><b><?php echo h($student['name']); ?></b></td>
                  <td><?php echo h($student['email']); ?></td>
                  <td><?php echo h($student['phone'] ?? ''); ?></td>
                  <td><?php echo h($student['subjects']); ?></td>
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
  a.classList.toggle('on', a.textContent.trim() === 'Students');
});
</script>
</body>
</html>