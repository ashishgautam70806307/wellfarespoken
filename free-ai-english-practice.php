<?php
require_once __DIR__ . '/includes/functions.php';
ensure_core_schema_columns();
ensure_schema_updates();
material_ensure_schema();
$page_title = app_setting('seo_practice_title', 'Free AI English Practice Lab');
$meta_description = app_setting('seo_practice_description', 'Practise English tenses, sentences, situations and speaking for free.');
$categories = fetch_practice_categories();
$lessons = fetch_practice_lessons(0, 100);
$summary = practice_attempt_summary();
$practiceHero = fetch_hero_banner('practice');
$initialLessonId = isset($_GET['lesson']) ? (int)$_GET['lesson'] : 0;
$csrf = csrf_token();
$lightweight_layout = true;
$page_final_styles = ['assets/css/phase180-old-design-mobile-fix.css'];
require_once __DIR__ . '/includes/header.php';
?>
<?php
wf_page_hero([
    'eyebrow' => 'Quick English Practice',
    'title' => 'Translate, correct and practise one question at a time.',
    'text' => 'Use the quick tool for help, then choose a lesson and complete practice without page refresh.',
    'icon' => 'fa-solid fa-wand-magic-sparkles',
    'actions' => [
        ['label' => 'Start Practice', 'url' => '#practice-app', 'icon' => 'fa-solid fa-play'],
        ['label' => 'Study Material', 'url' => 'spoken-materials.php', 'icon' => 'fa-solid fa-book-open'],
    ],
    'steps' => ['Choose lesson', 'Answer', 'Check', 'Next question'],
    'compact' => true,
]);
?>
<section class="wf127-tool-stats-section">
    <div class="container"><div class="wf127-tool-stats" id="practiceTopStats"><div><strong><?= e((string)$summary['total']) ?></strong><span>Today Attempts</span></div><div><strong><?= e((string)$summary['score']) ?></strong><span>Practice Score</span></div><div><strong><i class="fa-solid fa-bolt"></i></strong><span>No Page Refresh</span></div></div></div>
</section>

<section class="section" id="quick-tool">
    <div class="container">
        <div class="ai-simple-tool">
            <div class="section-head">
                <div>
                    <h2>Quick Translator + Sentence Corrector</h2>
                    <p>Use this only for quick help. For real improvement, students should practise lesson questions below. Online translation works when server internet/cURL is available.</p>
                </div>
                <a class="btn btn-light" href="spoken-materials.php">Open Study Material</a>
            </div>
            <div class="ai-tool-grid">
                <form class="ai-tool-box ajax-form" id="quickPracticeForm" method="post" action="practice-quick-api.php">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                    <input type="hidden" name="quick_practice" value="1">
                    <label><strong>Choose Mode</strong></label>
                    <div class="mode-pill-row">
                        <label><input type="radio" name="quick_mode" value="sentence_correction" checked> Correct Sentence</label>
                        <label><input type="radio" name="quick_mode" value="hindi_to_english"> Hindi to English</label>
                        <label><input type="radio" name="quick_mode" value="english_to_hindi"> English to Hindi</label>
                    </div>
                    <div class="quick-input-shell">
                        <textarea name="quick_input" id="quickInputText" placeholder="Example: मैं रोज अंग्रेजी बोलता हूँ। / I goes to markat."></textarea>
                        <button class="quick-mic-btn" id="quickMicBtn" type="button" aria-label="Speak and fill input">🎤</button>
                    </div>
                    <div class="quick-action-row">
                        <button class="btn btn-primary" type="submit">Practice Now</button>
                        <button class="btn btn-soft" id="quickClearBtn" type="button">Clear</button>
                    </div>
                    <span class="help">Hindi↔English uses online Google translation endpoint when server internet is available. Correction uses local + AI logic if connected.</span>
                </form>
                <div class="ai-tool-box">
                    <h3>Result</h3>
                    <div class="ai-tool-result" id="quickPracticeResult">
                        <strong>Ready</strong>
                        <p>Type a sentence, choose a mode, and click Practice Now. The result will appear here without page refresh.</p>
                        <small>Tip: Translate first, listen/speak, then practise lesson-wise questions below. For teacher-level explanation, use AI Teacher.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section-soft" id="practice-app">
    <div class="container">
        <div class="section-head">
            <div class="section-title">
                <span class="eyebrow">App-like Practice</span>
                <h2>Practise one question at a time</h2>
                <p>This is the easier flow for children and students: choose lesson, answer, check, next question.</p>
            </div>
            <a class="btn btn-soft" href="admission.php?source=practice">Need Teacher Help?</a>
        </div>

        <div class="practice-app-shell">
            <aside class="practice-lesson-panel">
                <div class="practice-step-title"><span>Step 1</span><strong>Choose Practice Lesson</strong></div>
                <?php foreach ($categories as $cat): ?>
                    <?php $catLessons = fetch_practice_lessons((int)$cat['id'], 30); if (!$catLessons) continue; ?>
                    <div class="lesson-group">
                        <h3><?= e($cat['icon'] ?: '✅') ?> <?= e($cat['category_name']) ?></h3>
                        <?php foreach ($catLessons as $lesson): ?>
                            <button type="button" class="lesson-pick-btn" data-lesson-id="<?= e((string)$lesson['id']) ?>">
                                <span><?= e($lesson['level'] ?: 'Practice') ?><?= $lesson['tense_name'] ? ' • ' . e($lesson['tense_name']) : '' ?></span>
                                <strong><?= e($lesson['lesson_title']) ?></strong>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </aside>

            <main class="practice-workspace">
                <div class="practice-work-head">
                    <div>
                        <span id="activeLessonMeta">Step 2</span>
                        <h2 id="activeLessonTitle">Select a lesson to start</h2>
                        <p id="activeLessonInstructions">No page refresh is needed. The question will appear here after selecting a lesson.</p>
                    </div>
                    <div class="practice-progress-card">
                        <span id="questionCounter">0 / 0</span>
                        <div class="progress-track"><i id="practiceProgressBar"></i></div>
                        <small id="practiceScoreLine">Score: <?= e((string)$summary['score']) ?></small>
                    </div>
                </div>

                <div class="practice-question-stage" id="practiceQuestionStage">
                    <div class="practice-empty-state">
                        <h3>Ready to practise?</h3>
                        <p>Choose any lesson from the left. Students will get one question at a time with instant correction.</p>
                        <div class="practice-flow-chips"><span>Hindi → English</span><span>English → Hindi</span><span>Tenses</span><span>Situation Answers</span><span>Voice Input</span></div>
                    </div>
                </div>

                <div class="practice-result-stage" id="practiceResultStage" hidden></div>

                <div class="practice-controls">
                    <button class="btn btn-soft" type="button" id="prevQuestionBtn" disabled>Previous</button>
                    <button class="btn btn-primary" type="button" id="checkAnswerBtn" disabled>Check Answer</button>
                    <button class="btn btn-soft" type="button" id="nextQuestionBtn" disabled>Next Question</button>
                </div>
            </main>
        </div>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <div class="dark-cta">
            <div><h2><?= e(app_setting('practice_cta_title', 'Want teacher guidance after practice?')) ?></h2><p><?= e(app_setting('practice_cta_body', 'Share your practice score with the counsellor and book a free demo class for personal spoken English correction.')) ?></p></div>
            <a class="btn btn-primary" href="admission.php?source=practice">Book Free Demo</a>
        </div>
    </div>
</section>

<script>
(function(){
    var csrfToken = <?= json_encode($csrf) ?>;
    var initialLessonId = <?= (int)$initialLessonId ?>;
    var currentLesson = null;
    var questions = [];
    var index = 0;
    var checkedMap = {};

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>'"]/g, function(char) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char];
        });
    }
    function qs(id){ return document.getElementById(id); }
    function setLoading(message) {
        qs('practiceQuestionStage').innerHTML = '<div class="practice-empty-state"><h3>Loading...</h3><p>' + escapeHtml(message || 'Please wait.') + '</p></div>';
        qs('practiceResultStage').hidden = true;
        qs('checkAnswerBtn').disabled = true;
        qs('nextQuestionBtn').disabled = true;
        qs('prevQuestionBtn').disabled = true;
    }
    function updateStats(summary) {
        if (!summary) return;
        qs('practiceScoreLine').textContent = 'Score: ' + (summary.score || 0);
        var top = qs('practiceTopStats');
        if (top) {
            top.innerHTML = '<div class="stat"><strong>' + escapeHtml(summary.total || 0) + '</strong><span>Today Attempts</span></div><div class="stat"><strong>' + escapeHtml(summary.score || 0) + '</strong><span>Practice Score</span></div><div class="stat"><strong>AJAX</strong><span>No Page Refresh</span></div>';
        }
    }
    function activateLessonButton(lessonId) {
        document.querySelectorAll('.lesson-pick-btn').forEach(function(btn){
            btn.classList.toggle('active', String(btn.dataset.lessonId) === String(lessonId));
        });
    }
    function loadLesson(lessonId) {
        setLoading('Opening selected practice lesson without page refresh.');
        activateLessonButton(lessonId);
        fetch('practice-session-api.php?action=lesson&lesson_id=' + encodeURIComponent(lessonId), {headers:{'X-Requested-With':'XMLHttpRequest'}})
            .then(function(res){ return res.json(); })
            .then(function(data){
                if (!data.success) throw new Error(data.message || 'Could not load lesson.');
                currentLesson = data.lesson;
                questions = data.questions || [];
                index = 0;
                checkedMap = {};
                qs('activeLessonMeta').textContent = (currentLesson.level || 'Practice') + (currentLesson.tense_name ? ' • ' + currentLesson.tense_name : '');
                qs('activeLessonTitle').textContent = currentLesson.title || 'Practice Lesson';
                qs('activeLessonInstructions').textContent = currentLesson.instructions || 'Answer one question at a time.';
                updateStats(data.summary);
                renderQuestion();
                history.replaceState(null, '', 'free-ai-english-practice.php?lesson=' + encodeURIComponent(lessonId) + '#practice-app');
            })
            .catch(function(err){
                qs('practiceQuestionStage').innerHTML = '<div class="practice-empty-state error"><h3>Could not open lesson</h3><p>' + escapeHtml(err.message) + '</p><small>Run Admin > System Check once, then import sql/practice-testing-data.sql.</small></div>';
            });
    }
    function renderQuestion() {
        var total = questions.length;
        qs('questionCounter').textContent = total ? (index + 1) + ' / ' + total : '0 / 0';
        qs('practiceProgressBar').style.width = total ? (((index + 1) / total) * 100) + '%' : '0%';
        qs('practiceResultStage').hidden = true;
        qs('practiceResultStage').innerHTML = '';
        qs('prevQuestionBtn').disabled = index <= 0;
        qs('nextQuestionBtn').disabled = !total || index >= total - 1;
        qs('checkAnswerBtn').disabled = !total;
        if (!total) {
            qs('practiceQuestionStage').innerHTML = '<div class="practice-empty-state"><h3>No questions yet</h3><p>Add questions in Admin > AI Practice Lab or import sql/practice-testing-data.sql.</p></div>';
            return;
        }
        var q = questions[index];
        var html = '<div class="practice-single-card">';
        html += '<div class="question-type">' + escapeHtml((q.question_type || 'practice').replace(/_/g, ' ')) + '</div>';
        html += '<h3>' + escapeHtml(q.question_text) + '</h3>';
        html += '<div class="student-answer-area">';
        if (q.options && q.options.length) {
            html += '<div class="option-stack app-options">';
            q.options.forEach(function(opt, i){
                html += '<label><input type="radio" name="app_answer" value="' + escapeHtml(opt) + '"> <span>' + escapeHtml(opt) + '</span></label>';
            });
            html += '</div>';
        } else {
            html += '<textarea id="studentAnswer" rows="4" placeholder="Write your answer here. Example: I speak English every day."></textarea>';
            if (q.has_voice) html += '<button class="btn btn-sm btn-soft" id="appVoiceBtn" type="button">🎤 Speak Answer</button>';
        }
        html += '</div>';
        html += '<small class="help">Click Check Answer. Your result will show here instantly without refreshing the page.</small>';
        html += '</div>';
        qs('practiceQuestionStage').innerHTML = html;
        if (checkedMap[q.id]) renderResult(checkedMap[q.id]);
        var voiceBtn = qs('appVoiceBtn');
        if (voiceBtn) voiceBtn.addEventListener('click', startVoiceInput);
    }
    function getAnswer() {
        var selected = document.querySelector('input[name="app_answer"]:checked');
        if (selected) return selected.value;
        var textarea = qs('studentAnswer');
        return textarea ? textarea.value : '';
    }
    function checkAnswer() {
        if (!questions.length) return;
        var q = questions[index];
        var answer = getAnswer().trim();
        if (!answer) {
            qs('practiceResultStage').hidden = false;
            qs('practiceResultStage').innerHTML = '<div class="practice-result is-improve"><strong>Write answer first</strong><p>Please write, speak, or select an answer before checking.</p></div>';
            return;
        }
        var btn = qs('checkAnswerBtn');
        var original = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Checking...';
        qs('practiceResultStage').hidden = false;
        qs('practiceResultStage').innerHTML = '<div class="practice-result"><strong>Checking...</strong><p>Your answer is being checked without page refresh.</p></div>';
        var fd = new FormData();
        fd.append('action', 'check');
        fd.append('csrf_token', csrfToken);
        fd.append('question_id', q.id);
        fd.append('answer', answer);
        fetch('practice-session-api.php', {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}})
            .then(function(res){ return res.json(); })
            .then(function(data){
                if (!data.success) throw new Error(data.message || 'Could not check answer.');
                checkedMap[q.id] = data.result;
                renderResult(data.result);
                updateStats(data.summary);
            })
            .catch(function(err){
                qs('practiceResultStage').innerHTML = '<div class="practice-result is-improve"><strong>Could not check</strong><p>' + escapeHtml(err.message) + '</p><small>No refresh happened. Please try again.</small></div>';
            })
            .finally(function(){ btn.disabled = false; btn.textContent = original; });
    }
    function renderResult(r) {
        var ok = r && r.is_correct;
        var html = '<div class="practice-result ' + (ok ? 'is-correct' : 'is-improve') + '">';
        html += '<div><strong>' + (ok ? '✅ Correct' : '💡 Improve this answer') + '</strong><p>' + escapeHtml(r.feedback || '') + '</p><span class="score-chip">Score: ' + escapeHtml(r.score || 0) + '/10</span></div>';
        html += '<div><span>Correct / Natural Answer</span><h3>' + escapeHtml(r.natural_answer || r.corrected_answer || r.correct_answer || '') + '</h3>';
        if (r.explanation) html += '<p>' + escapeHtml(r.explanation) + '</p>';
        if (r.answer_help) html += '<p><strong>Hint:</strong> ' + escapeHtml(r.answer_help) + '</p>';
        if (r.accepted_answers && r.accepted_answers.length) {
            html += '<div class="answer-list"><small>Teacher accepted answers</small>';
            r.accepted_answers.slice(0, 4).forEach(function(ans){ html += '<span>' + escapeHtml(ans) + '</span>'; });
            html += '</div>';
        }
        if (r.match_type) html += '<small>Match: ' + escapeHtml(r.match_type.replace(/_/g, ' ')) + '</small>';
        if (r.ai_feedback) html += '<div class="ai-feedback-box">' + escapeHtml(r.ai_feedback).replace(/\n/g, '<br>') + '</div>';
        if (r.ai_status === 'fallback') html += '<div class="ai-feedback-box muted">AI is unavailable, so local feedback is shown safely.</div>';
        html += '<small>' + escapeHtml(r.next_step || 'Now try the next question.') + '</small></div></div>';
        qs('practiceResultStage').hidden = false;
        qs('practiceResultStage').innerHTML = html;
    }
    function startVoiceInput() {
        var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        var textarea = qs('studentAnswer');
        if (!SpeechRecognition || !textarea) { alert('Voice input works best in Chrome. Please type your answer.'); return; }
        var recognition = new SpeechRecognition();
        recognition.lang = 'en-IN';
        recognition.interimResults = false;
        this.textContent = '🎙 Listening...';
        var btn = this;
        recognition.onresult = function(event){ textarea.value = event.results[0][0].transcript; };
        recognition.onerror = function(){ alert('Could not hear clearly. Please try again or type your answer.'); };
        recognition.onend = function(){ btn.textContent = '🎤 Speak Answer'; };
        recognition.start();
    }

    document.querySelectorAll('.lesson-pick-btn').forEach(function(btn){
        btn.addEventListener('click', function(){ loadLesson(btn.dataset.lessonId); });
    });
    qs('checkAnswerBtn').addEventListener('click', checkAnswer);
    qs('prevQuestionBtn').addEventListener('click', function(){ if (index > 0) { index--; renderQuestion(); } });
    qs('nextQuestionBtn').addEventListener('click', function(){ if (index < questions.length - 1) { index++; renderQuestion(); } });

    var quickPracticeForm = qs('quickPracticeForm');
    if (quickPracticeForm) {
        quickPracticeForm.addEventListener('submit', function(event) {
            event.preventDefault();
            var resultBox = qs('quickPracticeResult');
            var submitButton = quickPracticeForm.querySelector('button[type="submit"]');
            var originalText = submitButton ? submitButton.textContent : '';
            if (submitButton) { submitButton.disabled = true; submitButton.textContent = 'Checking...'; }
            resultBox.innerHTML = '<strong>Checking...</strong><p>Please wait. Auto local practice logic is preparing your result without page refresh.</p>';
            fetch(quickPracticeForm.getAttribute('action'), { method:'POST', body:new FormData(quickPracticeForm), headers:{'X-Requested-With':'XMLHttpRequest'} })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (!data.success) throw new Error(data.message || 'Could not check this sentence.');
                    var r = data.result || {};
                    resultBox.innerHTML = '<strong>' + escapeHtml(r.title || 'Result') + '</strong><p class="quick-answer">' + escapeHtml(r.answer || '') + '</p>' + (r.confidence ? '<span class="quick-badge">' + escapeHtml(r.confidence) + ' confidence</span>' : '') + (r.note ? '<small>' + escapeHtml(r.note) + '</small>' : '');
                })
                .catch(function(error) { resultBox.innerHTML = '<strong>Could not check</strong><p>' + escapeHtml(error.message || 'Please try again.') + '</p><small>No page refresh happened.</small>'; })
                .finally(function() { if (submitButton) { submitButton.disabled = false; submitButton.textContent = originalText; } });
        });
    }


    var quickMicBtn = qs('quickMicBtn');
    var quickClearBtn = qs('quickClearBtn');
    if (quickClearBtn) {
        quickClearBtn.addEventListener('click', function(){
            var input = qs('quickInputText');
            if (input) input.value = '';
            qs('quickPracticeResult').innerHTML = '<strong>Ready</strong><p>Type or speak a sentence, choose a mode, and click Practice Now.</p><small>Auto local logic works without admin-created rules.</small>';
        });
    }
    if (quickMicBtn) {
        quickMicBtn.addEventListener('click', function(){
            var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            var input = qs('quickInputText');
            if (!SpeechRecognition || !input) { alert('Mic input works best in Chrome. Please type your sentence.'); return; }
            var selectedMode = (quickPracticeForm.querySelector('input[name="quick_mode"]:checked') || {}).value || 'sentence_correction';
            var recognition = new SpeechRecognition();
            recognition.lang = selectedMode === 'hindi_to_english' ? 'hi-IN' : 'en-IN';
            recognition.interimResults = false;
            var oldText = quickMicBtn.textContent;
            quickMicBtn.textContent = '🎙';
            quickMicBtn.classList.add('listening');
            recognition.onresult = function(event){ input.value = event.results[0][0].transcript; input.focus(); };
            recognition.onerror = function(){ alert('Could not hear clearly. Please try again or type manually.'); };
            recognition.onend = function(){ quickMicBtn.textContent = oldText; quickMicBtn.classList.remove('listening'); };
            recognition.start();
        });
    }

    if (initialLessonId > 0) loadLesson(initialLessonId);
})();
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
