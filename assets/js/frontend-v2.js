(() => {
  const body = document.body;
  const drawer = document.querySelector('[data-wf-drawer]');
  const overlay = document.querySelector('[data-wf-overlay]');
  const openButton = document.querySelector('[data-wf-menu-open]');
  const closeButton = document.querySelector('[data-wf-menu-close]');

  const setDrawer = (open) => {
    if (!drawer || !overlay || !openButton) return;
    drawer.classList.toggle('is-open', open);
    drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
    openButton.setAttribute('aria-expanded', open ? 'true' : 'false');
    body.classList.toggle('wf-menu-open', open);
    overlay.hidden = !open;
    if (open && closeButton) setTimeout(() => closeButton.focus(), 80);
  };

  openButton?.addEventListener('click', () => setDrawer(true));
  closeButton?.addEventListener('click', () => setDrawer(false));
  overlay?.addEventListener('click', () => setDrawer(false));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setDrawer(false);
  });

  document.querySelectorAll('[data-wf-accordion]').forEach((button) => {
    button.addEventListener('click', () => {
      const submenu = button.nextElementSibling;
      if (!submenu) return;
      const willOpen = !button.classList.contains('is-open');
      document.querySelectorAll('[data-wf-accordion].is-open').forEach((other) => {
        if (other === button) return;
        other.classList.remove('is-open');
        other.nextElementSibling?.classList.remove('is-open');
      });
      button.classList.toggle('is-open', willOpen);
      submenu.classList.toggle('is-open', willOpen);
    });
  });

  document.querySelectorAll('.wf-mobile-drawer a').forEach((link) => {
    link.addEventListener('click', () => setDrawer(false));
  });

  const revealItems = document.querySelectorAll('[data-reveal]');
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -20px' });
    revealItems.forEach((item) => observer.observe(item));
  } else {
    revealItems.forEach((item) => item.classList.add('is-visible'));
  }

  document.querySelectorAll('.wf-faq-list details, .faq-list details').forEach((detail) => {
    detail.addEventListener('toggle', () => {
      if (!detail.open) return;
      const parent = detail.parentElement;
      parent?.querySelectorAll('details[open]').forEach((other) => {
        if (other !== detail) other.open = false;
      });
    });
  });

  const header = document.querySelector('[data-wf-header]');
  if (header) {
    const updateHeader = () => header.classList.toggle('is-scrolled', window.scrollY > 18);
    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });
  }
})();
