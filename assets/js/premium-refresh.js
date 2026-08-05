(() => {
  'use strict';
  document.documentElement.classList.add('has-js');

  const header = document.querySelector('.site-header.clean-sticky-header');
  const syncHeader = () => {
    if (header) header.classList.toggle('is-compact', window.scrollY > 24);
  };
  syncHeader();
  window.addEventListener('scroll', syncHeader, { passive: true });

  const navMenu = document.querySelector('.clean-main-menu');
  const navToggle = document.querySelector('.nav-toggle');
  if (navMenu && navToggle) {
    navMenu.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => {
        if (window.innerWidth > 980) return;
        navMenu.classList.remove('open');
        document.body.classList.remove('nav-open');
        navToggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  const staggerTargets = document.querySelectorAll('.grid-3 > *, .grid-4 > *, .batch-grid > *, .gallery-grid > *, .online-class-card-grid > *, .online-flow-grid > *, .about-highlight-grid-v2 > *, .contact-card-grid-v2 > *, .student-stat-grid > *');
  staggerTargets.forEach((item, index) => {
    item.style.setProperty('--reveal-delay', `${Math.min(index % 8, 7) * 55}ms`);
    item.classList.add('premium-reveal');
  });

  const revealTargets = document.querySelectorAll('.premium-reveal');
  if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-revealed');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -5% 0px' });
    revealTargets.forEach((item) => observer.observe(item));
  } else {
    revealTargets.forEach((item) => item.classList.add('is-revealed'));
  }

  document.querySelectorAll('.online-status-pill.is-live, .online-admin-status.is-live').forEach((badge) => {
    badge.setAttribute('aria-label', 'Class is live now');
  });

  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.querySelectorAll('.virtual-badge, .practice-ai-badge, .visual-badge').forEach((card, index) => {
      card.style.animationDelay = `${index * 240}ms`;
      card.classList.add('soft-floating-item');
    });
  }
})();
