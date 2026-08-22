(() => {
  'use strict';

  const isMobile = () => window.matchMedia('(max-width: 760px)').matches;
  const safeScroll = (target, block = 'start') => {
    if (!target) return;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    target.scrollIntoView({ behavior: (isMobile() || reduceMotion) ? 'auto' : 'smooth', block });
  };

  function installTapFeedback() {
    const selector = 'button:not(:disabled), a[href], .wf143-mode-card, .lesson-pick-btn, label';
    document.addEventListener('pointerdown', (event) => {
      if (!isMobile()) return;
      const target = event.target.closest(selector);
      if (!target) return;
      target.classList.add('wf175-tapping');
    }, { passive: true });
    ['pointerup', 'pointercancel', 'pointerleave'].forEach((name) => {
      document.addEventListener(name, (event) => {
        const target = event.target.closest?.(selector);
        if (target) target.classList.remove('wf175-tapping');
      }, { passive: true });
    });
  }

  function installScrollButtons() {
    document.querySelectorAll('[data-wf175-scroll]').forEach((button) => {
      button.addEventListener('click', () => {
        if (!isMobile()) return;
        const selector = button.getAttribute('data-wf175-scroll') || '';
        const target = selector ? document.querySelector(selector) : null;
        safeScroll(target, 'start');
      });
    });
  }

  function setupSpokenMaterials() {
    if (!document.body.classList.contains('page-spoken-materials')) return;
    const root = document.getElementById('practice-room');
    const app = document.getElementById('practiceApp');
    const search = document.getElementById('wf175ModeSearch');
    const clear = document.getElementById('wf175ModeSearchClear');
    const changeMode = document.getElementById('practiceChangeMode');
    const cards = [...document.querySelectorAll('.wf143-mode-card')];
    if (!root || !app) return;

    const syncActiveState = () => {
      if (!isMobile()) {
        document.body.classList.remove('wf175-practice-active');
        return;
      }
      document.body.classList.toggle('wf175-practice-active', !app.hidden);
    };
    new MutationObserver(syncActiveState).observe(app, { attributes: true, attributeFilter: ['hidden'] });
    syncActiveState();

    const filterModes = () => {
      const query = String(search?.value || '').trim().toLowerCase();
      cards.forEach((card) => {
        const text = card.textContent.toLowerCase();
        card.hidden = query !== '' && !text.includes(query);
      });
    };
    search?.addEventListener('input', filterModes);
    clear?.addEventListener('click', () => {
      if (!search) return;
      search.value = '';
      filterModes();
      search.focus({ preventScroll: true });
    });

    cards.forEach((card) => {
      card.addEventListener('click', () => {
        if (!isMobile()) return;
        card.setAttribute('aria-busy', 'true');
        cards.forEach((other) => { if (other !== card) other.removeAttribute('aria-busy'); });
        window.setTimeout(() => card.removeAttribute('aria-busy'), 1400);
      });
    });

    changeMode?.addEventListener('click', () => {
      if (!isMobile()) return;
      document.body.classList.remove('wf175-practice-active');
      window.setTimeout(() => safeScroll(document.querySelector('.wf175-materials-head') || root), 40);
    });
  }

  function setupPracticeMaterials() {
    if (!document.body.classList.contains('page-free-ai-english-practice')) return;
    const quickTool = document.getElementById('quick-tool');
    const lessonSelect = document.getElementById('wf175LessonSelect');
    const lessonButtons = [...document.querySelectorAll('.lesson-pick-btn')];
    const lessonStart = document.getElementById('wf175LessonStart');
    const workspace = document.querySelector('.practice-workspace');
    const title = document.getElementById('activeLessonTitle');

    document.querySelectorAll('[data-wf175-toggle="#quick-tool"]').forEach((button) => {
      button.addEventListener('click', () => {
        if (!isMobile() || !quickTool) return;
        const open = !quickTool.classList.contains('wf175-is-open');
        quickTool.classList.toggle('wf175-is-open', open);
        button.classList.toggle('is-active', open);
        button.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) window.setTimeout(() => safeScroll(quickTool), 30);
      });
    });

    const openSelectedLesson = () => {
      if (!isMobile() || !lessonSelect) return;
      const id = String(lessonSelect.value || '');
      if (!id) {
        lessonSelect.focus({ preventScroll: true });
        return;
      }
      const match = lessonButtons.find((button) => String(button.dataset.lessonId || '') === id);
      if (!match) return;
      lessonSelect.disabled = true;
      lessonSelect.setAttribute('aria-busy', 'true');
      if (lessonStart) lessonStart.disabled = true;
      match.click();
      window.setTimeout(() => safeScroll(workspace), 90);
      window.setTimeout(() => {
        lessonSelect.disabled = false;
        lessonSelect.removeAttribute('aria-busy');
        if (lessonStart) lessonStart.disabled = false;
      }, 1000);
    };
    lessonSelect?.addEventListener('change', openSelectedLesson);
    lessonStart?.addEventListener('click', openSelectedLesson);

    lessonButtons.forEach((button) => {
      button.addEventListener('click', () => {
        if (!lessonSelect) return;
        lessonSelect.value = String(button.dataset.lessonId || '');
      });
    });

    if (title && lessonSelect) {
      new MutationObserver(() => {
        lessonSelect.disabled = false;
        lessonSelect.removeAttribute('aria-busy');
        if (lessonStart) lessonStart.disabled = false;
      }).observe(title, { childList: true, characterData: true, subtree: true });
    }

    const questionStage = document.getElementById('practiceQuestionStage');
    if (questionStage) {
      new MutationObserver(() => {
        if (!isMobile()) return;
        const answer = questionStage.querySelector('#studentAnswer');
        if (answer) answer.setAttribute('inputmode', 'text');
      }).observe(questionStage, { childList: true, subtree: true });
    }
  }

  function setupRoadmap() {
    if (!document.body.classList.contains('page-learning-roadmap')) return;
    const tabs = [...document.querySelectorAll('[data-wf175-roadmap-view]')];
    const levels = [...document.querySelectorAll('.rm126-step')];
    const stages = document.querySelector('.rm126-stages');
    const continueButton = document.getElementById('roadmapContinueBtn');
    const sticky = document.getElementById('wf175RoadmapStickyContinue');
    const nextLabel = document.getElementById('roadmapNextLabel');

    const setTab = (name) => tabs.forEach((tab) => tab.classList.toggle('is-active', tab.dataset.wf175RoadmapView === name));
    tabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        if (!isMobile()) return;
        const view = tab.dataset.wf175RoadmapView || 'all';
        setTab(view);
        if (view === 'progress') {
          const current = document.querySelector('.rm126-step.is-current') || document.querySelector('.rm126-step.is-done:last-of-type') || levels[0];
          safeScroll(current, 'center');
        } else {
          safeScroll(stages, 'start');
        }
      });
    });

    const syncContinue = () => {
      if (!sticky || !continueButton) return;
      sticky.href = continueButton.href;
      const label = sticky.querySelector('b');
      if (label && nextLabel) label.textContent = nextLabel.textContent || 'Continue learning';
    };
    syncContinue();
    if (nextLabel) new MutationObserver(syncContinue).observe(nextLabel, { childList: true, characterData: true, subtree: true });

    document.querySelectorAll('.rm126-open, #roadmapContinueBtn, #wf175RoadmapStickyContinue').forEach((link) => {
      link.addEventListener('click', () => {
        if (!isMobile()) return;
        link.setAttribute('aria-busy', 'true');
        link.classList.add('wf175-opening');
      });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    installTapFeedback();
    installScrollButtons();
    setupSpokenMaterials();
    setupPracticeMaterials();
    setupRoadmap();
  }, { once: true });
})();
