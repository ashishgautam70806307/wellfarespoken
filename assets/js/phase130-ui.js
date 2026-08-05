(() => {
  'use strict';

  const body = document.body;
  const header = document.querySelector('[data-site-header]');
  const drawer = document.querySelector('[data-mobile-drawer]');
  const drawerOpen = document.querySelector('[data-drawer-open]');
  const drawerClosers = document.querySelectorAll('[data-drawer-close]');
  let lastFocused = null;

  function setDrawer(open) {
    if (!drawer || !drawerOpen) return;
    if (open) lastFocused = document.activeElement;
    body.classList.toggle('wf127-drawer-open', open);
    body.classList.toggle('nav-locked', open);
    drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
    drawerOpen.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) {
      window.setTimeout(() => drawer.querySelector('a,button')?.focus(), 40);
    } else if (lastFocused instanceof HTMLElement) {
      lastFocused.focus({ preventScroll: true });
    }
  }

  drawerOpen?.addEventListener('click', () => setDrawer(true));
  drawerClosers.forEach((item) => item.addEventListener('click', () => setDrawer(false)));
  drawer?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setDrawer(false)));

  document.querySelectorAll('[data-drawer-group]').forEach((button) => {
    button.addEventListener('click', () => {
      const group = button.closest('.wf127-drawer-group');
      const children = group?.querySelector('.wf127-drawer-children');
      if (!group || !children) return;
      const willOpen = !group.classList.contains('is-open');
      drawer?.querySelectorAll('.wf127-drawer-group.is-open').forEach((other) => {
        if (other === group) return;
        other.classList.remove('is-open');
        other.querySelector('[data-drawer-group]')?.setAttribute('aria-expanded', 'false');
        const panel = other.querySelector('.wf127-drawer-children');
        if (panel) panel.hidden = true;
      });
      group.classList.toggle('is-open', willOpen);
      button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      children.hidden = !willOpen;
    });
  });

  const navGroups = [...document.querySelectorAll('.wf127-nav-group')];
  const closeTimers = new WeakMap();
  const supportsHover = window.matchMedia('(hover:hover) and (pointer:fine)').matches;

  function clearCloseTimer(group) {
    const timer = closeTimers.get(group);
    if (timer) window.clearTimeout(timer);
    closeTimers.delete(group);
  }
  function closeNav(group, restoreFocus = false) {
    clearCloseTimer(group);
    group.classList.remove('is-open');
    const trigger = group.querySelector('.wf127-nav-trigger');
    trigger?.setAttribute('aria-expanded', 'false');
    if (restoreFocus) trigger?.focus({ preventScroll: true });
  }
  function closeAll(except = null) {
    navGroups.forEach((group) => { if (group !== except) closeNav(group); });
  }
  function openNav(group) {
    clearCloseTimer(group);
    closeAll(group);
    group.classList.add('is-open');
    group.querySelector('.wf127-nav-trigger')?.setAttribute('aria-expanded', 'true');
  }
  function scheduleClose(group, delay = 520) {
    clearCloseTimer(group);
    closeTimers.set(group, window.setTimeout(() => closeNav(group), delay));
  }

  navGroups.forEach((group) => {
    const trigger = group.querySelector('.wf127-nav-trigger');
    const panel = group.querySelector('.wf127-mega-panel');
    const links = [...(panel?.querySelectorAll('a') || [])];

    trigger?.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      group.classList.contains('is-open') ? closeNav(group) : openNav(group);
    });
    trigger?.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        openNav(group);
        links[0]?.focus();
      } else if (event.key === 'Escape') {
        event.preventDefault();
        closeNav(group, true);
      }
    });
    panel?.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        event.preventDefault();
        closeNav(group, true);
      }
      if (!['ArrowDown', 'ArrowUp'].includes(event.key)) return;
      const index = links.indexOf(document.activeElement);
      if (index < 0) return;
      event.preventDefault();
      const next = event.key === 'ArrowDown'
        ? (index + 1) % links.length
        : (index - 1 + links.length) % links.length;
      links[next]?.focus();
    });
    group.addEventListener('focusin', () => openNav(group));
    group.addEventListener('focusout', (event) => {
      if (!group.contains(event.relatedTarget)) scheduleClose(group, 220);
    });
    if (supportsHover) {
      group.addEventListener('pointerenter', () => openNav(group));
      group.addEventListener('pointerleave', () => scheduleClose(group));
      panel?.addEventListener('pointerenter', () => clearCloseTimer(group));
      panel?.addEventListener('pointerleave', () => scheduleClose(group));
    }
  });

  document.addEventListener('click', (event) => {
    if (!event.target.closest('.wf127-nav-group')) closeAll();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    closeAll();
    if (body.classList.contains('wf127-drawer-open')) setDrawer(false);
  });
  window.addEventListener('resize', () => {
    if (window.innerWidth > 980 && body.classList.contains('wf127-drawer-open')) setDrawer(false);
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
    dockToggle?.setAttribute('aria-expanded', 'false');
  });
  document.addEventListener('click', (event) => {
    if (dock && !dock.contains(event.target)) {
      dock.classList.remove('is-open');
      dockToggle?.setAttribute('aria-expanded', 'false');
    }
  });

  document.querySelectorAll('[data-faq-list]').forEach((list) => {
    list.querySelectorAll('details').forEach((item) => {
      item.addEventListener('toggle', () => {
        const icon = item.querySelector('summary > i');
        if (icon) icon.className = item.open ? 'fa-solid fa-minus' : 'fa-solid fa-plus';
        if (!item.open) return;
        list.querySelectorAll('details[open]').forEach((other) => {
          if (other !== item) other.open = false;
        });
      });
    });
  });

  document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', () => {
      const submit = form.querySelector('button[type="submit"],input[type="submit"]');
      if (!submit || submit.dataset.keepEnabled === 'true') return;
      window.setTimeout(() => {
        submit.disabled = true;
        submit.classList.add('is-loading');
        if (submit.tagName === 'BUTTON' && !submit.dataset.originalLabel) {
          submit.dataset.originalLabel = submit.innerHTML;
          submit.innerHTML = '<span>Processing...</span>';
        }
      }, 20);
    });
  });

  const revealItems = document.querySelectorAll('[data-reveal], .wf-section-heading, .wf-page-hero-copy');
  if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('wf127-visible');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.06, rootMargin: '0px 0px -18px 0px' });
    revealItems.forEach((item) => {
      item.classList.add('wf127-reveal');
      observer.observe(item);
    });
  } else {
    revealItems.forEach((item) => item.classList.add('wf127-visible'));
  }
})();
