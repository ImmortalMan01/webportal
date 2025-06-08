document.addEventListener('DOMContentLoaded',()=>{
  if(typeof currentUser==='undefined' || !currentUser) return;
  const container=document.createElement('div');
  container.id='msgToastContainer';
  const MAX_HEIGHT=Math.round(window.innerHeight*0.5);
  document.body.appendChild(container);

  if(!window.NO_TOAST_WS){
    const ws=new WebSocket(`ws://${location.hostname}:8080?user=${encodeURIComponent(currentUser)}`);
    ws.addEventListener('message',e=>{
      try{
        const data=JSON.parse(e.data);
        if(data.type==='message' && data.from!==currentUser){
          showToast(data.from,data.text);
        }
      }catch(err){console.error(err);}
    });
  }

  function colorFromName(name){
    let hash=0;
    for(let i=0;i<name.length;i++) hash=name.charCodeAt(i)+((hash<<5)-hash);
    const h=Math.abs(hash)%360;
    return `hsl(${h},70%,80%)`;
  }

  function escapeHtml(str){
    return str.replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[m]));
  }

  window.showToast=function(from,msg){
    const text = String(msg||'');
    const truncated = text.length>20 ? text.slice(0,17)+'...' : text;

    const note=document.createElement('div');
    note.className='msg-toast';
    const inner=document.createElement('div');
    inner.className='msg-toast__inner';

    const avatar=document.createElement('div');
    avatar.className='msg-toast__avatar';
    avatar.style.background=colorFromName(from);
    avatar.textContent=from.charAt(0).toUpperCase();

    const content=document.createElement('div');
    content.className='msg-toast__content';
    const title=document.createElement('div');
    title.className='msg-toast__title';
    title.textContent=from;
    const message=document.createElement('div');
    message.className='msg-toast__message';
    message.textContent=truncated;

    content.appendChild(title);
    content.appendChild(message);
    inner.appendChild(avatar);
    inner.appendChild(content);
    note.appendChild(inner);
    note.addEventListener('click',()=>{
      const prefix=location.pathname.includes('/pages/')?'':'pages/';
      location.href=prefix+'messages.php?user='+encodeURIComponent(from);
    });
    container.appendChild(note);
    while(container.offsetHeight>MAX_HEIGHT){
      const first=container.firstElementChild;
      if(first && !first.classList.contains('note--out')) first.classList.add('note--out');
      else break;
    }
    setTimeout(()=>note.classList.add('note--out'),4000);
    note.addEventListener('animationend',()=>{if(note.classList.contains('note--out')) note.remove();});
  };
});
