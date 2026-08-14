(() => {
  'use strict';
  const root = document.getElementById('practice-room');
  if (!root) return;

  const $ = (id) => document.getElementById(id);
  const modeButtons = [...root.querySelectorAll('.wf143-mode-card')];
  const ready = $('practiceReady');
  const loader = $('practiceLoader');
  const errorBox = $('practiceError');
  const errorText = $('practiceErrorText');
  const retryLoad = $('practiceRetryLoad');
  const app = $('practiceApp');
  const modeLabel = $('practiceModeLabel');
  const counter = $('practiceCounter');
  const meter = $('practiceMeterBar');
  const questionCard = $('practiceQuestionCard');
  const level = $('practiceLevel');
  const topic = $('practiceTopic');
  const instruction = $('practiceInstruction');
  const question = $('practiceQuestion');
  const roman = $('practiceRoman');
  const answerPanel = root.querySelector('.wf143-answer-panel');
  const answer = $('practiceAnswer');
  const handsfree = $('practiceHandsfree');
  const listen = $('practiceListen');
  const speak = $('practiceSpeak');
  const stop = $('practiceStop');
  const voiceStatus = $('practiceVoiceStatus');
  const check = $('practiceCheck');
  const clear = $('practiceClear');
  const result = $('practiceResult');
  const previous = $('practicePrevious');
  const next = $('practiceNext');
  const changeMode = $('practiceChangeMode');
  const navigation = root.querySelector('.wf143-navigation');

  const labels = {
    speak: 'Speak Daily',
    hindi_to_english: 'Hindi to English',
    english_to_hindi: 'English to Hindi',
    revision: 'Revision'
  };

  let selectedGoal = root.dataset.defaultGoal || 'speak';
  let selectedDirection = selectedGoal === 'english_to_hindi' ? 'english_to_hindi' : 'hindi_to_english';
  let items = [];
  let currentIndex = 0;
  let csrfToken = '';
  let listController = null;
  let answerController = null;
  let recognition = null;
  let recognitionTimer = null;
  let voiceCoachTimer = null;
  let autoCheckTimer = null;
  let speechStartTimer = null;
  let speechEndTimer = null;
  let checking = false;
  let requestId = 0;
  let voiceCycleId = 0;
  let activeCollectionId = Number(root.dataset.defaultCollection || 0);
  let pageOffset = 0;
  let nextPageOffset = 0;
  let hasMore = false;
  let sessionNumber = 1;
  let continuing = false;
  let autoRecoveryTimer = null;
  let voiceRecoveryCount = 0;
  let voicePausedByUser = false;

  const escapeHtml = (value) => String(value || '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
  })[character]);

  const currentItem = () => items[currentIndex] || null;

  function restoreVoicePreference() {
    try {
      const saved = window.localStorage.getItem('wf_voice_coach_enabled');
      if (saved !== null) handsfree.checked = saved === '1';
    } catch (error) {}
  }

  function storeVoicePreference() {
    try {
      window.localStorage.setItem('wf_voice_coach_enabled', handsfree.checked ? '1' : '0');
    } catch (error) {}
  }

  function clearVoiceTimers() {
    window.clearTimeout(voiceCoachTimer);
    window.clearTimeout(autoCheckTimer);
    window.clearTimeout(speechStartTimer);
    window.clearTimeout(speechEndTimer);
    window.clearTimeout(autoRecoveryTimer);
    voiceCoachTimer = null;
    autoCheckTimer = null;
    speechStartTimer = null;
    speechEndTimer = null;
    autoRecoveryTimer = null;
  }

  function stopSpeech() {
    window.clearTimeout(speechStartTimer);
    window.clearTimeout(speechEndTimer);
    speechStartTimer = null;
    speechEndTimer = null;
    if ('speechSynthesis' in window) {
      try { window.speechSynthesis.cancel(); } catch (error) {}
    }
  }

  function resetMicButtons() {
    speak.disabled = false;
    speak.removeAttribute('aria-busy');
    speak.innerHTML = '<i class="fa-solid fa-microphone" aria-hidden="true"></i><span>Speak answer</span>';
    stop.hidden = true;
  }

  function stopRecognition(message = '', silent = false) {
    window.clearTimeout(recognitionTimer);
    recognitionTimer = null;
    const active = recognition;
    recognition = null;
    if (active) {
      try {
        active.onresult = null;
        active.onerror = null;
        active.onend = null;
        active.onspeechend = null;
        active.stop();
      } catch (error) {}
    }
    resetMicButtons();
    if (!silent && message) voiceStatus.textContent = message;
  }

  function cleanupVoiceOnly() {
    voiceCycleId += 1;
    clearVoiceTimers();
    stopSpeech();
    stopRecognition('', true);
  }

  function cleanupInteractiveState() {
    cleanupVoiceOnly();
    if (answerController) {
      answerController.abort();
      answerController = null;
    }
    checking = false;
    check.disabled = false;
    check.removeAttribute('aria-busy');
    check.innerHTML = '<i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Check Answer</span>';
  }

  function setScreen(name, message = '') {
    ready.hidden = name !== 'ready';
    loader.hidden = name !== 'loading';
    errorBox.hidden = name !== 'error';
    app.hidden = name !== 'app';
    if (message) errorText.textContent = message;
  }

  function selectMode(goal, direction) {
    selectedGoal = goal;
    selectedDirection = direction;
    modeButtons.forEach((button) => {
      const active = button.dataset.goal === goal;
      button.classList.toggle('is-selected', active);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  function speakText(text, language, callbacks = {}) {
    const clean = String(text || '').trim();
    const onStart = typeof callbacks.onStart === 'function' ? callbacks.onStart : () => {};
    const onEnd = typeof callbacks.onEnd === 'function' ? callbacks.onEnd : () => {};
    const onError = typeof callbacks.onError === 'function' ? callbacks.onError : () => {};
    if (!clean || !('speechSynthesis' in window)) {
      onError('unsupported');
      return false;
    }
    stopSpeech();
    const utterance = new SpeechSynthesisUtterance(clean);
    utterance.lang = language || (/[\u0900-\u097F]/.test(clean) ? 'hi-IN' : 'en-IN');
    utterance.rate = 0.82;
    utterance.pitch = 1;
    const voices = window.speechSynthesis.getVoices();
    const languagePrefix = utterance.lang.toLowerCase().slice(0, 2);
    const matchingVoice = voices.find((voice) => String(voice.lang || '').toLowerCase().startsWith(languagePrefix));
    if (matchingVoice) utterance.voice = matchingVoice;
    let finished = false;
    let started = false;
    const finish = (type) => {
      if (finished) return;
      finished = true;
      window.clearTimeout(speechStartTimer);
      window.clearTimeout(speechEndTimer);
      speechStartTimer = null;
      speechEndTimer = null;
      if (type === 'error') onError('playback');
      else onEnd(type);
    };
    utterance.onstart = () => { started = true; onStart(); };
    utterance.onend = () => finish('ended');
    utterance.onerror = () => finish('error');
    speechStartTimer = window.setTimeout(() => {
      if (started || finished) return;
      try { window.speechSynthesis.cancel(); } catch (error) {}
      finish('timeout');
    }, 4500);
    speechEndTimer = window.setTimeout(() => {
      if (finished) return;
      try { window.speechSynthesis.cancel(); } catch (error) {}
      finish('timeout');
    }, Math.min(30000, Math.max(9000, clean.length * 130)));
    try {
      window.speechSynthesis.speak(utterance);
      return true;
    } catch (error) {
      finish('error');
      return false;
    }
  }

  function questionAndAnswer(item) {
    const direction = item.direction || selectedDirection;
    if (direction === 'english_to_hindi') {
      return { question: item.english, answer: item.hindi, direction, instruction: 'Translate this English sentence into Hindi' };
    }
    if (selectedGoal === 'speak') {
      return { question: item.english, answer: item.english, direction: 'hindi_to_english', instruction: 'Listen and repeat the same English sentence' };
    }
    return {
      question: item.hindi,
      answer: item.english,
      direction: 'hindi_to_english',
      instruction: selectedGoal === 'revision' ? 'Correct your earlier mistake' : 'Translate this Hindi sentence into English'
    };
  }

  function questionLanguage(qa) {
    return /[\u0900-\u097F]/.test(String(qa.question || '')) ? 'hi-IN' : 'en-IN';
  }

  function answerLanguage(qa) {
    return qa.direction === 'english_to_hindi' ? 'hi-IN' : 'en-IN';
  }

  function isRepeatCommand(text) {
    const cleaned = String(text || '').toLowerCase().replace(/[^\p{L}\p{N}\s]/gu, ' ').replace(/\s+/g, ' ').trim();
    if (!cleaned) return false;
    const commands = [
      'again', 'say again', 'once again', 'repeat', 'repeat question', 'please repeat',
      'again bolo', 'dobara bolo', 'doobara bolo', 'dubara bolo', 'phir se bolo', 'fir se bolo',
      'dobara', 'dubara', 'phir se', 'fir se', 'samajh nahi aaya', 'samajh nhi aaya',
      'nahi suna', 'nhi suna', 'sunai nahi diya', 'sunayi nahi diya'
    ];
    return commands.some((command) => cleaned === command || cleaned.includes(command));
  }

  function queueVoiceCoach(delay = 420) {
    window.clearTimeout(voiceCoachTimer);
    if (!handsfree.checked || app.hidden || !currentItem() || voicePausedByUser) {
      voiceStatus.textContent = voicePausedByUser
        ? 'Voice is paused. Tap Speak answer to resume, or turn Voice coach off.'
        : 'Voice coach is off. Use Listen or Speak answer manually.';
      return;
    }
    voiceStatus.textContent = 'Voice coach will read the question, then open the microphone.';
    voiceCoachTimer = window.setTimeout(() => runVoiceCoach(false), delay);
  }

  function runVoiceCoach(isRepeat) {
    const item = currentItem();
    if (!item || !handsfree.checked || app.hidden || voicePausedByUser) return;
    cleanupVoiceOnly();
    const cycle = voiceCycleId;
    const qa = questionAndAnswer(item);
    answer.value = '';
    result.hidden = true;
    voiceStatus.textContent = isRepeat ? 'Repeating the question...' : 'Reading the question...';
    const startMicAfterSpeech = () => {
      if (cycle !== voiceCycleId || !handsfree.checked || app.hidden || !currentItem()) return;
      voiceStatus.textContent = 'Now speak your answer.';
      voiceCoachTimer = window.setTimeout(() => {
        if (cycle === voiceCycleId && handsfree.checked && !voicePausedByUser) startRecognition(true);
      }, 550);
    };
    speakText(qa.question, questionLanguage(qa), {
      onStart: () => { if (cycle === voiceCycleId) voiceStatus.textContent = 'Playing the question...'; },
      onEnd: startMicAfterSpeech,
      onError: () => {
        if (cycle !== voiceCycleId) return;
        voiceStatus.textContent = 'Question audio was unavailable. Opening the microphone.';
        startMicAfterSpeech();
      }
    });
  }

  function renderQuestion() {
    cleanupInteractiveState();
    const item = currentItem();
    if (!item) {
      renderComplete();
      return;
    }
    const qa = questionAndAnswer(item);
    questionCard.hidden = false;
    answerPanel.hidden = false;
    navigation.hidden = false;
    result.hidden = true;
    result.className = 'wf143-result';
    result.innerHTML = '';
    answer.value = '';
    modeLabel.textContent = labels[selectedGoal] || 'Practice';
    counter.textContent = `Sentence ${sessionNumber}`;
    meter.style.width = `${Math.round(((currentIndex + 1) / items.length) * 100)}%`;
    level.textContent = item.level || 'Beginner';
    topic.textContent = item.topic || 'Spoken Practice';
    instruction.textContent = qa.instruction;
    question.textContent = qa.question || 'Question';
    roman.textContent = item.roman || '';
    roman.hidden = !String(item.roman || '').trim() || qa.direction === 'english_to_hindi';
    previous.disabled = currentIndex === 0;
    const isFinalRevision = selectedGoal === 'revision' && currentIndex === items.length - 1 && !hasMore;
    next.querySelector('span').textContent = isFinalRevision ? 'Finish Revision' : 'Next Sentence';
    queueVoiceCoach();
  }

  function scheduleMicRecovery(message = 'Listening again...', delay = 900) {
    window.clearTimeout(autoRecoveryTimer);
    if (!handsfree.checked || app.hidden || !currentItem() || voicePausedByUser) return;
    voiceRecoveryCount += 1;
    voiceStatus.textContent = message;
    autoRecoveryTimer = window.setTimeout(() => {
      if (!handsfree.checked || app.hidden || !currentItem() || voicePausedByUser || checking || recognition) return;
      if (voiceRecoveryCount >= 4) {
        voiceRecoveryCount = 0;
        runVoiceCoach(true);
        return;
      }
      startRecognition(true);
    }, Math.max(350, delay));
  }

  async function requestPracticePage(offset = 0) {
    if (listController) listController.abort();
    listController = new AbortController();
    const localRequestId = ++requestId;
    const params = new URLSearchParams({
      goal: selectedGoal,
      direction: selectedDirection,
      collection: String(activeCollectionId || root.dataset.defaultCollection || '0'),
      unit: '0',
      limit: '20',
      offset: String(Math.max(0, offset))
    });
    try {
      const response = await fetch(`material-practice-list-api.php?${params.toString()}`, {
        credentials: 'same-origin',
        signal: listController.signal,
        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
      });
      const data = await response.json().catch(() => ({}));
      if (localRequestId !== requestId) return null;
      if (!response.ok || !data.success) throw new Error(data.message || 'Could not load practice sentences.');
      return data;
    } finally {
      if (localRequestId === requestId) listController = null;
    }
  }

  function applyPracticePage(data, resetSession = false) {
    items = Array.isArray(data.items) ? data.items : [];
    csrfToken = String(data.csrf || csrfToken || '');
    activeCollectionId = Number(data.collection_id || activeCollectionId || 0);
    pageOffset = Number(data.offset || 0);
    nextPageOffset = Number(data.next_offset || (pageOffset + items.length));
    hasMore = Boolean(data.has_more);
    currentIndex = 0;
    if (resetSession) sessionNumber = 1;
  }

  async function continuePractice(autoMode = false) {
    if (continuing) return;
    continuing = true;
    cleanupVoiceOnly();
    voiceStatus.textContent = hasMore ? 'Loading the next sentences...' : 'Starting another practice round...';
    next.disabled = true;
    const targetOffset = hasMore ? nextPageOffset : 0;
    try {
      let data = await requestPracticePage(targetOffset);
      if (!data) return;
      if ((!Array.isArray(data.items) || !data.items.length) && targetOffset > 0 && selectedGoal !== 'revision') {
        data = await requestPracticePage(0);
      }
      if (!data) return;
      if (!Array.isArray(data.items) || !data.items.length) {
        renderComplete();
        return;
      }
      applyPracticePage(data, false);
      renderQuestion();
    } catch (error) {
      if (error && error.name === 'AbortError') return;
      voiceStatus.textContent = 'Connection was interrupted. Practice will retry automatically.';
      result.hidden = false;
      result.className = 'wf143-result is-warning';
      result.innerHTML = '<strong>Still here</strong><p>The next sentences could not load yet. Your current practice is safe.</p>';
      if (autoMode && handsfree.checked && !voicePausedByUser) {
        window.clearTimeout(autoRecoveryTimer);
        autoRecoveryTimer = window.setTimeout(() => continuePractice(true), 1800);
      }
    } finally {
      continuing = false;
      next.disabled = false;
    }
  }

  function moveNext(autoMode = false) {
    if (currentIndex < items.length - 1) {
      currentIndex += 1;
      sessionNumber += 1;
      renderQuestion();
      return;
    }
    if (selectedGoal === 'revision' && !hasMore) {
      renderComplete();
      return;
    }
    sessionNumber += 1;
    continuePractice(autoMode);
  }

  function renderComplete() {
    cleanupInteractiveState();
    questionCard.hidden = true;
    answerPanel.hidden = true;
    navigation.hidden = true;
    meter.style.width = '100%';
    counter.textContent = `Sentence ${Math.max(1, sessionNumber)}`;
    result.hidden = false;
    result.className = 'wf143-result is-correct is-complete';
    result.innerHTML = '<span class="wf143-result-icon"><i class="fa-solid fa-trophy" aria-hidden="true"></i></span><div><strong>Practice complete</strong><p>You completed this sentence set.</p><button type="button" id="practiceRestart"><i class="fa-solid fa-rotate-right" aria-hidden="true"></i><span>Practise Again</span></button></div>';
    $('practiceRestart')?.addEventListener('click', () => {
      currentIndex = 0;
      renderQuestion();
    }, { once: true });
  }

  async function loadPractice() {
    cleanupInteractiveState();
    activeCollectionId = Number(root.dataset.defaultCollection || 0);
    pageOffset = 0;
    nextPageOffset = 0;
    hasMore = false;
    sessionNumber = 1;
    voiceRecoveryCount = 0;
    voicePausedByUser = false;
    setScreen('loading');
    try {
      const data = await requestPracticePage(0);
      if (!data) return;
      applyPracticePage(data, true);
      if (!items.length) {
        if (selectedGoal === 'revision' && data.requires_login) {
          throw new Error('Student login is required to open saved wrong answers.');
        }
        throw new Error(selectedGoal === 'revision'
          ? 'No saved wrong answers are available yet. Complete normal practice first.'
          : 'No published practice sentences are available for this mode.');
      }
      setScreen('app');
      renderQuestion();
      app.scrollIntoView({ block: 'start' });
    } catch (error) {
      if (error && error.name === 'AbortError') return;
      setScreen('error', error instanceof Error ? error.message : 'Could not load practice sentences.');
    }
  }

  function startRecognition(autoMode = false) {
    const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!Recognition) {
      voiceStatus.textContent = 'Speech recognition is not supported here. Use Chrome or Edge, or type your answer.';
      return;
    }
    stopSpeech();
    stopRecognition('', true);
    const item = currentItem();
    if (!item) return;
    const qa = questionAndAnswer(item);
    const instance = new Recognition();
    recognition = instance;
    instance.lang = answerLanguage(qa);
    instance.continuous = false;
    instance.interimResults = true;
    instance.maxAlternatives = 1;
    let finalText = '';
    let repeatRequested = false;
    let recognitionError = '';
    let recognitionErrorCode = '';
    let ended = false;
    speak.disabled = true;
    speak.setAttribute('aria-busy', 'true');
    speak.innerHTML = '<i class="fa-solid fa-headphones" aria-hidden="true"></i><span>Listening</span>';
    stop.hidden = false;
    voiceStatus.textContent = autoMode ? 'Listening automatically. Speak one clear answer.' : 'Listening. Speak one clear answer.';
    instance.onresult = (event) => {
      let interim = '';
      for (let index = event.resultIndex; index < event.results.length; index += 1) {
        const transcript = event.results[index][0]?.transcript || '';
        if (event.results[index].isFinal) finalText = `${finalText} ${transcript}`.trim();
        else interim += transcript;
      }
      const captured = `${finalText} ${interim}`.trim();
      answer.value = captured;
      voiceStatus.textContent = captured ? `Heard: ${captured}` : 'Listening...';
      if (finalText) voiceRecoveryCount = 0;
      if (finalText && isRepeatCommand(finalText)) {
        repeatRequested = true;
        answer.value = '';
        voiceStatus.textContent = 'Repeating the question...';
        try { instance.stop(); } catch (error) {}
      }
    };
    instance.onspeechend = () => { try { instance.stop(); } catch (error) {} };
    instance.onerror = (event) => {
      const messages = {
        'not-allowed': 'Microphone permission was blocked. Allow it in the browser or type your answer.',
        'audio-capture': 'No microphone was found. Connect a microphone or type your answer.',
        'network': 'Voice recognition could not connect. Try again or type your answer.',
        'no-speech': 'No voice was heard. Tap Speak answer and try again.'
      };
      recognitionErrorCode = String(event.error || '');
      recognitionError = messages[event.error] || 'Voice capture paused for a moment.';
      voiceStatus.textContent = recognitionError;
    };
    instance.onend = () => {
      if (ended) return;
      ended = true;
      window.clearTimeout(recognitionTimer);
      recognitionTimer = null;
      if (recognition === instance) recognition = null;
      resetMicButtons();
      if (recognitionError) {
        voiceStatus.textContent = recognitionError;
        const recoverable = ['no-speech', 'network', 'aborted'].includes(recognitionErrorCode);
        if (autoMode && handsfree.checked && recoverable && !voicePausedByUser) {
          scheduleMicRecovery(
            recognitionErrorCode === 'no-speech' ? 'I did not hear you. Listening again...' : 'Voice paused briefly. Reconnecting...',
            recognitionErrorCode === 'network' ? 1600 : 850
          );
        }
        return;
      }
      if (repeatRequested && handsfree.checked) {
        voiceCoachTimer = window.setTimeout(() => runVoiceCoach(true), 350);
        return;
      }
      const captured = answer.value.trim();
      if (!captured) {
        voiceStatus.textContent = 'No voice was captured.';
        if (autoMode && handsfree.checked && !voicePausedByUser) {
          scheduleMicRecovery('I did not hear an answer. Listening again...', 850);
        } else {
          voiceStatus.textContent = 'No voice was captured. Try again or type your answer.';
        }
        return;
      }
      if (autoMode && handsfree.checked) {
        voiceStatus.textContent = 'Voice captured. Checking your answer...';
        autoCheckTimer = window.setTimeout(() => checkAnswer('voice'), 450);
      } else {
        voiceStatus.textContent = 'Voice captured. Tap Check Answer when ready.';
      }
    };
    recognitionTimer = window.setTimeout(() => {
      if (recognition === instance) {
        try { instance.stop(); } catch (error) {}
      }
    }, 30000);
    try { instance.start(); }
    catch (error) {
      recognition = null;
      resetMicButtons();
      if (autoMode && handsfree.checked && !voicePausedByUser) {
        scheduleMicRecovery('Microphone paused briefly. Trying again...', 1200);
      } else {
        voiceStatus.textContent = 'Microphone could not start. Please tap Speak answer and try again.';
      }
    }
  }

  function listenCorrectAnswer(text, language, callbacks = {}) {
    const onEnd = typeof callbacks.onEnd === 'function' ? callbacks.onEnd : null;
    const onError = typeof callbacks.onError === 'function' ? callbacks.onError : null;
    voiceStatus.textContent = 'Playing the correct answer...';
    speakText(text, language, {
      onStart: () => { voiceStatus.textContent = 'Playing the correct answer...'; },
      onEnd: () => {
        voiceStatus.textContent = onEnd ? 'Correct answer played. Now say it again.' : 'Correct answer played. Repeat it once, then continue.';
        if (onEnd) onEnd();
      },
      onError: () => {
        voiceStatus.textContent = onError ? 'Audio was unavailable. Try speaking the answer again.' : 'The correct answer could not be played in this browser.';
        if (onError) onError();
      }
    });
  }

  function advanceAfterVoiceCorrect() {
    const item = currentItem();
    if (!item || !handsfree.checked || app.hidden || voicePausedByUser) return;
    const expectedIndex = currentIndex;
    const expectedId = String(item.id || '');
    let advanced = false;
    const advance = () => {
      if (advanced || !handsfree.checked || app.hidden || voicePausedByUser || currentIndex !== expectedIndex) return;
      const now = currentItem();
      if (!now || String(now.id || '') !== expectedId) return;
      advanced = true;
      moveNext(true);
    };
    const queueAdvance = () => {
      window.clearTimeout(autoCheckTimer);
      autoCheckTimer = window.setTimeout(advance, 320);
    };
    voiceStatus.textContent = 'Correct. Moving to the next sentence...';
    const spoken = speakText('Correct. Next sentence.', 'en-IN', { onEnd: queueAdvance, onError: queueAdvance });
    if (!spoken) queueAdvance();
  }

  function retryVoiceAfterWrong(correctAnswer, language) {
    const item = currentItem();
    if (!item || !handsfree.checked || app.hidden || voicePausedByUser) return;
    const expectedIndex = currentIndex;
    const expectedId = String(item.id || '');
    const retry = () => {
      if (!handsfree.checked || app.hidden || voicePausedByUser || currentIndex !== expectedIndex) return;
      const now = currentItem();
      if (!now || String(now.id || '') !== expectedId) return;
      answer.value = '';
      voiceRecoveryCount = 0;
      voiceStatus.textContent = 'Now say the answer again.';
      window.clearTimeout(voiceCoachTimer);
      voiceCoachTimer = window.setTimeout(() => {
        if (handsfree.checked && !app.hidden && !voicePausedByUser && currentIndex === expectedIndex) startRecognition(true);
      }, 520);
    };
    answer.value = '';
    voiceStatus.textContent = 'Not correct yet. Listen carefully, then say it again.';
    listenCorrectAnswer(correctAnswer, language, { onEnd: retry, onError: retry });
  }

  async function checkAnswer(source = 'manual') {
    const item = currentItem();
    const value = answer.value.trim();
    if (!item || checking) return;
    if (isRepeatCommand(value)) {
      answer.value = '';
      runVoiceCoach(true);
      return;
    }
    if (value.length < 2) {
      result.hidden = false;
      result.className = 'wf143-result is-warning';
      result.innerHTML = '<strong>Write or speak an answer first.</strong>';
      answer.focus();
      return;
    }
    stopSpeech();
    stopRecognition('', true);
    checking = true;
    check.disabled = true;
    check.setAttribute('aria-busy', 'true');
    check.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i><span>Checking</span>';
    result.hidden = false;
    result.className = 'wf143-result is-checking';
    result.innerHTML = '<strong>Checking your answer...</strong>';
    answerController = new AbortController();
    const localAnswerController = answerController;
    let answerTimedOut = false;
    const answerTimeout = window.setTimeout(() => {
      if (answerController === localAnswerController) {
        answerTimedOut = true;
        try { localAnswerController.abort(); } catch (error) {}
      }
    }, 12000);
    const qa = questionAndAnswer(item);
    const body = new FormData();
    body.append('csrf_token', csrfToken);
    body.append('pair_id', String(item.id || 0));
    body.append('direction', qa.direction);
    body.append('answer', value);
    try {
      const response = await fetch('material-practice-api.php', {
        method: 'POST', body, credentials: 'same-origin', signal: localAnswerController.signal,
        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
      });
      const data = await response.json().catch(() => ({}));
      if (!response.ok || !data.success) {
        const requestError = new Error(data.message || 'Could not check this answer.');
        requestError.status = response.status;
        throw requestError;
      }
      const checked = data.result || {};
      const correct = Boolean(checked.is_correct);
      const correctAnswer = String(checked.correct_answer || qa.answer || '').trim();
      result.className = `wf143-result ${correct ? 'is-correct' : 'is-wrong'}`;
      if (correct) {
        result.innerHTML = '<span class="wf143-result-icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span><div><strong>Correct answer</strong><p>Your sentence matches the accepted answer.</p></div>';
        voiceStatus.textContent = source === 'voice' && handsfree.checked ? 'Correct. Moving to the next sentence...' : 'Correct. Continue when you are ready.';
        if (source === 'voice' && handsfree.checked) advanceAfterVoiceCorrect();
      } else {
        result.innerHTML = `<span class="wf143-result-icon"><i class="fa-solid fa-lightbulb" aria-hidden="true"></i></span><div><strong>Review this answer</strong><p>${escapeHtml(checked.feedback || 'Use the same tense and sentence order.')}</p><div class="wf143-correct-answer"><span>Correct answer</span><b>${escapeHtml(correctAnswer)}</b></div><button type="button" class="wf144-listen-correct"><i class="fa-solid fa-volume-high" aria-hidden="true"></i><span>Listen Correct Answer</span></button></div>`;
        result.querySelector('.wf144-listen-correct')?.addEventListener('click', () => listenCorrectAnswer(correctAnswer, answerLanguage(qa)));
        voiceStatus.textContent = 'Answer needs improvement. Stay on this sentence and try again.';
        if (source === 'voice' && handsfree.checked && correctAnswer) {
          window.clearTimeout(autoCheckTimer);
          autoCheckTimer = window.setTimeout(() => retryVoiceAfterWrong(correctAnswer, answerLanguage(qa)), 420);
        }
      }
    } catch (error) {
      if (error && error.name === 'AbortError' && !answerTimedOut) return;
      const message = answerTimedOut
        ? 'Checking took too long. The same sentence will continue.'
        : (error instanceof Error ? error.message : 'Please try again.');
      result.className = 'wf143-result is-warning';
      result.innerHTML = `<strong>Practice is still running</strong><p>${escapeHtml(message)}</p>`;
      const status = Number(error && error.status ? error.status : 0);
      if (source === 'voice' && handsfree.checked && !voicePausedByUser) {
        if (status === 419) {
          voiceStatus.textContent = 'Refreshing the practice session...';
          window.clearTimeout(autoRecoveryTimer);
          autoRecoveryTimer = window.setTimeout(async () => {
            try {
              const refreshed = await requestPracticePage(pageOffset);
              if (refreshed && refreshed.csrf) {
                csrfToken = String(refreshed.csrf);
                checkAnswer('voice');
                return;
              }
            } catch (refreshError) {}
            scheduleMicRecovery('Session refreshed. Listening again on the same sentence...', 900);
          }, 650);
        } else if (status === 429) {
          voiceStatus.textContent = 'Short safety pause. Checking the same answer again...';
          window.clearTimeout(autoRecoveryTimer);
          autoRecoveryTimer = window.setTimeout(() => checkAnswer('voice'), 3200);
        } else {
          scheduleMicRecovery('Checking was interrupted. Listening again on the same sentence...', 1400);
        }
      } else {
        voiceStatus.textContent = 'Answer checking failed. Please try again.';
      }
    } finally {
      window.clearTimeout(answerTimeout);
      checking = false;
      if (answerController === localAnswerController) answerController = null;
      check.disabled = false;
      check.removeAttribute('aria-busy');
      check.innerHTML = '<i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Check Answer</span>';
    }
  }

  modeButtons.forEach((button) => button.addEventListener('click', () => {
    selectMode(button.dataset.goal || 'speak', button.dataset.direction || 'hindi_to_english');
    loadPractice();
  }));
  retryLoad.addEventListener('click', loadPractice);
  changeMode.addEventListener('click', () => {
    cleanupInteractiveState();
    if (listController) listController.abort();
    setScreen('ready');
    root.scrollIntoView({ block: 'start', behavior: 'smooth' });
  });
  handsfree.addEventListener('change', () => {
    storeVoicePreference();
    cleanupVoiceOnly();
    voicePausedByUser = false;
    voiceRecoveryCount = 0;
    if (handsfree.checked && !app.hidden && currentItem()) queueVoiceCoach(250);
    else voiceStatus.textContent = 'Voice coach is off. Use Listen or Speak answer manually.';
  });
  listen.addEventListener('click', () => {
    cleanupVoiceOnly();
    const item = currentItem();
    if (!item) return;
    const qa = questionAndAnswer(item);
    voiceStatus.textContent = 'Playing the question...';
    speakText(qa.question, questionLanguage(qa), {
      onStart: () => { voiceStatus.textContent = 'Playing the question...'; },
      onEnd: () => { voiceStatus.textContent = 'Question played. Speak or type your answer.'; },
      onError: () => { voiceStatus.textContent = 'Question audio is unavailable. You can still type or use the microphone.'; }
    });
  });
  speak.addEventListener('click', () => {
    cleanupVoiceOnly();
    voicePausedByUser = false;
    voiceRecoveryCount = 0;
    startRecognition(false);
  });
  stop.addEventListener('click', () => {
    voicePausedByUser = true;
    cleanupVoiceOnly();
    voiceStatus.textContent = 'Voice paused. Tap Speak answer to resume, or turn Voice coach off.';
  });
  check.addEventListener('click', () => checkAnswer('manual'));
  clear.addEventListener('click', () => {
    cleanupVoiceOnly();
    answer.value = '';
    result.hidden = true;
    voiceStatus.textContent = handsfree.checked ? 'Answer cleared. Tap Speak answer to try again.' : 'Answer cleared.';
    answer.focus();
  });
  previous.addEventListener('click', () => {
    if (currentIndex > 0) {
      currentIndex -= 1;
      renderQuestion();
    }
  });
  next.addEventListener('click', () => moveNext(false));
  window.addEventListener('pagehide', cleanupInteractiveState, { once: true });
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      cleanupVoiceOnly();
      return;
    }
    if (handsfree.checked && !app.hidden && currentItem() && !voicePausedByUser) {
      voiceStatus.textContent = 'Welcome back. Voice coach is resuming...';
      queueVoiceCoach(700);
    }
  });
  window.addEventListener('pageshow', () => {
    if (handsfree.checked && !app.hidden && currentItem() && !voicePausedByUser && !recognition && !checking) {
      queueVoiceCoach(650);
    }
  });
  restoreVoicePreference();
  selectMode(selectedGoal, selectedDirection);
  setScreen('ready');
})();
