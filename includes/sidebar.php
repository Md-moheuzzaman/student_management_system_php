  <aside class="side" id="admin-sidebar">
    <div class="logo">
      <img src="../assets/images/logo.jpg" alt="logo">
      <span class="brand-name">Ustudent</span>
      <button class="sidebar-toggle" type="button" aria-controls="admin-sidebar" aria-label="Collapse sidebar" aria-expanded="true" title="Collapse sidebar">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 6l-6 6 6 6"/></svg>
      </button>
    </div>
    <nav aria-label="Main">
      <a class="on" href="dashboard_admin.php" title="Dashboard"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg><span class="nav-label">Dashboard</span></a>
      <div class="group">People</div>
      <a href="student.php" title="Students"><svg viewBox="0 0 24 24"><path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.500 6-3v-5"/></svg><span class="nav-label">Students</span></a>
      <a href="teacher.php" title="Teachers"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg><span class="nav-label">Teachers</span></a>
      <div class="group">Academics</div>
      <a href="courses.php" title="Subjects"><svg viewBox="0 0 24 24"><path d="M4 5a2 2 0 012-2h13v16H6a2 2 0 00-2 2z"/><path d="M4 19V5"/></svg><span class="nav-label">Courses</span></a>
      <a href="classes.php" title="Classes"><svg viewBox="0 0 24 24"><path d="M4 5a2 2 0 012-2h13v16H6a2 2 0 00-2 2z"/><path d="M4 19V5"/></svg><span class="nav-label">Departments</span></a>
      <a href="marks.php" title="Marks"><svg viewBox="0 0 24 24"><path d="M9 11l3 3 8-8"/><path d="M20 12v7a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2h9"/></svg><span class="nav-label">Marks</span></a>
      <div class="group">Finance</div>
      <a href="fees.php" title="Fees"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg><span class="nav-label">Fees</span></a>
      <a href="reports.php" title="Reports"><svg viewBox="0 0 24 24"><path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/></svg><span class="nav-label">Reports</span></a>
    </nav>
    <div class="foot"><span>Ustudent</span></div>
  </aside>

  <script>
    (function () {
      var app = document.querySelector('.app');
      var toggle = document.querySelector('.sidebar-toggle');

      if (!app || !toggle) {
        return;
      }

      var collapsed = localStorage.getItem('admin-sidebar-collapsed') === 'true';
      app.classList.toggle('sidebar-collapsed', collapsed);
      toggle.setAttribute('aria-expanded', String(!collapsed));
      toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
      toggle.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar');

      toggle.addEventListener('click', function () {
        collapsed = app.classList.toggle('sidebar-collapsed');
        localStorage.setItem('admin-sidebar-collapsed', String(collapsed));
        toggle.setAttribute('aria-expanded', String(!collapsed));
        toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        toggle.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
      });
    })();
  </script>