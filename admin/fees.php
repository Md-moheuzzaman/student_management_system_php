<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Fees | Ustudent</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">

<!-- Bootstrap first, then your own CSS so it wins -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<link rel="stylesheet" href="../assets/css/academic_common.css">
<link rel="stylesheet" href="../assets/css/fees.css">
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
      <a class="on" href="fees.php"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>Fees</a>
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
          <h1>Fees</h1>
          <p>Record and track fee payments.</p>
        </div>
        <button class="btn" type="button" data-bs-toggle="modal" data-bs-target="#feeModal">+ Record fee</button>
      </div>

      <!-- ===== PART 4: Summary cards ===== -->
      <section class="stats" aria-label="Summary">
        <div class="stat"><span class="ico b"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></span><div><b>$24,860</b><span>Total collected</span></div></div>
        <div class="stat"><span class="ico g"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span><div><b>182</b><span>Paid</span></div></div>
        <div class="stat"><span class="ico y"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><div><b>14</b><span>Partial</span></div></div>
        <div class="stat"><span class="ico r"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg></span><div><b>31</b><span>Unpaid</span></div></div>
      </section>

      <!-- ===== PART 5: Fees table ===== -->
      <section class="card">
        <h2>All payments</h2>

        <!-- Toolbar: search + filter -->
        <div class="tool-bar">
          <div class="search">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
            <input type="search" placeholder="Search by student or receipt no" aria-label="Search fees">
          </div>
          <select class="form-select" aria-label="Filter by status">
            <option>All statuses</option>
            <option>Paid</option>
            <option>Unpaid</option>
            <option>Partial</option>
          </select>
          <input class="form-control w-auto" type="date" aria-label="Payment date">
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>Receipt no</th><th>Student</th><th class="num">Amount</th><th>Payment date</th><th>Status</th><th class="num">Actions</th></tr>
            </thead>
            <tbody>
              <tr>
                <td class="receipt">RC-1006</td>
                <td><div class="who"><span class="dot">AR</span><div><b>Aisha Rahman</b><small>Class 9 - A</small></div></div></td>
                <td class="num amount">$120.00</td>
                <td>10 Nov 2026</td>
                <td><span class="pill paid">Paid</span></td>
                <td><div class="row-actions">
                  <button class="small-btn" type="button" data-bs-toggle="modal" data-bs-target="#feeModal">Edit</button>
                  <button class="small-btn danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete</button>
                </div></td>
              </tr>
              <tr>
                <td class="receipt">RC-1005</td>
                <td><div class="who"><span class="dot">BH</span><div><b>Bilal Hossain</b><small>Class 8 - B</small></div></div></td>
                <td class="num amount">$120.00</td>
                <td class="muted">&mdash;</td>
                <td><span class="pill unpaid">Unpaid</span></td>
                <td><div class="row-actions">
                  <button class="small-btn" type="button" data-bs-toggle="modal" data-bs-target="#feeModal">Edit</button>
                  <button class="small-btn danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete</button>
                </div></td>
              </tr>
              <tr>
                <td class="receipt">RC-1004</td>
                <td><div class="who"><span class="dot">FA</span><div><b>Farzana Akter</b><small>Class 10 - A</small></div></div></td>
                <td class="num amount">$75.00</td>
                <td>08 Nov 2026</td>
                <td><span class="pill partial">Partial</span></td>
                <td><div class="row-actions">
                  <button class="small-btn" type="button" data-bs-toggle="modal" data-bs-target="#feeModal">Edit</button>
                  <button class="small-btn danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete</button>
                </div></td>
              </tr>
              <tr>
                <td class="receipt">RC-1003</td>
                <td><div class="who"><span class="dot">IC</span><div><b>Imran Chowdhury</b><small>Class 7 - C</small></div></div></td>
                <td class="num amount">$120.00</td>
                <td>05 Nov 2026</td>
                <td><span class="pill paid">Paid</span></td>
                <td><div class="row-actions">
                  <button class="small-btn" type="button" data-bs-toggle="modal" data-bs-target="#feeModal">Edit</button>
                  <button class="small-btn danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete</button>
                </div></td>
              </tr>
              <tr>
                <td class="receipt">RC-1002</td>
                <td><div class="who"><span class="dot">NJ</span><div><b>Nusrat Jahan</b><small>Class 9 - B</small></div></div></td>
                <td class="num amount">$120.00</td>
                <td class="muted">&mdash;</td>
                <td><span class="pill unpaid">Unpaid</span></td>
                <td><div class="row-actions">
                  <button class="small-btn" type="button" data-bs-toggle="modal" data-bs-target="#feeModal">Edit</button>
                  <button class="small-btn danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete</button>
                </div></td>
              </tr>
              <tr>
                <td class="receipt">RC-1001</td>
                <td><div class="who"><span class="dot">RA</span><div><b>Rafiq Ahmed</b><small>Class 9 - A</small></div></div></td>
                <td class="num amount">$120.00</td>
                <td>02 Nov 2026</td>
                <td><span class="pill paid">Paid</span></td>
                <td><div class="row-actions">
                  <button class="small-btn" type="button" data-bs-toggle="modal" data-bs-target="#feeModal">Edit</button>
                  <button class="small-btn danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete</button>
                </div></td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Card footer: count + pagination -->
        <div class="card-foot">
          <span>Showing 1&ndash;6 of 227 payments</span>
          <nav aria-label="Fees pages">
            <ul class="pagination pagination-sm">
              <li class="page-item disabled"><a class="page-link" href="#">Prev</a></li>
              <li class="page-item active"><a class="page-link" href="#">1</a></li>
              <li class="page-item"><a class="page-link" href="#">2</a></li>
              <li class="page-item"><a class="page-link" href="#">3</a></li>
              <li class="page-item"><a class="page-link" href="#">Next</a></li>
            </ul>
          </nav>
        </div>
      </section>

    </main>

    <!-- ===== PART 6: Footer ===== -->
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

<!-- ===== PART 7: Record / edit fee modal ===== -->
<div class="modal fade" id="feeModal" tabindex="-1" aria-labelledby="feeModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title" id="feeModalTitle">Fee payment</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" for="feeStudent">Student</label>
              <select class="form-select" id="feeStudent">
                <option>Aisha Rahman &ndash; Class 9 - A</option>
                <option>Bilal Hossain &ndash; Class 8 - B</option>
                <option>Farzana Akter &ndash; Class 10 - A</option>
                <option>Imran Chowdhury &ndash; Class 7 - C</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="feeAmount">Amount</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input class="form-control" type="number" min="0" step="0.01" id="feeAmount" placeholder="0.00">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="feeDate">Payment date</label>
              <input class="form-control" type="date" id="feeDate">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="feeStatus">Status</label>
              <select class="form-select" id="feeStatus">
                <option>Paid</option>
                <option>Unpaid</option>
                <option>Partial</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="feeReceipt">Receipt no</label>
              <input class="form-control" type="text" id="feeReceipt" placeholder="e.g. RC-1007" maxlength="50">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn">Save payment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ===== PART 8: Delete confirmation modal ===== -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title" id="deleteModalTitle">Delete payment?</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="confirm-text">This payment record will be removed. This cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn ghost" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn danger">Delete</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>