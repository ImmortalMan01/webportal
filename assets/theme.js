document.addEventListener('DOMContentLoaded',function(){
  const current = localStorage.getItem('theme') || 'light';
  document.body.classList.add(current);
  const toggle = document.getElementById('themeToggleGlobal');
  function updateButton(theme){
    if(toggle){ toggle.textContent = theme === 'dark' ? '☀️' : '🌙'; }
  }
  updateButton(current);
  if(toggle){
    toggle.addEventListener('click',function(){
      document.body.classList.toggle('dark');
      document.body.classList.toggle('light');
      const now = document.body.classList.contains('dark') ? 'dark' : 'light';
      localStorage.setItem('theme', now);
      updateButton(now);
    });
  }
});
