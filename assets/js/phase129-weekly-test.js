(function () {
  'use strict';
  const dataNode = document.getElementById('wfWeeklyTestData');
  if (!dataNode) return;
  let config;
  try { config = JSON.parse(dataNode.textContent || '{}'); } catch (_) { return; }

  const pools = config.pools || {};
  const isStudent = Boolean(config.isStudent);
  const setup = document.getElementById('wfTestSetup');
  const select = document.getElementById('wfTestPaper');
  const startButton = document.getElementById('wfStartTest');
  const message = document.getElementById('wfTestMessage');
  const nameInput = document.getElementById('wfGuestName');
  const phoneInput = document.getElementById('wfGuestPhone');
  let currentType = 'basic';
  let currentTest = null;

  const typeInfo = {
    basic: { label: 'Basic Test', icon: 'fa-solid fa-seedling' },
    previous: { label: 'Previous Test', icon: 'fa-solid fa-clock-rotate-left' },
    upcoming: { label: 'Upcoming Test', icon: 'fa-solid fa-calendar-check' }
  };

  function cleanPhone(value) { return String(value || '').replace(/\D+/g, '').slice(0, 10); }
  function normalized(test) {
    test = test || {};
    return {
      id: Number(test.id || 0),
      title: String(test.title || 'Test paper'),
      type: String(test.test_type || currentType),
      status: String(test.status || '').toLowerCase(),
      ready: Number(test.ready_now || 0),
      duration: Number(test.duration_minutes || 0),
      questions: Number(test.question_count || 0),
      requiresLogin: String(test.requires_login || 'No') === 'Yes',
      batch: String(test.batch_label || test.batch_name || ''),
      starts: String(test.starts_at || ''),
      ends: String(test.ends_at || '')
    };
  }
  function preferred(list) {
    return list.find(item => Number(item.ready_now || 0) === 1)
      || list.find(item => String(item.status || '').toLowerCase() === 'active')
      || list[0] || null;
  }
  function formatDate(value) {
    if (!value) return '';
    const date = new Date(String(value).replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return '';
    return new Intl.DateTimeFormat('en-IN', { day: '2-digit', month: 'short', hour: 'numeric', minute: '2-digit' }).format(date);
  }
  function setMessage(text, state) {
    if (!message) return;
    message.textContent = text;
    message.classList.toggle('is-ready', state === 'ready');
    message.classList.toggle('is-error', state === 'error');
  }
  function validate() {
    let disabled = false;
    let text = 'Ready. Start Test par click karein.';
    let state = 'ready';
    if (!currentTest || !currentTest.id) {
      disabled = true; text = 'No test paper is available.'; state = 'error';
    } else if (currentTest.status !== 'active') {
      disabled = true; text = 'This paper is not active yet.'; state = 'error';
    } else if (currentTest.questions < 1) {
      disabled = true; text = 'Questions have not been published yet.'; state = 'error';
    } else if (currentTest.ready !== 1) {
      disabled = true;
      text = currentTest.starts ? 'Test opens ' + formatDate(currentTest.starts) + '.' : 'Test is outside its available time.';
      state = 'error';
    } else if ((currentType === 'upcoming' || currentTest.requiresLogin) && !isStudent) {
      disabled = true; text = 'Student login is required for this test.'; state = 'error';
    } else if (!isStudent) {
      const studentName = nameInput ? nameInput.value.trim() : '';
      const phone = phoneInput ? cleanPhone(phoneInput.value) : '';
      if (phoneInput) phoneInput.value = phone;
      if (studentName.length < 2 || phone.length !== 10) {
        disabled = true; text = 'Enter student name and a valid 10 digit mobile number.'; state = 'error';
      }
    }
    if (startButton) startButton.disabled = disabled;
    setMessage(text, state);
  }
  function updateSummary() {
    const info = typeInfo[currentType] || typeInfo.basic;
    const icon = document.getElementById('wfSelectedIcon');
    if (icon) icon.className = info.icon;
    const typeLabel = document.getElementById('wfSelectedType');
    if (typeLabel) typeLabel.textContent = info.label;
    const title = document.getElementById('wfSelectedTitle');
    const meta = document.getElementById('wfSelectedMeta');
    if (title) title.textContent = currentTest && currentTest.id ? currentTest.title : 'No test paper available';
    if (meta) {
      meta.textContent = currentTest && currentTest.id
        ? currentTest.questions + ' questions · ' + currentTest.duration + ' minutes' + (currentTest.batch ? ' · ' + currentTest.batch : '')
        : 'Admin can publish this paper from Weekly Tests.';
    }
    validate();
  }
  function renderPool() {
    const list = Array.isArray(pools[currentType]) ? pools[currentType] : [];
    if (!select) return;
    select.innerHTML = '';
    if (!list.length) {
      const option = document.createElement('option');
      option.value = '0'; option.textContent = 'No paper available';
      select.appendChild(option); select.disabled = true;
      currentTest = null; updateSummary(); return;
    }
    const best = preferred(list);
    list.forEach(item => {
      const test = normalized(item);
      const option = document.createElement('option');
      option.value = String(test.id);
      option.textContent = test.title + ' · ' + test.questions + 'Q · ' + test.duration + ' min';
      option.selected = best && Number(best.id) === test.id;
      select.appendChild(option);
    });
    select.disabled = list.length === 1;
    currentTest = normalized(best);
    updateSummary();
  }
  function openSetup(type, trigger) {
    currentType = ['basic', 'previous', 'upcoming'].includes(type) ? type : 'basic';
    document.querySelectorAll('[data-test-card]').forEach(card => card.classList.toggle('is-selected', card.dataset.testCard === currentType));
    document.querySelectorAll('[data-select-test]').forEach(button => button.setAttribute('aria-pressed', button === trigger ? 'true' : 'false'));
    renderPool();
    if (!setup) return;
    setup.hidden = false;
    requestAnimationFrame(() => setup.classList.add('is-open'));
    setup.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
  function closeSetup() {
    if (!setup) return;
    setup.classList.remove('is-open');
    window.setTimeout(() => { setup.hidden = true; }, 180);
    document.querySelectorAll('[data-test-card]').forEach(card => card.classList.remove('is-selected'));
  }

  document.querySelectorAll('[data-select-test]').forEach(button => {
    button.addEventListener('click', () => openSetup(button.dataset.selectTest || 'basic', button));
  });
  document.getElementById('wfCloseTestSetup')?.addEventListener('click', closeSetup);
  select?.addEventListener('change', () => {
    const list = Array.isArray(pools[currentType]) ? pools[currentType] : [];
    currentTest = normalized(list.find(item => Number(item.id) === Number(select.value)) || list[0]);
    updateSummary();
  });
  [nameInput, phoneInput].filter(Boolean).forEach(input => {
    input.addEventListener('input', validate);
    input.addEventListener('change', validate);
  });

  startButton?.addEventListener('click', async () => {
    validate();
    if (startButton.disabled || !currentTest) return;
    const body = new FormData();
    body.append('csrf_token', String(config.csrf || ''));
    body.append('action', 'start');
    body.append('test_id', String(currentTest.id));
    body.append('test_type', currentTest.type);
    if (!isStudent) {
      body.append('guest_name', nameInput ? nameInput.value.trim() : '');
      body.append('guest_phone', phoneInput ? cleanPhone(phoneInput.value) : '');
    }
    startButton.disabled = true;
    setMessage('Secure test room is opening...', 'ready');
    try {
      const response = await fetch('weekly-test-api.php', { method: 'POST', body, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const result = await response.json();
      if (!result.success) {
        if (result.login_required) {
          window.location.href = 'student-auth.php?redirect=weekly-test.php';
          return;
        }
        throw new Error(result.message || 'Test could not start.');
      }
      window.location.href = 'weekly-exam-room.php?attempt_id=' + encodeURIComponent(result.attempt_id) + '&token=' + encodeURIComponent(result.access_token);
    } catch (error) {
      setMessage(error && error.message ? error.message : 'Network error. Please try again.', 'error');
      validate();
    }
  });
})();
