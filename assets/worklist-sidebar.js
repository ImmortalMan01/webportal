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
    if(window.innerWidth>768){
      return; // desktop layout does not toggle
    }
    sidebar.classList.add('open');
    if(!overlay){
      overlay=document.createElement('div');
      overlay.className='sidebar-overlay';
      overlay.addEventListener('click',closeSidebar);
      document.body.appendChild(overlay);
    }
  }

  function toggleSidebar(){
    if(window.innerWidth>768){
      return; // ignore clicks on desktop
    }
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
