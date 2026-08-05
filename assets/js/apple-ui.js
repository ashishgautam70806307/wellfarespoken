(function(){
  const ready=()=>{
    const body=document.body;
    const topbar=document.querySelector('.topbar');
    const mobileToggle=topbar?.querySelector('[data-sidebar-toggle]');
    if(topbar && !topbar.querySelector('[data-desktop-sidebar-toggle]')){
      const btn=document.createElement('button');
      btn.type='button';
      btn.className='icon-btn sidebar-collapse-btn';
      btn.setAttribute('data-desktop-sidebar-toggle','');
      btn.setAttribute('aria-label','Collapse sidebar');
      btn.innerHTML='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m14 18-6-6 6-6"/><path d="M20 4v16"/></svg>';
      if(mobileToggle) mobileToggle.insertAdjacentElement('afterend',btn); else topbar.prepend(btn);
      const stored=localStorage.getItem('bfs-student-sidebar-collapsed')==='1';
      if(stored) body.classList.add('sidebar-collapsed');
      btn.addEventListener('click',()=>{
        body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('bfs-student-sidebar-collapsed',body.classList.contains('sidebar-collapsed')?'1':'0');
      });
    }

    document.querySelectorAll('.btn').forEach(btn=>btn.classList.add('wave-button'));
    document.querySelectorAll('.nav-link').forEach(link=>{const label=link.textContent.trim();if(label){link.setAttribute('aria-label',label);if(link.classList.contains('active'))link.setAttribute('aria-current','page')}});
    const revealTargets=[...document.querySelectorAll('.card,.apple-metric,.apple-service,.attendance-panel,.attendance-subject-card')];
    revealTargets.forEach((el,index)=>{
      el.setAttribute('data-apple-reveal','');
      el.style.transitionDelay=Math.min(index%8,5)*35+'ms';
    });
    if('IntersectionObserver' in window){
      const observer=new IntersectionObserver(entries=>entries.forEach(entry=>{
        if(entry.isIntersecting){entry.target.classList.add('apple-visible');observer.unobserve(entry.target)}
      }),{threshold:.08,rootMargin:'0px 0px -25px 0px'});
      revealTargets.forEach(el=>observer.observe(el));
    }else revealTargets.forEach(el=>el.classList.add('apple-visible'));

    document.querySelectorAll('a.student-module').forEach(link=>{
      link.addEventListener('pointermove',e=>{
        const r=link.getBoundingClientRect();
        link.style.setProperty('--pointer-x',`${e.clientX-r.left}px`);
        link.style.setProperty('--pointer-y',`${e.clientY-r.top}px`);
      });
    });
  };
  document.readyState==='loading'?document.addEventListener('DOMContentLoaded',ready):ready();
})();
