(function () {
  'use strict';

  const dataNode = document.getElementById('wfWeeklyTestData');
  const form = document.getElementById('wfTestSetup');
  if (!dataNode || !form) return;

  let config = {};
  try { config = JSON.parse(dataNode.textContent || '{}'); } catch (_) { config = {}; }

  const pools = config.pools || {};
  const isStudent = Boolean(config.isStudent);
  const select = document.getElementById('wfTestPaper');
  const typeInput = document.getElementById('wfSelectedTestTypeInput');
  const startButton = document.getElementById('wfStartTest');
  const message = document.getElementById('wfTestMessage');
  const nameInput = document.getElementById('wfGuestName');
  const phoneInput = document.getElementById('wfGuestPhone');
  let currentType = ['basic', 'previous', 'upcoming'].includes(config.requestedType) ? config.requestedType : 'basic';
  let currentTest = null;

  const typeInfo = {
    basic: { label: 'Basic Test', icon: 'fa-solid fa-seedling' },
    previous: { label: 'Previous Test', icon: 'fa-solid fa-clock-rotate-left' },
    upcoming: { label: 'Upcoming Test', icon: 'fa-solid fa-calendar-check' }
  };

  const cleanPhone = (value) => String(value || '').replace(/\D+/g, '').slice(0, 10);
  const normalize = (test) => {
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
  };
  const preferred = (list) => list.find((item) => Number(item.ready_now || 0) === 1)
    || list.find((item) => String(item.status || '').toLowerCase() === 'active')
    || list[0] || null;

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
      disabled = true;
      text = 'No test paper is available.';
      state = 'error';
    } else if (currentTest.status !== 'active') {
      disabled = true;
      text = 'This paper is not active yet.';
      state = 'error';
    } else if (currentTest.questions < 1) {
      disabled = true;
      text = 'Questions have not been published yet.';
      state = 'error';
    } else if (currentTest.ready !== 1) {
      disabled = true;
      text = currentTest.starts ? 'Test opens ' + formatDate(currentTest.starts) + '.' : 'Test is outside its available time.';
      state = 'error';
    } else if ((currentType === 'upcoming' || currentTest.requiresLogin) && !isStudent) {
      disabled = true;
      text = 'Student login is required for this test.';
      state = 'error';
    } else if (!isStudent) {
      const studentName = nameInput ? nameInput.value.trim() : '';
      const phone = phoneInput ? cleanPhone(phoneInput.value) : '';
      if (phoneInput) phoneInput.value = phone;
      if (studentName.length < 2 || phone.length !== 10) {
        disabled = true;
        text = 'Enter student name and a valid 10 digit mobile number.';
        state = 'error';
      }
    }

    if (startButton) startButton.disabled = disabled;
    setMessage(text, state);
    return !disabled;
  }

  function updateSummary() {
    const info = typeInfo[currentType] || typeInfo.basic;
    const icon = document.getElementById('wfSelectedIcon');
    const typeLabel = document.getElementById('wfSelectedType');
    const title = document.getElementById('wfSelectedTitle');
    const meta = document.getElementById('wfSelectedMeta');

    if (icon) icon.className = info.icon;
    if (typeLabel) typeLabel.textContent = info.label;
    if (typeInput) typeInput.value = currentType;
    if (title) title.textContent = currentTest && currentTest.id ? currentTest.title : 'No test paper available';
    if (meta) {
      meta.textContent = currentTest && currentTest.id
        ? currentTest.questions + ' questions · ' + currentTest.duration + ' minutes' + (currentTest.batch ? ' · ' + currentTest.batch : '')
        : 'Admin can publish this paper from Weekly Tests.';
    }
    validate();
  }

  function renderPool(preferredId) {
    const list = Array.isArray(pools[currentType]) ? pools[currentType] : [];
    if (!select) return;
    select.innerHTML = '';

    if (!list.length) {
      const option = document.createElement('option');
      option.value = '';
      option.textContent = 'No paper available';
      select.appendChild(option);
      select.disabled = true;
      currentTest = null;
      updateSummary();
      return;
    }

    const selectedRaw = list.find((item) => Number(item.id) === Number(preferredId)) || preferred(list);
    list.forEach((item) => {
      const test = normalize(item);
      const option = document.createElement('option');
      option.value = String(test.id);
      option.textContent = test.title + ' · ' + test.questions + 'Q · ' + test.duration + ' min';
      option.selected = selectedRaw && Number(selectedRaw.id) === test.id;
      select.appendChild(option);
    });
    select.disabled = false;
    currentTest = normalize(selectedRaw);
    updateSummary();
  }

  function openSetup(type, trigger, preferredId) {
    currentType = ['basic', 'previous', 'upcoming'].includes(type) ? type : 'basic';
    document.querySelectorAll('[data-test-card]').forEach((card) => card.classList.toggle('is-selected', card.dataset.testCard === currentType));
    document.querySelectorAll('[data-select-test]').forEach((button) => button.setAttribute('aria-current', button === trigger ? 'true' : 'false'));
    renderPool(preferredId);
    form.hidden = false;
    requestAnimationFrame(() => form.classList.add('is-open'));
    window.setTimeout(() => form.scrollIntoView({ behavior: 'smooth', block: 'center' }), 60);
  }

  function closeSetup() {
    form.classList.remove('is-open');
    window.setTimeout(() => { form.hidden = true; }, 180);
    document.querySelectorAll('[data-test-card]').forEach((card) => card.classList.remove('is-selected'));
  }

  document.querySelectorAll('[data-select-test]').forEach((link) => {
    if (link.getAttribute('aria-disabled') === 'true') {
      link.addEventListener('click', (event) => event.preventDefault());
      return;
    }
    link.addEventListener('click', (event) => {
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
      event.preventDefault();
      const url = new URL(link.href, window.location.href);
      const preferredId = Number(url.searchParams.get('test_id') || 0);
      openSetup(link.dataset.selectTest || 'basic', link, preferredId);
      window.history.replaceState({}, '', url.pathname + url.search + '#wfTestSetup');
    });
  });

  document.getElementById('wfCloseTestSetup')?.addEventListener('click', closeSetup);
  select?.addEventListener('change', () => {
    const list = Array.isArray(pools[currentType]) ? pools[currentType] : [];
    currentTest = normalize(list.find((item) => Number(item.id) === Number(select.value)) || list[0]);
    updateSummary();
  });
  [nameInput, phoneInput].filter(Boolean).forEach((input) => {
    input.addEventListener('input', validate);
    input.addEventListener('change', validate);
  });

  if (!form.hidden) {
    renderPool(select ? Number(select.value || 0) : 0);
  }

  form.addEventListener('submit', async (event) => {
    if (form.dataset.nativeSubmit === '1') return;
    event.preventDefault();
    if (!validate() || !currentTest) return;

    const body = new FormData(form);
    body.set('action', 'start');
    body.set('test_id', String(currentTest.id));
    body.set('test_type', currentTest.type);
    if (!isStudent) {
      body.set('guest_name', nameInput ? nameInput.value.trim() : '');
      body.set('guest_phone', phoneInput ? cleanPhone(phoneInput.value) : '');
    }

    if (startButton) startButton.disabled = true;
    setMessage('Secure test room is opening...', 'ready');

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body,
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });
      const contentType = response.headers.get('content-type') || '';
      if (!contentType.includes('application/json')) throw new Error('Native fallback');
      const result = await response.json();
      if (!response.ok || !result.success) {
        if (result.result_url) {
          window.location.href = String(result.result_url);
          return;
        }
        if (result.login_required) {
          window.location.href = 'student-auth.php?redirect=' + encodeURIComponent('weekly-test.php?type=' + currentType);
          return;
        }
        throw new Error(result.message || 'Test could not start.');
      }
      window.location.href = 'weekly-exam-room.php?attempt_id=' + encodeURIComponent(result.attempt_id) + '&token=' + encodeURIComponent(result.access_token);
    } catch (error) {
      if (error && error.message === 'Native fallback') {
        form.dataset.nativeSubmit = '1';
        HTMLFormElement.prototype.submit.call(form);
        return;
      }
      setMessage(error && error.message ? error.message : 'Network error. Please try again.', 'error');
      validate();
    }
  });
})();
