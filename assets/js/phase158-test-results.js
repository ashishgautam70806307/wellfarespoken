(function () {
  'use strict';
  function init() {
    document.querySelectorAll('details[data-answer-review]').forEach(function (details) {
      if (details.dataset.reviewBound === '1') return;
      details.dataset.reviewBound = '1';
      details.addEventListener('toggle', function () {
        if (!details.open || details.dataset.reviewLoaded === '1' || details.dataset.reviewLoading === '1') return;
        var body = details.querySelector('[data-answer-review-body]');
        var url = details.getAttribute('data-answer-review');
        if (!body || !url) return;
        details.dataset.reviewLoading = '1';
        body.innerHTML = '<p class="wf158-answer-loading"><span></span> Loading saved questions and answers...</p>';
        fetch(url, { credentials: 'same-origin', cache: 'no-store', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(function (response) {
            if (!response.ok) throw new Error('review-load');
            return response.text();
          })
          .then(function (html) {
            body.innerHTML = html;
            details.dataset.reviewLoaded = '1';
          })
          .catch(function () {
            body.innerHTML = '<p class="wf158-answer-inline-error">Could not load the answer review. Use the full result button instead.</p>';
          })
          .finally(function () { details.dataset.reviewLoading = '0'; });
      });
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
})();
