<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Attendance | Ustudent</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">

<!-- Bootstrap first, then your own CSS so it wins -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<link rel="stylesheet" href="../assets/css/academic_common.css">
<link rel="stylesheet" href="../assets/css/attendance.css">
</head>
<body>
<div class="app">

  <!-- ===== PART 1: Sidebar (same on every page, only "on" changes) ===== -->
  <aside class="side">
    <div class="logo">
      <img src="../assets/images/logo.jpg" alt="logo">
      <span>Ustudent</span>
    </div>
    <nav aria-label="Main">
      <a href="dashboard_admin.php"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>Dashboard</a>
      <div class="group">People</div>
      <a href="#"><svg viewBox="0 0 24 24"><path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.5 6-3v-5"/></svg>Students</a>
      <a href="#"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>Teachers</a>
      <div class="group">Academics</div>
      <a href="courses.php"><svg viewBox="0 0 24 24"><path d="M4 5a2 2 0 012-2h13v16H6a2 2 0 00-2 2z"/><path d="M4 19V5"/></svg>Courses</a>
      <a class="on" href="attendance.php"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/></svg>Attendance</a>
      <a href="marks.php"><svg viewBox="0 0 24 24"><path d="M9 11l3 3 8-8"/><path d="M20 12v7a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2h9"/></svg>Marks</a>
      <div class="group">Finance</div>
      <a href="fees.php"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>Fees</a>
      <a href="reports.php"><svg viewBox="0 0 24 24"><path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/></svg>Reports</a>
    </nav>
    <div class="foot">Ustudent</div>
  </aside>

  <div class="main">

    <!-- ===== PART 2: Topbar ===== -->
    <header class="top">
      <div class="search">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
        <input type="search" placeholder="Search students, teachers, classes" aria-label="Search">
      </div>
      <div class="me">
        <div><b>admin</b><small>admin@gmail.com</small></div>
        <span class="av">AD</span>
        <button class="logout" type="button"><a href="logout.php">Logout</a></button>
      </div>
    </header>

    <main class="content">

      <!-- ===== PART 3: Page header ===== -->
      <div class="head">
        <div>
          <h1>Attendance</h1>
          <p>Take attendance for a class, subject and date.</p>
        </div>
      </div>

      <!-- ===== PART 4: Filters ===== -->
      <section class="card mb-4">
        <h2>Select class</h2>
        <div class="card-body">
          <form class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label" for="classSel">Class</label>
              <select class="form-select" id="classSel">
                <option>Class 9 - A</option>
                <option>Class 9 - B</option>
                <option>Class 10 - A</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="subjectSel">Subject</label>
              <select class="form-select" id="subjectSel">
                <option>Mathematics I</option>
                <option>English Language II</option>
                <option>Physics I</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label" for="dateSel">Date</label>
              <input class="form-control" type="date" id="dateSel" value="2026-11-12">
            </div>
            <div class="col-md-2 d-grid">
              <button class="btn" type="button">Load students</button>
            </div>
          </form>
        </div>
      </section>

      <!-- ===== PART 5: Summary cards ===== -->
      <section class="stats" aria-label="Summary">
        <div class="stat"><span class="ico b"><svg viewBox="0 0 24 24"><path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.5 6-3v-5"/></svg></span><div><b>6</b><span>Total students</span></div></div>
        <div class="stat"><span class="ico g"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span><div><b id="cP">4</b><span>Present</span></div></div>
        <div class="stat"><span class="ico r"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg></span><div><b id="cA">1</b><span>Absent</span></div></div>
        <div class="stat"><span class="ico y"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><div><b id="cL">1</b><span>Late</span></div></div>
      </section>

      <!-- ===== PART 6: Attendance table ===== -->
      <section class="card">
        <h2>Mark attendance <button class="small-btn" type="button" id="markAll">Mark all present</button></h2>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Roll</th><th>Student</th><th>Status</th></tr></thead>
            <tbody>
              <tr>
                <td class="roll">01</td>
                <td><div class="who"><span class="dot">AR</span><div><b>Aisha Rahman</b><small>aisha@example.com</small></div></div></td>
                <td><div class="opts">
                  <input type="radio" class="btn-check" name="att1" id="a1p" value="P" checked><label class="opt present" for="a1p">Present</label>
                  <input type="radio" class="btn-check" name="att1" id="a1a" value="A"><label class="opt absent" for="a1a">Absent</label>
                  <input type="radio" class="btn-check" name="att1" id="a1l" value="L"><label class="opt late" for="a1l">Late</label>
                </div></td>
              </tr>
              <tr>
                <td class="roll">02</td>
                <td><div class="who"><span class="dot">BH</span><div><b>Bilal Hossain</b><small>bilal@example.com</small></div></div></td>
                <td><div class="opts">
                  <input type="radio" class="btn-check" name="att2" id="a2p" value="P" checked><label class="opt present" for="a2p">Present</label>
                  <input type="radio" class="btn-check" name="att2" id="a2a" value="A"><label class="opt absent" for="a2a">Absent</label>
                  <input type="radio" class="btn-check" name="att2" id="a2l" value="L"><label class="opt late" for="a2l">Late</label>
                </div></td>
              </tr>
              <tr>
                <td class="roll">03</td>
                <td><div class="who"><span class="dot">FA</span><div><b>Farzana Akter</b><small>farzana@example.com</small></div></div></td>
                <td><div class="opts">
                  <input type="radio" class="btn-check" name="att3" id="a3p" value="P"><label class="opt present" for="a3p">Present</label>
                  <input type="radio" class="btn-check" name="att3" id="a3a" value="A" checked><label class="opt absent" for="a3a">Absent</label>
                  <input type="radio" class="btn-check" name="att3" id="a3l" value="L"><label class="opt late" for="a3l">Late</label>
                </div></td>
              </tr>
              <tr>
                <td class="roll">04</td>
                <td><div class="who"><span class="dot">IC</span><div><b>Imran Chowdhury</b><small>imran@example.com</small></div></div></td>
                <td><div class="opts">
                  <input type="radio" class="btn-check" name="att4" id="a4p" value="P" checked><label class="opt present" for="a4p">Present</label>
                  <input type="radio" class="btn-check" name="att4" id="a4a" value="A"><label class="opt absent" for="a4a">Absent</label>
                  <input type="radio" class="btn-check" name="att4" id="a4l" value="L"><label class="opt late" for="a4l">Late</label>
                </div></td>
              </tr>
              <tr>
                <td class="roll">05</td>
                <td><div class="who"><span class="dot">NJ</span><div><b>Nusrat Jahan</b><small>nusrat@example.com</small></div></div></td>
                <td><div class="opts">
                  <input type="radio" class="btn-check" name="att5" id="a5p" value="P"><label class="opt present" for="a5p">Present</label>
                  <input type="radio" class="btn-check" name="att5" id="a5a" value="A"><label class="opt absent" for="a5a">Absent</label>
                  <input type="radio" class="btn-check" name="att5" id="a5l" value="L" checked><label class="opt late" for="a5l">Late</label>
                </div></td>
              </tr>
              <tr>
                <td class="roll">06</td>
                <td><div class="who"><span class="dot">RA</span><div><b>Rafiq Ahmed</b><small>rafiq@example.com</small></div></div></td>
                <td><div class="opts">
                  <input type="radio" class="btn-check" name="att6" id="a6p" value="P" checked><label class="opt present" for="a6p">Present</label>
                  <input type="radio" class="btn-check" name="att6" id="a6a" value="A"><label class="opt absent" for="a6a">Absent</label>
                  <input type="radio" class="btn-check" name="att6" id="a6l" value="L"><label class="opt late" for="a6l">Late</label>
                </div></td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- ===== PART 7: Card footer (legend + save) ===== -->
        <div class="card-foot">
          <div class="legend">
            <span><i class="p"></i>Present (P)</span>
            <span><i class="a"></i>Absent (A)</span>
            <span><i class="l"></i>Late (L)</span>
          </div>
          <button class="btn" type="button">Save attendance</button>
        </div>
      </section>

    </main>

    <!-- ===== PART 8: Footer ===== -->
    <footer class="site-footer">
      <span>&copy; Ustudent. 2026. All Rights Reserved.</span>
      <nav aria-label="Footer">
        <a href="#">Help</a>
        <a href="#">Privacy</a>
        <a href="#">Contact</a>
      </nav>
    </footer>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* Only for the demo: live counters and "Mark all present" */
function count() {
  ['P','A','L'].forEach(function (v) {
    document.getElementById('c' + v).textContent =
      document.querySelectorAll('.opts input[value="' + v + '"]:checked').length;
  });
}
document.querySelectorAll('.opts input').forEach(function (r) { r.addEventListener('change', count); });
document.getElementById('markAll').addEventListener('click', function () {
  document.querySelectorAll('.opts input[value="P"]').forEach(function (r) { r.checked = true; });
  count();
});
count();
</script>
</body>
</html>