document.addEventListener('DOMContentLoaded',()=>{
  const sidebar=document.querySelector('.admin-sidebar');
  const toggle=document.getElementById('sidebarToggle');
  if(sidebar && toggle){
    toggle.addEventListener('click',()=>{
      sidebar.classList.toggle('open');
    });
  }
});
