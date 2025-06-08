(function(){
  function setup(container){
    const items = container.querySelectorAll('li');
    items.forEach(li => {
      const article = li.querySelector('article');
      const dismiss = article.querySelector('.dismiss');
      article.addEventListener('click', () => activate(li, container));
      dismiss.addEventListener('click', e => { e.stopPropagation(); deactivate(li, container); });
    });
  }
  function activate(li, container){
    if(li.classList.contains('active')) return;
    container.classList.add('inactive');
    container.querySelectorAll('li.active').forEach(l=>l.classList.remove('active'));
    li.classList.add('active');
  }
  function deactivate(li, container){
    li.classList.remove('active');
    container.classList.remove('inactive');
  }
  window.setupHolidayCalendar = setup;
})();
