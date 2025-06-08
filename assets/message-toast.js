document.addEventListener('DOMContentLoaded',()=>{
  if(typeof currentUser==='undefined' || !currentUser) return;
  const container=document.createElement('div');
  container.id='msgToastContainer';
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

  window.showToast=function(from,msg){
    const note=document.createElement('div');
    note.className='msg-toast';
    note.innerHTML=`<div class="msg-toast__inner">
      <div class="msg-toast__avatar" style="background:${colorFromName(from)}">${from.charAt(0).toUpperCase()}</div>
      <div class="msg-toast__content">
        <div class="msg-toast__title">${from}</div>
        <div class="msg-toast__message">${msg.replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[m]))}</div>
      </div>
    </div>`;
    container.appendChild(note);
    setTimeout(()=>note.classList.add('note--out'),4000);
    note.addEventListener('animationend',()=>{if(note.classList.contains('note--out')) note.remove();});
  };
});
