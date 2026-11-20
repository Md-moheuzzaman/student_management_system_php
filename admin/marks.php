<?php
session_start();
include '../database/dbconfig.php';

// Admin Authentication (same check you use on your other admin pages)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* ---------- small helpers ---------- */
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Run a SELECT with prepared-statement values and return all rows
function rows($conn, $sql, $types = '', $params = []) {
    $st = $conn->prepare($sql);
    if (!$st) return [];
    if ($types !== '') $st->bind_param($types, ...$params);
    $st->execute();
    $res = $st->get_result();
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

// Marks are out of 100: returns [label, css class]
function gradeOf($m) {
    if ($m >= 80) return ['A+', 'ap'];
    if ($m >= 70) return ['A',  'a'];
    if ($m >= 60) return ['B',  'b'];
    if ($m >= 50) return ['C',  'c'];
    if ($m >= 40) return ['D',  'd'];
    return ['F', 'f'];
}

/* ---------- filters (from the "Load students" form) ---------- */
$examTypes = ['Quiz', 'Assignment', 'Midterm', 'Final'];
$classId   = (int)($_REQUEST['class_id'] ?? 0);
$subjectId = (int)($_REQUEST['subject_id'] ?? 0);
$examType  = $_REQUEST['exam_type'] ?? 'Midterm';
$examDate  = $_REQUEST['exam_date'] ?? date('Y-m-d');
if (!in_array($examType, $examTypes, true)) $examType = 'Midterm';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $examDate)) $examDate = date('Y-m-d');

$filterQuery = http_build_query([
    'class_id' => $classId, 'subject_id' => $subjectId,
    'exam_type' => $examType, 'exam_date' => $examDate,
]);

$selectedClass = $classId > 0 ? rows($conn,
    "SELECT course_id FROM classes WHERE id = ?",
    "i", [$classId])[0] ?? null : null;
$selectionValid = $selectedClass && $subjectId > 0 && !empty(rows($conn,
    "SELECT id FROM subjects WHERE id = ? AND course_id = ? LIMIT 1",
    "ii", [$subjectId, (int)$selectedClass['course_id']]));

/* ---------- save marks (form POST) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_marks'])) {
    $saved = 0;
    $bad = 0;

    if ($selectionValid) {
        // Only accept students who really are in the selected class
        $classStudents = array_map('intval', array_column(
            rows($conn, "SELECT id FROM students WHERE class_id = ?", "i", [$classId]), 'id'));

        $find = $conn->prepare("SELECT id FROM marks WHERE student_id = ? AND subject_id = ? AND exam_type = ? ORDER BY id DESC LIMIT 1");
        $upd  = $conn->prepare("UPDATE marks SET marks = ?, exam_date = ? WHERE id = ?");
        $ins  = $conn->prepare("INSERT INTO marks (student_id, subject_id, exam_type, marks, exam_date) VALUES (?, ?, ?, ?, ?)");
        if (!$find || !$upd || !$ins) {
            throw new RuntimeException("Could not prepare mark saving queries.");
        }

        $conn->begin_transaction();
        try {
            $markSubjectId = $subjectId;

            foreach (($_POST['marks'] ?? []) as $sid => $val) {
                $sid = (int)$sid;
                $val = trim((string)$val);
                if ($val === '' || !in_array($sid, $classStudents, true)) continue;   // empty box = skip
                if (!is_numeric($val) || $val < 0 || $val > 100) { $bad++; continue; }
                $val = (float)$val;

                $find->bind_param("iis", $sid, $markSubjectId, $examType);
                if (!$find->execute()) throw new RuntimeException("Could not check existing marks.");
                $old = $find->get_result()->fetch_assoc();

                if ($old) {                       // already has marks for this exam -> update
                    $oldId = (int)$old['id'];
                    $upd->bind_param("dsi", $val, $examDate, $oldId);
                    if (!$upd->execute()) throw new RuntimeException("Could not update marks.");
                } else {                          // first time -> insert
                    $ins->bind_param("iisds", $sid, $markSubjectId, $examType, $val, $examDate);
                    if (!$ins->execute()) throw new RuntimeException("Could not save marks.");
                }
                $saved++;
            }
            $conn->commit();
        } catch (Throwable $error) {
            $conn->rollback();
            throw $error;
        }
    } else {
        header("Location: marks.php?" . $filterQuery . "&msg=invalid_selection");
        exit;
    }
    header("Location: marks.php?" . $filterQuery . "&msg=saved&n=$saved&bad=$bad");
    exit;
}

/* ---------- data for the page ---------- */
$classes  = rows($conn, "SELECT id, class_name, section, course_id FROM classes ORDER BY class_name, section");
$subjects = $selectedClass
    ? rows($conn, "SELECT id, subject_name FROM subjects WHERE course_id = ? ORDER BY subject_name", "i", [(int)$selectedClass['course_id']])
    : rows($conn, "SELECT id, subject_name FROM subjects ORDER BY subject_name");

$students = [];
$markSubjectId = $selectionValid ? $subjectId : 0;
if ($selectionValid) {
    $students = rows($conn,
        "SELECT s.id, s.name, s.email, m.marks
           FROM students s
           LEFT JOIN marks m ON m.id = (
               SELECT MAX(m2.id) FROM marks m2
                WHERE m2.student_id = s.id AND m2.subject_id = ? AND m2.exam_type = ?
           )
          WHERE s.class_id = ?
          ORDER BY s.name",
        "isi", [$markSubjectId, $examType, $classId]);
}

// numbers for the summary cards
$entered = [];
foreach ($students as $s) if ($s['marks'] !== null) $entered[] = (float)$s['marks'];
$avg  = count($entered) ? round(array_sum($entered) / count($entered), 1) : 0;
$high = count($entered) ? max($entered) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Marks | Ustudent</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<link rel="stylesheet" href="../assets/css/academic_common.css">
<link rel="stylesheet" href="../assets/css/mark.css">
</head>
<body>
<div class="app">

  <?php include '../includes/sidebar.php'; ?>

  <div class="main">
    <?php include '../includes/topbar.php'; ?>

    <main class="content">

      <div class="head">
        <div>
          <h1>Marks</h1>
          <p>Choose a class, subject and exam, then enter marks out of 100.</p>
        </div>
      </div>

      <?php if (($_GET['msg'] ?? '') === 'saved'): ?>
        <div class="alert alert-success">
          Saved marks for <?php echo (int)($_GET['n'] ?? 0); ?> student(s).
          <?php if ((int)($_GET['bad'] ?? 0) > 0): ?>
            <b><?php echo (int)$_GET['bad']; ?> row(s) skipped</b> because the mark was not a number from 0 to 100.
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if (($_GET['msg'] ?? '') === 'invalid_selection'): ?>
        <div class="alert alert-warning">The selected subject must belong to the selected class course.</div>
      <?php elseif ($classId > 0 && $subjectId > 0 && !$selectionValid): ?>
        <div class="alert alert-warning">Choose the subject that belongs to this class course, then load the students again.</div>
      <?php endif; ?>

      <!-- Step 1: filters -->
      <section class="card mb-4">
        <h2>Select exam</h2>
        <div class="card-body">
          <form class="row g-3 align-items-end" method="get" action="marks.php">
            <div class="col-md-3">
              <label class="form-label" for="classSel">Class</label>
              <select class="form-select" id="classSel" name="class_id" required>
                <option value="">Select class</option>
                <?php foreach ($classes as $c): ?>
                  <option value="<?php echo (int)$c['id']; ?>"
                    <?php echo $classId === (int)$c['id'] ? 'selected' : ''; ?>>
                    <?php echo h($c['class_name'] . ' - ' . $c['section']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label" for="subjectSel">Subject</label>
              <select class="form-select" id="subjectSel" name="subject_id" required>
                <option value="">Select subject</option>
                <?php foreach ($subjects as $s): ?>
                  <option value="<?php echo (int)$s['id']; ?>"
                    <?php echo $subjectId === (int)$s['id'] ? 'selected' : ''; ?>>
                    <?php echo h($s['subject_name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label" for="examType">Exam</label>
              <select class="form-select" id="examType" name="exam_type">
                <?php foreach ($examTypes as $t): ?>
                  <option <?php echo $examType === $t ? 'selected' : ''; ?>><?php echo h($t); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label" for="examDate">Exam date</label>
              <input class="form-control" type="date" id="examDate" name="exam_date" value="<?php echo h($examDate); ?>" required>
            </div>
            <div class="col-md-2 d-grid">
              <button class="btn" type="submit">Load students</button>
            </div>
          </form>
        </div>
      </section>

      <?php if (!$classes || !$subjects): ?>
        <div class="alert alert-warning">
          <?php if (!$classes): ?>
            You need at least one <b>class</b> before you can enter marks. Add one from the Classes page.
          <?php else: ?>
            No subjects are available for this class course. Add subject records linked to the course in the database.
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if ($classId > 0 && $subjectId > 0): ?>

        <!-- summary -->
        <section class="stats" aria-label="Summary">
          <div class="stat"><span class="ico b"><svg viewBox="0 0 24 24"><path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.5 6-3v-5"/></svg></span><div><b><?php echo count($students); ?></b><span>Students</span></div></div>
          <div class="stat"><span class="ico g"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span><div><b><?php echo count($entered); ?></b><span>Marks entered</span></div></div>
          <div class="stat"><span class="ico y"><svg viewBox="0 0 24 24"><path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/></svg></span><div><b><?php echo $avg; ?></b><span>Class average</span></div></div>
          <div class="stat"><span class="ico r"><svg viewBox="0 0 24 24"><path d="M12 3l3 6 6 1-4.5 4.5L18 21l-6-3-6 3 1.5-6.5L3 10l6-1z"/></svg></span><div><b><?php echo $high; ?></b><span>Highest mark</span></div></div>
        </section>

        <!-- Step 2: enter marks -->
        <form method="post" action="marks.php">
          <input type="hidden" name="class_id" value="<?php echo $classId; ?>">
          <input type="hidden" name="subject_id" value="<?php echo $subjectId; ?>">
          <input type="hidden" name="exam_type" value="<?php echo h($examType); ?>">
          <input type="hidden" name="exam_date" value="<?php echo h($examDate); ?>">

          <section class="card">
            <h2>Enter marks &mdash; <?php echo h($examType); ?></h2>
            <div class="table-wrap">
              <table>
                <thead><tr><th>#</th><th>Student</th><th>Marks (0-100)</th><th>Grade</th></tr></thead>
                <tbody>
                <?php if (!$students): ?>
                  <tr><td colspan="4" class="muted" style="padding:28px;text-align:center">No students in this class yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($students as $i => $s):
                    $g = $s['marks'] !== null ? gradeOf((float)$s['marks']) : null; ?>
                  <tr>
                    <td class="roll"><?php echo $i + 1; ?></td>
                    <td><div class="who"><span class="dot"><?php echo h(strtoupper(substr($s['name'], 0, 2))); ?></span>
                      <div><b><?php echo h($s['name']); ?></b><small><?php echo h($s['email']); ?></small></div></div></td>
                    <td><input class="form-control mark-input" type="number" name="marks[<?php echo (int)$s['id']; ?>]"
                         min="0" max="100" step="0.01" value="<?php echo $s['marks'] !== null ? h($s['marks'] + 0) : ''; ?>"></td>
                    <td><span class="grade <?php echo $g ? $g[1] : ''; ?>"><?php echo $g ? $g[0] : '&mdash;'; ?></span></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div class="card-foot">
              <div class="scale"><span><b>A+</b> 80+</span><span><b>A</b> 70+</span><span><b>B</b> 60+</span><span><b>C</b> 50+</span><span><b>D</b> 40+</span><span><b>F</b> below 40</span></div>
              <?php if ($students): ?><button class="btn" type="submit" name="save_marks" value="1">Save marks</button><?php endif; ?>
            </div>
          </section>
        </form>

      <?php endif; ?>
    </main>
  </div>
</div>
<?php include '../includes/footer.php'; ?>

<script>
// 1) Highlight "Marks" in the sidebar (your sidebar.php always highlights Dashboard)
document.querySelectorAll('aside nav a').forEach(function (a) {
  a.classList.toggle('on', a.getAttribute('href') === 'marks.php');
});

// 2) Update the grade badge while typing
function gradeOf(m) {
  if (m >= 80) return ['A+', 'ap']; if (m >= 70) return ['A', 'a']; if (m >= 60) return ['B', 'b'];
  if (m >= 50) return ['C', 'c'];  if (m >= 40) return ['D', 'd']; return ['F', 'f'];
}
document.querySelectorAll('.mark-input').forEach(function (inp) {
  inp.addEventListener('input', function () {
    var badge = inp.closest('tr').querySelector('.grade');
    var v = parseFloat(inp.value);
    if (isNaN(v) || v < 0 || v > 100) { badge.className = 'grade'; badge.innerHTML = '&mdash;'; return; }
    var g = gradeOf(v); badge.className = 'grade ' + g[1]; badge.textContent = g[0];
  });
});
</script>
</body>
</html>