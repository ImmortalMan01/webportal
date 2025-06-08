document.addEventListener('DOMContentLoaded',()=>{
  const header=document.querySelector('.dash-header');
  let lastY=window.scrollY;
  window.addEventListener('scroll',()=>{
    if(window.scrollY>lastY && window.scrollY>80){
      header.classList.add('hide');
    }else{
      header.classList.remove('hide');
    }
    lastY=window.scrollY;
  });
  const menu=document.getElementById('settingsMenu');
  const openBtn=document.getElementById('settingsBtn');
  const closeBtn=menu?.querySelector('.close-btn');
  function toggle(open){
    if(open){
      menu.classList.add('open');
      menu.setAttribute('aria-hidden','false');
      document.body.style.overflow='hidden';
    }else{
      menu.classList.remove('open');
      menu.setAttribute('aria-hidden','true');
      document.body.style.overflow='';
    }
  }
  openBtn?.addEventListener('click',()=>toggle(true));
  closeBtn?.addEventListener('click',()=>toggle(false));
});
