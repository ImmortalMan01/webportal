document.addEventListener('DOMContentLoaded',()=>{
  const menuBtn=document.getElementById('mobileMenuBtn');
  const menu=document.getElementById('mobileMenu');
  const closeBtn=document.getElementById('closeMenu');
  menuBtn&&menuBtn.addEventListener('click',()=>menu&&menu.classList.add('open'));
  closeBtn&&closeBtn.addEventListener('click',()=>menu&&menu.classList.remove('open'));
  let last=window.scrollY;const header=document.querySelector('.portal-nav');
  window.addEventListener('scroll',()=>{
    if(!header)return;
    const cur=window.scrollY;
    if(cur>last&&cur>50){header.classList.add('hide');}
    else{header.classList.remove('hide');}
    last=cur;
  });

  const logo=document.querySelector('.portal-logo');
  const actions=document.querySelector('.nav-actions');
  function adjustLogo(){
    if(!logo||!actions||!header)return;
    if(window.innerWidth>600){logo.style.fontSize='';return;}
    const maxWidth=header.clientWidth-actions.clientWidth-16;
    logo.style.fontSize='';
    let size=parseFloat(getComputedStyle(logo).fontSize);
    while(logo.scrollWidth>maxWidth&&size>12){
      size-=1;
      logo.style.fontSize=size+'px';
    }
  }
  window.addEventListener('resize',adjustLogo);
  adjustLogo();
});
