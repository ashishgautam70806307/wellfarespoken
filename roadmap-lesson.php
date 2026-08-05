<?php
require_once __DIR__ . '/includes/functions.php';
ensure_schema_updates();
roadmap_seed_defaults();

$unitId = max(0, (int)($_GET['id'] ?? 0));
$unit = $unitId > 0 ? roadmap_fetch_unit($unitId) : null;
$unitsFlat = roadmap_fetch_all_units_flat();
if (!$unit && $unitsFlat) {
    $unit = $unitsFlat[0];
    $unitId = (int)$unit['id'];
}
if (!$unit) {
    $page_title = 'Learning Roadmap | ' . app_setting('site_name', APP_NAME);
    $meta_description = 'Learning roadmap lessons will appear here.';
    $lightweight_layout = true;
    $page_styles = ['assets/css/phase130-roadmap-lesson.css'];
    require_once __DIR__ . '/includes/header.php';
    ?>
    <section class="duo-lesson-page"><div class="duo-lesson-shell"><div class="duo-empty"><h1>No lesson is available yet.</h1><p>The institute can add roadmap lessons from the admin panel.</p><a class="wf-btn wf-btn-primary" href="learning-roadmap.php"><span>Back to Roadmap</span></a></div></div></section>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
$items = roadmap_fetch_unit_items_smart($unit);
$nextId = $unit ? roadmap_next_unit_id((int)$unit['id']) : 0;
$prevId = $unit ? roadmap_previous_unit_id((int)$unit['id']) : 0;
$roadmapStudentLoggedIn = is_student();
$roadmapServerCompleted = $roadmapStudentLoggedIn ? roadmap_student_completed_ids(current_student_id()) : [];
if ($roadmapStudentLoggedIn && $prevId > 0 && !in_array($prevId, $roadmapServerCompleted, true)) {
    redirect('learning-roadmap.php?locked=1#roadmapPath');
}

$page_title = ($unit ? $unit['title'] : 'Lesson') . ' | Learning Roadmap';
$meta_description = 'Learn and practice this roadmap lesson.';
$lightweight_layout = true;
$page_styles = ['assets/css/phase130-roadmap-lesson.css'];
require_once __DIR__ . '/includes/header.php';


function roadmap_unique_practice_rows(array $rows, int $limit = 100): array
{
    $out = [];
    $seen = [];
    foreach ($rows as $r) {
        $answer = trim((string)($r['col_1'] ?: $r['col_3'] ?: ''));
        $question = trim((string)($r['col_2'] ?: $r['col_4'] ?: $r['item_key'] ?: ''));
        if ($answer === '' && $question === '') {
            continue;
        }
        $key = strtolower(preg_replace('/\s+/', ' ', $answer . '|' . $question));
        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $out[] = $r;
        if (count($out) >= $limit) {
            break;
        }
    }
    return $out;
}

$type = strtolower((string)($unit['unit_type'] ?? 'lesson'));
$pronounRows = array_values(array_filter($items, fn($it) => ($it['col_6'] ?? '') === 'pronoun' || stripos((string)($it['example_text'] ?? ''), 'Subject | Object') !== false));
$demoRows = array_values(array_filter($items, fn($it) => ($it['col_6'] ?? '') === 'demonstrative' || in_array(($it['item_key'] ?? ''), ['This','That','These','Those'], true)));
$practiceLimit = 100;
$practiceRows = roadmap_unique_practice_rows(array_values(array_filter($items, fn($it) => trim((string)($it['col_1'] ?? '')) !== '' || trim((string)($it['col_3'] ?? '')) !== '')), $practiceLimit);
$smartSourceTitle = trim((string)($items[0]['source_unit_title'] ?? ''));
$smartSourceId = (int)($items[0]['source_unit_id'] ?? 0);
?>
<section class="duo-lesson-page" data-unit-id="<?= e((string)$unitId) ?>" data-next-id="<?= e((string)$nextId) ?>">
    <div class="duo-celebrate" id="duoCelebrate"></div>

    <div class="duo-lesson-shell">
        <div class="duo-top-progress lesson-count-top">
            <a class="duo-back-link" href="learning-roadmap.php#roadmapPath"><i class="fa-solid fa-arrow-left"></i> Roadmap</a>
            <div class="duo-progress-track"><span id="lessonUnlockProgress"></span></div>
            <div class="duo-energy"><span id="lessonUnlockCount">0 / 0</span></div>
        </div>

        <div class="duo-lesson-header">
            <span class="duo-label"><?= e($unit['group_title'] ?? 'LESSON') ?></span>
            <h1><?= e($unit['title'] ?? 'Lesson') ?></h1>
            <p><?= e((string)(($unit['description'] ?? '') ?: ($unit['subtitle'] ?? ''))) ?></p><?php if ($smartSourceTitle !== ''): ?><?php endif; ?>
        </div>

        <div class="duo-lesson-tabs">
            <button class="active" type="button" data-tab="learn">Learn</button>
            <button type="button" data-tab="practice">Practice</button>
            <button type="button" data-tab="finish">Finish</button>
        </div>

        <div class="duo-panel active" data-panel="learn">
            <?php if (!$items): ?>
                <div class="duo-empty">No learning rows yet. Add data from admin roadmap.</div>
            <?php elseif ($type === 'meaning' && $pronounRows): ?>
                <div class="duo-learn-title">
                    <h2>Pronouns + Demonstrative words</h2>
                    <p>First clear these basics, then start sentence practice.</p>
                </div>
                <div class="duo-pronoun-cards">
                    <?php foreach ($pronounRows as $it): ?>
                    <article>
                        <b><?= e($it['item_key'] ?: $it['col_1']) ?></b>
                        <span><?= e($it['col_1']) ?></span>
                        <span><?= e($it['col_2']) ?></span>
                        <span><?= e($it['col_3']) ?></span>
                        <span><?= e($it['col_4']) ?></span>
                        <span><?= e($it['col_5']) ?></span>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php if ($demoRows): ?>
                <div class="duo-word-options">
                    <?php foreach ($demoRows as $it): ?>
                    <article>
                        <div><i class="fa-solid fa-hand-pointer"></i></div>
                        <h3><?= e($it['col_1']) ?></h3>
                        <p><?= e($it['col_2']) ?></p>
                        <small><?= e($it['col_3']) ?></small>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="duo-rule-cards duo-rule-cards-compact">
                    <?php foreach ($items as $it): ?>
                    <?php
                        $mainText = trim((string)($it['col_1'] ?: $it['col_3'] ?: $unit['title']));
                        $meaningText = trim((string)($it['col_2'] ?: $it['col_4']));
                        $extraText = trim((string)($it['col_3'] ?? ''));
                        $mainKey = strtolower(preg_replace('/\s+/', ' ', $mainText));
                        $extraKey = strtolower(preg_replace('/\s+/', ' ', $extraText));
                    ?>
                    <article>
                        <h3><?= e($mainText) ?></h3>
                        <?php if ($meaningText !== ''): ?><p><?= e($meaningText) ?></p><?php endif; ?>
                        <?php if ($extraText !== '' && $extraKey !== $mainKey): ?><small><?= e($extraText) ?></small><?php endif; ?>
                    </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <button class="duo-big-green next-tab" type="button" data-next="practice">START PRACTICE</button>
        </div>

        <div class="duo-panel" data-panel="practice">
            <div class="duo-exercise" data-items='<?= e(json_encode(array_slice($practiceRows, 0, $practiceLimit), JSON_UNESCAPED_UNICODE)) ?>'>
                <div class="duo-practice-progress-line">
                    <span id="practiceQuestionCount">0 / 0</span>
                    <div><b id="practiceQuestionBar"></b></div>
                </div>
                <span class="duo-label">NEW PRACTICE</span>
                <h2 id="duoQuestionTitle">Select the correct answer</h2>
                <div class="duo-sound-row">
                    <button type="button" id="duoSpeakQuestion" aria-label="Listen to question"><i class="fa-solid fa-volume-high"></i></button>
                    <b id="duoQuestionText">Ready?</b>
                </div>
                <div class="duo-answer-grid" id="duoAnswerGrid"></div>
                <div class="duo-result-box" id="duoResultBox" hidden></div>
                <button class="duo-check-btn" type="button" id="duoStartPractice">START</button>
                <button class="duo-big-green" type="button" id="duoNextQuestion" hidden>CONTINUE</button>
            </div>
        </div>

        <div class="duo-panel" data-panel="finish">
            <div class="duo-finish-card">
                <h2>Level complete?</h2>
                <p>Finish this lesson to save progress and unlock the next step.</p>
                <button class="duo-big-green completeLevel" type="button">COMPLETE LEVEL</button>
                <?php if ($prevId): ?><a class="duo-small-link" href="roadmap-lesson.php?id=<?= e((string)$prevId) ?>">Previous lesson</a><?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
(function(){
    const KEY='wfRoadmapCompleted';
    const studentMode = <?= $roadmapStudentLoggedIn ? 'true' : 'false' ?>;
    let serverCompleted = <?= json_encode(array_values($roadmapServerCompleted)) ?>;
    const csrfToken = <?= json_encode(csrf_token()) ?>;
    const root=document.querySelector('.duo-lesson-page');
    const unitId=Number(root?.dataset.unitId||0);
    const nextId=Number(root?.dataset.nextId||0);
    const allLessonIds = <?= json_encode(array_map(fn($u)=>(int)$u['id'], $unitsFlat)) ?>;
    const totalActiveLevels = allLessonIds.length || 1;

    function getCompletedLevels(){
        if (studentMode) return serverCompleted.map(Number);
        try { return JSON.parse(localStorage.getItem(KEY)||'[]').map(Number); }
        catch(e){ return []; }
    }
    function updateUnlockProgress(){
        const completed = getCompletedLevels();
        const completedCount = completed.filter(id => allLessonIds.includes(id)).length;
        const unlockedCount = Math.min(totalActiveLevels, completedCount + 1);
        const pct = Math.max(8, Math.round((completedCount / totalActiveLevels) * 100));
        const bar = document.getElementById('lessonUnlockProgress');
        const count = document.getElementById('lessonUnlockCount');
        if(bar) bar.style.width = pct + '%';
        if(count) count.textContent = unlockedCount + ' / ' + totalActiveLevels;
    }
    updateUnlockProgress();

    function show(tab){
        document.querySelectorAll('.duo-lesson-tabs button').forEach(b=>b.classList.toggle('active', b.dataset.tab===tab));
        document.querySelectorAll('.duo-panel').forEach(p=>p.classList.toggle('active', p.dataset.panel===tab));
    }
    document.querySelectorAll('.duo-lesson-tabs button').forEach(b=>b.addEventListener('click',()=>show(b.dataset.tab)));
    document.querySelectorAll('.next-tab').forEach(b=>b.addEventListener('click',()=>{
        show(b.dataset.next);
        setTimeout(()=>{
            document.querySelector('[data-panel="'+b.dataset.next+'"]')?.scrollIntoView({behavior:'smooth', block:'start'});
        }, 80);
    }));

    function celebrate(){
        const box=document.getElementById('duoCelebrate');
        if(!box) return;
        box.innerHTML='';
        const icons=['🌸','🌼','✨','🎉','⭐','🌺'];
        for(let i=0;i<90;i++){
            const s=document.createElement('span');
            s.textContent=icons[Math.floor(Math.random()*icons.length)];
            s.style.left=Math.random()*100+'%';
            s.style.animationDelay=(Math.random()*0.7)+'s';
            s.style.fontSize=(18+Math.random()*18)+'px';
            box.appendChild(s);
        }
        box.classList.add('show');
        setTimeout(()=>box.classList.remove('show'),2500);
    }
    async function complete(){
        if (studentMode) {
            const form = new URLSearchParams({action:'complete', unit_id:String(unitId), csrf_token:csrfToken});
            const response = await fetch('roadmap-progress-api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'}, body:form.toString(), credentials:'same-origin'});
            const data = await response.json().catch(() => ({}));
            if (!response.ok || !data.success) { window.alert(data.message || 'Could not save progress.'); return; }
            serverCompleted = Array.isArray(data.completed_ids) ? data.completed_ids.map(Number) : serverCompleted;
        } else {
            let arr=[]; try{arr=JSON.parse(localStorage.getItem(KEY)||'[]').map(Number)}catch(e){}
            if(unitId && !arr.includes(unitId)) arr.push(unitId);
            localStorage.setItem(KEY,JSON.stringify(arr));
        }
        celebrate();
        setTimeout(()=>{ location.href = nextId ? ('roadmap-lesson.php?id='+nextId) : 'learning-roadmap.php#roadmapPath'; },1800);
    }
    document.querySelectorAll('.completeLevel').forEach(b=>b.addEventListener('click',()=>{ complete().catch(()=>window.alert('Could not save progress.')); }));

    const app=document.querySelector('.duo-exercise');
    let rows=[]; try{rows=JSON.parse(app?.dataset.items||'[]')}catch(e){}
    const totalPracticeQuestions = rows.length || 0;
    const practiceCountEl = document.getElementById('practiceQuestionCount');
    const practiceBarEl = document.getElementById('practiceQuestionBar');
    function updatePracticeProgress(){
        const done = Math.min(idx, totalPracticeQuestions);
        const pct = totalPracticeQuestions ? Math.round((done / totalPracticeQuestions) * 100) : 0;
        if(practiceCountEl) practiceCountEl.textContent = done + ' / ' + totalPracticeQuestions;
        if(practiceBarEl) practiceBarEl.style.width = Math.max(totalPracticeQuestions ? 6 : 0, pct) + '%';
    }
    const qText=document.getElementById('duoQuestionText');
    const grid=document.getElementById('duoAnswerGrid');
    const start=document.getElementById('duoStartPractice');
    const next=document.getElementById('duoNextQuestion');
    const result=document.getElementById('duoResultBox');
    let idx=0, correctAnswer='', rightCount=0, wrongCount=0;

    function normalize(v){return String(v||'').toLowerCase().replace(/[^\p{L}\p{N}\s]/gu,' ').replace(/\s+/g,' ').trim();}
    function pair(row){
        return {
            question: row.col_2 || row.item_key || row.col_1 || 'Question',
            answer: row.col_1 || row.col_3 || row.item_key || ''
        };
    }
    function randomPraise(){
        const praise = ['Good!', 'Amazing!', 'Excellent!', 'Very good!', 'Great job!'];
        return praise[Math.floor(Math.random() * praise.length)];
    }
    function randomWrongHelp(){
        const lines = [
            'Try again.',
            'Almost there.',
            'Listen carefully and choose again.',
            'Read the correct answer once.',
            'No problem, practise one more time.'
        ];
        return lines[Math.floor(Math.random() * lines.length)];
    }
    function makeUtterance(text){
        const u=new SpeechSynthesisUtterance(text);
        u.lang=/[\u0900-\u097F]/.test(text)?'hi-IN':'en-IN';
        return u;
    }
    function speak(text){
        if(!window.speechSynthesis || !text) return;
        speechSynthesis.cancel();
        speechSynthesis.speak(makeUtterance(text));
    }
    function speakSequence(lines){
        if(!window.speechSynthesis) return;
        const clean=(lines||[]).map(v=>String(v||'').trim()).filter(Boolean);
        if(!clean.length) return;
        speechSynthesis.cancel();
        let i=0;
        const run=()=>{
            if(i>=clean.length) return;
            const u=makeUtterance(clean[i++]);
            u.onend=()=>setTimeout(run,120);
            speechSynthesis.speak(u);
        };
        run();
    }
    document.getElementById('duoSpeakQuestion')?.addEventListener('click',()=>speak(qText?.textContent||''));

    function render(){
        updatePracticeProgress();
        result.hidden=true; next.hidden=true;
        if(!rows.length){
            qText.textContent='No practice rows added yet.';
            grid.innerHTML='<div class="duo-empty"><b>No practice data found.</b><br>Admin me isi lesson/topic ke andar records import ya add karo. Correct answer ko Column 1 me aur question/Hindi ko Column 2 me rakho.</div>';
            start.hidden=true; return;
        }
        if(idx>=rows.length){
            updatePracticeProgress();
            qText.textContent='Practice complete!';
            const total = rightCount + wrongCount;
            const percent = total ? Math.round((rightCount / total) * 100) : 0;
            grid.innerHTML='<div class="duo-practice-summary"><h3>'+percent+'%</h3><p>Correct: '+rightCount+' / Wrong: '+wrongCount+'</p><small>'+ (percent>=80?'Excellent practice!':(percent>=50?'Good, revise mistakes once.':'Repeat this lesson once more.')) +'</small></div><button class="duo-big-green completeLevel" type="button">COMPLETE LEVEL</button>';
            grid.querySelector('.completeLevel').addEventListener('click',complete);
            start.hidden=true; return;
        }
        const cur=pair(rows[idx]);
        correctAnswer=cur.answer;
        qText.textContent=cur.question;
        let choices=[correctAnswer];
        const pool=[...new Set(rows.map(pair).map(x=>x.answer).filter(Boolean))].filter(v=>normalize(v)!==normalize(correctAnswer));
        // Important: never use an endless random while loop. If unique options are low, use fewer options.
        for(let i=0; i<pool.length && choices.length<4; i++){
            choices.push(pool[i]);
        }
        choices=choices.sort(()=>Math.random()-.5);
        grid.innerHTML='';
        choices.forEach(c=>{
            const btn=document.createElement('button');
            btn.type='button'; btn.className='duo-choice'; btn.textContent=c;
            btn.addEventListener('click',()=>{
                const ok=normalize(c)===normalize(correctAnswer);
                btn.classList.add(ok?'correct':'wrong');
                if(ok){ rightCount++; } else { wrongCount++; }
                result.hidden=false;
                result.className='duo-result-box '+(ok?'ok':'bad');
                const feedbackText = ok ? randomPraise() : randomWrongHelp();
                result.innerHTML = ok
                    ? '<strong>'+feedbackText+'</strong><span>You selected the right answer.</span>'
                    : '<strong>Wrong.</strong><span>'+feedbackText+' Correct answer: <b>'+correctAnswer+'</b></span>';
                speakSequence(ok ? [c, feedbackText] : [c, 'Wrong.', feedbackText]);
                grid.querySelectorAll('button').forEach(b=>b.disabled=true);
                next.hidden=false;
            });
            grid.appendChild(btn);
        });
        start.hidden=true;
        speak(cur.question);
    }
    updatePracticeProgress();
    start?.addEventListener('click',()=>{idx=0;rightCount=0;wrongCount=0;updatePracticeProgress();render();});
    next?.addEventListener('click',()=>{idx++;updatePracticeProgress();render();});
})();
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
