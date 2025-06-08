document.addEventListener('DOMContentLoaded',()=>{
  const sidebar=document.querySelector('.wl-sidebar');
  const toggle=document.getElementById('wlSidebarToggle');
  let overlay;

  function closeSidebar(){
    sidebar.classList.remove('open');
    if(overlay){
      overlay.removeEventListener('click',closeSidebar);
      overlay.remove();
      overlay=null;
    }
  }

  function openSidebar(){
    sidebar.classList.add('open');
    if(!overlay){
      overlay=document.createElement('div');
      overlay.className='sidebar-overlay';
      overlay.addEventListener('click',closeSidebar);
      document.body.appendChild(overlay);
    }
  }

  function toggleSidebar(){
    if(sidebar.classList.contains('open')){
      closeSidebar();
    }else{
      openSidebar();
    }
  }

  if(sidebar && toggle){
    toggle.addEventListener('click',toggleSidebar);
  }
});
