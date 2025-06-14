(function(){
  const storageKey = 'acibadem-requests';
  let data = JSON.parse(localStorage.getItem(storageKey) || '{}');
  const monthNames = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
  let current = new Date();
  const typeColors = { g: '#3fa7ff', n: '#dc3545', i: '#198754', d: '#6c757d' };
  function capitalize(text){
    const map={i:'İ',ş:'Ş',ğ:'Ğ',ü:'Ü',ö:'Ö',ç:'Ç',ı:'I'};
    const f=text.charAt(0);
    return (map[f]||f.toUpperCase())+text.slice(1);
  }
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
    const cont = document.getElementById('calendarComponent');
    if(!cont) return;
    cont.innerHTML='';

    const wrap=document.createElement('div');
    wrap.className='hc-container';

    const y=document.createElement('h1');
    y.className='year';
    y.textContent='\u2014 '+year+' \u2014';
    wrap.appendChild(y);

    const desc=document.createElement('h2');
    desc.className='description';
    desc.textContent='İstek/İzin Takvimi';
    wrap.appendChild(desc);

    const ul=document.createElement('ul');

    for(let month=0; month<12; month++){
      const li=document.createElement('li');
      const article=document.createElement('article');
      article.tabIndex=0;
      article.innerHTML='<div class="outline"></div><div class="dismiss"></div><div class="binding"></div>';
      const h=document.createElement('h1');
      h.textContent=monthNames[month];
      article.appendChild(h);

      const table=document.createElement('table');
      const thead=document.createElement('thead');
      const trh=document.createElement('tr');
      ['Paz','Pzt','Sal','Çar','Per','Cum','Cmt'].forEach(d=>{ const th=document.createElement('th'); th.textContent=d; trh.appendChild(th); });
      thead.appendChild(trh); table.appendChild(thead);
      const tbody=document.createElement('tbody');

      const first=new Date(year, month,1);
      const start=first.getDay();
      const days=new Date(year, month+1,0).getDate();
      let day=1;
      for(let r=0;r<6;r++){
        const tr=document.createElement('tr');
        for(let c=0;c<7;c++){
          const td=document.createElement('td');
          if(r===0 && c<start || day>days){
          }else{
            const dateStr=`${year}-${String(month+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
            const divDay=document.createElement('div');
            divDay.className='day';
            divDay.textContent=day;
            td.appendChild(divDay);
            const entry=data[dateStr];
            if(entry){
              const code = entry.type.charAt(0);
              td.classList.add('is-holiday', code);
              td.style.backgroundColor = typeColors[code] || '';
              const hol=document.createElement('div');
              hol.className='holiday';
              hol.textContent=capitalize(entry.type);
              td.appendChild(hol);
            }
            td.dataset.date=dateStr;
            td.addEventListener('click',e=>{e.stopPropagation(); openModal(dateStr);});
            day++;
          }
          tr.appendChild(td);
        }
        tbody.appendChild(tr);
        if(day>days) break;
      }
      table.appendChild(tbody);
      article.appendChild(table);
      li.appendChild(article);
      ul.appendChild(li);
    }

    wrap.appendChild(ul);

    const legend=document.createElement('div');
    legend.className='legend';
    [
      ['Gündüz','g'],
      ['Nöbet','n'],
      ['İzin','i'],
      ['Diğer','d']
    ].forEach(([text,cls])=>{
      const span=document.createElement('span');
      const color=document.createElement('div');
      color.className='color '+cls;
      span.appendChild(color);
      span.appendChild(document.createTextNode(text));
      legend.appendChild(span);
    });
    wrap.appendChild(legend);

    cont.appendChild(wrap);

    if(typeof setupHolidayCalendar==='function') setupHolidayCalendar(ul);
  }

  // Modal logic
  const modal = document.getElementById('wls-modal');
  const modalDate = modal.querySelector('.subtitle');
  const radios = modal.querySelectorAll('input[name="type"]');

  function openModal(date){
    modal.classList.add('active');
    modal.dataset.date=date;
    const d=new Date(date); modalDate.textContent='Seçilen Tarih: '+d.toLocaleDateString('tr-TR',{day:'2-digit',month:'long',year:'numeric',weekday:'long'});
    const entry=data[date]||{};
    radios.forEach(r=>r.checked=r.value===entry.type);
  }
  function closeModal(){ modal.classList.remove('active'); }
  modal.querySelector('.cancel').addEventListener('click',closeModal);
  modal.addEventListener('click',e=>{if(e.target===modal) closeModal();});
  document.addEventListener('keydown',e=>{if(e.key==='Escape') closeModal();});
  modal.querySelector('.save').addEventListener('click',()=>{
    const date=modal.dataset.date;
    const type=[...radios].find(r=>r.checked)?.value;
    if(type){ data[date]={type}; save(); }
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
