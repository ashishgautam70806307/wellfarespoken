(function () {
  'use strict';

  var STORAGE_PREFIX = 'wf149-admin-draft:';
  var MAX_AGE = 60 * 60 * 1000; // same tab, one hour

  function qs(selector, root) { return (root || document).querySelector(selector); }
  function esc(value) { if (window.CSS && typeof window.CSS.escape === 'function') return window.CSS.escape(String(value)); return String(value).replace(/([\"'\[\]#.:>+~*=\s])/g, '\\$1'); }
  function qsa(selector, root) { return Array.prototype.slice.call((root || document).querySelectorAll(selector)); }

  function formKey(form, index) {
    var explicit = form.getAttribute('data-form-draft-key') || form.id || '';
    var idField = qs('input[name="id"]', form);
    var suffix = explicit || ('form-' + index);
    if (idField && idField.value) suffix += ':id-' + String(idField.value);
    return STORAGE_PREFIX + location.pathname + ':' + suffix;
  }

  function isDraftForm(form) {
    if (!form || String(form.method || '').toLowerCase() !== 'post') return false;
    if (form.hasAttribute('data-no-form-draft')) return false;
    var action = qs('input[name="action"]', form);
    var actionValue = action ? String(action.value || '').toLowerCase() : '';
    if (['delete', 'status', 'toggle', 'bulk', 'publish', 'remove'].indexOf(actionValue) !== -1) return false;
    var editable = qsa('input:not([type="hidden"]):not([type="submit"]):not([type="button"]),select,textarea', form)
      .filter(function (field) { return field.type !== 'file' && field.type !== 'password'; });
    return editable.length >= 2;
  }

  function collect(form) {
    var values = {};
    qsa('input,select,textarea', form).forEach(function (field) {
      if (!field.name || field.disabled) return;
      var type = String(field.type || '').toLowerCase();
      if (['password', 'file', 'submit', 'button', 'reset'].indexOf(type) !== -1) return;
      if (field.name === 'csrf_token' || field.name === 'action' || field.name === 'id') return;
      if (type === 'checkbox' || type === 'radio') {
        if (!values[field.name]) values[field.name] = [];
        if (field.checked) values[field.name].push(field.value || '1');
        return;
      }
      values[field.name] = field.value;
    });
    return { savedAt: Date.now(), values: values };
  }

  function save(form, key) {
    try { sessionStorage.setItem(key, JSON.stringify(collect(form))); } catch (e) {}
  }

  function read(key) {
    try {
      var raw = sessionStorage.getItem(key);
      if (!raw) return null;
      var data = JSON.parse(raw);
      if (!data || !data.savedAt || Date.now() - data.savedAt > MAX_AGE) {
        sessionStorage.removeItem(key);
        return null;
      }
      return data;
    } catch (e) { return null; }
  }

  function restore(form, data) {
    if (!data || !data.values) return false;
    var changed = false;
    Object.keys(data.values).forEach(function (name) {
      var fields = qsa('[name="' + esc(name) + '"]', form);
      if (!fields.length) return;
      var stored = data.values[name];
      fields.forEach(function (field) {
        var type = String(field.type || '').toLowerCase();
        if (type === 'checkbox' || type === 'radio') {
          var list = Array.isArray(stored) ? stored : [stored];
          field.checked = list.indexOf(field.value || '1') !== -1;
        } else if (stored !== undefined && stored !== null) {
          field.value = String(stored);
        }
        field.dispatchEvent(new Event('change', { bubbles: true }));
        changed = true;
      });
    });
    return changed;
  }

  function showRestoreNotice() {
    if (qs('[data-wf149-draft-notice]')) return;
    var notice = document.createElement('div');
    notice.className = 'wf149-draft-notice';
    notice.setAttribute('data-wf149-draft-notice', '');
    notice.innerHTML = '<i class="fa-solid fa-rotate-left" aria-hidden="true"></i><span><b>Your form entries were restored.</b> Fix the highlighted problem and submit again.</span>';
    var main = qs('.admin-main');
    var topbar = qs('.admin-topbar');
    if (main) main.insertBefore(notice, topbar ? topbar.nextSibling : main.firstChild);
  }

  function hasErrorState() {
    return !!qs('.alert-danger,.alert-error,.toast-error,[data-form-error="1"]');
  }

  function isReload() {
    try {
      var nav = performance.getEntriesByType && performance.getEntriesByType('navigation')[0];
      return nav ? nav.type === 'reload' : false;
    } catch (e) { return false; }
  }

  function setupDrafts() {
    var forms = qsa('form').filter(isDraftForm);
    forms.forEach(function (form, index) {
      var key = formKey(form, index);
      var timer = 0;
      var doSave = function () {
        window.clearTimeout(timer);
        timer = window.setTimeout(function () { save(form, key); }, 120);
      };
      form.addEventListener('input', doSave);
      form.addEventListener('change', doSave);
      form.addEventListener('submit', function () { save(form, key); });
      form.addEventListener('reset', function () { try { sessionStorage.removeItem(key); } catch (e) {} });

      var draft = read(key);
      if (draft && (hasErrorState() || isReload() || form.hasAttribute('data-restore-draft'))) {
        if (restore(form, draft)) showRestoreNotice();
      }
    });

    if (qs('.alert-success,.toast-success')) {
      try {
        Object.keys(sessionStorage).forEach(function (key) {
          if (key.indexOf(STORAGE_PREFIX + location.pathname + ':') === 0) sessionStorage.removeItem(key);
        });
      } catch (e) {}
    }
  }

  function previewTarget(input) {
    var id = input.getAttribute('data-preview-target');
    if (id) return document.getElementById(id);
    var selector = input.getAttribute('data-preview');
    if (selector) {
      try { return qs(selector); } catch (e) {}
    }
    var row = input.closest('.admission-photo-row');
    if (row) return qs('.admission-photo-preview', row);
    return null;
  }

  function renderPreview(input, file) {
    if (!file || !String(file.type || '').match(/^image\//i)) return;
    var target = previewTarget(input);
    var reader = new FileReader();
    reader.onload = function (event) {
      if (!event.target || !event.target.result) return;
      if (!target) {
        target = document.createElement('div');
        target.className = 'wf149-live-image-preview';
        input.insertAdjacentElement('afterend', target);
      }
      target.innerHTML = '<img src="' + String(event.target.result).replace(/"/g, '&quot;') + '" alt="Selected image preview">';
      target.classList.add('is-live-preview');
    };
    reader.readAsDataURL(file);
  }

  function setupImagePreviews() {
    qsa('input[type="file"]').forEach(function (input) {
      var accept = String(input.getAttribute('accept') || '').toLowerCase();
      if (!(accept.indexOf('image') !== -1 || /\.(png|jpe?g|gif|webp|svg|ico)/.test(accept))) return;
      input.addEventListener('change', function () {
        var file = input.files && input.files[0];
        if (file) renderPreview(input, file);
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    setupDrafts();
    setupImagePreviews();
  });
})();
