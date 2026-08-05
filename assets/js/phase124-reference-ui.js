(() => {
  const body = document.body;
  const toggle = document.querySelector('.nav-toggle');
  const close = document.querySelector('.mobile-drawer-close');
  const backdrop = document.querySelector('.mobile-drawer-backdrop');
  const more = document.querySelector('[data-mobile-more]');
  const menu = document.querySelector('.app-main-menu');
  const openDrawer = () => {
    body.classList.add('nav-open');
    menu?.classList.add('open');
    toggle?.setAttribute('aria-expanded', 'true');
    body.style.overflow = 'hidden';
  };
  const closeDrawer = () => {
    body.classList.remove('nav-open');
    menu?.classList.remove('open');
    toggle?.setAttribute('aria-expanded', 'false');
    body.style.overflow = '';
  };
  close?.addEventListener('click', closeDrawer);
  backdrop?.addEventListener('click', closeDrawer);
  more?.addEventListener('click', (event) => { event.preventDefault(); openDrawer(); });
  document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeDrawer(); });

  document.querySelectorAll('.nav-drop-btn').forEach((button) => {
    button.addEventListener('click', (event) => {
      if (window.innerWidth > 1120) return;
      event.preventDefault();
      const parent = button.closest('.nav-dropdown');
      document.querySelectorAll('.nav-dropdown.is-open').forEach((item) => { if (item !== parent) item.classList.remove('is-open'); });
      parent?.classList.toggle('is-open');
      button.setAttribute('aria-expanded', parent?.classList.contains('is-open') ? 'true' : 'false');
    });
  });

  const rail = document.querySelector('[data-action-rail]');
  const railToggle = rail?.querySelector('.wf-action-toggle');
  railToggle?.addEventListener('click', () => {
    const open = rail.classList.toggle('is-open');
    railToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
  rail?.querySelector('.wf-action-item.top')?.addEventListener('click', (event) => {
    event.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
    rail.classList.remove('is-open');
  });
  document.addEventListener('click', (event) => {
    if (rail && !rail.contains(event.target)) rail.classList.remove('is-open');
  });
})();
