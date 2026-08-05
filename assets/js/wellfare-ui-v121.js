(function(){
  'use strict';
  var body=document.body;
  if(!body||!body.classList.contains('wf-ui-v121')) return;

  var file=(location.pathname.split('/').pop()||'index.php').split('?')[0].toLowerCase();
  var families={
    'student-dashboard.php':['student-dashboard.php','student-auth.php','student-revision.php','weekly-result.php'],
    'spoken-materials.php':['spoken-materials.php','roadmap-lesson.php','free-ai-english-practice.php','ai-teacher.php'],
    'learning-roadmap.php':['learning-roadmap.php'],
    'weekly-test.php':['weekly-test.php','weekly-exam-room.php'],
    'index.php':['index.php']
  };

  document.querySelectorAll('.wf-mobile-app-nav [data-mobile-item]').forEach(function(link){
    var key=(link.getAttribute('data-mobile-item')||'').toLowerCase();
    var active=(families[key]||[key]).indexOf(file)!==-1;
    link.classList.toggle('active',active);
    if(active) link.setAttribute('aria-current','page'); else link.removeAttribute('aria-current');
  });

  document.querySelectorAll('.wf-learning-app-nav__links a').forEach(function(link){
    var href=(link.getAttribute('href')||'').split('?')[0].toLowerCase();
    var active=(families[href]||[href]).indexOf(file)!==-1;
    link.classList.toggle('active',active);
    if(active) link.setAttribute('aria-current','page');
  });

  var navToggle=document.querySelector('.nav-toggle');
  var navMenu=document.querySelector('.clean-main-menu');
  if(navToggle&&navMenu){
    navToggle.addEventListener('click',function(){
      window.setTimeout(function(){
        var open=navMenu.classList.contains('open');
        body.classList.toggle('wf-nav-open',open);
        navToggle.setAttribute('aria-expanded',open?'true':'false');
        navToggle.textContent=open?'×':'☰';
      },0);
    });
    document.addEventListener('click',function(event){
      if(!navMenu.classList.contains('open')) return;
      if(navMenu.contains(event.target)||navToggle.contains(event.target)) return;
      navMenu.classList.remove('open');
      body.classList.remove('wf-nav-open');
      navToggle.setAttribute('aria-expanded','false');
      navToggle.textContent='☰';
    });
  }

  document.querySelectorAll('main .section-title, main .section-head').forEach(function(head){
    if(head.querySelector('.wf-title-rule')) return;
    var rule=document.createElement('span');
    rule.className='wf-title-rule';
    rule.setAttribute('aria-hidden','true');
    var text=head.querySelector('h1,h2');
    if(text) text.insertAdjacentElement('afterend',rule);
  });

  document.querySelectorAll('.course-card,.feature-card,.batch-card,.gallery-card,.google-review-card,.faculty-card,.dash-card').forEach(function(card,index){
    card.style.setProperty('--wf-card-order',String(index%6));
  });

  document.querySelectorAll('main img').forEach(function(img,index){
    if(index>0&&!img.loading) img.loading='lazy';
    if(!img.decoding) img.decoding='async';
  });

  // Header elevation and reading progress.
  var header=document.querySelector('.site-header');
  if(header&&!header.querySelector('.wf-scroll-progress')){
    var progress=document.createElement('div');
    progress.className='wf-scroll-progress';
    progress.setAttribute('aria-hidden','true');
    progress.innerHTML='<span></span>';
    header.appendChild(progress);
  }
  var progressBar=document.querySelector('.wf-scroll-progress span');
  function updateScrollUi(){
    var y=window.scrollY||document.documentElement.scrollTop||0;
    if(header) header.classList.toggle('wf-header-scrolled',y>12);
    if(progressBar){
      var max=Math.max(1,document.documentElement.scrollHeight-window.innerHeight);
      progressBar.style.width=Math.min(100,(y/max)*100)+'%';
    }
  }
  updateScrollUi();
  window.addEventListener('scroll',updateScrollUi,{passive:true});

  // Keep one FAQ open in each group and preserve keyboard-friendly native details.
  document.querySelectorAll('.faq-list').forEach(function(group){
    group.querySelectorAll('details').forEach(function(item){
      item.addEventListener('toggle',function(){
        if(!item.open) return;
        group.querySelectorAll('details[open]').forEach(function(other){if(other!==item) other.open=false;});
      });
    });
  });

  // Make wide tables accessible on keyboard and mobile.
  document.querySelectorAll('.table-wrap,.table-responsive').forEach(function(wrap){
    if(!wrap.hasAttribute('tabindex')) wrap.setAttribute('tabindex','0');
    if(!wrap.hasAttribute('role')) wrap.setAttribute('role','region');
    if(!wrap.hasAttribute('aria-label')) wrap.setAttribute('aria-label','Scrollable data table');
  });

  // Calm reveal animation; content remains visible when JavaScript or motion is unavailable.
  var reduceMotion=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var targets=document.querySelectorAll('main .section,main .card,main .course-card,main .batch-card,main .gallery-card,main .faculty-card,main .google-review-card,main .video-card,main .panel-card,main .form-box,main .roadmap-step,main .material-card,main .exam-question-card');
  if(!reduceMotion&&'IntersectionObserver' in window){
    targets.forEach(function(target){target.classList.add('wf-reveal');});
    var observer=new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){entry.target.classList.add('wf-visible');observer.unobserve(entry.target);}
      });
    },{threshold:.06,rootMargin:'0px 0px -24px'});
    targets.forEach(function(target){observer.observe(target);});
  }else{
    targets.forEach(function(target){target.classList.add('wf-visible');});
  }

  body.classList.add('wf-ui-ready');
})();
