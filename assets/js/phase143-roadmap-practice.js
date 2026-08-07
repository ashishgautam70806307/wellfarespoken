(() => {
  'use strict';

  const configNode = document.getElementById('roadmapPracticeConfig');
  const root = document.querySelector('.duo-lesson-page');
  if (!configNode || !root) return;

  let config = {};
  try { config = JSON.parse(configNode.textContent || '{}'); }
  catch (error) { config = {}; }

  const storageKey = String(config.storageKey || 'wfRoadmapCompleted');
  const voiceStorageKey = 'wfRoadmapVoiceGuide';
  const studentMode = Boolean(config.studentMode);
  let serverCompleted = Array.isArray(config.serverCompleted) ? config.serverCompleted.map(Number) : [];
  const csrfToken = String(config.csrfToken || '');
  const unitId = Number(config.unitId || root.dataset.unitId || 0);
  const nextId = Number(config.nextId || root.dataset.nextId || 0);
  const allLessonIds = Array.isArray(config.allLessonIds) ? config.allLessonIds.map(Number) : [];
  const rows = Array.isArray(config.items) ? config.items : [];

  const tabButtons = [...document.querySelectorAll('.duo-lesson-tabs button[data-tab]')];
  const panels = [...document.querySelectorAll('.duo-panel[data-panel]')];
  const progressBar = document.getElementById('lessonUnlockProgress');
  const progressCount = document.getElementById('lessonUnlockCount');
  const exercise = document.querySelector('.duo-exercise');
  const practiceCount = document.getElementById('practiceQuestionCount');
  const practiceBar = document.getElementById('practiceQuestionBar');
  const questionText = document.getElementById('duoQuestionText');
  const answerGrid = document.getElementById('duoAnswerGrid');
  const startButton = document.getElementById('duoStartPractice');
  const nextButton = document.getElementById('duoNextQuestion');
  const resultBox = document.getElementById('duoResultBox');
  const speakButton = document.getElementById('duoSpeakQuestion');
  const voiceToggle = document.getElementById('duoVoiceGuide');
  const voiceStatus = document.getElementById('duoVoiceStatus');

  let completing = false;
  let currentIndex = 0;
  let correctCount = 0;
  let wrongCount = 0;
  let started = false;
  let answered = false;
  let correctAnswer = '';
  let speechTimer = 0;
  let speechSequence = 0;
  let voiceEnabled = true;

  try {
    const savedVoice = localStorage.getItem(voiceStorageKey);
    voiceEnabled = savedVoice === null ? true : savedVoice === '1';
  } catch (error) {
    voiceEnabled = true;
  }
  if (voiceToggle) voiceToggle.checked = voiceEnabled;

  function setVoiceStatus(message) {
    if (voiceStatus) voiceStatus.textContent = message;
  }

  function cancelSpeech() {
    speechSequence += 1;
    window.clearTimeout(speechTimer);
    speechTimer = 0;
    if ('speechSynthesis' in window) window.speechSynthesis.cancel();
  }

  function speakText(text, options = {}) {
    const value = String(text || '').trim();
    const force = Boolean(options.force);
    if (!value || !('speechSynthesis' in window) || (!voiceEnabled && !force)) return false;

    cancelSpeech();
    const sequence = speechSequence;
    const utterance = new SpeechSynthesisUtterance(value);
    utterance.lang = /[\u0900-\u097F]/.test(value) ? 'hi-IN' : 'en-IN';
    utterance.rate = Number(options.rate || 0.9);
    utterance.pitch = Number(options.pitch || 1);
    utterance.volume = 1;
    utterance.onstart = () => {
      if (sequence === speechSequence) setVoiceStatus(options.startMessage || 'Speaking…');
    };
    utterance.onend = () => {
      if (sequence !== speechSequence) return;
      setVoiceStatus(voiceEnabled ? 'Voice guide is on.' : 'Voice guide is off.');
      if (typeof options.onEnd === 'function') options.onEnd();
    };
    utterance.onerror = () => {
      if (sequence !== speechSequence) return;
      setVoiceStatus('Voice is unavailable on this browser. Use the speaker button to retry.');
    };
    window.speechSynthesis.speak(utterance);
    speechTimer = window.setTimeout(() => {
      if (sequence !== speechSequence) return;
      if (window.speechSynthesis.speaking || window.speechSynthesis.pending) {
        window.speechSynthesis.cancel();
        setVoiceStatus('Voice stopped. You can tap the speaker button to replay.');
      }
    }, Math.max(5000, Math.min(18000, value.length * 110)));
    return true;
  }

  voiceToggle?.addEventListener('change', () => {
    voiceEnabled = voiceToggle.checked;
    try { localStorage.setItem(voiceStorageKey, voiceEnabled ? '1' : '0'); } catch (error) {}
    if (!voiceEnabled) cancelSpeech();
    setVoiceStatus(voiceEnabled ? 'Question and answer feedback will be spoken.' : 'Voice guide is off. Speaker button still works.');
    if (voiceEnabled && started && !answered) speakText(questionText?.textContent || '', { startMessage: 'Reading question…' });
  });
  setVoiceStatus(voiceEnabled ? 'Question and answer feedback will be spoken.' : 'Voice guide is off. Speaker button still works.');

  function completedIds() {
    if (studentMode) return serverCompleted;
    try {
      const saved = JSON.parse(localStorage.getItem(storageKey) || '[]');
      return Array.isArray(saved) ? saved.map(Number) : [];
    } catch (error) {
      return [];
    }
  }

  function updateUnlockProgress() {
    const completed = completedIds();
    const total = allLessonIds.length || 1;
    const completedCount = completed.filter((id) => allLessonIds.includes(id)).length;
    const unlockedCount = Math.min(total, completedCount + 1);
    const percentage = Math.max(8, Math.round((completedCount / total) * 100));
    if (progressBar) progressBar.style.width = `${percentage}%`;
    if (progressCount) progressCount.textContent = `${unlockedCount} / ${total}`;
  }

  function showPanel(name) {
    cancelSpeech();
    tabButtons.forEach((button) => {
      const active = button.dataset.tab === name;
      button.classList.toggle('active', active);
      button.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    panels.forEach((panel) => {
      const active = panel.dataset.panel === name;
      panel.classList.toggle('active', active);
      if (active) panel.scrollTop = 0;
    });
    if (name === 'practice' && started && !answered) {
      window.setTimeout(() => speakText(questionText?.textContent || '', { startMessage: 'Reading question…' }), 160);
    }
  }

  tabButtons.forEach((button) => button.addEventListener('click', () => showPanel(button.dataset.tab || 'learn')));
  document.querySelectorAll('.next-tab[data-next]').forEach((button) => {
    button.addEventListener('click', () => showPanel(button.dataset.next || 'practice'));
  });

  function celebrate() {
    const box = document.getElementById('duoCelebrate');
    if (!box) return;
    box.replaceChildren();
    const icons = ['🌸', '✨', '🎉', '⭐'];
    const fragment = document.createDocumentFragment();
    for (let index = 0; index < 20; index += 1) {
      const particle = document.createElement('span');
      particle.textContent = icons[index % icons.length];
      particle.style.left = `${(index * 17) % 100}%`;
      particle.style.animationDelay = `${(index % 8) * 0.05}s`;
      particle.style.fontSize = `${16 + (index % 4) * 3}px`;
      fragment.appendChild(particle);
    }
    box.appendChild(fragment);
    box.classList.add('show');
    window.setTimeout(() => box.classList.remove('show'), 1600);
  }

  const allCompleteButtons = () => [...document.querySelectorAll('.completeLevel')];

  async function completeLevel() {
    if (completing) return;
    completing = true;
    cancelSpeech();
    allCompleteButtons().forEach((button) => {
      button.disabled = true;
      button.setAttribute('aria-busy', 'true');
    });
    try {
      if (studentMode) {
        const body = new URLSearchParams({ action: 'complete', unit_id: String(unitId), csrf_token: csrfToken });
        const response = await fetch('roadmap-progress-api.php', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', Accept: 'application/json' },
          body: body.toString()
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok || !data.success) throw new Error(data.message || 'Could not save progress.');
        if (Array.isArray(data.completed_ids)) serverCompleted = data.completed_ids.map(Number);
      } else {
        const saved = completedIds();
        if (unitId && !saved.includes(unitId)) saved.push(unitId);
        localStorage.setItem(storageKey, JSON.stringify(saved));
      }
      updateUnlockProgress();
      celebrate();
      speakText('Level complete. Great work!', { startMessage: 'Level completed.' });
      window.setTimeout(() => {
        window.location.href = nextId ? `roadmap-lesson.php?id=${nextId}` : 'learning-roadmap.php#roadmapPath';
      }, 1200);
    } catch (error) {
      window.alert(error instanceof Error ? error.message : 'Could not save progress.');
      completing = false;
      allCompleteButtons().forEach((button) => {
        button.disabled = false;
        button.removeAttribute('aria-busy');
      });
    }
  }

  allCompleteButtons().forEach((button) => button.addEventListener('click', completeLevel));
  updateUnlockProgress();

  if (!exercise || !questionText || !answerGrid || !startButton || !nextButton || !resultBox) return;

  const normalize = (value) => String(value || '')
    .toLowerCase()
    .replace(/[^\p{L}\p{N}\s]/gu, ' ')
    .replace(/\s+/g, ' ')
    .trim();

  function rowPair(row) {
    return {
      question: String(row.col_2 || row.item_key || row.col_1 || 'Question').trim(),
      answer: String(row.col_1 || row.col_3 || row.item_key || '').trim()
    };
  }

  function updatePracticeProgress() {
    const total = rows.length;
    const visibleNumber = started && total ? Math.min(currentIndex + 1, total) : 0;
    const percentage = total ? Math.round((visibleNumber / total) * 100) : 0;
    if (practiceCount) practiceCount.textContent = `${visibleNumber} / ${total}`;
    if (practiceBar) practiceBar.style.width = `${percentage}%`;
  }

  function shuffle(values) {
    const result = [...values];
    for (let index = result.length - 1; index > 0; index -= 1) {
      const swapIndex = Math.floor(Math.random() * (index + 1));
      [result[index], result[swapIndex]] = [result[swapIndex], result[index]];
    }
    return result;
  }

  function speakQuestion(force = false) {
    speakText(questionText.textContent || '', { force, startMessage: 'Reading question…' });
  }
  speakButton?.addEventListener('click', () => speakQuestion(true));

  function resetFeedback() {
    answered = false;
    resultBox.hidden = true;
    resultBox.className = 'duo-result-box';
    resultBox.replaceChildren();
    exercise.classList.remove('has-result');
    nextButton.hidden = true;
  }

  function renderSummary() {
    cancelSpeech();
    const totalAnswers = correctCount + wrongCount;
    const percentage = totalAnswers ? Math.round((correctCount / totalAnswers) * 100) : 0;
    questionText.textContent = 'Practice complete!';
    answerGrid.replaceChildren();

    const summary = document.createElement('div');
    summary.className = 'duo-practice-summary';
    const score = document.createElement('h3');
    score.textContent = `${percentage}%`;
    const totals = document.createElement('p');
    totals.textContent = `Correct: ${correctCount} / Wrong: ${wrongCount}`;
    const note = document.createElement('small');
    note.textContent = percentage >= 80 ? 'Excellent practice!' : (percentage >= 50 ? 'Good, revise mistakes once.' : 'Repeat this lesson once more.');
    summary.append(score, totals, note);

    const complete = document.createElement('button');
    complete.type = 'button';
    complete.className = 'duo-big-green duo-complete-cta completeLevel';
    complete.innerHTML = '<span>COMPLETE LEVEL</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i>';
    complete.addEventListener('click', completeLevel);
    answerGrid.append(summary, complete);

    startButton.hidden = true;
    nextButton.hidden = true;
    resultBox.hidden = true;
    updatePracticeProgress();
    speakText(`Practice complete. You scored ${percentage} percent. ${note.textContent}`, { startMessage: 'Reading your result…' });
  }

  function renderQuestion() {
    cancelSpeech();
    resetFeedback();
    updatePracticeProgress();
    if (!rows.length) {
      questionText.textContent = 'No practice rows added yet.';
      answerGrid.innerHTML = '<div class="duo-empty"><b>No practice data found.</b><br>Add records to this roadmap lesson from Admin.</div>';
      startButton.hidden = true;
      return;
    }
    if (currentIndex >= rows.length) {
      renderSummary();
      return;
    }

    const current = rowPair(rows[currentIndex]);
    correctAnswer = current.answer;
    questionText.textContent = current.question;

    const answerPool = [...new Set(rows.map(rowPair).map((item) => item.answer).filter(Boolean))]
      .filter((value) => normalize(value) !== normalize(correctAnswer));
    const choices = shuffle([correctAnswer, ...shuffle(answerPool).slice(0, 3)]);
    answerGrid.replaceChildren();

    choices.forEach((choice) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'duo-choice';
      button.textContent = choice;
      button.addEventListener('click', () => {
        if (answered) return;
        answered = true;
        cancelSpeech();
        const isCorrect = normalize(choice) === normalize(correctAnswer);
        button.classList.add(isCorrect ? 'correct' : 'wrong');
        if (isCorrect) correctCount += 1;
        else wrongCount += 1;
        answerGrid.querySelectorAll('button').forEach((option) => { option.disabled = true; });
        resultBox.hidden = false;
        resultBox.className = `duo-result-box ${isCorrect ? 'ok' : 'bad'}`;
        exercise.classList.add('has-result');
        resultBox.replaceChildren();
        const heading = document.createElement('strong');
        heading.textContent = isCorrect ? 'Correct.' : 'Not quite.';
        const detail = document.createElement('span');
        detail.textContent = isCorrect ? 'You selected the right answer.' : `Correct answer: ${correctAnswer}`;
        resultBox.append(heading, detail);
        nextButton.hidden = false;
        speakText(isCorrect ? 'Correct. Well done.' : `Not quite. The correct answer is ${correctAnswer}.`, { startMessage: 'Reading feedback…' });
      }, { once: true });
      answerGrid.appendChild(button);
    });
    startButton.hidden = true;
    window.setTimeout(() => speakQuestion(false), 140);
  }

  startButton.addEventListener('click', () => {
    started = true;
    currentIndex = 0;
    correctCount = 0;
    wrongCount = 0;
    renderQuestion();
  });

  nextButton.addEventListener('click', () => {
    if (!answered) return;
    currentIndex += 1;
    renderQuestion();
  });

  window.addEventListener('pagehide', cancelSpeech, { once: true });
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) cancelSpeech();
  });

  updatePracticeProgress();
})();
