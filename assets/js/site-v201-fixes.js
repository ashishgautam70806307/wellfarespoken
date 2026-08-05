(() => {
  'use strict';

  document.querySelectorAll('[data-go-back]').forEach((button) => {
    button.addEventListener('click', () => {
      if (window.history.length > 1 && document.referrer) {
        window.history.back();
      } else {
        window.location.href = 'index.php';
      }
    });
  });

  document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const shell = button.closest('.password-shell');
      const input = shell ? shell.querySelector('input') : null;
      if (!input) return;
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      const icon = button.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-eye', !show);
        icon.classList.toggle('fa-eye-slash', show);
      }
    });
  });

  document.querySelectorAll('.faq-list').forEach((list) => {
    list.querySelectorAll('details').forEach((item) => {
      item.addEventListener('toggle', () => {
        if (!item.open) return;
        list.querySelectorAll('details[open]').forEach((other) => {
          if (other !== item) other.open = false;
        });
      });
    });
  });
})();
