(() => {
  'use strict';

  const body = document.body;
  const header = document.querySelector('[data-site-header]');
  const drawer = document.querySelector('[data-mobile-drawer]');
  const drawerOpen = document.querySelector('[data-drawer-open]');
  const drawerClosers = document.querySelectorAll('[data-drawer-close]');

  const setDrawer = (open) => {
    if (!drawer || !drawerOpen) return;
    body.classList.toggle('wf127-drawer-open', open);
    body.classList.toggle('nav-locked', open);
    drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
    drawerOpen.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) {
      const first = drawer.querySelector('a,button');
      window.setTimeout(() => first && first.focus(), 100);
    }
  };

  drawerOpen?.addEventListener('click', () => setDrawer(true));
  drawerClosers.forEach((item) => item.addEventListener('click', () => setDrawer(false)));
  drawer?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setDrawer(false)));

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setDrawer(false);
  });

  document.querySelectorAll('[data-drawer-group]').forEach((button) => {
    button.addEventListener('click', () => {
      const group = button.closest('.wf127-drawer-group');
      const children = group?.querySelector('.wf127-drawer-children');
      if (!group || !children) return;
      const willOpen = !group.classList.contains('is-open');
      group.classList.toggle('is-open', willOpen);
      button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      children.hidden = !willOpen;
    });
  });

  document.querySelectorAll('.wf127-nav-group').forEach((group) => {
    const button = group.querySelector('.wf127-nav-trigger');
    const close = () => {
      group.classList.remove('is-open');
      button?.setAttribute('aria-expanded', 'false');
    };
    button?.addEventListener('click', (event) => {
      event.preventDefault();
      const willOpen = !group.classList.contains('is-open');
      document.querySelectorAll('.wf127-nav-group.is-open').forEach((openGroup) => {
        if (openGroup !== group) {
          openGroup.classList.remove('is-open');
          openGroup.querySelector('.wf127-nav-trigger')?.setAttribute('aria-expanded', 'false');
        }
      });
      group.classList.toggle('is-open', willOpen);
      button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });
    group.addEventListener('mouseleave', close);
  });

  document.addEventListener('click', (event) => {
    if (!event.target.closest('.wf127-nav-group')) {
      document.querySelectorAll('.wf127-nav-group.is-open').forEach((group) => {
        group.classList.remove('is-open');
        group.querySelector('.wf127-nav-trigger')?.setAttribute('aria-expanded', 'false');
      });
    }
  });

  const syncHeader = () => header?.classList.toggle('is-scrolled', window.scrollY > 12);
  syncHeader();
  window.addEventListener('scroll', syncHeader, { passive: true });

  const dock = document.querySelector('[data-contact-dock]');
  const dockToggle = document.querySelector('[data-contact-toggle]');
  dockToggle?.addEventListener('click', () => {
    const open = !dock?.classList.contains('is-open');
    dock?.classList.toggle('is-open', open);
    dockToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
  document.querySelector('[data-scroll-top]')?.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    dock?.classList.remove('is-open');
  });
  document.addEventListener('click', (event) => {
    if (dock && !dock.contains(event.target)) {
      dock.classList.remove('is-open');
      dockToggle?.setAttribute('aria-expanded', 'false');
    }
  });

  const revealItems = document.querySelectorAll('[data-reveal], .wf-section-heading, .wf-page-hero-copy');
  if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('wf127-visible');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
    revealItems.forEach((item) => {
      item.classList.add('wf127-reveal');
      observer.observe(item);
    });
  } else {
    revealItems.forEach((item) => item.classList.add('wf127-visible'));
  }
})();
