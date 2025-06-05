document.addEventListener('DOMContentLoaded',()=>{
  const btn=document.getElementById('dropDown');
  const menu=document.querySelector('.drop-down');
  if(!btn||!menu)return;
  btn.addEventListener('click',e=>{
    e.stopPropagation();
    menu.classList.toggle('drop-down--active');
  });
  document.addEventListener('click',e=>{
    if(!menu.contains(e.target)) menu.classList.remove('drop-down--active');
  });
});
