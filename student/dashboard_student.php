<?php
session_start();

include '../database/dbconfig.php';

// Admin Authentication
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Dashboard | Ustudent</title>
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
    <?php include '../includes/sidebar_student.php'; ?>

   

  <!-- Main -->
  <div class="main">
     <!-- Topbar -->
     <?php include '../includes/topbar.php'; ?>

    <main class="content">
      <div class="head">
        <div>
          <h1>Student Dashboard</h1>
          <p>Here is what is happening at your institute today.</p>
        </div>
        <!-- <button class="btn" type="button">+ Add student</button> -->
      </div>

      <!-- Stat cards -->
      <section class="stats" aria-label="Summary">
        <div class="stat"><span class="ico b"><svg viewBox="0 0 24 24"><path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.500 6-3v-5"/></svg></span><div><b>248</b><span>Students</span></div></div>
        <div class="stat"><span class="ico g"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg></span><div><b>5</b><span>Teachers</span></div></div>
        <div class="stat"><span class="ico y"><svg viewBox="0 0 24 24"><path d="M4 5a2 2 0 012-2h13v16H6a2 2 0 00-2 2z"/><path d="M4 19V5"/></svg></span><div><b>7</b><span>Courses</span></div></div>
        <!-- <div class="stat"><span class="ico r"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></span><div><b>$24,860</b><span>Fees collected</span></div></div> -->
      </section>

      <!-- Chart + quick actions -->
       
      <div class="row2">
        <section class="card">
          <h2>Attendance this week <a href="#">View report</a></h2>
          <div class="chart" role="img" aria-label="Attendance by day: Monday 94 percent, Tuesday 91, Wednesday 88, Thursday 93, Friday 79, Saturday 85">
            <div class="col"><div class="bar" style="height:94%"><em>94%</em></div><span>Mon</span></div>
            <div class="col"><div class="bar" style="height:91%"><em>91%</em></div><span>Tue</span></div>
            <div class="col"><div class="bar" style="height:88%"><em>88%</em></div><span>Wed</span></div>
            <div class="col"><div class="bar" style="height:93%"><em>93%</em></div><span>Thu</span></div>
            <div class="col"><div class="bar low" style="height:79%"><em>79%</em></div><span>Fri</span></div>
            <div class="col"><div class="bar" style="height:85%"><em>85%</em></div><span>Sat</span></div>
          </div>
        </section>
      

      <!-- Students + fees -->
      <div class="row2">
        

        
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