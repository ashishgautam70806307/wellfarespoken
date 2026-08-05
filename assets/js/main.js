
// Phase 113: premium custom toast + confirm modal (no browser alert/confirm for project actions)
(() => {
  if (window.AppUI) return;
  const ensureZone = () => {
    let zone = document.querySelector('.admin-toast-zone');
    if (!zone) {
      zone = document.createElement('div');
      zone.className = 'admin-toast-zone';
      zone.setAttribute('aria-live','polite');
      document.body.appendChild(zone);
    }
    return zone;
  };
  const toast = (type, message, title) => {
    const zone = ensureZone();
    const item = document.createElement('div');
    const kind = ['success','error','warning','info'].includes(type) ? type : 'info';
    item.className = 'app-toast toast-' + kind;
    item.setAttribute('data-toast','1');
    const icon = kind === 'success' ? '✓' : (kind === 'error' ? '!' : (kind === 'warning' ? '!' : 'i'));
    item.innerHTML = `<span class="toast-icon">${icon}</span><div><b>${title || (kind.charAt(0).toUpperCase()+kind.slice(1))}</b><p>${String(message||'Done')}</p></div><button type="button" class="toast-close" aria-label="Close">×</button>`;
    zone.appendChild(item);
    const close = () => { item.style.opacity='0'; item.style.transform='translateY(-8px) translateX(14px)'; setTimeout(()=>item.remove(),220); };
    item.querySelector('.toast-close').addEventListener('click', close);
    setTimeout(close, 6500);
  };
  const confirmBox = (options) => new Promise((resolve) => {
    const opts = typeof options === 'string' ? {message: options} : (options || {});
    const overlay = document.createElement('div');
    overlay.className = 'wf-modal-overlay';
    overlay.innerHTML = `<div class="wf-modal-card" role="dialog" aria-modal="true"><span class="wf-modal-icon">!</span><h3>${opts.title || 'Please confirm'}</h3><p>${opts.message || 'Are you sure?'}</p><div class="wf-modal-actions"><button type="button" class="btn btn-soft" data-modal-cancel>${opts.cancelText || 'Cancel'}</button><button type="button" class="btn btn-primary" data-modal-ok>${opts.okText || 'Yes, continue'}</button></div></div>`;
    document.body.appendChild(overlay);
    const done = (val) => { overlay.classList.add('closing'); setTimeout(()=>overlay.remove(),160); resolve(val); };
    overlay.querySelector('[data-modal-cancel]').addEventListener('click', () => done(false));
    overlay.querySelector('[data-modal-ok]').addEventListener('click', () => done(true));
    overlay.addEventListener('click', (e) => { if (e.target === overlay) done(false); });
    document.addEventListener('keydown', function esc(e){ if(e.key==='Escape'){ document.removeEventListener('keydown', esc); done(false); } }, {once:true});
    setTimeout(()=>overlay.querySelector('[data-modal-ok]').focus(), 60);
  });
  window.AppUI = { toast, confirm: confirmBox };
  document.querySelectorAll('[data-auto-toast], .alert-success, .alert-danger, .alert-error').forEach((el) => {
    const text = (el.textContent || '').trim();
    if (!text) return;
    const type = el.dataset.autoToast || (el.classList.contains('alert-success') ? 'success' : 'error');
    toast(type, text);
  });
  const extractConfirm = (attr, fallback) => {
    if (!attr) return fallback || '';
    const m = attr.match(/confirm\((['\"])(.*?)\1\)/);
    return m ? m[2] : (fallback || 'Are you sure?');
  };
  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!form || !form.matches('form')) return;
    if (form.dataset.confirmed === '1') { delete form.dataset.confirmed; return; }
    const attr = form.getAttribute('onsubmit') || '';
    const msg = form.dataset.confirm || (attr.includes('confirm(') ? extractConfirm(attr) : '');
    if (!msg) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    confirmBox({title:'Confirm action', message:msg, okText:'Continue', cancelText:'Cancel'}).then(ok => {
      if (!ok) return;
      form.dataset.confirmed = '1';
      const old = form.getAttribute('onsubmit');
      if (old) form.removeAttribute('onsubmit');
      if (typeof form.requestSubmit === 'function') form.requestSubmit();
      else form.dispatchEvent(new Event('submit', {bubbles:true, cancelable:true}));
      if (old) setTimeout(()=>form.setAttribute('onsubmit', old), 400);
    });
  }, true);
  document.addEventListener('click', (event) => {
    const link = event.target.closest('a[onclick]');
    if (!link) return;
    if (link.dataset.confirmed === '1') { delete link.dataset.confirmed; return; }
    const attr = link.getAttribute('onclick') || '';
    if (!attr.includes('confirm(')) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    confirmBox({title:'Confirm action', message:extractConfirm(attr), okText:'Continue', cancelText:'Cancel'}).then(ok => { if(ok){ link.dataset.confirmed='1'; location.href = link.href; }});
  }, true);
})();

// Phase 5 admin polish: reusable confirmation and image preview.
document.querySelectorAll('form[data-confirm]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    if (window.AppUI) return; // Phase 113 uses the premium async modal in capture phase.
    event.preventDefault();
  });
});

document.querySelectorAll('input[type="file"][data-preview]').forEach((input) => {
  const target = document.querySelector(input.getAttribute('data-preview'));
  if (!target) return;
  input.addEventListener('change', () => {
    const file = input.files && input.files[0];
    if (!file || !file.type.startsWith('image/')) {
      target.innerHTML = '<span>No preview selected</span>';
      return;
    }
    const reader = new FileReader();
    reader.onload = () => { target.innerHTML = `<img src="${reader.result}" alt="Selected image preview">`; };
    reader.readAsDataURL(file);
  });
});


// Phase 15: app-like micro interactions
(() => {
  const path = (location.pathname.split('/').pop() || 'index.php').toLowerCase();
  document.querySelectorAll('.mobile-bottom-nav a').forEach((a) => {
    const href = (a.getAttribute('href') || '').split('?')[0].toLowerCase();
    if (href === path) a.classList.add('active');
  });
  const items = document.querySelectorAll('.card,.course-card,.translation-card,.practice-single-card,.section-title,.hero-card');
  if ('IntersectionObserver' in window) {
    const obs = new IntersectionObserver((entries) => {
      entries.forEach((entry) => { if (entry.isIntersecting) { entry.target.classList.add('is-visible'); obs.unobserve(entry.target); } });
    }, {threshold: .08});
    items.forEach((el) => { el.classList.add('reveal-item'); obs.observe(el); });
  }
})();

// Phase 17: premium admin interactions, toast and table pagination.
(() => {
  const openBtn = document.querySelector('[data-admin-menu-open]');
  const closeBtn = document.querySelector('[data-admin-menu-close]');
  const side = document.querySelector('#adminSide');
  if (openBtn && side) openBtn.addEventListener('click', () => side.classList.add('open'));
  if (closeBtn && side) closeBtn.addEventListener('click', () => side.classList.remove('open'));
  document.addEventListener('click', (event) => {
    if (!side || !side.classList.contains('open')) return;
    if (side.contains(event.target) || (openBtn && openBtn.contains(event.target))) return;
    side.classList.remove('open');
  });

  document.querySelectorAll('[data-toast]').forEach((toast) => {
    const close = toast.querySelector('.toast-close');
    const remove = () => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(-8px) translateX(14px)';
      setTimeout(() => toast.remove(), 220);
    };
    if (close) close.addEventListener('click', remove);
    setTimeout(remove, 7000);
  });

  document.querySelectorAll('.table-wrap table, .admin-table').forEach((table) => {
    if (table.dataset.enhanced === '1') return;
    table.dataset.enhanced = '1';
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
    table.querySelectorAll('tbody tr').forEach((tr) => {
      Array.from(tr.children).forEach((td, idx) => {
        if (!td.hasAttribute('data-label') && headers[idx]) td.setAttribute('data-label', headers[idx]);
      });
    });
    const tbody = table.tBodies[0];
    if (!tbody) return;
    const rows = Array.from(tbody.rows).filter(row => !row.querySelector('.empty-state'));
    const pageSize = parseInt(table.dataset.pageSize || '10', 10);
    if (rows.length <= pageSize) return;
    let page = 1;
    const wrap = table.closest('.table-wrap') || table.parentElement;
    const pager = document.createElement('div');
    pager.className = 'admin-pagination';
    wrap.insertAdjacentElement('afterend', pager);
    const totalPages = Math.ceil(rows.length / pageSize);
    const render = () => {
      rows.forEach((row, idx) => {
        row.style.display = (idx >= (page - 1) * pageSize && idx < page * pageSize) ? '' : 'none';
      });
      pager.innerHTML = '';
      const info = document.createElement('span');
      info.className = 'admin-table-info';
      const start = (page - 1) * pageSize + 1;
      const end = Math.min(page * pageSize, rows.length);
      info.textContent = `Showing ${start}-${end} of ${rows.length}`;
      pager.appendChild(info);
      const makeBtn = (text, disabled, onClick, active = false) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = text;
        btn.disabled = disabled;
        if (active) btn.classList.add('active');
        btn.addEventListener('click', onClick);
        pager.appendChild(btn);
      };
      makeBtn('‹', page === 1, () => { page--; render(); });
      const from = Math.max(1, page - 2);
      const to = Math.min(totalPages, page + 2);
      for (let i = from; i <= to; i++) makeBtn(String(i), false, () => { page = i; render(); }, i === page);
      makeBtn('›', page === totalPages, () => { page++; render(); });
    };
    render();
  });
})();

// Phase 18: compact admin menu search and icon actions.
(() => {
  const input = document.querySelector('#adminMenuSearch');
  const wrap = input ? input.closest('.admin-search-wrap') : null;
  const results = document.querySelector('#adminMenuResults');
  if (input && wrap && results) {
    const items = Array.from(results.querySelectorAll('[data-menu-search-item]'));
    const empty = results.querySelector('[data-menu-search-empty]');
    const open = () => wrap.classList.add('is-open');
    const close = () => wrap.classList.remove('is-open');
    const filter = () => {
      const q = input.value.trim().toLowerCase();
      let shown = 0;
      items.forEach((item) => {
        const text = item.getAttribute('data-search-text') || item.textContent.toLowerCase();
        const ok = q === '' || text.includes(q);
        item.style.display = ok ? 'grid' : 'none';
        if (ok) shown++;
      });
      results.classList.toggle('no-results', shown === 0);
      if (empty) empty.style.display = shown === 0 ? 'block' : 'none';
      open();
    };
    input.addEventListener('focus', filter);
    input.addEventListener('input', filter);
    input.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') { close(); input.blur(); }
      if (event.key === 'Enter') {
        const first = items.find((item) => item.style.display !== 'none');
        if (first) { event.preventDefault(); location.href = first.getAttribute('href'); }
      }
    });
    document.addEventListener('click', (event) => {
      if (!wrap.contains(event.target)) close();
    });
  }

  const iconMap = {
    'edit': '✎', 'delete': '🗑', 'view': '👁', 'update': '↻', 'whatsapp': '☘', 'call': '☎',
    'publish': '✓', 'unpublish': '○', 'save': '✓', 'export csv': '⇩', 'open': '↗', 'details':'↗', 'tests':'🧪', 'approve':'✓', 'not approve':'○', 'filter':'⌕', 'reset':'↺', 'apply':'✓', 'change password':'🔐'
  };
  document.querySelectorAll('.table-actions .btn, .admin-table .btn-sm, .table-wrap .btn-sm').forEach((btn) => {
    if (btn.dataset.iconified === '1') return;
    const label = btn.textContent.trim();
    const key = label.toLowerCase();
    const icon = iconMap[key];
    if (!icon) return;
    btn.dataset.iconified = '1';
    btn.setAttribute('title', label);
    btn.setAttribute('aria-label', label);
    btn.innerHTML = `<span aria-hidden="true">${icon}</span>`;
    btn.classList.add('admin-icon-action');
  });
})();

// Phase 27: premium responsive dropdown navigation.
(() => {
  const navToggle = document.querySelector('.nav-toggle');
  const navMenu = document.querySelector('.premium-main-menu') || document.querySelector('.app-main-menu');
  const closeAllDropdowns = () => {
    document.querySelectorAll('.nav-dropdown.is-open').forEach((item) => {
      item.classList.remove('is-open');
      const btn = item.querySelector('.nav-drop-btn');
      if (btn) btn.setAttribute('aria-expanded', 'false');
    });
  };
  const closeMenu = () => {
    if (!navMenu || !navToggle) return;
    navMenu.classList.remove('open');
    document.body.classList.remove('nav-open');
    navToggle.setAttribute('aria-expanded', 'false');
    closeAllDropdowns();
  };
  if (navToggle && navMenu) {
    navToggle.addEventListener('click', () => {
      const willOpen = !navMenu.classList.contains('open');
      navMenu.classList.toggle('open', willOpen);
      document.body.classList.toggle('nav-open', willOpen);
      navToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      if (!willOpen) closeAllDropdowns();
    });
  }
  document.querySelectorAll('.nav-drop-btn').forEach((btn) => {
    btn.addEventListener('click', (event) => {
      const parent = btn.closest('.nav-dropdown');
      if (!parent) return;
      if (window.innerWidth > 980) return;
      event.preventDefault();
      const willOpen = !parent.classList.contains('is-open');
      closeAllDropdowns();
      parent.classList.toggle('is-open', willOpen);
      btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });
  });
  document.addEventListener('click', (event) => {
    if (!navMenu || !navToggle) return;
    const insideMenu = navMenu.contains(event.target);
    const insideToggle = navToggle.contains(event.target);
    if (!insideMenu && !insideToggle) closeMenu();
  });
  window.addEventListener('resize', () => {
    if (window.innerWidth > 980) closeMenu();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeMenu();
  });
})();

// Phase 61: Live-server friendly PWA install button.
(() => {
  let deferredInstallPrompt = null;
  const buttons = Array.from(document.querySelectorAll('[data-install-webapp]'));
  const helpItems = Array.from(document.querySelectorAll('[data-install-help]'));
  if (!buttons.length) return;

  const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  const isSecure = () => window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
  const setHelp = (text) => helpItems.forEach((item) => { item.textContent = text; });
  const setButton = (state) => {
    buttons.forEach((btn) => {
      btn.classList.remove('is-ready', 'is-installed', 'is-waiting');
      if (state === 'ready') {
        btn.classList.add('is-ready');
        btn.innerHTML = '<i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i> Install Web App';
      } else if (state === 'installed') {
        btn.classList.add('is-installed');
        btn.innerHTML = '<i class="fa-solid fa-circle-check" aria-hidden="true"></i> App Installed';
      } else {
        btn.classList.add('is-waiting');
        btn.innerHTML = '<i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i> Install Web App';
      }
    });
  };

  window.__pwaStatus = {
    secure: isSecure(),
    standalone: isStandalone(),
    manifest: document.querySelector('link[rel="manifest"]')?.href || '',
    serviceWorker: 'serviceWorker' in navigator,
    beforeInstallPromptReady: false
  };

  if (isStandalone()) {
    setButton('installed');
    setHelp('This website is already running as an installed web app.');
    return;
  }

  if (!isSecure()) {
    setButton('waiting');
    setHelp('Live server par PWA install ke liye HTTPS required hai. SSL enable karo, then reload.');
  } else {
    setButton('waiting');
    setHelp('Install prompt ready hone ke liye Chrome/Edge manifest + service worker validate karta hai. Page reload ke baad prompt ready hoga.');
  }

  window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;
    window.__pwaStatus.beforeInstallPromptReady = true;
    setButton('ready');
    setHelp('Ready to install. Click “Install Web App”.');
  });

  buttons.forEach((btn) => {
    btn.addEventListener('click', async () => {
      if (isStandalone()) {
        setButton('installed');
        setHelp('This website is already installed.');
        return;
      }
      if (deferredInstallPrompt) {
        deferredInstallPrompt.prompt();
        const choice = await deferredInstallPrompt.userChoice.catch(() => null);
        if (choice && choice.outcome === 'accepted') {
          setButton('installed');
          setHelp('Web app installation started successfully.');
        } else {
          setButton('waiting');
          setHelp('Install cancel hua. Chrome/Edge prompt dobara ready hone par install kar sakte hain.');
        }
        deferredInstallPrompt = null;
        return;
      }

      const msg = isSecure()
        ? 'Install prompt abhi ready nahi hai. Live server par check karo: HTTPS active ho, manifest.webmanifest browser me open ho, sw.js 200 OK ho, aur old service worker/cache clear ho. Chrome menu (⋮) me Install app option bhi check karo.'
        : 'PWA install ke liye HTTPS required hai. SSL enable karo.';
      setHelp(msg);
      if(window.AppUI){window.AppUI.toast('info', msg, 'Install app');}
    });
  });

  window.addEventListener('appinstalled', () => {
    deferredInstallPrompt = null;
    setButton('installed');
    setHelp('Web app installed successfully.');
  });
})();


// Phase 46: lightweight global loader for frontend + admin page changes/forms.
(() => {
  const loader = document.getElementById('appLoader');
  if (!loader) return;
  let hideTimer = null;
  const show = () => {
    clearTimeout(hideTimer);
    loader.classList.add('is-active');
    loader.setAttribute('aria-hidden', 'false');
  };
  const hide = () => {
    hideTimer = setTimeout(() => {
      loader.classList.remove('is-active');
      loader.setAttribute('aria-hidden', 'true');
    }, 120);
  };
  window.appLoader = { show, hide };
  window.addEventListener('load', hide);
  window.addEventListener('pageshow', hide);
  window.addEventListener('beforeunload', show);
  document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link) return;
    const href = link.getAttribute('href') || '';
    if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('tel:') || href.startsWith('mailto:') || link.target === '_blank' || link.hasAttribute('download')) return;
    show();
  }, true);
  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (form && form.matches('form') && !form.hasAttribute('data-no-loader') && !form.hasAttribute('data-ajax-form')) show();
  }, true);
  document.addEventListener('ajaxStart', show);
  document.addEventListener('ajaxEnd', hide);
})();

// Phase 46: show loader during normal fetch/XHR requests too; page-specific AJAX can opt out with window.skipGlobalAjaxLoader.
(() => {
  if (!window.appLoader) return;
  let ajaxCount = 0;
  const start = () => { ajaxCount += 1; window.appLoader.show(); };
  const done = () => { ajaxCount = Math.max(0, ajaxCount - 1); if (ajaxCount === 0) window.appLoader.hide(); };
  if (window.fetch) {
    const nativeFetch = window.fetch.bind(window);
    window.fetch = (...args) => {
      if (!window.skipGlobalAjaxLoader) start();
      return nativeFetch(...args).finally(() => { if (!window.skipGlobalAjaxLoader) done(); });
    };
  }
  if (window.XMLHttpRequest) {
    const NativeXHR = window.XMLHttpRequest;
    window.XMLHttpRequest = function() {
      const xhr = new NativeXHR();
      const send = xhr.send;
      xhr.send = function(...args) {
        if (!window.skipGlobalAjaxLoader) start();
        xhr.addEventListener('loadend', () => { if (!window.skipGlobalAjaxLoader) done(); }, { once: true });
        return send.apply(xhr, args);
      };
      return xhr;
    };
  }
})();

// Phase 51: keep active mobile bottom nav item visible after page load.
(() => {
  const bar = document.querySelector('.mobile-bottom-nav.mobile-scroll-nav');
  if (!bar) return;
  const active = bar.querySelector('a.active') || bar.querySelector(`a[href="${(location.pathname.split('/').pop() || 'index.php')}"]`);
  if (active && typeof active.scrollIntoView === 'function') {
    setTimeout(() => active.scrollIntoView({inline: 'center', block: 'nearest', behavior: 'smooth'}), 160);
  }
})();


// Phase 62: live image upload validation for allowed public images.
(() => {
  const allowed = ['png','jpg','jpeg','gif'];
  document.querySelectorAll('input[type="file"][accept*=".png"], input[type="file"][accept*="image/png"]').forEach((input) => {
    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      if (!file) return;
      const ext = (file.name.split('.').pop() || '').toLowerCase();
      if (!allowed.includes(ext)) {
        if(window.AppUI){window.AppUI.toast('error','Only PNG, JPG, JPEG and GIF images are allowed.');}
        input.value = '';
      }
    });
  });
})();

// Phase 120: compact pagination for admin card grids (students/admissions)
(() => {
  const paginateGrid = (grid, pageSize = 12) => {
    if (!grid || grid.dataset.cardPaged === '1') return;
    const cards = Array.from(grid.children).filter(el => !el.classList.contains('empty-state'));
    if (cards.length <= pageSize) return;
    grid.dataset.cardPaged = '1';
    let page = 1;
    const total = Math.ceil(cards.length / pageSize);
    const pager = document.createElement('div');
    pager.className = 'admin-pagination card-grid-pagination';
    grid.insertAdjacentElement('afterend', pager);
    const render = () => {
      cards.forEach((card, idx) => { card.style.display = (idx >= (page-1)*pageSize && idx < page*pageSize) ? '' : 'none'; });
      pager.innerHTML = '';
      const info = document.createElement('span');
      info.className = 'admin-table-info';
      info.textContent = `Showing ${(page-1)*pageSize+1}-${Math.min(page*pageSize,cards.length)} of ${cards.length}`;
      pager.appendChild(info);
      const btn = (label, disabled, fn, active=false) => {
        const b = document.createElement('button'); b.type='button'; b.textContent=label; b.disabled=disabled; if(active) b.classList.add('active'); b.addEventListener('click', fn); pager.appendChild(b);
      };
      btn('‹', page===1, () => { page--; render(); });
      const from = Math.max(1, page-2), to = Math.min(total, page+2);
      for(let i=from;i<=to;i++) btn(String(i), false, () => { page=i; render(); }, i===page);
      btn('›', page===total, () => { page++; render(); });
    };
    render();
  };
  document.querySelectorAll('.student-card-grid,.admission-card-grid').forEach(grid => paginateGrid(grid, 12));
})();
