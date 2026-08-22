(() => {
  'use strict';
  const mobile = () => window.matchMedia('(max-width: 760px)').matches;

  function setupSpokenFilters() {
    if (!document.body.classList.contains('page-spoken-materials')) return;
    const cards = [...document.querySelectorAll('.wf143-mode-card')];
    const filters = [...document.querySelectorAll('[data-wf176-mode-filter]')];
    const search = document.getElementById('wf175ModeSearch');
    let active = 'all';

    const apply = () => {
      const query = String(search?.value || '').trim().toLowerCase();
      cards.forEach((card) => {
        const matchesType = active === 'all' || String(card.dataset.goal || '') === active;
        const matchesSearch = query === '' || card.textContent.toLowerCase().includes(query);
        card.hidden = !(matchesType && matchesSearch);
      });
    };
    filters.forEach((button) => button.addEventListener('click', () => {
      if (!mobile()) return;
      active = String(button.dataset.wf176ModeFilter || 'all');
      filters.forEach((item) => item.classList.toggle('is-active', item === button));
      apply();
    }));
    search?.addEventListener('input', apply);
  }

  function setupPracticeSearch() {
    if (!document.body.classList.contains('page-free-ai-english-practice')) return;
    const input = document.getElementById('wf176LessonSearch');
    const clear = document.getElementById('wf176LessonSearchClear');
    const select = document.getElementById('wf175LessonSelect');
    if (!input || !select) return;
    const original = [...select.options].map((option) => ({ value: option.value, text: option.textContent, selected: option.selected }));
    const rebuild = () => {
      const query = input.value.trim().toLowerCase();
      const selectedValue = select.value;
      select.innerHTML = '';
      original.forEach((item, index) => {
        if (index !== 0 && query && !item.text.toLowerCase().includes(query)) return;
        const option = new Option(item.text, item.value, false, item.value === selectedValue);
        select.add(option);
      });
      if (select.options.length === 1 && query) {
        const option = new Option('No matching lesson', '', false, false);
        option.disabled = true;
        select.add(option);
      }
    };
    input.addEventListener('input', rebuild);
    clear?.addEventListener('click', () => {
      input.value = '';
      rebuild();
      select.focus({ preventScroll: true });
    });
  }

  function makeTapImmediate() {
    document.addEventListener('pointerdown', (event) => {
      if (!mobile()) return;
      const target = event.target.closest('button:not(:disabled),a[href]');
      if (!target) return;
      target.style.setProperty('transition-duration', '0s', 'important');
      target.classList.add('wf176-tap-now');
    }, { passive: true });
    const clear = (event) => {
      const target = event.target.closest?.('.wf176-tap-now');
      if (!target) return;
      target.classList.remove('wf176-tap-now');
      window.setTimeout(() => target.style.removeProperty('transition-duration'), 0);
    };
    document.addEventListener('pointerup', clear, { passive: true });
    document.addEventListener('pointercancel', clear, { passive: true });
  }

  document.addEventListener('DOMContentLoaded', () => {
    setupSpokenFilters();
    setupPracticeSearch();
    makeTapImmediate();
  }, { once: true });
})();
