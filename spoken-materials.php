<?php
require_once __DIR__ . '/includes/functions.php';
ensure_schema_updates();
$page_title = 'Spoken Practice Room | ' . app_setting('site_name', APP_NAME);
$lightweight_layout = true;
$page_styles = ['assets/css/phase130-materials.css'];
require_once __DIR__ . '/includes/header.php';

wf_page_hero([
    'eyebrow' => 'Daily Practice',
    'title' => 'Listen, speak and check one sentence at a time.',
    'text' => 'Choose a mode, practise one sentence and check your answer.',
    'icon' => 'fa-solid fa-microphone-lines',
    'actions' => [
        ['label' => 'Start Practice', 'url' => '#practice-room', 'icon' => 'fa-solid fa-play'],
        ['label' => 'My Roadmap', 'url' => 'learning-roadmap.php', 'icon' => 'fa-solid fa-route'],
    ],
    'steps' => ['Choose mode', 'Listen', 'Speak', 'Check answer'],
    'compact' => true,
]);
material_ensure_schema();
$collections = fetch_material_practice_collections(200);
$defaultCollection = material_default_practice_collection_id() ?: ($collections[0]['id'] ?? 0);
$units = fetch_material_units((int)$defaultCollection, 200);
?>
<section class="section ajax-practice-room" id="practice-room">
    <div class="container">
        <?php wf_section_heading('Practice Room', 'Choose one practice mode.', 'Choose a mode and start one sentence at a time.', ['label' => 'Teacher Help', 'url' => 'admission.php?source=practice-room']); ?>

        <div class="practice-goal-panel panel-card">
            <div class="goal-tabs ajax-goal-tabs" role="tablist">
                <button type="button" class="goal-tab active" data-goal="speak" data-direction="hindi_to_english"><b><i class="fa-solid fa-microphone" aria-hidden="true"></i> Speak Daily</b><span>Listen and repeat English</span></button>
                <button type="button" class="goal-tab" data-goal="hindi_to_english" data-direction="hindi_to_english"><b><i class="fa-solid fa-language" aria-hidden="true"></i> Hindi → English</b><span>Speak Hindi sentence in English</span></button>
                <button type="button" class="goal-tab" data-goal="english_to_hindi" data-direction="english_to_hindi"><b><i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i> English → Hindi</b><span>Understand meaning and revise</span></button>
                <button type="button" class="goal-tab" data-goal="revision" data-direction="hindi_to_english"><b><i class="fa-solid fa-star" aria-hidden="true"></i> Revision</b><span>Repeat saved material</span></button>
            </div>
            <form id="practiceFilterForm" class="ajax-practice-filter" autocomplete="off">
                <input type="hidden" name="goal" value="speak">
                <input type="hidden" name="direction" value="hindi_to_english">
                <label>Lesson Group
                    <select name="collection" id="practiceCollection">
                        <?php foreach($collections as $c): ?><option value="<?= e((string)$c['id']) ?>" <?= (int)$defaultCollection===(int)$c['id']?'selected':'' ?>><?= e($c['title']) ?></option><?php endforeach; ?>
                    </select>
                </label>
                <label>Topic / Tense / Use
                    <select name="unit" id="practiceUnit"><option value="0">All Topics</option><?php foreach($units as $u): ?><option value="<?= e((string)$u['id']) ?>"><?= e($u['title']) ?></option><?php endforeach; ?></select>
                </label>
                <label>Search
                    <input name="q" placeholder="Search is/am/are, present, interview, market...">
                </label>
                <button class="btn btn-primary" type="submit">Start</button>
            </form>
        </div>

        <div id="practiceLoader" class="panel-card practice-loader">Loading practice records...</div>
        <div id="practiceEmpty" class="panel-card empty-state" hidden>No practice sentences found. Admin can upload Hindi-English sentences from Study Materials Manager.</div>
        <div id="practiceApp" class="practice-room-shell enhanced-practice-shell" hidden>
            <aside class="practice-progress-card panel-card">
                <span class="badge badge-green">Today Plan</span>
                <h3>One sentence at a time</h3>
                <p>Listen carefully, speak aloud, then type your answer. Practice without rushing.</p>
                <div class="practice-meter"><span id="practiceMeterBar"></span></div>
                <strong id="practiceCounter">1 / 1</strong>
                <div class="mini-tips">
                    <div><i class="fa-solid fa-volume-high" aria-hidden="true"></i> Read loudly</div>
                    <div><i class="fa-solid fa-headphones" aria-hidden="true"></i> Listen twice</div>
                    <div><i class="fa-solid fa-microphone" aria-hidden="true"></i> Speak answer</div>
                    <div><i class="fa-solid fa-pen" aria-hidden="true"></i> Type from memory</div>
                </div>
            </aside>
            <div class="practice-stepper" id="practiceStepper"></div>
        </div>
    </div>
</section>

<section class="section section-cream">
    <div class="container">
        <div class="dark-cta">
            <div><h2>Want personal feedback from a teacher?</h2><p>Use Practice Room daily, then book a counselling call for live spoken English guidance.</p></div>
            <a class="btn btn-primary" href="admission.php?source=practice-room">Book Free Counselling</a>
        </div>
    </div>
</section>

<script>
(function(){
    const filterForm = document.getElementById('practiceFilterForm');
    const loader = document.getElementById('practiceLoader');
    const empty = document.getElementById('practiceEmpty');
    const app = document.getElementById('practiceApp');
    const stepper = document.getElementById('practiceStepper');
    const counter = document.getElementById('practiceCounter');
    const meter = document.getElementById('practiceMeterBar');

    let items = [], current = 0, csrf = '';
    let autoTimers = new WeakMap(), checkingNow = new WeakSet();
    let autoMicTimer = null, activeRecognition = null, micStopTimer = null, micSettleTimer = null, questionRetryTimer = null;
    const MAX_WRONG_RETRY = 3;

    function esc(v){ return String(v||'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c])); }
    function hasSpeech(){ return 'speechSynthesis' in window; }
    function hasMic(){ return !!(window.SpeechRecognition || window.webkitSpeechRecognition); }
    function getVoices(){ return hasSpeech() ? window.speechSynthesis.getVoices() : []; }
    function chooseVoice(lang){
        const voices = getVoices();
        return voices.find(v=>/female|zira|heera|susan|samantha|google uk english female|google हिन्दी/i.test(v.name))
            || voices.find(v=>(lang||'en-IN').startsWith('hi') ? /hi-IN|Hindi/i.test(v.lang+v.name) : /en-IN|en-GB|English/i.test(v.lang+v.name))
            || voices[0];
    }
    function speakText(text, lang, onEnd, retryCount){
        text = String(text||'').trim();
        if(!text){ if(onEnd) onEnd(); return; }
        if(!hasSpeech()){ if(onEnd) setTimeout(onEnd, 500); return; }

        const voices = getVoices();
        if(!voices.length && (retryCount||0) < 10){
            setTimeout(() => speakText(text, lang, onEnd, (retryCount||0)+1), 300);
            return;
        }

        window.speechSynthesis.cancel();
        const u = new SpeechSynthesisUtterance(text);
        u.lang = lang || 'en-IN';
        u.rate = 0.78;
        u.pitch = 1.05;
        const voice = chooseVoice(u.lang);
        if(voice) u.voice = voice;

        let done = false;
        const finish = () => { if(done) return; done = true; if(onEnd) onEnd(); };
        u.onend = finish;
        u.onerror = finish;

        setTimeout(() => {
            try { window.speechSynthesis.speak(u); } catch(e){ finish(); }
        }, 80);

        setTimeout(() => {
            if(!done && !window.speechSynthesis.speaking) finish();
        }, Math.max(2600, text.length * 110));
    }
    function speakMany(parts, index, done){
        index = index || 0;
        if(index >= parts.length){ if(done) done(); return; }
        const p = parts[index];
        speakText(p.text, p.lang, function(){ setTimeout(() => speakMany(parts, index + 1, done), p.pause || 350); });
    }

    if(hasSpeech()){
        window.speechSynthesis.onvoiceschanged = function(){
            const active = document.querySelector('.practice-slide.active');
            if(active && !active.dataset.voiceStarted){
                active.dataset.voiceStarted = '1';
                setTimeout(() => playQuestionThenMic(active, false), 350);
            }
        };
    }

    function questionLangFor(form){ return form.dataset.direction === 'english_to_hindi' ? 'en-IN' : 'hi-IN'; }
    function answerLangFor(form){ return form.dataset.direction === 'english_to_hindi' ? 'hi-IN' : 'en-IN'; }
    function getQuestionText(form){ return form.dataset.question || ''; }
    function getAnswerText(form){ return form.dataset.answer || ''; }

    function cleanupMic(){
        clearTimeout(autoMicTimer);
        clearTimeout(micStopTimer);
        clearTimeout(micSettleTimer);
        clearTimeout(questionRetryTimer);
        if(activeRecognition){
            try{ activeRecognition.onend = null; activeRecognition.abort(); }catch(e){}
            activeRecognition = null;
        }
    }

    function loadPractice(){
        cleanupMic();
        loader.hidden=false; empty.hidden=true; app.hidden=true; stepper.innerHTML='';
        const qs = new URLSearchParams(new FormData(filterForm));
        fetch('material-practice-list-api.php?'+qs.toString(), {headers:{'X-Requested-With':'XMLHttpRequest'}})
            .then(r=>r.json()).then(data=>{
                if(!data.success) throw new Error(data.message||'Could not load practice.');
                csrf = data.csrf; items = data.items || []; current = 0;
                syncUnits(data.units || [], data.unit_id || 0);
                renderItems(data.direction || filterForm.direction.value, data.goal || filterForm.goal.value);
            }).catch(err=>{ loader.hidden=true; empty.hidden=false; empty.textContent=err.message; });
    }

    function syncUnits(units, selected){
        const sel = document.getElementById('practiceUnit'); const old = selected || sel.value;
        sel.innerHTML = '<option value="0">All Topics</option>' + units.map(u=>'<option value="'+u.id+'">'+esc(u.title)+'</option>').join('');
        if([...sel.options].some(o=>o.value==old)) sel.value=old;
    }

    function modeGuide(direction, goal){
        if(goal === 'speak') return {text:'Speak daily mode. Listen and repeat the sentence.', lang:'en-IN'};
        if(goal === 'revision') return {text:'Revision mode. Listen and repeat saved material.', lang:'en-IN'};
        if(direction === 'english_to_hindi') return {text:'English to Hindi practice. Listen to English and answer in Hindi.', lang:'en-IN'};
        return {text:'Hindi to English practice. Listen to Hindi and answer in English.', lang:'en-IN'};
    }

    function renderItems(direction, goal){
        loader.hidden=true;
        if(!items.length){ empty.hidden=false; app.hidden=true; return; }
        empty.hidden=true; app.hidden=false;

        stepper.innerHTML = items.map((p,i)=>{
            const isEngToHindi = direction==='english_to_hindi';
            const isSpeakMode = goal==='speak' || goal==='revision';
            const question = isEngToHindi ? p.english : (isSpeakMode ? p.english : p.hindi);
            const answer = isEngToHindi ? p.hindi : p.english;
            const help = isEngToHindi
                ? 'Listen to the English question. If needed, say again bolo / repeat question.'
                : (isSpeakMode ? 'Listen and speak the same English sentence.' : 'Listen to the Hindi question. If needed, say again bolo / dobara bolo.');
            return '<form class="practice-slide material-ajax-card auto-voice-practice '+(i===0?'active':'')+'" data-index="'+i+'" data-id="'+p.id+'" data-answer="'+esc(answer)+'" data-question="'+esc(question)+'" data-english="'+esc(p.english)+'" data-hindi="'+esc(p.hindi)+'" data-direction="'+esc(direction)+'" data-goal="'+esc(goal)+'" data-wrong-count="0">'
            + '<input type="hidden" name="csrf_token" value="'+esc(csrf)+'"><input type="hidden" name="pair_id" value="'+p.id+'"><input type="hidden" name="direction" value="'+esc(direction)+'">'
            + '<div class="practice-card-top"><span class="question-type">'+esc(p.level)+' • '+esc(p.topic)+' • '+esc(p.sentence_type||'Practice')+'</span><span class="badge badge-muted">'+(i+1)+' / '+items.length+'</span></div>'
            + '<h3>'+esc(question)+'</h3>'
            + (p.roman && !isEngToHindi ? '<p class="roman-line">'+esc(p.roman)+'</p>' : '')
            + '<p class="muted-text">'+esc(help)+'</p>'
            + '<div class="handsfree-switch"><label><input type="checkbox" class="handsfree-toggle" checked> Hands-free auto mic</label><small>Question ek baar bolega. Dobara sunna ho to again bolo.</small></div>'
            + '<div class="voice-status" hidden></div>'
            + '<div class="practice-action-row"><button type="button" class="btn btn-soft listen-question"><i class="fa-solid fa-volume-high" aria-hidden="true"></i> Read Question</button><button type="button" class="btn btn-primary mic-answer"><i class="fa-solid fa-microphone" aria-hidden="true"></i> Speak Now</button><button type="button" class="btn btn-light stop-auto">Stop Auto</button></div>'
            + '<textarea name="answer" rows="4" placeholder="'+(isEngToHindi?'Hindi answer yahan dikhega/type karein...':'English answer yahan dikhega/type karein...')+'"></textarea>'
            + '<div class="translation-actions"><button class="btn btn-soft" type="submit">Manual Check</button><button class="btn btn-soft finish-check" type="button">Finish & Check</button><button class="btn btn-soft next-slide" type="button">Next</button></div>'
            + '<div class="material-inline-result" hidden></div></form>';
        }).join('');

        bindPracticeCards();
        const guide = modeGuide(direction, goal);
        speakText(guide.text, guide.lang, function(){ showSlide(0); });
    }

    function showSlide(i){
        cleanupMic();
        current=Math.max(0,Math.min(i,items.length-1));
        document.querySelectorAll('.practice-slide').forEach((s,idx)=>{
            s.classList.toggle('active',idx===current);
            if(idx===current){
                delete s.dataset.voiceStarted;
                s.dataset.wrongCount = '0';
            }
        });
        counter.textContent=(current+1)+' / '+items.length;
        meter.style.width=(((current+1)/items.length)*100)+'%';

        const active = document.querySelector('.practice-slide.active');
        if(active){
            active.querySelector('textarea').value = '';
            const result = active.querySelector('.material-inline-result');
            if(result){ result.hidden = true; result.innerHTML = ''; }
            const status = active.querySelector('.voice-status');
            if(status){ status.hidden=false; status.textContent='Question will be read now...'; }
            setTimeout(() => playQuestionThenMic(active, false), 450);
        }
    }

    function questionPrompt(form){
        const qNo = (parseInt(form.dataset.index || '0', 10) + 1);
        return 'Question number ' + qNo + '. ' + getQuestionText(form);
    }

    function playQuestionThenMic(form, repeatAfterWrong){
        cleanupMic();
        const qText = questionPrompt(form);
        const qLang = questionLangFor(form);
        const toggle = form.querySelector('.handsfree-toggle');
        const status = form.querySelector('.voice-status');

        if(status){ status.hidden=false; status.textContent='Reading question...'; }
        form.dataset.voiceStarted = '1';

        const parts = [
            {text:qText, lang:qLang, pause:450}
        ];

        // Only question number + actual question sentence is spoken first.
        // Correct answer is never spoken before student answers.
        speakMany(parts, 0, function(){
            if(!form.classList.contains('active')) return;
            if(status){ status.textContent='Get ready. Now speak will play, then mic will start...'; }
            autoMicTimer = setTimeout(function(){
                if(toggle && toggle.checked && form.classList.contains('active')){
                    startMic(form, true);
                }
            }, 900);
        });

        questionRetryTimer = setTimeout(function(){
            if(!form.classList.contains('active')) return;
            if(status && status.textContent === 'Reading question...'){
                status.textContent = 'Voice not started. Mic will start, please answer.';
                if(toggle && toggle.checked) startMic(form, true);
            }
        }, 15000);
    }


    function isRepeatQuestionCommand(text){
        const t = String(text || '').toLowerCase().trim();
        const cleaned = t.replace(/[^\p{L}\p{N}\s]/gu, ' ').replace(/\s+/g, ' ').trim();
        if(!cleaned) return false;
        const commands = [
            'again bolo','dobara bolo','doobara bolo','dubara bolo','phir se bolo','fir se bolo',
            'repeat','repeat question','question repeat','once again','say again','please repeat',
            'again','dobara','dubara','phir se','fir se','samajh nahi aaya','samajh nhi aaya',
            'nahi suna','nhi suna','sunai nahi diya','sunayi nahi diya','awaaz nahi aayi'
        ];
        return commands.some(cmd => cleaned === cmd || cleaned.includes(cmd));
    }

    function repeatCurrentQuestion(form, message){
        cleanupMic();
        const status = form.querySelector('.voice-status');
        const textarea = form.querySelector('textarea');
        if(textarea) textarea.value = '';
        if(status){ status.hidden = false; status.textContent = message || 'Repeating question...'; }
        setTimeout(() => playQuestionThenMic(form, true), 500);
    }

    function renderResult(form, r){
        const box=form.querySelector('.material-inline-result');
        const ok=!!r.is_correct;
        box.hidden=false; box.className='material-inline-result '+(ok?'is-correct':'is-improve');
        box.innerHTML='<strong>'+(ok?'<i class="fa-solid fa-circle-check" aria-hidden="true"></i> Correct':'<i class="fa-solid fa-lightbulb" aria-hidden="true"></i> Correction needed')+'</strong>'
            + '<p>'+esc(r.feedback||'')+'</p>'
            + '<div class="correct-speak-box"><span>Correct answer</span><h4>'+esc(r.correct_answer||getAnswerText(form)||'')+'</h4></div>'
            + (r.explanation?'<small>'+esc(r.explanation)+'</small>':'')
            + (r.match_type?'<div class="mini-chip">Match: '+esc(r.match_type)+' • Score: '+esc(r.score||0)+'/10</div>':'')
            + '<div class="practice-action-row result-actions"><button type="button" class="btn btn-soft listen-correct"><i class="fa-solid fa-volume-high" aria-hidden="true"></i> Listen Correct</button><button type="button" class="btn btn-primary next-slide">Next Sentence</button></div>';

        if(ok){
            form.dataset.wrongCount = '0';
            speakText('Correct.', 'en-IN');
            box.insertAdjacentHTML('beforeend','<div class="auto-next-note">Correct. Next in 2 seconds...</div>');
            setTimeout(()=>showSlide(current+1), 2000);
            return;
        }

        const count = (parseInt(form.dataset.wrongCount || '0', 10) || 0) + 1;
        form.dataset.wrongCount = String(count);
        const correct = r.correct_answer || getAnswerText(form) || '';

        if(count >= MAX_WRONG_RETRY){
            speakText('Not correct. Please revise this answer. '+correct, answerLangFor(form), function(){
                const status = form.querySelector('.voice-status');
                if(status){ status.hidden=false; status.textContent='Auto repeat stopped after 3 tries. Revise, then click Next or Speak Now.'; }
            });
            box.insertAdjacentHTML('beforeend','<div class="auto-next-note is-stop">Auto repeat stopped after 3 tries.</div>');
            return;
        }

        speakText('Not correct. Speak with me. '+correct, answerLangFor(form), function(){
            const status = form.querySelector('.voice-status');
            if(status){ status.hidden=false; status.textContent='Try again. Question will repeat...'; }
            setTimeout(function(){
                if(form.classList.contains('active')){
                    form.querySelector('textarea').value = '';
                    playQuestionThenMic(form, true);
                }
            }, 1500);
        });
    }

    function checkForm(form, source){
        const answer = form.querySelector('textarea').value.trim();
        const box=form.querySelector('.material-inline-result');
        if(isRepeatQuestionCommand(answer)){
            repeatCurrentQuestion(form, 'Repeating question as requested...');
            return;
        }
        if(answer.length < 2){ return; }
        if(checkingNow.has(form)) return;
        checkingNow.add(form);
        cleanupMic();

        const btn=form.querySelector('button[type="submit"]'); const old=btn.textContent;
        btn.disabled=true; btn.textContent='Checking...';
        box.hidden=false; box.className='material-inline-result is-checking'; box.innerHTML='<strong>Checking...</strong><p>Please wait.</p>';

        fetch('material-practice-api.php',{method:'POST',body:new FormData(form),headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(data=>{
            if(!data.success) throw new Error(data.message||'Could not check answer.');
            renderResult(form, data.result||{});
        }).catch(err=>{
            box.className='material-inline-result is-improve';
            box.innerHTML='<strong>Could not check</strong><p>'+esc(err.message)+'</p>';
        }).finally(()=>{
            checkingNow.delete(form);
            btn.disabled=false;
            btn.textContent=old;
        });
    }

    function scheduleAutoCheck(form){
        clearTimeout(autoTimers.get(form));
        const t = setTimeout(()=>checkForm(form, 'typing'), 1200);
        autoTimers.set(form, t);
    }

    function bindPracticeCards(){
        document.querySelectorAll('.practice-slide').forEach(form=>{
            const textarea = form.querySelector('textarea');
            form.addEventListener('submit',function(e){ e.preventDefault(); checkForm(form, 'manual'); });
            textarea.addEventListener('input',function(){ if(textarea.value.trim().length >= 4) scheduleAutoCheck(form); });
        });
    }

    function startMic(form, autoMode){
        const SpeechRecognition=window.SpeechRecognition||window.webkitSpeechRecognition;
        const status=form.querySelector('.voice-status');
        const mic=form.querySelector('.mic-answer');
        const textarea=form.querySelector('textarea');

        if(!SpeechRecognition){
            if(status){ status.hidden=false; status.textContent='Auto mic is not supported. Please use Chrome/Edge or type your answer.'; }
            return;
        }

        cleanupMic();

        const rec=new SpeechRecognition();
        activeRecognition = rec;
        rec.lang=answerLangFor(form);
        rec.interimResults=true;
        rec.continuous=true;
        rec.maxAlternatives=1;

        let finalText = textarea.value.trim();
        const startedAt = Date.now();
        let lastHeardAt = Date.now();
        let stoppedBySystem = false;

        function updateStatus(text){
            if(status){ status.hidden=false; status.textContent=text; }
        }
        function finishAndCheck(){
            if(stoppedBySystem) return;
            stoppedBySystem = true;
            clearTimeout(micStopTimer);
            clearTimeout(micSettleTimer);
            if(activeRecognition){ try{ activeRecognition.stop(); }catch(e){} }
            activeRecognition = null;
            if(mic){ mic.disabled=false; mic.innerHTML='<i class="fa-solid fa-microphone" aria-hidden="true"></i> Speak Now'; }
            const heard = textarea.value.trim();
            if(heard){
                if(isRepeatQuestionCommand(heard)){
                    repeatCurrentQuestion(form, 'Repeating question as requested...');
                    return;
                }
                updateStatus('Checking automatically...');
                setTimeout(()=>checkForm(form, 'speech'), 350);
            }else{
                updateStatus('No voice captured. Say again bolo, tap Speak Now, or type your answer.');
            }
        }
        function schedulePossibleFinish(){
            clearTimeout(micSettleTimer);
            micSettleTimer = setTimeout(function(){
                if(textarea.value.trim().length >= 2 && Date.now() - lastHeardAt >= 3500){
                    finishAndCheck();
                }
            }, 3800);
        }

        if(mic){ mic.disabled=true; mic.innerHTML='<i class="fa-solid fa-headphones" aria-hidden="true"></i> Listening...'; }
        updateStatus('Now speak...');
        speakText('Now speak', 'en-IN', function(){
            if(!form.classList.contains('active')) return;
            updateStatus(autoMode ? 'Listening automatically up to 1 minute...' : 'Listening up to 1 minute...');

            rec.onresult=(ev)=>{
                let interim='';
                for(let i=ev.resultIndex;i<ev.results.length;i++){
                    const transcript=ev.results[i][0].transcript;
                    if(ev.results[i].isFinal){
                        finalText = (finalText + ' ' + transcript).trim();
                    }else{
                        interim += transcript;
                    }
                }
                const value = (finalText + ' ' + interim).trim();
                textarea.value = value;
                lastHeardAt = Date.now();
                updateStatus(value ? 'Heard: '+value : 'Listening...');
                schedulePossibleFinish();
            };
            rec.onerror=(ev)=>{
                if(stoppedBySystem) return;
                updateStatus('Listening paused. Speak again or use Finish & Check.');
            };
            rec.onend=()=>{
                if(stoppedBySystem) return;
                if(Date.now() - startedAt < 60000 && form.classList.contains('active')){
                    try{ rec.start(); updateStatus('Still listening...'); }catch(e){ schedulePossibleFinish(); }
                }else{
                    finishAndCheck();
                }
            };

            micStopTimer = setTimeout(finishAndCheck, 60000);
            try{ rec.start(); }catch(e){
                updateStatus('Mic could not start. Please try again.');
                if(mic){ mic.disabled=false; mic.innerHTML='<i class="fa-solid fa-microphone" aria-hidden="true"></i> Speak Now'; }
            }
        });
    }

    document.addEventListener('click',function(e){
        const tab=e.target.closest('.goal-tab');
        if(tab){
            document.querySelectorAll('.goal-tab').forEach(t=>t.classList.remove('active'));
            tab.classList.add('active');
            filterForm.goal.value=tab.dataset.goal;
            filterForm.direction.value=tab.dataset.direction;
            const guide = modeGuide(tab.dataset.direction, tab.dataset.goal);
            speakText(guide.text, guide.lang, function(){ loadPractice(); });
            return;
        }
        const next=e.target.closest('.next-slide'); if(next){ showSlide(current+1); }
        const listen=e.target.closest('.listen-question'); if(listen){ playQuestionThenMic(listen.closest('.practice-slide'), false); }
        const listenCorrect=e.target.closest('.listen-correct'); if(listenCorrect){ const f=listenCorrect.closest('.practice-slide'); speakText(getAnswerText(f), answerLangFor(f)); }
        const finish=e.target.closest('.finish-check'); if(finish){ const f=finish.closest('.practice-slide'); checkForm(f, 'finish'); }
        const stop=e.target.closest('.stop-auto');
        if(stop){
            cleanupMic();
            const f=stop.closest('.practice-slide');
            const t=f.querySelector('.handsfree-toggle'); if(t) t.checked=false;
            const s=f.querySelector('.voice-status'); if(s){ s.hidden=false; s.textContent='Auto mic stopped. Use Speak Now manually.'; }
        }
        const mic=e.target.closest('.mic-answer'); if(mic){ startMic(mic.closest('.practice-slide'), false); }
    });

    filterForm.addEventListener('submit',function(e){ e.preventDefault(); loadPractice(); });
    filterForm.querySelectorAll('select').forEach(el=>el.addEventListener('change',loadPractice));
    let qTimer=null; filterForm.q.addEventListener('input',function(){ clearTimeout(qTimer); qTimer=setTimeout(loadPractice,500); });
    loadPractice();
})();
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
