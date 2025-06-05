document.addEventListener('DOMContentLoaded', () => {
  const current = localStorage.getItem('theme') || 'light';
  document.body.classList.add(current);
  const toggles = document.querySelectorAll('#themeToggleGlobal');

  function updateButtons(theme) {
    toggles.forEach(btn => {
      btn.textContent = theme === 'dark' ? '☀️' : '🌙';
    });
  }

  updateButtons(current);

  toggles.forEach(btn => {
    btn.addEventListener('click', () => {
      document.body.classList.toggle('dark');
      document.body.classList.toggle('light');
      const now = document.body.classList.contains('dark') ? 'dark' : 'light';
      localStorage.setItem('theme', now);
      updateButtons(now);
    });
  });
});
