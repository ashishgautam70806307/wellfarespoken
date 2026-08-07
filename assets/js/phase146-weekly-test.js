(() => {
  'use strict';

  const onReady = (callback) => {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', callback, { once: true });
      return;
    }
    callback();
  };

  onReady(() => {
    const carousel = document.querySelector('[data-test-carousel]');
    const track = carousel?.querySelector('[data-test-track]');
    const cards = track ? Array.from(track.querySelectorAll('[data-test-card]')) : [];
    const previousButton = carousel?.querySelector('[data-test-prev]');
    const nextButton = carousel?.querySelector('[data-test-next]');
    const dots = carousel ? Array.from(carousel.querySelectorAll('.wf145-test-dots span')) : [];
    const compactQuery = window.matchMedia('(max-width: 720px)');

    let currentIndex = 0;
    let scrollFrame = 0;

    const cardLeft = (index) => {
      if (!track || !cards[index]) return 0;
      return Math.max(0, cards[index].offsetLeft - track.offsetLeft);
    };

    const closestIndex = () => {
      if (!track || cards.length === 0 || !compactQuery.matches) return 0;
      const center = track.scrollLeft + (track.clientWidth / 2);
      let bestIndex = 0;
      let bestDistance = Number.POSITIVE_INFINITY;
      cards.forEach((card, index) => {
        const cardCenter = (card.offsetLeft - track.offsetLeft) + (card.offsetWidth / 2);
        const distance = Math.abs(cardCenter - center);
        if (distance < bestDistance) {
          bestDistance = distance;
          bestIndex = index;
        }
      });
      return bestIndex;
    };

    const updateCarousel = () => {
      currentIndex = compactQuery.matches ? closestIndex() : 0;
      dots.forEach((dot, index) => {
        const active = index === currentIndex;
        dot.classList.toggle('is-active', active);
        dot.setAttribute('aria-current', active ? 'true' : 'false');
      });
      if (previousButton) previousButton.disabled = !compactQuery.matches || currentIndex <= 0;
      if (nextButton) nextButton.disabled = !compactQuery.matches || currentIndex >= cards.length - 1;
    };

    const goToCard = (index, behavior = 'smooth') => {
      if (!track || cards.length === 0) return;
      const safeIndex = Math.max(0, Math.min(cards.length - 1, index));
      currentIndex = safeIndex;
      track.scrollTo({ left: cardLeft(safeIndex), behavior });
      updateCarousel();
    };

    previousButton?.addEventListener('click', () => goToCard(currentIndex - 1));
    nextButton?.addEventListener('click', () => goToCard(currentIndex + 1));

    dots.forEach((dot, index) => {
      dot.setAttribute('role', 'button');
      dot.setAttribute('tabindex', '0');
      dot.setAttribute('aria-label', `Show test option ${index + 1}`);
      const activate = () => goToCard(index);
      dot.addEventListener('click', activate);
      dot.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          activate();
        }
      });
    });

    track?.addEventListener('scroll', () => {
      window.cancelAnimationFrame(scrollFrame);
      scrollFrame = window.requestAnimationFrame(updateCarousel);
    }, { passive: true });

    const selectedIndex = Math.max(0, cards.findIndex((card) => card.classList.contains('is-selected')));
    const syncLayout = () => {
      if (compactQuery.matches && cards.length) {
        goToCard(selectedIndex >= 0 ? selectedIndex : currentIndex, 'auto');
      } else {
        updateCarousel();
      }
    };

    compactQuery.addEventListener?.('change', syncLayout);
    window.addEventListener('resize', () => {
      window.cancelAnimationFrame(scrollFrame);
      scrollFrame = window.requestAnimationFrame(syncLayout);
    }, { passive: true });
    if ('ResizeObserver' in window && track) {
      const observer = new ResizeObserver(() => {
        window.cancelAnimationFrame(scrollFrame);
        scrollFrame = window.requestAnimationFrame(syncLayout);
      });
      observer.observe(track);
    }
    window.requestAnimationFrame(syncLayout);

    const form = document.getElementById('wfTestSetup');
    const loginGate = document.querySelector('[data-test-setup-gate]');
    const shouldRevealSetup = window.location.hash === '#wfTestSetup';

    if (!form) {
      if (loginGate && shouldRevealSetup) {
        window.requestAnimationFrame(() => loginGate.scrollIntoView({ behavior: 'smooth', block: 'start' }));
      }
      return;
    }

    const paperSelect = document.getElementById('wfTestPaper');
    const title = document.getElementById('wfSelectedTitle');
    const meta = document.getElementById('wfSelectedMeta');
    const message = document.getElementById('wfTestMessage');
    const submitButton = document.getElementById('wfStartTest');
    const closeButton = document.getElementById('wfCloseTestSetup');
    const guestPhone = document.getElementById('wfGuestPhone');
    let submitting = false;

    const selectedOption = () => paperSelect?.selectedOptions?.[0] || null;
    const updatePaper = () => {
      const option = selectedOption();
      const hasPaper = Boolean(option?.value);
      const questionCount = Number.parseInt(option?.dataset.questions || '0', 10) || 0;
      const duration = Number.parseInt(option?.dataset.duration || '0', 10) || 0;
      const status = String(option?.dataset.status || '').toLowerCase();
      const readyNow = option?.dataset.ready === '1' && status === 'active';
      const usable = hasPaper && readyNow && questionCount > 0;
      const batch = String(option?.dataset.batch || '').trim();

      if (title) title.textContent = hasPaper ? String(option.dataset.title || option.textContent || 'Selected test') : 'Choose a test paper';
      if (meta) meta.textContent = hasPaper
        ? `${questionCount} questions · ${duration} minutes${batch ? ` · ${batch}` : ''}`
        : 'Paper details will appear here.';
      if (submitButton) submitButton.disabled = !usable || submitting;
      if (message) {
        message.classList.toggle('is-ready', usable);
        message.classList.toggle('is-error', hasPaper && !usable);
        message.textContent = !hasPaper
          ? 'No test paper is available.'
          : (usable ? 'Ready. Start the selected paper when you are comfortable.' : 'This paper is not open yet. Check its schedule or ask the institute.');
      }
    };

    paperSelect?.addEventListener('change', updatePaper);
    closeButton?.addEventListener('click', () => window.location.assign('weekly-test.php'));
    guestPhone?.addEventListener('input', () => {
      guestPhone.value = guestPhone.value.replace(/\D+/g, '').slice(0, 10);
      guestPhone.setCustomValidity('');
    });

    form.addEventListener('submit', (event) => {
      if (submitting) {
        event.preventDefault();
        return;
      }
      if (guestPhone && guestPhone.value.length !== 10) {
        guestPhone.setCustomValidity('Enter a valid 10 digit mobile number.');
      }
      if (!form.reportValidity()) {
        event.preventDefault();
        return;
      }
      const option = selectedOption();
      const usable = Boolean(
        option?.value
        && option.dataset.ready === '1'
        && String(option.dataset.status || '').toLowerCase() === 'active'
        && Number.parseInt(option.dataset.questions || '0', 10) > 0
      );
      if (!usable) {
        event.preventDefault();
        if (message) {
          message.classList.add('is-error');
          message.textContent = 'This paper cannot start yet. Select an open test paper.';
        }
        return;
      }

      submitting = true;
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.setAttribute('aria-busy', 'true');
        const label = submitButton.querySelector('.wf-btn-label');
        if (label) label.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i><span>Opening Test…</span>';
      }
      if (message) {
        message.classList.remove('is-error');
        message.classList.add('is-ready');
        message.textContent = 'Creating your secure attempt. Please wait…';
      }
    });

    updatePaper();
    if (!form.hidden && (shouldRevealSetup || form.classList.contains('is-open'))) {
      window.requestAnimationFrame(() => form.scrollIntoView({ behavior: 'smooth', block: 'start' }));
    }
  });
})();
