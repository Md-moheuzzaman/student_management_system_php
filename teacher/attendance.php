<?php
session_start();
include '../database/dbconfig.php';

// Teacher Authentication
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
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

/* ---------- who is this teacher? ----------
   $_SESSION['id'] is the id from the USERS table (set in login.php).
   The teachers table has its own id, linked by teachers.user_id. */
$teacher = rows($conn, "SELECT id, name FROM teachers WHERE user_id = ?", "i", [(int)$_SESSION['id']])[0] ?? null;

// The class + subject pairs this teacher is allowed to teach (teacher_subjects table)
$assignments = $teacher ? rows($conn,
    "SELECT ts.id, ts.class_id, ts.subject_id, c.class_name, c.section, sub.subject_name
       FROM teacher_subjects ts
       JOIN classes c ON c.id = ts.class_id
       JOIN subjects sub ON sub.id = ts.subject_id
      WHERE ts.teacher_id = ?
      ORDER BY c.class_name, c.section, sub.subject_name",
    "i", [(int)$teacher['id']]) : [];

/* ---------- selected assignment + date ---------- */
$assignId = (int)($_REQUEST['assign'] ?? 0);
$date     = $_REQUEST['date'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date > date('Y-m-d')) $date = date('Y-m-d');   // no future dates

$current = null;
foreach ($assignments as $a) if ((int)$a['id'] === $assignId) $current = $a;   // must belong to this teacher

/* ---------- save attendance ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance']) && $current) {
    $classId   = (int)$current['class_id'];
    $subjectId = (int)$current['subject_id'];
    $classStudents = array_map('intval', array_column(
        rows($conn, "SELECT id FROM students WHERE class_id = ?", "i", [$classId]), 'id'));

    $find = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND subject_id = ? AND date = ?");
    $upd  = $conn->prepare("UPDATE attendance SET status = ? WHERE id = ?");
    $ins  = $conn->prepare("INSERT INTO attendance (student_id, subject_id, date, status) VALUES (?, ?, ?, ?)");

    $saved = 0;
    $conn->begin_transaction();
    foreach (($_POST['status'] ?? []) as $sid => $status) {
        $sid = (int)$sid;
        if (!in_array($sid, $classStudents, true) || !in_array($status, ['P', 'A', 'L'], true)) continue;

        $find->bind_param("iis", $sid, $subjectId, $date);
        $find->execute();
        $old = $find->get_result()->fetch_assoc();

        if ($old) {                              // already taken today -> change it
            $oldId = (int)$old['id'];
            $upd->bind_param("si", $status, $oldId);
            $upd->execute();
        } else {
            $ins->bind_param("iiss", $sid, $subjectId, $date, $status);
            $ins->execute();
        }
        $saved++;
    }
    $conn->commit();
    header("Location: attendance.php?assign=$assignId&date=" . urlencode($date) . "&saved=$saved");
    exit;
}

/* ---------- students + any attendance already saved ---------- */
$students = [];
$already = false;
if ($current) {
    $students = rows($conn,
        "SELECT s.id, s.name, s.email, a.status
           FROM students s
           LEFT JOIN attendance a ON a.student_id = s.id AND a.subject_id = ? AND a.date = ?
          WHERE s.class_id = ?
          ORDER BY s.name",
        "isi", [(int)$current['subject_id'], $date, (int)$current['class_id']]);
    foreach ($students as $s) if ($s['status'] !== null) $already = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Attendance | Ustudent</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<link rel="stylesheet" href="../assets/css/academic_common.css">
<link rel="stylesheet" href="../assets/css/attendance.css">
</head>
<body>
<div class="app">

  <?php include '../includes/sidebar_teacher.php'; ?>

  <div class="main">
    <?php include '../includes/topbar.php'; ?>

    <main class="content">

      <div class="head">
        <div>
          <h1>Attendance</h1>
          <p>Take attendance for your class, subject and date.</p>
        </div>
      </div>

      <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">Attendance saved for <?php echo (int)$_GET['saved']; ?> student(s).</div>
      <?php endif; ?>

      <?php if (!$teacher): ?>
        <div class="alert alert-warning">Your teacher profile has not been created yet. Please ask the admin to add you in the Teachers page.</div>
      <?php elseif (!$assignments): ?>
        <div class="alert alert-warning">No class or subject is assigned to you yet. Please ask the admin to assign your subjects (teacher_subjects).</div>
      <?php endif; ?>

      <!-- Step 1: pick class + subject + date -->
      <section class="card mb-4">
        <h2>Select class</h2>
        <div class="card-body">
          <form class="row g-3 align-items-end" method="get" action="attendance.php">
            <div class="col-md-6">
              <label class="form-label" for="assignSel">Class and subject</label>
              <select class="form-select" id="assignSel" name="assign" required>
                <option value="">Select</option>
                <?php foreach ($assignments as $a): ?>
                  <option value="<?php echo (int)$a['id']; ?>" <?php echo $assignId === (int)$a['id'] ? 'selected' : ''; ?>>
                    <?php echo h($a['class_name'] . ' - ' . $a['section'] . '  |  ' . $a['subject_name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="dateSel">Date</label>
              <input class="form-control" type="date" id="dateSel" name="date" value="<?php echo h($date); ?>" max="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-2 d-grid">
              <button class="btn" type="submit">Load students</button>
            </div>
          </form>
        </div>
      </section>

      <?php if ($current): ?>

        <section class="stats" aria-label="Summary">
          <div class="stat"><span class="ico b"><svg viewBox="0 0 24 24"><path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.5 6-3v-5"/></svg></span><div><b><?php echo count($students); ?></b><span>Total students</span></div></div>
          <div class="stat"><span class="ico g"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span><div><b id="cP">0</b><span>Present</span></div></div>
          <div class="stat"><span class="ico r"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg></span><div><b id="cA">0</b><span>Absent</span></div></div>
          <div class="stat"><span class="ico y"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><div><b id="cL">0</b><span>Late</span></div></div>
        </section>

        <form method="post" action="attendance.php">
          <input type="hidden" name="assign" value="<?php echo $assignId; ?>">
          <input type="hidden" name="date" value="<?php echo h($date); ?>">

          <section class="card">
            <h2>Mark attendance
              <?php if ($students): ?><button class="small-btn" type="button" id="markAll">Mark all present</button><?php endif; ?>
            </h2>
            <?php if ($already): ?>
              <div class="alert alert-info m-3 mb-0">Attendance for this date is already saved. You can change it and save again.</div>
            <?php endif; ?>
            <div class="table-wrap">
              <table>
                <thead><tr><th>#</th><th>Student</th><th>Status</th></tr></thead>
                <tbody>
                <?php if (!$students): ?>
                  <tr><td colspan="3" class="muted" style="padding:28px;text-align:center">No students in this class yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($students as $i => $s):
                    $sid = (int)$s['id'];
                    $now = $s['status'] ?? 'P';          // new students start as Present ?>
                  <tr>
                    <td class="roll"><?php echo $i + 1; ?></td>
                    <td><div class="who"><span class="dot"><?php echo h(strtoupper(substr($s['name'], 0, 2))); ?></span>
                      <div><b><?php echo h($s['name']); ?></b><small><?php echo h($s['email']); ?></small></div></div></td>
                    <td><div class="opts">
                      <input type="radio" class="btn-check" name="status[<?php echo $sid; ?>]" id="a<?php echo $sid; ?>p" value="P" <?php echo $now === 'P' ? 'checked' : ''; ?>><label class="opt present" for="a<?php echo $sid; ?>p">Present</label>
                      <input type="radio" class="btn-check" name="status[<?php echo $sid; ?>]" id="a<?php echo $sid; ?>a" value="A" <?php echo $now === 'A' ? 'checked' : ''; ?>><label class="opt absent" for="a<?php echo $sid; ?>a">Absent</label>
                      <input type="radio" class="btn-check" name="status[<?php echo $sid; ?>]" id="a<?php echo $sid; ?>l" value="L" <?php echo $now === 'L' ? 'checked' : ''; ?>><label class="opt late" for="a<?php echo $sid; ?>l">Late</label>
                    </div></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div class="card-foot">
              <div class="legend"><span><i class="p"></i>Present</span><span><i class="a"></i>Absent</span><span><i class="l"></i>Late</span></div>
              <?php if ($students): ?><button class="btn" type="submit" name="save_attendance" value="1">Save attendance</button><?php endif; ?>
            </div>
          </section>
        </form>

      <?php endif; ?>
    </main>
  </div>
</div>
<?php include '../includes/footer.php'; ?>

<script>
// Highlight "Attendance" in the sidebar (your teacher sidebar links are still "#")
document.querySelectorAll('aside nav a').forEach(function (a) {
  a.classList.toggle('on', a.textContent.trim() === 'Attendance');
});

// Live Present / Absent / Late counters + "Mark all present"
function count() {
  var c = { P: 0, A: 0, L: 0 };
  document.querySelectorAll('input[name^="status["]:checked').forEach(function (r) { c[r.value]++; });
  if (document.getElementById('cP')) {
    document.getElementById('cP').textContent = c.P;
    document.getElementById('cA').textContent = c.A;
    document.getElementById('cL').textContent = c.L;
  }
}
document.addEventListener('change', count);
var all = document.getElementById('markAll');
if (all) all.addEventListener('click', function () {
  document.querySelectorAll('input[name^="status["][value="P"]').forEach(function (r) { r.checked = true; });
  count();
});
count();
</script>
</body>
</html>