(() => {
  'use strict';

  const ready = (callback) => {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', callback, { once: true });
    } else {
      callback();
    }
  };

  ready(() => {
    const carousel = document.querySelector('[data-test-carousel]');
    const track = carousel?.querySelector('[data-test-track]');
    const cards = track ? Array.from(track.querySelectorAll('[data-test-card]')) : [];
    const previousButton = carousel?.querySelector('[data-test-prev]');
    const nextButton = carousel?.querySelector('[data-test-next]');
    const dots = carousel ? Array.from(carousel.querySelectorAll('.wf145-test-dots span')) : [];

    const isCompact = () => window.matchMedia('(max-width: 720px)').matches;
    const cardStep = () => {
      if (!track || cards.length === 0) return 0;
      const first = cards[0];
      const gap = Number.parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || '0') || 0;
      return first.getBoundingClientRect().width + gap;
    };
    const activeIndex = () => {
      const step = cardStep();
      if (!track || step <= 0) return 0;
      return Math.max(0, Math.min(cards.length - 1, Math.round(track.scrollLeft / step)));
    };
    const updateCarouselState = () => {
      const index = activeIndex();
      dots.forEach((dot, dotIndex) => dot.classList.toggle('is-active', dotIndex === index));
      if (previousButton) previousButton.disabled = !isCompact() || index <= 0;
      if (nextButton) nextButton.disabled = !isCompact() || index >= cards.length - 1;
    };
    const scrollToCard = (index) => {
      if (!track || cards.length === 0) return;
      const safeIndex = Math.max(0, Math.min(cards.length - 1, index));
      track.scrollTo({ left: safeIndex * cardStep(), behavior: 'smooth' });
    };

    previousButton?.addEventListener('click', () => scrollToCard(activeIndex() - 1));
    nextButton?.addEventListener('click', () => scrollToCard(activeIndex() + 1));
    dots.forEach((dot, index) => {
      dot.setAttribute('role', 'button');
      dot.setAttribute('tabindex', '0');
      dot.setAttribute('aria-label', `Show test option ${index + 1}`);
      const activate = () => scrollToCard(index);
      dot.addEventListener('click', activate);
      dot.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          activate();
        }
      });
    });
    let scrollFrame = 0;
    track?.addEventListener('scroll', () => {
      window.cancelAnimationFrame(scrollFrame);
      scrollFrame = window.requestAnimationFrame(updateCarouselState);
    }, { passive: true });
    window.addEventListener('resize', updateCarouselState, { passive: true });
    updateCarouselState();
    const initiallySelected = cards.findIndex((card) => card.classList.contains('is-selected'));
    if (initiallySelected > 0 && isCompact() && track) {
      window.requestAnimationFrame(() => {
        track.scrollLeft = initiallySelected * cardStep();
        updateCarouselState();
      });
    }

    const form = document.getElementById('wfTestSetup');
    const loginGate = document.querySelector('[data-test-setup-gate]');
    if (!form) {
      if (loginGate && window.location.hash === '#wfTestSetup') {
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
      const hasPaper = Boolean(option && option.value);
      const readyNow = hasPaper && option.dataset.ready === '1' && String(option.dataset.status || '').toLowerCase() === 'active';
      const questionCount = Number.parseInt(option?.dataset.questions || '0', 10) || 0;
      const duration = Number.parseInt(option?.dataset.duration || '0', 10) || 0;
      const batch = String(option?.dataset.batch || '').trim();
      const usable = readyNow && questionCount > 0;

      if (title) title.textContent = hasPaper ? String(option.dataset.title || option.textContent || 'Selected test') : 'Choose a test paper';
      if (meta) {
        meta.textContent = hasPaper
          ? `${questionCount} questions · ${duration} minutes${batch ? ` · ${batch}` : ''}`
          : 'Paper details will appear here.';
      }
      if (submitButton) submitButton.disabled = !usable || submitting;
      if (message) {
        message.textContent = !hasPaper
          ? 'No test paper is available.'
          : (!usable ? 'This paper is not open yet. Check its schedule or ask the institute.' : 'Ready. Start the selected paper when you are comfortable.');
      }
    };

    paperSelect?.addEventListener('change', updatePaper);
    closeButton?.addEventListener('click', () => {
      window.location.assign('weekly-test.php');
    });
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
      const usable = Boolean(option && option.value && option.dataset.ready === '1' && String(option.dataset.status || '').toLowerCase() === 'active' && Number.parseInt(option.dataset.questions || '0', 10) > 0);
      if (!usable) {
        event.preventDefault();
        if (message) message.textContent = 'This paper cannot start yet. Select an open test paper.';
        return;
      }
      submitting = true;
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.setAttribute('aria-busy', 'true');
        const label = submitButton.querySelector('.wf-btn-label');
        if (label) label.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i><span>Opening Test…</span>';
      }
      if (message) message.textContent = 'Creating your secure attempt. Please wait…';
    });

    updatePaper();
    if (!form.hidden && (window.location.hash === '#wfTestSetup' || form.classList.contains('is-open'))) {
      window.requestAnimationFrame(() => form.scrollIntoView({ behavior: 'smooth', block: 'start' }));
    }
  });
})();
