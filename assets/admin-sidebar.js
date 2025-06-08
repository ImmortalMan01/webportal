document.addEventListener('DOMContentLoaded',()=>{
  const sidebar=document.querySelector('.admin-sidebar');
  const toggle=document.getElementById('sidebarToggle');
  let overlay;

  const closeSidebar=()=>{
    sidebar.classList.remove('open');
    if(overlay){
      overlay.removeEventListener('click',closeSidebar);
      overlay.remove();
      overlay=null;
    }
  };

  const openSidebar=()=>{
    sidebar.classList.add('open');
    if(!overlay){
      overlay=document.createElement('div');
      overlay.className='sidebar-overlay';
      overlay.addEventListener('click',closeSidebar);
      document.body.appendChild(overlay);
    }
  };

  const toggleSidebar=()=>{
    if(sidebar.classList.contains('open')){
      closeSidebar();
    }else{
      openSidebar();
    }
  };

  if(sidebar && toggle){
    toggle.addEventListener('click',toggleSidebar);
  }
});
