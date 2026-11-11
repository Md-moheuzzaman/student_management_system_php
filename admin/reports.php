<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reports | Ustudent</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">

<!-- Bootstrap first, then your own CSS so it wins -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<link rel="stylesheet" href="../assets/css/academic_common.css">
<link rel="stylesheet" href="../assets/css/reports.css">
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
      <a href="marks.php"><svg viewBox="0 0 24 24"><path d="M9 11l3 3 8-8"/><path d="M20 12v7a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2h9"/></svg>Marks</a>
      <div class="group">Finance</div>
      <a href="fees.php"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>Fees</a>
      <a class="on" href="reports.php"><svg viewBox="0 0 24 24"><path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/></svg>Reports</a>
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
          <h1>Reports</h1>
          <p>Attendance, marks and fee summaries for your institute.</p>
        </div>
        <div class="head-actions">
          <button class="btn ghost" type="button">Print</button>
          <button class="btn" type="button">Export CSV</button>
        </div>
      </div>

      <!-- ===== PART 4: Filters ===== -->
      <section class="card mb-4">
        <h2>Report filters</h2>
        <div class="card-body">
          <form class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label" for="classSel">Class</label>
              <select class="form-select" id="classSel">
                <option>All classes</option>
                <option>Class 9 - A</option>
                <option>Class 9 - B</option>
                <option>Class 10 - A</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label" for="courseSel">Course</label>
              <select class="form-select" id="courseSel">
                <option>All courses</option>
                <option>Mathematics I</option>
                <option>English Language II</option>
                <option>Physics I</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label" for="fromDate">From</label>
              <input class="form-control" type="date" id="fromDate" value="2026-11-01">
            </div>
            <div class="col-md-2">
              <label class="form-label" for="toDate">To</label>
              <input class="form-control" type="date" id="toDate" value="2026-11-12">
            </div>
            <div class="col-md-2 d-grid">
              <button class="btn" type="button">Generate</button>
            </div>
          </form>
        </div>
      </section>

      <!-- ===== PART 5: Summary cards ===== -->
      <section class="stats" aria-label="Summary">
        <div class="stat"><span class="ico b"><svg viewBox="0 0 24 24"><path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.5 6-3v-5"/></svg></span><div><b>1,248</b><span>Total students</span></div></div>
        <div class="stat"><span class="ico g"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/></svg></span><div><b>89%</b><span>Average attendance</span></div></div>
        <div class="stat"><span class="ico y"><svg viewBox="0 0 24 24"><path d="M9 11l3 3 8-8"/><path d="M20 12v7a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2h9"/></svg></span><div><b>68.4</b><span>Average marks</span></div></div>
        <div class="stat"><span class="ico r"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></span><div><b>$24,860</b><span>Fees collected</span></div></div>
      </section>

      <!-- ===== PART 6: Attendance chart + Fee breakdown ===== -->
      <div class="row2">
        <section class="card">
          <h2>Attendance this week</h2>
          <div class="chart" role="img" aria-label="Attendance by day: Monday 94 percent, Tuesday 91, Wednesday 88, Thursday 93, Friday 79, Saturday 85">
            <div class="col"><div class="bar" style="height:94%"><em>94%</em></div><span>Mon</span></div>
            <div class="col"><div class="bar" style="height:91%"><em>91%</em></div><span>Tue</span></div>
            <div class="col"><div class="bar" style="height:88%"><em>88%</em></div><span>Wed</span></div>
            <div class="col"><div class="bar" style="height:93%"><em>93%</em></div><span>Thu</span></div>
            <div class="col"><div class="bar low" style="height:79%"><em>79%</em></div><span>Fri</span></div>
            <div class="col"><div class="bar" style="height:85%"><em>85%</em></div><span>Sat</span></div>
          </div>
        </section>

        <section class="card">
          <h2>Fee status</h2>
          <div class="breakdown">
            <div>
              <div class="line-head"><span>Paid</span><span>182 students &middot; 80%</span></div>
              <div class="progress" role="progressbar" aria-label="Paid" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar paid" style="width:80%"></div></div>
            </div>
            <div>
              <div class="line-head"><span>Partial</span><span>14 students &middot; 6%</span></div>
              <div class="progress" role="progressbar" aria-label="Partial" aria-valuenow="6" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar partial" style="width:6%"></div></div>
            </div>
            <div>
              <div class="line-head"><span>Unpaid</span><span>31 students &middot; 14%</span></div>
              <div class="progress" role="progressbar" aria-label="Unpaid" aria-valuenow="14" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar unpaid" style="width:14%"></div></div>
            </div>
          </div>
          <div class="breakdown-note">Based on 227 payment records.</div>
        </section>
      </div>

      <!-- ===== PART 7: Class performance + Top students ===== -->
      <div class="row2">
        <section class="card">
          <h2>Class performance</h2>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Class</th><th class="num">Students</th><th>Attendance</th><th class="num">Avg marks</th><th class="num">Pass rate</th></tr></thead>
              <tbody>
                <tr><td class="strong">Class 10 - A</td><td class="num">38</td><td><span class="pill paid">94%</span></td><td class="num">74.2</td><td class="num">97%</td></tr>
                <tr><td class="strong">Class 9 - A</td><td class="num">42</td><td><span class="pill paid">92%</span></td><td class="num">71.4</td><td class="num">95%</td></tr>
                <tr><td class="strong">Class 9 - B</td><td class="num">40</td><td><span class="pill partial">88%</span></td><td class="num">66.8</td><td class="num">90%</td></tr>
                <tr><td class="strong">Class 8 - B</td><td class="num">41</td><td><span class="pill partial">85%</span></td><td class="num">63.5</td><td class="num">88%</td></tr>
                <tr><td class="strong">Class 7 - C</td><td class="num">39</td><td><span class="pill unpaid">79%</span></td><td class="num">58.9</td><td class="num">82%</td></tr>
              </tbody>
            </table>
          </div>
        </section>

        <section class="card">
          <h2>Top students</h2>
          <table>
            <thead><tr><th>Student</th><th class="num">Avg marks</th></tr></thead>
            <tbody>
              <tr><td><div class="who"><span class="rank first">1</span><div><b>Aisha Rahman</b><small>Class 9 - A</small></div></div></td><td class="num strong">92.5</td></tr>
              <tr><td><div class="who"><span class="rank">2</span><div><b>Rafiq Ahmed</b><small>Class 9 - A</small></div></div></td><td class="num strong">88.0</td></tr>
              <tr><td><div class="who"><span class="rank">3</span><div><b>Farzana Akter</b><small>Class 10 - A</small></div></div></td><td class="num strong">85.3</td></tr>
              <tr><td><div class="who"><span class="rank">4</span><div><b>Bilal Hossain</b><small>Class 8 - B</small></div></div></td><td class="num strong">81.7</td></tr>
              <tr><td><div class="who"><span class="rank">5</span><div><b>Imran Chowdhury</b><small>Class 7 - C</small></div></div></td><td class="num strong">79.2</td></tr>
            </tbody>
          </table>
        </section>
      </div>

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