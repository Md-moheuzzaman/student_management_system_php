<?php
session_start();

include '../database/dbconfig.php';

// Admin Authentication
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Teacher Dashboard | Ustudent</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">

<!-- admin dashboard css -->
<!-- <link rel="stylesheet" href="../assets/css/admin_dashboard.css"> -->

<?php  include '../includes/admin_dashboard_css.php'; ?>
</head>
<body>
<div class="app">

  <!-- Sidebar -->
    <?php include '../includes/sidebar_teacher.php'; ?>

   

  <!-- Main -->
  <div class="main">
     <!-- Topbar -->
     <?php include '../includes/topbar.php'; ?>

    <main class="content">
      <div class="head">
        <div>
          <h1>Teacher Dashboard</h1>
          <p>Here is what is happening at your institute today.</p>
        </div>
        
      </div>

      <!-- Stat cards -->
      <section class="stats" aria-label="Summary">
        <div class="stat"><span class="ico b"><svg viewBox="0 0 24 24"><path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.500 6-3v-5"/></svg></span><div><b>8,248</b><span>Students</span></div></div>
        <div class="stat"><span class="ico g"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg></span><div><b>645</b><span>Teachers</span></div></div>
        <div class="stat"><span class="ico y"><svg viewBox="0 0 24 24"><path d="M4 5a2 2 0 012-2h13v16H6a2 2 0 00-2 2z"/><path d="M4 19V5"/></svg></span><div><b>412</b><span>Courses</span></div></div>
        
      </section>

      <!-- Chart + quick actions -->

        <section class="card">
          <h2>Quick actions</h2>
          <div class="actions">
            
            <a href="#"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/></svg>Take attendance</a>
            
          </div>
        </section>
      </div>


      <!-- Notices -->
      <section class="card">
        <h2>Notices</h2>
        <ul class="notices">
          <li><b>Midterm exams begin on 20 November</b><small>Posted 3 Nov 2026</small></li>
          <li><b>Parent meeting for Class 10 on Saturday</b><small>Posted 1 Nov 2026</small></li>
          <li><b>Fee payment deadline is 30 November</b><small>Posted 28 Oct 2026</small></li>
        </ul>
      </section>
    </main>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>