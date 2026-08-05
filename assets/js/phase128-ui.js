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
      window.setTimeout(() => first?.focus(), 80);
    } else {
      drawerOpen.focus({ preventScroll: true });
    }
  };

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
        const otherButton = other.querySelector('[data-drawer-group]');
        const otherChildren = other.querySelector('.wf127-drawer-children');
        otherButton?.setAttribute('aria-expanded', 'false');
        if (otherChildren) otherChildren.hidden = true;
      });

      group.classList.toggle('is-open', willOpen);
      button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      children.hidden = !willOpen;
    });
  });

  const desktopGroups = [...document.querySelectorAll('.wf127-nav-group')];
  const closeTimers = new WeakMap();
  const hoverSupported = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

  const clearCloseTimer = (group) => {
    const timer = closeTimers.get(group);
    if (timer) window.clearTimeout(timer);
    closeTimers.delete(group);
  };

  const closeGroup = (group, restoreFocus = false) => {
    clearCloseTimer(group);
    group.classList.remove('is-open');
    const trigger = group.querySelector('.wf127-nav-trigger');
    trigger?.setAttribute('aria-expanded', 'false');
    if (restoreFocus) trigger?.focus({ preventScroll: true });
  };

  const closeOthers = (current) => {
    desktopGroups.forEach((group) => {
      if (group !== current) closeGroup(group);
    });
  };

  const openGroup = (group) => {
    clearCloseTimer(group);
    closeOthers(group);
    group.classList.add('is-open');
    group.querySelector('.wf127-nav-trigger')?.setAttribute('aria-expanded', 'true');
  };

  const scheduleClose = (group) => {
    clearCloseTimer(group);
    closeTimers.set(group, window.setTimeout(() => closeGroup(group), 240));
  };

  desktopGroups.forEach((group) => {
    const button = group.querySelector('.wf127-nav-trigger');
    const panel = group.querySelector('.wf127-mega-panel');

    button?.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      if (group.classList.contains('is-open')) closeGroup(group);
      else openGroup(group);
    });

    button?.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowDown') {
        event.preventDefault();
        openGroup(group);
        panel?.querySelector('a')?.focus();
      }
      if (event.key === 'Escape') {
        event.preventDefault();
        closeGroup(group, true);
      }
    });

    panel?.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        event.preventDefault();
        closeGroup(group, true);
      }
    });

    group.addEventListener('focusin', () => openGroup(group));
    group.addEventListener('focusout', (event) => {
      if (!group.contains(event.relatedTarget)) scheduleClose(group);
    });

    if (hoverSupported) {
      group.addEventListener('pointerenter', () => openGroup(group));
      group.addEventListener('pointerleave', () => scheduleClose(group));
      panel?.addEventListener('pointerenter', () => clearCloseTimer(group));
    }
  });

  document.addEventListener('click', (event) => {
    if (!event.target.closest('.wf127-nav-group')) desktopGroups.forEach((group) => closeGroup(group));
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    desktopGroups.forEach((group) => closeGroup(group));
    if (body.classList.contains('wf127-drawer-open')) setDrawer(false);
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
    }, { threshold: 0.06, rootMargin: '0px 0px -18px 0px' });
    revealItems.forEach((item) => {
      item.classList.add('wf127-reveal');
      observer.observe(item);
    });
  } else {
    revealItems.forEach((item) => item.classList.add('wf127-visible'));
  }
})();
