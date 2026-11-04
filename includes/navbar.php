<header class="admin-topbar">
  <div class="topbar-heading">
    <span class="eyebrow">Student management</span>
    <h1><?= e($pageTitle ?? 'Dashboard') ?></h1>
  </div>
  <div class="topbar-account">
    <button class="theme-toggle" id="theme-toggle" type="button" aria-label="Switch to dark mode" aria-pressed="false" title="Switch to dark mode">
      <svg class="moon-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.2 15.4A8.5 8.5 0 0 1 8.6 3.8 8.6 8.6 0 1 0 20.2 15.4Z"/></svg>
      <svg class="sun-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
    </button>
    <span class="account-avatar" aria-hidden="true"><?= e(strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1))) ?></span>
    <span class="account-name"><?= e($_SESSION['username'] ?? 'Administrator') ?></span>
    <form method="post" action="logout.php">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <button class="icon-button" type="submit" aria-label="Log out" title="Log out">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3m9-7h6a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-6"/></svg>
      </button>
    </form>
  </div>
</header>