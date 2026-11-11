<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Marks | Ustudent</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">

<!-- Bootstrap first, then your own CSS so it wins -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<link rel="stylesheet" href="../assets/css/academic_common.css">
<link rel="stylesheet" href="../assets/css/marks.css">
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
      <a href="attendance.php"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/></svg>Attendance</a>
      <a class="on" href="marks.php"><svg viewBox="0 0 24 24"><path d="M9 11l3 3 8-8"/><path d="M20 12v7a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2h9"/></svg>Marks</a>
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
          <h1>Marks</h1>
          <p>Enter and review exam marks for a class and subject.</p>
        </div>
      </div>

      <!-- ===== PART 4: Filters ===== -->
      <section class="card mb-4">
        <h2>Select exam</h2>
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
            <div class="col-md-3">
              <label class="form-label" for="subjectSel">Subject</label>
              <select class="form-select" id="subjectSel">
                <option>Mathematics I</option>
                <option>English Language II</option>
                <option>Physics I</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label" for="examSel">Exam type</label>
              <select class="form-select" id="examSel">
                <option>Midterm</option>
                <option>Final</option>
                <option>Quiz</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label" for="dateSel">Exam date</label>
              <input class="form-control" type="date" id="dateSel" value="2026-11-20">
            </div>
            <div class="col-md-2 d-grid">
              <button class="btn" type="button">Load students</button>
            </div>
          </form>
        </div>
      </section>

      <!-- ===== PART 5: Summary cards ===== -->
      <section class="stats" aria-label="Summary">
        <div class="stat"><span class="ico b"><svg viewBox="0 0 24 24"><path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/></svg></span><div><b>68.0</b><span>Class average</span></div></div>
        <div class="stat"><span class="ico g"><svg viewBox="0 0 24 24"><path d="M12 19V5M5 12l7-7 7 7"/></svg></span><div><b>92</b><span>Highest</span></div></div>
        <div class="stat"><span class="ico r"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12l7 7 7-7"/></svg></span><div><b>38</b><span>Lowest</span></div></div>
        <div class="stat"><span class="ico y"><svg viewBox="0 0 24 24"><path d="M9 11l3 3 8-8"/><path d="M20 12v7a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2h9"/></svg></span><div><b>83%</b><span>Pass rate</span></div></div>
      </section>

      <!-- ===== PART 6: Marks table ===== -->
      <section class="card">
        <h2>Midterm marks <span class="small text-muted fw-normal">Out of 100</span></h2>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Roll</th><th>Student</th><th class="num">Marks</th><th>Grade</th><th>Result</th></tr></thead>
            <tbody>
              <tr>
                <td class="roll">01</td>
                <td><div class="who"><span class="dot">AR</span><div><b>Aisha Rahman</b><small>aisha@example.com</small></div></div></td>
                <td class="num"><input class="form-control mark-input ms-auto" type="number" min="0" max="100" step="0.01" value="92" aria-label="Marks for Aisha Rahman"></td>
                <td><span class="grade ap">A+</span></td>
                <td><span class="pill paid">Pass</span></td>
              </tr>
              <tr>
                <td class="roll">02</td>
                <td><div class="who"><span class="dot">BH</span><div><b>Bilal Hossain</b><small>bilal@example.com</small></div></div></td>
                <td class="num"><input class="form-control mark-input ms-auto" type="number" min="0" max="100" step="0.01" value="78" aria-label="Marks for Bilal Hossain"></td>
                <td><span class="grade a">A</span></td>
                <td><span class="pill paid">Pass</span></td>
              </tr>
              <tr>
                <td class="roll">03</td>
                <td><div class="who"><span class="dot">FA</span><div><b>Farzana Akter</b><small>farzana@example.com</small></div></div></td>
                <td class="num"><input class="form-control mark-input ms-auto" type="number" min="0" max="100" step="0.01" value="65" aria-label="Marks for Farzana Akter"></td>
                <td><span class="grade b">B</span></td>
                <td><span class="pill paid">Pass</span></td>
              </tr>
              <tr>
                <td class="roll">04</td>
                <td><div class="who"><span class="dot">IC</span><div><b>Imran Chowdhury</b><small>imran@example.com</small></div></div></td>
                <td class="num"><input class="form-control mark-input ms-auto" type="number" min="0" max="100" step="0.01" value="54" aria-label="Marks for Imran Chowdhury"></td>
                <td><span class="grade c">C</span></td>
                <td><span class="pill paid">Pass</span></td>
              </tr>
              <tr>
                <td class="roll">05</td>
                <td><div class="who"><span class="dot">NJ</span><div><b>Nusrat Jahan</b><small>nusrat@example.com</small></div></div></td>
                <td class="num"><input class="form-control mark-input ms-auto" type="number" min="0" max="100" step="0.01" value="38" aria-label="Marks for Nusrat Jahan"></td>
                <td><span class="grade f">F</span></td>
                <td><span class="pill unpaid">Fail</span></td>
              </tr>
              <tr>
                <td class="roll">06</td>
                <td><div class="who"><span class="dot">RA</span><div><b>Rafiq Ahmed</b><small>rafiq@example.com</small></div></div></td>
                <td class="num"><input class="form-control mark-input ms-auto" type="number" min="0" max="100" step="0.01" value="81" aria-label="Marks for Rafiq Ahmed"></td>
                <td><span class="grade ap">A+</span></td>
                <td><span class="pill paid">Pass</span></td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- ===== PART 7: Card footer (grading scale + save) ===== -->
        <div class="card-foot">
          <div class="scale">
            <span><b>A+</b> 80+</span>
            <span><b>A</b> 70+</span>
            <span><b>B</b> 60+</span>
            <span><b>C</b> 50+</span>
            <span><b>D</b> 40+</span>
            <span><b>F</b> below 40</span>
          </div>
          <button class="btn" type="button">Save marks</button>
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
</body>
</html>