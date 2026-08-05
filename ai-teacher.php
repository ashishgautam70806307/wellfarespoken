<?php
require_once __DIR__ . '/includes/functions.php';
if (!defined('APP_AI_TEACHER_ENABLED') || !APP_AI_TEACHER_ENABLED) { redirect('spoken-materials.php'); }
ensure_schema_updates();
$page_title = 'AI Practice Teacher | ' . app_setting('site_name', APP_NAME);
$meta_description = 'Ask an AI-style English teacher for grammar, Hindi translation, sentence correction and speaking practice.';
$csrf = csrf_token();
$lightweight_layout = true;
$page_styles = ['assets/css/phase129-ai-teacher.css'];
require_once __DIR__ . '/includes/header.php';
?>
<?php
wf_page_hero([
    'eyebrow' => 'AI Practice Teacher',
    'title' => 'Ask a question. Listen to the answer. Practise again.',
    'text' => 'Use voice or text for grammar, translation and sentence correction in a simple student-friendly flow.',
    'icon' => 'fa-solid fa-robot',
    'actions' => [
        ['label' => 'Start Teacher', 'url' => '#teacher-app', 'icon' => 'fa-solid fa-play'],
        ['label' => 'Weekly Test', 'url' => 'weekly-test.php', 'icon' => 'fa-solid fa-clipboard-check'],
    ],
    'steps' => ['Ask', 'Listen', 'Repeat', 'Improve'],
    'compact' => true,
]);
?>
<section class="section" id="teacher-app">
    <div class="container ai-teacher-shell">
        <div class="ai-avatar-card">
            <div class="avatar-face" id="teacherAvatar"></div>
            <h2>Female AI Practice Teacher</h2>
            <p class="muted">Female browser voice is selected automatically when available. Real video avatar can be connected later with D-ID/HeyGen API.</p>
            <div class="teacher-tip-list">
                <div><strong>Ask grammar</strong><br><span class="muted">Example: Correct this sentence: i goes to market</span></div>
                <div><strong>Ask translation</strong><br><span class="muted">Example: Translate I have to go to Delhi in Hindi</span></div>
                <div><strong>Speak with mic</strong><br><span class="muted">Use Chrome for best speech input.</span></div>
            </div>
        </div>
        <div class="teacher-chat-card">
            <div class="section-title"><span class="eyebrow">Live Practice</span><h2>Teacher Chat</h2><p>Ask anything about English speaking, grammar, verbs, word meaning, translation or interview English.</p></div>
            <div class="chat-window" id="teacherChat"><div class="chat-msg teacher">Hello! I am your English practice teacher. Ask me any question or use the mic button.</div></div>
            <form id="teacherForm" class="chat-input-row" method="post" action="ai-teacher-api.php">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="text" name="message" id="teacherInput" placeholder="Ask: Correct this sentence / Translate this / Explain tense..." autocomplete="off">
                <button class="btn btn-soft" type="button" id="teacherMic" aria-label="Speak question"><i class="fa-solid fa-microphone"></i></button>
                <button class="btn btn-primary" type="submit">Ask</button>
            </form>
            <div class="api-note">Current version works locally. For human-level AI answers, connect OpenAI API in the next advanced layer.</div>
        </div>
    </div>
</section>
<script>
(function(){
 const form=document.getElementById('teacherForm'), input=document.getElementById('teacherInput'), chat=document.getElementById('teacherChat'), avatar=document.getElementById('teacherAvatar'), mic=document.getElementById('teacherMic');
 function esc(v){return String(v||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));}
 function add(type,msg){const d=document.createElement('div');d.className='chat-msg '+type;d.innerHTML=esc(msg).replace(/\n/g,'<br>');chat.appendChild(d);chat.scrollTop=chat.scrollHeight;}
 let cachedVoice=null;
 function pickFemaleVoice(langHint){
   if(!('speechSynthesis' in window)) return null;
   const voices=speechSynthesis.getVoices()||[];
   const femaleHints=['heera','zira','jenny','aria','sonia','susan','female','woman','google uk english female','google us english'];
   const lang=langHint||'en';
   const byFemale=voices.find(v=>femaleHints.some(h=>String(v.name||'').toLowerCase().includes(h)) && String(v.lang||'').toLowerCase().startsWith(lang));
   if(byFemale) return byFemale;
   const byLang=voices.find(v=>String(v.lang||'').toLowerCase().startsWith(lang));
   return byLang||voices[0]||null;
 }
 if('speechSynthesis' in window){ speechSynthesis.onvoiceschanged=function(){ cachedVoice=pickFemaleVoice('en'); }; }
 function speak(text){
   if(!('speechSynthesis' in window)) return;
   const clean=String(text||'').replace(/[#*_`]/g,' ').replace(/\s+/g,' ').trim();
   if(!clean) return;
   const hasHindi=/[\u0900-\u097F]/.test(clean);
   const u=new SpeechSynthesisUtterance(clean);
   u.lang=hasHindi?'hi-IN':'en-IN';
   u.rate=.88; u.pitch=1.08;
   u.voice=pickFemaleVoice(hasHindi?'hi':'en')||cachedVoice;
   avatar.classList.add('speaking');
   u.onend=()=>avatar.classList.remove('speaking'); u.onerror=()=>avatar.classList.remove('speaking');
   speechSynthesis.cancel(); speechSynthesis.speak(u);
 }
 form.addEventListener('submit',function(e){e.preventDefault(); const msg=input.value.trim(); if(!msg) return; add('user',msg); input.value=''; const fd=new FormData(form); fd.set('message',msg); add('teacher','Thinking...'); fetch(form.action,{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(data=>{chat.lastChild.remove(); if(!data.success) throw new Error(data.message||'Could not answer.'); add('teacher',data.reply); speak(data.reply);}).catch(err=>{chat.lastChild.remove(); add('teacher',err.message||'Please try again.');}); });
 mic.addEventListener('click',function(){ const SR=window.SpeechRecognition||window.webkitSpeechRecognition; if(!SR){alert('Mic works best in Chrome.');return;} const r=new SR(); r.lang='en-IN'; r.interimResults=false; mic.innerHTML='<i class="fa-solid fa-wave-square"></i>'; r.onresult=e=>{input.value=e.results[0][0].transcript;}; r.onend=()=>{mic.innerHTML='<i class="fa-solid fa-microphone"></i>';}; r.start(); });
})();
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
