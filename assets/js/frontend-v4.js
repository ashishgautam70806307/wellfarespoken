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

// Phase 124: application-like polish without heavy animation libraries.
(() => {
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Page scroll progress.
  const progress = document.querySelector('[data-wf-scroll-progress]');
  if (progress) {
    const updateProgress = () => {
      const max = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
      const value = Math.min(100, Math.max(0, (window.scrollY / max) * 100));
      progress.style.width = `${value}%`;
    };
    updateProgress();
    window.addEventListener('scroll', updateProgress, { passive: true });
    window.addEventListener('resize', updateProgress, { passive: true });
  }

  // Stagger sections and cards so content flows rather than appearing together.
  document.querySelectorAll('.wf-course-grid,.wf-feature-grid,.wf-journey-grid,.wf-tool-grid,.wf-faculty-grid,.wf-review-grid,.grid-3,.grid-4').forEach((group) => {
    Array.from(group.children).forEach((item, index) => {
      if (!item.hasAttribute('data-reveal')) item.setAttribute('data-reveal', '');
      item.style.setProperty('--reveal-delay', `${Math.min(index * 75, 375)}ms`);
    });
  });

  // The first observer was initialized earlier. Observe late-tagged items too.
  const pending = document.querySelectorAll('[data-reveal]:not(.is-visible)');
  if ('IntersectionObserver' in window && !reduceMotion) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.07, rootMargin: '0px 0px -18px' });
    pending.forEach((item) => observer.observe(item));
  } else {
    pending.forEach((item) => item.classList.add('is-visible'));
  }

  // Subtle cursor light on premium cards. Disabled on touch devices.
  if (window.matchMedia('(hover: hover) and (pointer: fine)').matches && !reduceMotion) {
    const selectors = [
      '.wf-course-card', '.wf-feature-card', '.wf-tool-card', '.card',
      '.about-mini-card-v2', '.contact-mini-card-v2'
    ].join(',');
    document.querySelectorAll(selectors).forEach((card) => {
      card.addEventListener('pointermove', (event) => {
        const rect = card.getBoundingClientRect();
        card.style.setProperty('--mouse-x', `${event.clientX - rect.left}px`);
        card.style.setProperty('--mouse-y', `${event.clientY - rect.top}px`);
      });
    });
  }

  // Material-style click feedback on CTAs.
  document.querySelectorAll('.wf-btn,.btn,.footer-btn').forEach((button) => {
    button.addEventListener('click', (event) => {
      if (reduceMotion) return;
      const rect = button.getBoundingClientRect();
      const ripple = document.createElement('span');
      const size = Math.max(rect.width, rect.height);
      ripple.className = 'wf-ripple';
      ripple.style.width = ripple.style.height = `${size}px`;
      ripple.style.left = `${event.clientX - rect.left - size / 2}px`;
      ripple.style.top = `${event.clientY - rect.top - size / 2}px`;
      button.appendChild(ripple);
      window.setTimeout(() => ripple.remove(), 680);
    });
  });

  // Very light hero parallax. It only moves decorative floating cards.
  const hero = document.querySelector('.wf-home-hero');
  if (hero && window.matchMedia('(min-width: 1181px) and (hover: hover)').matches && !reduceMotion) {
    const floaters = hero.querySelectorAll('.wf-hero-float-card');
    hero.addEventListener('pointermove', (event) => {
      const rect = hero.getBoundingClientRect();
      const x = ((event.clientX - rect.left) / rect.width - 0.5) * 10;
      const y = ((event.clientY - rect.top) / rect.height - 0.5) * 8;
      floaters.forEach((item, index) => {
        const factor = index === 0 ? 1 : -0.75;
        item.style.translate = `${x * factor}px ${y * factor}px`;
      });
    });
    hero.addEventListener('pointerleave', () => {
      floaters.forEach((item) => { item.style.translate = ''; });
    });
  }
})();
