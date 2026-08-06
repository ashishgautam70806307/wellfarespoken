(function(){
  'use strict';
  function syncIconField(shell){
    var control=shell.querySelector('input,select,textarea');
    if(!control)return;
    var update=function(){shell.classList.toggle('is-filled',String(control.value||'').trim()!=='');};
    control.addEventListener('input',update,{passive:true});
    control.addEventListener('change',update,{passive:true});
    update();
  }
  function init(){
    document.querySelectorAll('.wf129-input-icon,.wf-input-shell').forEach(syncIconField);
    document.querySelectorAll('[data-mobile-drawer] a').forEach(function(link){
      link.addEventListener('click',function(){
        var close=document.querySelector('[data-drawer-close]');
        if(close && document.body.classList.contains('drawer-open')) close.click();
      });
    });
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
})();
