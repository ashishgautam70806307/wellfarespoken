(() => {
  'use strict';

  const isMobile = () => window.matchMedia('(max-width: 700px)').matches;

  function setupPracticeSession() {
    const app = document.getElementById('practiceApp');
    const command = document.querySelector('.practice-command-center');
    if (!app || !command) return;

    let bar = document.querySelector('.wf139-session-bar');
    if (!bar) {
      bar = document.createElement('div');
      bar.className = 'wf139-session-bar';
      bar.hidden = true;
      bar.innerHTML = '<span><b>Practice active</b> · one sentence at a time</span><button type="button"><i class="fa-solid fa-sliders" aria-hidden="true"></i> Change</button>';
      app.parentNode.insertBefore(bar, app);
    }

    const enterSession = () => {
      if (!isMobile() || app.hidden) return;
      document.body.classList.add('wf139-practice-session');
      bar.hidden = false;
      requestAnimationFrame(() => document.getElementById('practice-room')?.scrollIntoView({ block: 'start' }));
    };

    const leaveSession = (scrollToOptions = true) => {
      document.body.classList.remove('wf139-practice-session');
      bar.hidden = true;
      window.dispatchEvent(new CustomEvent('wf:practice-config'));
      if (scrollToOptions) requestAnimationFrame(() => command.scrollIntoView({ behavior: 'smooth', block: 'start' }));
    };

    bar.querySelector('button')?.addEventListener('click', () => leaveSession(true));
    window.addEventListener('wf:practice-start', enterSession);
    window.addEventListener('resize', () => {
      if (!isMobile()) {
        document.body.classList.remove('wf139-practice-session');
        bar.hidden = true;
      }
    }, { passive: true });
  }

  function keepLessonPanelAtTop() {
    const lesson = document.querySelector('.duo-lesson-shell');
    if (!lesson) return;
    document.querySelectorAll('.duo-lesson-tabs button').forEach((button) => {
      button.addEventListener('click', () => {
        requestAnimationFrame(() => {
          const panel = document.querySelector('.duo-panel.active');
          if (panel) panel.scrollTop = 0;
        });
      });
    });
  }

  function start() {
    setupPracticeSession();
    keepLessonPanelAtTop();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();
})();
