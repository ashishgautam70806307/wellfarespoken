(function(){
  'use strict';

  function pad(n){ return String(n).padStart(2,'0'); }

  function parseNative(value){
    var m=String(value||'').match(/^(\d{4}-\d{2}-\d{2})T(\d{2}):(\d{2})/);
    if(!m) return null;
    var h24=parseInt(m[2],10);
    return {
      date:m[1],
      hour:String((h24%12)||12),
      minute:m[3],
      meridiem:h24>=12?'PM':'AM'
    };
  }

  function toNative(date,hour,minute,meridiem){
    if(!date || !hour || minute==='' || !meridiem) return '';
    var h=parseInt(hour,10);
    if(!Number.isFinite(h) || h<1 || h>12) return '';
    var h24=(h%12)+(meridiem==='PM'?12:0);
    return date+'T'+pad(h24)+':'+pad(parseInt(minute,10)||0);
  }

  function option(value,label){
    var o=document.createElement('option');
    o.value=value;
    o.textContent=label;
    return o;
  }

  function enhance(input){
    if(!input || input.dataset.wf12hReady==='1') return;
    input.dataset.wf12hReady='1';
    input.classList.add('wf166-native-datetime');

    var wrap=document.createElement('div');
    wrap.className='wf166-datetime12';
    wrap.setAttribute('data-wf-datetime12','1');

    var date=document.createElement('input');
    date.type='date';
    date.className='wf166-date-part';
    date.setAttribute('aria-label','Date');

    var hour=document.createElement('select');
    hour.className='wf166-hour-part';
    hour.setAttribute('aria-label','Hour');
    hour.appendChild(option('','Hour'));
    for(var h=1;h<=12;h++) hour.appendChild(option(String(h),String(h)));

    var minute=document.createElement('select');
    minute.className='wf166-minute-part';
    minute.setAttribute('aria-label','Minute');
    minute.appendChild(option('','Min'));
    for(var m=0;m<60;m++) minute.appendChild(option(pad(m),pad(m)));

    var meridiem=document.createElement('select');
    meridiem.className='wf166-meridiem-part';
    meridiem.setAttribute('aria-label','AM or PM');
    meridiem.appendChild(option('','AM/PM'));
    meridiem.appendChild(option('AM','AM'));
    meridiem.appendChild(option('PM','PM'));

    wrap.append(date,hour,minute,meridiem);
    input.insertAdjacentElement('afterend',wrap);
    input.type='hidden';

    function syncFromNative(){
      var parsed=parseNative(input.value);
      if(!parsed){ date.value=''; hour.value=''; minute.value=''; meridiem.value=''; return; }
      date.value=parsed.date;
      hour.value=parsed.hour;
      minute.value=parsed.minute;
      meridiem.value=parsed.meridiem;
    }

    function syncToNative(){
      input.value=toNative(date.value,hour.value,minute.value,meridiem.value);
      input.dispatchEvent(new Event('input',{bubbles:true}));
      input.dispatchEvent(new Event('change',{bubbles:true}));
    }

    [date,hour,minute,meridiem].forEach(function(el){ el.addEventListener('change',syncToNative); });
    input.addEventListener('wf-native-value-changed',syncFromNative);
    input.addEventListener('reset',function(){ setTimeout(syncFromNative,0); });
    syncFromNative();
  }

  function boot(root){
    (root||document).querySelectorAll('input[type="datetime-local"]').forEach(enhance);
    (root||document).querySelectorAll('a[href="#manual-question-editor"]').forEach(function(link){
      if(link.dataset.wf166ManualReady==='1') return;
      link.dataset.wf166ManualReady='1';
      link.addEventListener('click',function(){
        var panel=document.getElementById('manual-question-editor');
        if(panel&&panel.tagName.toLowerCase()==='details') panel.open=true;
      });
    });
    if(location.hash==='#manual-question-editor'){
      var panel=document.getElementById('manual-question-editor');
      if(panel&&panel.tagName.toLowerCase()==='details') panel.open=true;
    }
  }

  document.addEventListener('submit',function(event){
    var form=event.target;
    if(!form || !form.querySelectorAll) return;
    var invalid=null;
    form.querySelectorAll('[data-wf-datetime12]').forEach(function(wrap){
      if(invalid) return;
      var date=wrap.querySelector('.wf166-date-part');
      var hour=wrap.querySelector('.wf166-hour-part');
      var minute=wrap.querySelector('.wf166-minute-part');
      var meridiem=wrap.querySelector('.wf166-meridiem-part');
      var any=!!(date.value||hour.value||minute.value||meridiem.value);
      var complete=!!(date.value&&hour.value&&minute.value!==''&&meridiem.value);
      if(any&&!complete) invalid=[date,hour,minute,meridiem].find(function(el){return !el.value;})||date;
    });
    if(invalid){
      event.preventDefault();
      event.stopImmediatePropagation();
      invalid.focus();
      if(window.AppUI&&window.AppUI.toast) window.AppUI.toast('error','Complete Date, Hour, Minute and AM/PM, or leave the schedule blank.');
      else alert('Complete Date, Hour, Minute and AM/PM, or leave the schedule blank.');
    }
  },true);

  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',function(){boot(document);},{once:true});
  else boot(document);

  window.WF12HourTime={enhance:enhance,boot:boot};
})();
