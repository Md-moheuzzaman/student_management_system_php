<?php
session_start();

include '../database/dbconfig.php';

// Admin Authentication
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

//  Add course 
include 'courses_add.php';

// Update course 
include 'courses_edit.php';

// Delete course
include 'courses_delete.php';

// Get all courses
$result = $conn->query("SELECT * FROM courses ORDER BY id ASC");

// Message
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard | Ustudent</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">

<?php  include '../includes/admin_dashboard_css.php'; ?>

<style>
/* Courses page */
.alert{padding:12px 20px;border-radius:8px;margin-bottom:18px;font-weight:600}
.alert.ok{background:var(--green-soft);color:#126b35}
.alert.bad{background:var(--red-soft);color:#8f2a1c}
.row-actions{display:flex;gap:8px;justify-content:flex-end}
.row-actions form{margin:0}
.small-btn{border:1.5px solid var(--line);background:var(--white);border-radius:8px;padding:6px 12px;font:600 14px var(--body);cursor:pointer;color:var(--navy)}
.small-btn:hover{background:var(--paper)}
.small-btn.danger{color:var(--red)}
.small-btn.danger:hover{background:var(--red-soft);border-color:var(--red)}
.empty{padding:40px 20px;text-align:center;color:var(--muted)}

/* modal */
.overlay{position:fixed;inset:0;background:rgba(20,38,74,.55);display:none;align-items:center;justify-content:center;padding:18px;z-index:50}
.overlay.open{display:flex}
.modal{background:var(--white);border-radius:12px;width:100%;max-width:500px}
.modal-head{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid var(--line)}
.modal-head h2{font-size:18px}
.close{border:0;background:none;font-size:26px;line-height:1;cursor:pointer;color:var(--muted)}
.modal-body{padding:20px;display:grid;gap:14px}
.field{display:flex;flex-direction:column;gap:6px}
.field label{font-weight:600;font-size:14px}
.field input,.field textarea{padding:10px 12px;border:1.5px solid var(--line);border-radius:8px;font:inherit}
.field input:focus,.field textarea:focus{outline:none;border-color:var(--navy)}
.field textarea{resize:vertical;min-height:90px}
.modal-foot{display:flex;justify-content:flex-end;gap:10px;padding:14px 20px;border-top:1px solid var(--line)}
.btn.ghost{background:var(--white);color:var(--navy);border:1.5px solid var(--line)}
.btn.ghost:hover{background:var(--paper)}
</style>
</head>
<body>
<div class="app">

  <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

  <!-- Main -->
  <div class="main">
     <!-- Topbar -->
     <?php include '../includes/topbar.php'; ?>

    <main class="content">
      <div class="head">
        <div>
          <h1>Courses</h1>
          <p>Add, edit and delete the courses here</p>
        </div>
        <button class="btn" type="button" onclick="openAdd()">+ Add course</button>
      </div>

      <!-- Message -->
      <?php if ($msg == 'added') { ?>
        <div class="alert ok">Course added successfully.</div>
      <?php } elseif ($msg == 'updated') { ?>
        <div class="alert ok">Course updated successfully.</div>
      <?php } elseif ($msg == 'deleted') { ?>
        <div class="alert ok">Course deleted successfully.</div>
      <?php } elseif ($msg == 'empty') { ?>
        <div class="alert bad">Course name is required.</div>
      <?php } elseif ($msg == 'inuse') { ?>
        <div class="alert bad">This course is used by classes or subjects, so it cannot be deleted.</div>
      <?php } ?>

      <!-- Courses table -->
      <section class="card">
        <h2>All courses</h2>
        <table>
          <thead>
            <tr><th>#</th><th>Course name</th><th>Description</th><th class="num">Actions</th></tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($result) > 0) { ?>
              <?php while ($row = mysqli_fetch_assoc($result)) { ?>
              <tr>
                <td><?php echo $row['id']; ?></td>
                <td><b><?php echo htmlspecialchars($row['course_name']); ?></b></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td>
                  <div class="row-actions">
                    <button type="button" class="small-btn"
                      data-id="<?php echo $row['id']; ?>"
                      data-name="<?php echo htmlspecialchars($row['course_name']); ?>"
                      data-description="<?php echo htmlspecialchars($row['description']); ?>"
                      onclick="openEdit(this)">Edit</button>

                    <form method="post" onsubmit="return confirm('Delete this course?');">
                      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                      <button type="submit" name="delete_course" class="small-btn danger">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php } ?>
            <?php } else { ?>
              <tr><td colspan="4" class="empty">No courses yet. Click "+ Add course" to create one.</td></tr>
            <?php } ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>
</div>

<!-- Add / Edit modal -->
<div class="overlay" id="modal">
  <div class="modal">
    <div class="modal-head">
      <h2 id="modalTitle">Add course</h2>
      <button type="button" class="close" onclick="closeModal()">&times;</button>
    </div>
    <form method="post">
      <input type="hidden" name="id" id="course_id">
      <div class="modal-body">
        <div class="field">
          <label for="course_name">Course name</label>
          <input type="text" name="course_name" id="course_name" maxlength="100" required>
        </div>
        <div class="field">
          <label for="description">Description (optional)</label>
          <textarea name="description" id="description"></textarea>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn ghost" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn" id="saveBtn" name="add_course">Save course</button>
      </div>
    </form>
  </div>
</div>

<script>
var modal = document.getElementById('modal');

function openAdd() {
  document.getElementById('modalTitle').textContent = 'Add course';
  document.getElementById('saveBtn').name = 'add_course';
  document.getElementById('course_id').value = '';
  document.getElementById('course_name').value = '';
  document.getElementById('description').value = '';
  modal.classList.add('open');
}

function openEdit(btn) {
  document.getElementById('modalTitle').textContent = 'Edit course';
  document.getElementById('saveBtn').name = 'update_course';
  document.getElementById('course_id').value = btn.dataset.id;
  document.getElementById('course_name').value = btn.dataset.name;
  document.getElementById('description').value = btn.dataset.description;
  modal.classList.add('open');
}

function closeModal() {
  modal.classList.remove('open');
}
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>