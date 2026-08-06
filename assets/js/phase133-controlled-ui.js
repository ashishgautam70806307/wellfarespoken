(function () {
  'use strict';

  function setupReviewMarquee() {
    const rows = document.querySelectorAll('.wf127-review-marquee .wf127-review-row');
    if (!rows.length) return;
    const measure = () => {
      rows.forEach((row, index) => {
        const set = row.querySelector('.wf127-review-set');
        if (!set) return;
        const distance = Math.max(1, Math.ceil(set.getBoundingClientRect().width));
        const speed = window.innerWidth < 680 ? 32 : 43;
        const duration = Math.max(24, distance / speed) + index * 2;
        row.style.setProperty('--wf-review-distance-negative', '-' + distance + 'px');
        row.style.setProperty('--wf-review-duration', duration.toFixed(2) + 's');
      });
    };
    measure();
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(measure).catch(function () {});
    let timer = 0;
    window.addEventListener('resize', function () {
      window.clearTimeout(timer);
      timer = window.setTimeout(measure, 140);
    }, { passive: true });
  }

  function preventDisabledLinks() {
    document.querySelectorAll('a[aria-disabled="true"],a.is-disabled').forEach(function (link) {
      link.addEventListener('click', function (event) { event.preventDefault(); });
    });
  }

  function init() {
    setupReviewMarquee();
    preventDisabledLinks();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
})();
