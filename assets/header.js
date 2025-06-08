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
});
