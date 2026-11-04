<aside class="admin-sidebar">
  <?php $activePage = $activePage ?? 'dashboard'; ?>
  <a class="sidebar-brand" href="../index.php">
    <span class="brand-symbol" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M4 11.5 16 5l12 6.5L16 18 4 11.5Z"/><path d="M9 14.5v7c4.5 3.2 9.5 3.2 14 0v-7M28 12v8"/></svg></span>
    <span>Ustudent<span class="brand-subtitle">STUDENT SERVICES</span></span>
  </a>
  <div class="sidebar-label">Workspace</div>
  <nav class="sidebar-nav" aria-label="Dashboard navigation">
    <a class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="dashboard.php#overview"<?= $activePage === 'dashboard' ? ' aria-current="page"' : '' ?>><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="11" width="7" height="10" rx="1"/><rect x="3" y="14" width="8" height="7" rx="1"/></svg><span>Overview</span></a>
    <a class="nav-link <?= $activePage === 'students' ? 'active' : '' ?>" href="students.php"<?= $activePage === 'students' ? ' aria-current="page"' : '' ?>><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1M16 5.5a3 3 0 0 1 0 5.8M18 14a5 5 0 0 1 3 4.5V20"/></svg><span>Students</span></a>
    <a class="nav-link <?= $activePage === 'attendance' ? 'active' : '' ?>" href="attendance.php"<?= $activePage === 'attendance' ? ' aria-current="page"' : '' ?>><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18m-13 5 2 2 5-5"/></svg><span>Attendance</span></a>
    <a class="nav-link <?= $activePage === 'academics' ? 'active' : '' ?>" href="academics.php"<?= $activePage === 'academics' ? ' aria-current="page"' : '' ?>><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 8 9-5 9 5-9 5-9-5Z"/><path d="M7 11v5c3 2.5 7 2.5 10 0v-5m4-3v7"/></svg><span>Academics</span></a>
    <a class="nav-link <?= $activePage === 'fees' ? 'active' : '' ?>" href="fees.php"<?= $activePage === 'fees' ? ' aria-current="page"' : '' ?>><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18m-13 5h4"/></svg><span>Fees</span></a>
  </nav>
  <div class="sidebar-note">
    <span class="note-mark" aria-hidden="true"></span>
    <div><strong>Good systems,</strong><br>better school days.</div>
  </div>
</aside>