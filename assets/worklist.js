(function(){
  const storageKey = 'acibadem-requests';
  let data = JSON.parse(localStorage.getItem(storageKey) || '{}');
  const monthNames = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
  const dayNames = ['Pzt','Sal','Çar','Per','Cum','Cmt','Paz'];
  let current = new Date();
  const views = document.querySelectorAll('.wl-view');
  document.querySelectorAll('.wl-sidebar li').forEach(li=>{
    li.addEventListener('click',()=>{
      document.querySelector('.wl-sidebar li.active')?.classList.remove('active');
      li.classList.add('active');
      const v = li.getAttribute('data-view');
      views.forEach(div=>div.classList.toggle('active', div.id===v));
      if(v==='requests'){ openDrawer(); } else { closeDrawer(); }
    });
  });

  function save(){ localStorage.setItem(storageKey, JSON.stringify(data)); }

  function renderCalendar(){
    const year = current.getFullYear();
    const month = current.getMonth();
    const first = new Date(year, month, 1);
    const startDay = (first.getDay()+6)%7; // monday start
    const daysInMonth = new Date(year, month+1, 0).getDate();
    const tbl = document.createElement('table');
    const thead = document.createElement('thead');
    const trh = document.createElement('tr');
    dayNames.forEach(d=>{ const th=document.createElement('th'); th.textContent=d; trh.appendChild(th); });
    thead.appendChild(trh); tbl.appendChild(thead);
    const tbody = document.createElement('tbody');
    let d = 1; let done=false;
    for(let r=0;r<6;r++){
      const tr=document.createElement('tr');
      for(let c=0;c<7;c++){
        const td=document.createElement('td');
        if(r===0 && c<startDay || d>daysInMonth){ td.innerHTML=''; } else {
          const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
          const span=document.createElement('span'); span.textContent=d; td.appendChild(span);
          const entry=data[dateStr];
          if(entry){ const code=document.createElement('div'); code.className='code'; code.textContent=entry.type[0].toUpperCase(); code.classList.add(entry.type.charAt(0)); td.appendChild(code); td.style.background=bgColor(entry.type); }
          td.dataset.date=dateStr;
          td.addEventListener('click',()=>openModal(dateStr));
          d++; if(d>daysInMonth) done=true;
        }
        tr.appendChild(td);
      }
      tbody.appendChild(tr);
      if(done) break;
    }
    tbl.appendChild(tbody);
    const cont=document.getElementById('calendarComponent');
    cont.innerHTML='';
    const header=document.createElement('div');
    header.className='cal-header';
    const prev=document.createElement('button'); prev.textContent='Önceki Ay'; prev.addEventListener('click',()=>{current.setMonth(current.getMonth()-1); renderCalendar();});
    const next=document.createElement('button'); next.textContent='Sonraki Ay'; next.addEventListener('click',()=>{current.setMonth(current.getMonth()+1); renderCalendar();});
    const title=document.createElement('div'); title.textContent=monthNames[month]+ ' '+year;
    header.appendChild(prev); header.appendChild(title); header.appendChild(next);
    cont.appendChild(header); cont.appendChild(tbl);
    const legend=document.createElement('div'); legend.className='legend';
    legend.innerHTML=`<span><div class="color g"></div>Gündüz</span><span><div class="color n"></div>Nöbet</span><span><div class="color i"></div>İzin</span><span><div class="color d"></div>Diğer</span>`;
    cont.appendChild(legend);
    tbl.classList.add('wl-calendar');
  }

  function bgColor(t){ return t==='gündüz'? '#0b3e6f' : t==='nöbet'? '#661314' : t==='izin'? '#0f4d2f' : '#3a3a3a'; }

  // Modal logic
  const modal = document.getElementById('wls-modal');
  const modalDate = modal.querySelector('.subtitle');
  const radios = modal.querySelectorAll('input[name="type"]');
  const noteEl = modal.querySelector('textarea');

  function openModal(date){
    modal.classList.add('active');
    modal.dataset.date=date;
    const d=new Date(date); modalDate.textContent='Seçilen Tarih: '+d.toLocaleDateString('tr-TR',{day:'2-digit',month:'long',year:'numeric',weekday:'long'});
    const entry=data[date]||{};
    radios.forEach(r=>r.checked=r.value===entry.type);
    noteEl.value=entry.note||'';
  }
  function closeModal(){ modal.classList.remove('active'); }
  modal.querySelector('.cancel').addEventListener('click',closeModal);
  modal.addEventListener('click',e=>{if(e.target===modal) closeModal();});
  document.addEventListener('keydown',e=>{if(e.key==='Escape') closeModal();});
  modal.querySelector('.save').addEventListener('click',()=>{
    const date=modal.dataset.date;
    const type=[...radios].find(r=>r.checked)?.value;
    const note=noteEl.value.trim();
    if(type){ data[date]={type,note}; save(); }
    closeModal(); renderCalendar(); refreshList();
  });

  // Drawer
  const drawer=document.getElementById('wls-drawer');
  drawer.querySelector('.close').addEventListener('click',closeDrawer);
  function openDrawer(){ drawer.classList.add('open'); refreshList(); }
  function closeDrawer(){ drawer.classList.remove('open'); }

  function refreshList(){
    const list=drawer.querySelector('.list');
    list.innerHTML='';
    Object.keys(data).sort().forEach(date=>{
      const entry=data[date];
      const div=document.createElement('div');
      div.className='request-item';
      div.innerHTML=`<div>${new Date(date).toLocaleDateString('tr-TR',{day:'2-digit',month:'long',year:'numeric',weekday:'long'})} <span class="badge ${entry.type.charAt(0)}">${entry.type[0].toUpperCase()}</span></div>`;
      if(entry.note) div.innerHTML+=`<div class="note">${entry.note}</div>`;
      const actions=document.createElement('div'); actions.className='actions';
      const edit=document.createElement('i'); edit.className='fa-solid fa-pencil';
      const del=document.createElement('i'); del.className='fa-solid fa-trash';
      edit.addEventListener('click',()=>openModal(date));
      del.addEventListener('click',()=>{ delete data[date]; save(); refreshList(); renderCalendar(); });
      actions.appendChild(edit); actions.appendChild(del); div.appendChild(actions);
      list.appendChild(div);
    });
  }

  renderCalendar();
})();
