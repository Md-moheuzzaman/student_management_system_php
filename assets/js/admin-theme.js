(function () {
  var root = document.documentElement;
  var toggle = document.getElementById('theme-toggle');

  if (!toggle) {
    return;
  }

  function updateToggle(theme) {
    var nextTheme = theme === 'dark' ? 'light' : 'dark';
    toggle.setAttribute('aria-label', 'Switch to ' + nextTheme + ' mode');
    toggle.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
    toggle.setAttribute('title', 'Switch to ' + nextTheme + ' mode');
  }

  updateToggle(root.dataset.theme || 'light');
  toggle.addEventListener('click', function () {
    var theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
    root.dataset.theme = theme;
    updateToggle(theme);
    try {
      localStorage.setItem('admin-theme', theme);
    } catch (error) {}
  });
})();