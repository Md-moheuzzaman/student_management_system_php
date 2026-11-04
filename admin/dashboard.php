<?php
require __DIR__ . '/config.php';
require_login();

$role     = $_SESSION['role'];
$username = $_SESSION['username'];
$userId   = (int) $_SESSION['user_id'];

/** Run a query and return its first column (0 when there is no row). */
function scalar(mysqli $db, $sql, array $params = [])
{
    $stmt = db_execute($db, $sql, $params);
    $row = $stmt->get_result()->fetch_row();
    $stmt->close();
    $v = $row[0] ?? null;
    return ($v === false || $v === null) ? 0 : $v;
}

/** Run a query and return all rows. */
function rows(mysqli $db, $sql, array $params = [])
{
    $stmt = db_execute($db, $sql, $params);
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/** Run a query and return one row (or null). */
function one(mysqli $db, $sql, array $params = [])
{
    $stmt = db_execute($db, $sql, $params);
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

$data = [];

if ($role === 'admin') {
    $todayTotal   = (int) scalar($db, 'SELECT COUNT(*) FROM attendance WHERE date = CURDATE()');
    $todayPresent = (int) scalar($db, "SELECT COUNT(*) FROM attendance WHERE date = CURDATE() AND status = 'P'");

    $data['students']  = (int) scalar($db, 'SELECT COUNT(*) FROM students');
    $data['teachers']  = (int) scalar($db, 'SELECT COUNT(*) FROM teachers');
    $data['classes']   = (int) scalar($db, 'SELECT COUNT(*) FROM classes');
    $data['courses']   = (int) scalar($db, 'SELECT COUNT(*) FROM courses');
    $data['collected'] = (float) scalar($db, "SELECT COALESCE(SUM(amount),0) FROM fees WHERE status = 'Paid'");
    $data['pending']   = (int) scalar($db, "SELECT COUNT(*) FROM fees WHERE status <> 'Paid'");
    $data['att_pct']   = $todayTotal > 0 ? round($todayPresent / $todayTotal * 100) : null;
    $data['att_total'] = $todayTotal;

    $data['recent_students'] = rows($db,
        'SELECT s.name, s.email, c.class_name, c.section
           FROM students s JOIN classes c ON c.id = s.class_id
          ORDER BY s.id DESC LIMIT 5');
    $data['pending_fees'] = rows($db,
        "SELECT s.name, f.amount, f.status
           FROM fees f JOIN students s ON s.id = f.student_id
          WHERE f.status <> 'Paid'
          ORDER BY f.id DESC LIMIT 5");

} elseif ($role === 'teacher') {
    $teacher = one($db, 'SELECT id, name FROM teachers WHERE user_id = ? LIMIT 1', [$userId]);
    $data['teacher'] = $teacher;
    if ($teacher) {
        $tid = (int) $teacher['id'];
        $data['assignments'] = rows($db,
            'SELECT sub.subject_name, c.class_name, c.section
               FROM teacher_subjects ts
               JOIN subjects sub ON sub.id = ts.subject_id
               JOIN classes c    ON c.id   = ts.class_id
              WHERE ts.teacher_id = ?
              ORDER BY c.class_name, c.section, sub.subject_name', [$tid]);
        $data['classes']  = (int) scalar($db, 'SELECT COUNT(DISTINCT class_id) FROM teacher_subjects WHERE teacher_id = ?', [$tid]);
        $data['subjects'] = (int) scalar($db, 'SELECT COUNT(DISTINCT subject_id) FROM teacher_subjects WHERE teacher_id = ?', [$tid]);
        $data['students'] = (int) scalar($db,
            'SELECT COUNT(DISTINCT s.id) FROM students s
               JOIN teacher_subjects ts ON ts.class_id = s.class_id
              WHERE ts.teacher_id = ?', [$tid]);
    }

} elseif ($role === 'student') {
    $student = one($db,
        'SELECT s.id, s.name, c.class_name, c.section
           FROM students s JOIN classes c ON c.id = s.class_id
          WHERE s.user_id = ? LIMIT 1', [$userId]);
    $data['student'] = $student;
    if ($student) {
        $sid = (int) $student['id'];
        $att = one($db,
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(status = 'P'),0) AS p,
                    COALESCE(SUM(status = 'A'),0) AS a,
                    COALESCE(SUM(status = 'L'),0) AS l
               FROM attendance WHERE student_id = ?", [$sid]);
        $data['att']     = $att;
        $data['att_pct'] = $att['total'] > 0 ? round($att['p'] / $att['total'] * 100) : null;
        $data['avg']     = scalar($db, 'SELECT AVG(marks) FROM marks WHERE student_id = ?', [$sid]);
        $data['marks']   = rows($db,
            'SELECT sub.subject_name, m.exam_type, m.marks, m.exam_date
               FROM marks m JOIN subjects sub ON sub.id = m.subject_id
              WHERE m.student_id = ?
              ORDER BY m.exam_date DESC, m.id DESC LIMIT 5', [$sid]);
        $data['fees']    = rows($db,
            'SELECT amount, status, payment_date FROM fees WHERE student_id = ? ORDER BY id DESC LIMIT 5', [$sid]);
        $data['due']     = (int) scalar($db, "SELECT COUNT(*) FROM fees WHERE student_id = ? AND status <> 'Paid'", [$sid]);
    }
}

$roleLabels = ['admin' => 'Administrator', 'teacher' => 'Teacher', 'student' => 'Student', 'parent' => 'Parent'];
$roleLabel  = $roleLabels[$role] ?? ucfirst($role);

$displayName = $username;
if ($role === 'teacher' && !empty($data['teacher']['name'])) { $displayName = $data['teacher']['name']; }
if ($role === 'student' && !empty($data['student']['name'])) { $displayName = $data['student']['name']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard | Student Management System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<header class="topbar">
  <div class="in">
    <div class="brand"><span class="mark" aria-hidden="true"></span>Student Management System</div>
    <div class="who">
      <span class="name"><?= e($displayName) ?></span>
      <span class="role"><?= e($roleLabel) ?></span>
      <form method="post" action="logout.php">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <button class="linkbtn" type="submit">Log out</button>
      </form>
    </div>
  </div>
</header>

<main class="page">
  <h1>Welcome, <?= e($displayName) ?></h1>
  <p class="sub"><?= e($roleLabel) ?> dashboard &middot; <?= e(date('l, j F Y')) ?></p>

<?php if ($role === 'admin'): ?>

  <div class="stats">
    <div class="stat"><b><?= number_format($data['students']) ?></b><span>Students</span></div>
    <div class="stat"><b><?= number_format($data['teachers']) ?></b><span>Teachers</span></div>
    <div class="stat"><b><?= number_format($data['classes']) ?></b><span>Classes in <?= number_format($data['courses']) ?> courses</span></div>
    <div class="stat good"><b><?= number_format($data['collected'], 2) ?></b><span>Fees collected</span></div>
  </div>

  <div class="grid">
    <section class="panel">
      <h2>Recently added students</h2>
      <?php if (!$data['recent_students']): ?>
        <p class="empty">No students yet.</p>
      <?php else: ?>
        <table>
          <thead><tr><th>Name</th><th>Class</th></tr></thead>
          <tbody>
          <?php foreach ($data['recent_students'] as $r): ?>
            <tr><td><?= e($r['name']) ?><br><small style="color:var(--muted)"><?= e($r['email']) ?></small></td>
                <td><?= e($r['class_name'] . ' ' . $r['section']) ?></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <div style="display:grid;gap:24px;align-content:start">
      <section class="panel">
        <h2>Attendance today</h2>
        <div class="attn">
          <?php if ($data['att_pct'] === null): ?>
            <p>No attendance has been recorded today.</p>
          <?php else: ?>
            <b><?= (int) $data['att_pct'] ?>%</b>
            <div class="meter"><i style="width:<?= (int) $data['att_pct'] ?>%"></i></div>
            <p>present out of <?= (int) $data['att_total'] ?> records</p>
          <?php endif; ?>
        </div>
      </section>

      <section class="panel">
        <h2>Pending fees (<?= (int) $data['pending'] ?>)</h2>
        <?php if (!$data['pending_fees']): ?>
          <p class="empty">No pending fees.</p>
        <?php else: ?>
          <table>
            <thead><tr><th>Student</th><th class="num">Amount</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($data['pending_fees'] as $r): ?>
              <tr><td><?= e($r['name']) ?></td>
                  <td class="num"><?= number_format($r['amount'], 2) ?></td>
                  <td><span class="pill <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>
    </div>
  </div>

<?php elseif ($role === 'teacher'): ?>

  <?php if (!$data['teacher']): ?>
    <div class="alert info">Your login is not linked to a teacher profile yet. Ask an administrator to add one.</div>
  <?php else: ?>
    <div class="stats" style="grid-template-columns:repeat(3,1fr)">
      <div class="stat"><b><?= (int) $data['classes'] ?></b><span>Classes</span></div>
      <div class="stat"><b><?= (int) $data['subjects'] ?></b><span>Subjects</span></div>
      <div class="stat"><b><?= (int) $data['students'] ?></b><span>Students</span></div>
    </div>

    <section class="panel">
      <h2>Your assignments</h2>
      <?php if (!$data['assignments']): ?>
        <p class="empty">You have no subjects or classes assigned yet.</p>
      <?php else: ?>
        <table>
          <thead><tr><th>Subject</th><th>Class</th></tr></thead>
          <tbody>
          <?php foreach ($data['assignments'] as $r): ?>
            <tr><td><?= e($r['subject_name']) ?></td><td><?= e($r['class_name'] . ' ' . $r['section']) ?></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  <?php endif; ?>

<?php elseif ($role === 'student'): ?>

  <?php if (!$data['student']): ?>
    <div class="alert info">Your login is not linked to a student profile yet. Ask an administrator to add one.</div>
  <?php else: ?>
    <div class="stats">
      <div class="stat"><b><?= e($data['student']['class_name'] . ' ' . $data['student']['section']) ?></b><span>Your class</span></div>
      <div class="stat good"><b><?= $data['att_pct'] === null ? '-' : (int) $data['att_pct'] . '%' ?></b><span>Attendance</span></div>
      <div class="stat"><b><?= $data['avg'] ? number_format($data['avg'], 1) : '-' ?></b><span>Average marks</span></div>
      <div class="stat <?= $data['due'] > 0 ? 'warn' : '' ?>"><b><?= (int) $data['due'] ?></b><span>Fees due</span></div>
    </div>

    <div class="grid">
      <section class="panel">
        <h2>Latest marks</h2>
        <?php if (!$data['marks']): ?>
          <p class="empty">No marks have been entered yet.</p>
        <?php else: ?>
          <table>
            <thead><tr><th>Subject</th><th>Exam</th><th class="num">Marks</th></tr></thead>
            <tbody>
            <?php foreach ($data['marks'] as $r): ?>
              <tr><td><?= e($r['subject_name']) ?></td><td><?= e($r['exam_type']) ?></td>
                  <td class="num"><?= number_format($r['marks'], 2) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>

      <div style="display:grid;gap:24px;align-content:start">
        <section class="panel">
          <h2>Attendance record</h2>
          <?php if ($data['att']['total'] == 0): ?>
            <p class="empty">No attendance has been recorded yet.</p>
          <?php else: ?>
            <table>
              <tbody>
                <tr><td>Present</td><td class="num"><?= (int) $data['att']['p'] ?></td></tr>
                <tr><td>Absent</td><td class="num"><?= (int) $data['att']['a'] ?></td></tr>
                <tr><td>Late</td><td class="num"><?= (int) $data['att']['l'] ?></td></tr>
              </tbody>
            </table>
          <?php endif; ?>
        </section>

        <section class="panel">
          <h2>Fees</h2>
          <?php if (!$data['fees']): ?>
            <p class="empty">No fee records yet.</p>
          <?php else: ?>
            <table>
              <thead><tr><th class="num">Amount</th><th>Status</th><th>Paid on</th></tr></thead>
              <tbody>
              <?php foreach ($data['fees'] as $r): ?>
                <tr><td class="num"><?= number_format($r['amount'], 2) ?></td>
                    <td><span class="pill <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
                    <td><?= $r['payment_date'] ? e($r['payment_date']) : '-' ?></td></tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </section>
      </div>
    </div>
  <?php endif; ?>

<?php else: ?>

  <div class="alert info">The parent view is not set up yet. The database has no table linking a parent to a child, so there is nothing to show here.</div>

<?php endif; ?>
</main>
</body>
</html>
