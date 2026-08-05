(() => {
  'use strict';

  const body = document.body;
  const header = document.querySelector('[data-site-header]');
  const drawer = document.querySelector('[data-mobile-drawer]');
  const drawerOpen = document.querySelector('[data-drawer-open]');
  const drawerClosers = document.querySelectorAll('[data-drawer-close]');
  let lastFocused = null;

  const setDrawer = (open) => {
    if (!drawer || !drawerOpen) return;
    if (open) lastFocused = document.activeElement;
    body.classList.toggle('wf127-drawer-open', open);
    body.classList.toggle('nav-locked', open);
    drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
    drawerOpen.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) {
      window.setTimeout(() => drawer.querySelector('a,button')?.focus(), 50);
    } else if (lastFocused instanceof HTMLElement) {
      lastFocused.focus({ preventScroll: true });
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
      const open = !group.classList.contains('is-open');
      drawer?.querySelectorAll('.wf127-drawer-group.is-open').forEach((other) => {
        if (other === group) return;
        other.classList.remove('is-open');
        other.querySelector('[data-drawer-group]')?.setAttribute('aria-expanded', 'false');
        const otherChildren = other.querySelector('.wf127-drawer-children');
        if (otherChildren) otherChildren.hidden = true;
      });
      group.classList.toggle('is-open', open);
      button.setAttribute('aria-expanded', open ? 'true' : 'false');
      children.hidden = !open;
    });
  });

  const groups = [...document.querySelectorAll('.wf127-nav-group')];
  const timers = new WeakMap();
  const hover = matchMedia('(hover:hover) and (pointer:fine)').matches;

  const cancelClose = (group) => {
    const timer = timers.get(group);
    if (timer) clearTimeout(timer);
    timers.delete(group);
  };
  const closeGroup = (group, focus = false) => {
    cancelClose(group);
    group.classList.remove('is-open');
    const trigger = group.querySelector('.wf127-nav-trigger');
    trigger?.setAttribute('aria-expanded', 'false');
    if (focus) trigger?.focus({ preventScroll: true });
  };
  const closeOthers = (current) => groups.forEach((group) => group !== current && closeGroup(group));
  const openGroup = (group) => {
    cancelClose(group);
    closeOthers(group);
    group.classList.add('is-open');
    group.querySelector('.wf127-nav-trigger')?.setAttribute('aria-expanded', 'true');
  };
  const delayedClose = (group) => {
    cancelClose(group);
    timers.set(group, setTimeout(() => closeGroup(group), 360));
  };

  groups.forEach((group) => {
    const trigger = group.querySelector('.wf127-nav-trigger');
    const panel = group.querySelector('.wf127-mega-panel');
    trigger?.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      group.classList.contains('is-open') ? closeGroup(group) : openGroup(group);
    });
    trigger?.addEventListener('keydown', (event) => {
      if (['ArrowDown', 'Enter', ' '].includes(event.key)) {
        event.preventDefault();
        openGroup(group);
        panel?.querySelector('a')?.focus();
      } else if (event.key === 'Escape') {
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
      if (!group.contains(event.relatedTarget)) delayedClose(group);
    });
    if (hover) {
      group.addEventListener('mouseenter', () => openGroup(group));
      group.addEventListener('mouseleave', () => delayedClose(group));
      panel?.addEventListener('mouseenter', () => cancelClose(group));
      panel?.addEventListener('mouseleave', () => delayedClose(group));
    }
  });

  document.addEventListener('click', (event) => {
    if (!event.target.closest('.wf127-nav-group')) groups.forEach((group) => closeGroup(group));
  });
  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    groups.forEach((group) => closeGroup(group));
    if (body.classList.contains('wf127-drawer-open')) setDrawer(false);
  });

  const syncHeader = () => header?.classList.toggle('is-scrolled', scrollY > 12);
  syncHeader();
  addEventListener('scroll', syncHeader, { passive: true });

  const dock = document.querySelector('[data-contact-dock]');
  const dockToggle = document.querySelector('[data-contact-toggle]');
  dockToggle?.addEventListener('click', () => {
    const open = !dock?.classList.contains('is-open');
    dock?.classList.toggle('is-open', open);
    dockToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
  document.querySelector('[data-scroll-top]')?.addEventListener('click', () => {
    scrollTo({ top: 0, behavior: 'smooth' });
    dock?.classList.remove('is-open');
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

  const revealItems = document.querySelectorAll('[data-reveal], .wf-section-heading, .wf-page-hero-copy');
  if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('wf127-visible');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.05, rootMargin: '0px 0px -18px 0px' });
    revealItems.forEach((item) => {
      item.classList.add('wf127-reveal');
      observer.observe(item);
    });
  } else {
    revealItems.forEach((item) => item.classList.add('wf127-visible'));
  }
})();
