(function(){
  'use strict';
  document.addEventListener('click', function(event){
    var button = event.target.closest('[data-password-toggle]');
    if (!button) return;
    var id = button.getAttribute('data-password-toggle');
    var input = id ? document.getElementById(id) : null;
    if (!input) return;
    var showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';
    button.setAttribute('aria-pressed', showing ? 'false' : 'true');
    button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
    var icon = button.querySelector('i');
    if (icon) {
      icon.classList.toggle('fa-eye', showing);
      icon.classList.toggle('fa-eye-slash', !showing);
    }
    input.focus({preventScroll:true});
  });
})();
