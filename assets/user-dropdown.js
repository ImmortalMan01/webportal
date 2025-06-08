document.addEventListener('DOMContentLoaded',()=>{
  const dropdowns=document.querySelectorAll('.drop-down');
  if(!dropdowns.length) return;
  dropdowns.forEach(dd=>{
    const btn=dd.querySelector('.drop-down__button');
    if(!btn) return;
    if(btn.id==='mobileMenuBtn' && window.innerWidth<768) return;
    btn.addEventListener('click',e=>{
      e.stopPropagation();
      dd.classList.toggle('drop-down--active');
    });
  });
  document.addEventListener('click',e=>{
    dropdowns.forEach(dd=>{
      if(!dd.contains(e.target)) dd.classList.remove('drop-down--active');
    });
  });
});
