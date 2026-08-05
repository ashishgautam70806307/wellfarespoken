(() => {
  'use strict';

  const body = document.body;
  const header = document.querySelector('[data-site-header]');
  const drawer = document.querySelector('[data-mobile-drawer]');
  const overlay = document.querySelector('[data-menu-overlay]');
  const toggle = document.querySelector('[data-menu-toggle]');
  const closeBtn = document.querySelector('[data-menu-close]');

  const setMenu = (open) => {
    body.classList.toggle('menu-open', open);
    if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (drawer) drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
  };

  toggle?.addEventListener('click', () => setMenu(!body.classList.contains('menu-open')));
  closeBtn?.addEventListener('click', () => setMenu(false));
  overlay?.addEventListener('click', () => setMenu(false));
  drawer?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setMenu(false)));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setMenu(false);
  });

  const progress = document.querySelector('.wf-scroll-progress span');
  const updateScroll = () => {
    const y = window.scrollY || document.documentElement.scrollTop;
    const total = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
    if (progress) progress.style.width = `${Math.min(100, Math.max(0, y / total * 100))}%`;
    header?.classList.toggle('is-compact', y > 20);
  };
  updateScroll();
  window.addEventListener('scroll', updateScroll, { passive: true });

  const revealTargets = document.querySelectorAll(
    '.section-title,.feature-card,.course-card,.premium-course,.batch-card,.gallery-card,.faculty-card,.google-review-card,.about-mini-card-v2,.contact-mini-card-v2,.card,.panel-card,.wf-process-step,.dark-cta'
  );
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
    revealTargets.forEach((el, index) => {
      el.classList.add('wf-reveal');
      el.style.transitionDelay = `${Math.min(index % 6, 5) * 55}ms`;
      observer.observe(el);
    });
  } else {
    revealTargets.forEach((el) => el.classList.add('is-visible'));
  }

  const current = (location.pathname.split('/').pop() || 'index.php').toLowerCase();
  document.querySelectorAll('.wf-mobile-bottom a').forEach((link) => {
    const href = (link.getAttribute('href') || '').split('?')[0].toLowerCase();
    if (href === current || (current === 'student-dashboard.php' && href === 'student-auth.php')) link.classList.add('active');
  });

  document.querySelectorAll('.btn,.wf-admission-btn,.wf-prefooter-cta a').forEach((button) => {
    button.addEventListener('pointerdown', (event) => {
      const rect = button.getBoundingClientRect();
      const ripple = document.createElement('span');
      const size = Math.max(rect.width, rect.height) * 1.4;
      ripple.style.cssText = `position:absolute;pointer-events:none;width:${size}px;height:${size}px;left:${event.clientX - rect.left - size / 2}px;top:${event.clientY - rect.top - size / 2}px;border-radius:50%;background:rgba(255,255,255,.28);transform:scale(0);animation:wfRipple .55s ease-out forwards;`;
      if (getComputedStyle(button).position === 'static') button.style.position = 'relative';
      button.style.overflow = 'hidden';
      button.appendChild(ripple);
      setTimeout(() => ripple.remove(), 620);
    });
  });

  const style = document.createElement('style');
  style.textContent = '@keyframes wfRipple{to{transform:scale(1);opacity:0}}';
  document.head.appendChild(style);

  window.addEventListener('load', () => {
    const loader = document.getElementById('appLoader');
    if (loader) {
      loader.classList.remove('is-active');
      loader.classList.add('hide');
      loader.setAttribute('aria-hidden', 'true');
    }
  });
})();
