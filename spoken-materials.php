<?php
require_once __DIR__ . '/includes/functions.php';
ensure_schema_updates();
material_ensure_schema();

$page_title = 'Spoken Practice Room | ' . app_setting('site_name', APP_NAME);
$meta_description = 'Practise spoken English one sentence at a time with listening, speaking and answer checking.';
$lightweight_layout = true;
$skip_phase139_learning_css = true;
$skip_phase141_learning_css = true;
$skip_phase142_interaction_css = true;
$skip_phase139_mobile_learning_script = true;
$page_late_styles = ['assets/css/phase143-practice-stability.css'];
$page_scripts = ['assets/js/phase143-spoken-practice.js'];

$allowedGoals = ['speak', 'hindi_to_english', 'english_to_hindi', 'revision'];
$requestedGoal = strtolower(trim((string)($_GET['goal'] ?? 'speak')));
if (!in_array($requestedGoal, $allowedGoals, true)) {
    $requestedGoal = 'speak';
}
$defaultCollectionId = material_default_practice_collection_id();

require_once __DIR__ . '/includes/header.php';

wf_page_hero([
    'eyebrow' => 'Daily Practice',
    'title' => 'One sentence. One clear action.',
    'text' => 'Choose a practice mode, then use the voice coach or manual controls to listen, speak and check one sentence at a time.',
    'icon' => 'fa-solid fa-microphone-lines',
    'actions' => [
        ['label' => 'Open Practice', 'url' => '#practice-room', 'icon' => 'fa-solid fa-play'],
        ['label' => 'My Roadmap', 'url' => 'learning-roadmap.php', 'icon' => 'fa-solid fa-route'],
    ],
    'steps' => ['Choose', 'Listen', 'Answer', 'Check'],
    'compact' => true,
]);
?>
<section
    class="section wf143-practice-page"
    id="practice-room"
    data-default-goal="<?= e($requestedGoal) ?>"
    data-default-collection="<?= e((string)$defaultCollectionId) ?>"
>
    <div class="container">
        <header class="wf143-practice-heading">
            <div>
                <span>Practice Room</span>
                <h2>Choose how you want to practise.</h2>
                <p>No filter form and no repeated background loading. Voice output and voice input stay available in one stable flow.</p>
            </div>
        </header>

        <div class="wf143-mode-grid" role="list" aria-label="Practice modes">
            <button type="button" class="wf143-mode-card" data-goal="speak" data-direction="hindi_to_english" role="listitem">
                <span class="wf143-mode-icon"><i class="fa-solid fa-microphone" aria-hidden="true"></i></span>
                <span class="wf143-mode-copy"><b>Speak Daily</b><small>Listen and repeat English</small></span>
                <span class="wf143-mode-arrow"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            </button>
            <button type="button" class="wf143-mode-card" data-goal="hindi_to_english" data-direction="hindi_to_english" role="listitem">
                <span class="wf143-mode-icon"><i class="fa-solid fa-language" aria-hidden="true"></i></span>
                <span class="wf143-mode-copy"><b>Hindi to English</b><small>Translate into English</small></span>
                <span class="wf143-mode-arrow"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            </button>
            <button type="button" class="wf143-mode-card" data-goal="english_to_hindi" data-direction="english_to_hindi" role="listitem">
                <span class="wf143-mode-icon"><i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i></span>
                <span class="wf143-mode-copy"><b>English to Hindi</b><small>Understand the meaning</small></span>
                <span class="wf143-mode-arrow"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            </button>
            <button type="button" class="wf143-mode-card" data-goal="revision" data-direction="hindi_to_english" role="listitem">
                <span class="wf143-mode-icon"><i class="fa-solid fa-star" aria-hidden="true"></i></span>
                <span class="wf143-mode-copy"><b>Revision</b><small>Repeat saved wrong answers</small></span>
                <span class="wf143-mode-arrow"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            </button>
        </div>

        <div class="wf143-practice-status" id="practiceReady" role="status">
            <span><i class="fa-solid fa-hand-pointer" aria-hidden="true"></i></span>
            <div><b>Select one mode to begin</b><small>Practice loads only after your tap.</small></div>
        </div>

        <div class="wf143-practice-status is-loading" id="practiceLoader" role="status" hidden>
            <span><i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i></span>
            <div><b>Preparing practice</b><small>Loading a small set of sentences...</small></div>
        </div>

        <div class="wf143-practice-status is-error" id="practiceError" role="alert" hidden>
            <span><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
            <div><b>Practice could not load</b><small id="practiceErrorText">Please try again.</small></div>
            <button type="button" id="practiceRetryLoad">Try Again</button>
        </div>

        <div class="wf143-practice-workspace" id="practiceApp" hidden>
            <div class="wf143-workspace-top">
                <div>
                    <span id="practiceModeLabel">Practice</span>
                    <strong id="practiceCounter">1 / 1</strong>
                </div>
                <button type="button" id="practiceChangeMode"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i><span>Change mode</span></button>
            </div>
            <div class="wf143-progress-track" aria-hidden="true"><span id="practiceMeterBar"></span></div>

            <article class="wf143-question-card" id="practiceQuestionCard">
                <div class="wf143-question-meta">
                    <span id="practiceLevel">Beginner</span>
                    <span id="practiceTopic">Spoken Practice</span>
                </div>
                <p class="wf143-question-kicker"><i class="fa-solid fa-volume-high" aria-hidden="true"></i><span id="practiceInstruction">Listen and answer</span></p>
                <h3 id="practiceQuestion">Question</h3>
                <p class="wf143-roman" id="practiceRoman" hidden></p>
            </article>

            <div class="wf143-answer-panel">
                <div class="wf144-voice-coach">
                    <label class="wf144-voice-toggle" for="practiceHandsfree">
                        <input type="checkbox" id="practiceHandsfree" checked>
                        <span class="wf144-voice-switch" aria-hidden="true"><i></i></span>
                        <span class="wf144-voice-copy"><b>Voice coach</b><small>Question plays once, then the mic listens for one answer.</small></span>
                    </label>
                    <p><i class="fa-solid fa-rotate-right" aria-hidden="true"></i><span>Say “again” or “dobara bolo” to hear the question again.</span></p>
                </div>
                <div class="wf143-audio-actions">
                    <button type="button" id="practiceListen"><i class="fa-solid fa-volume-high" aria-hidden="true"></i><span>Listen</span></button>
                    <button type="button" id="practiceSpeak"><i class="fa-solid fa-microphone" aria-hidden="true"></i><span>Speak answer</span></button>
                    <button type="button" id="practiceStop" hidden><i class="fa-solid fa-stop" aria-hidden="true"></i><span>Stop</span></button>
                </div>
                <p class="wf143-voice-status" id="practiceVoiceStatus" aria-live="polite">Voice coach is ready. You can also use Listen or Speak answer manually.</p>
                <label for="practiceAnswer"><span>Your answer</span><small>Type or use the microphone.</small></label>
                <textarea id="practiceAnswer" rows="3" spellcheck="false" autocomplete="off" placeholder="Write your answer here..."></textarea>
                <div class="wf143-answer-actions">
                    <button type="button" id="practiceCheck" class="is-primary"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Check Answer</span></button>
                    <button type="button" id="practiceClear"><i class="fa-solid fa-eraser" aria-hidden="true"></i><span>Clear</span></button>
                </div>
            </div>

            <div class="wf143-result" id="practiceResult" hidden></div>

            <div class="wf143-navigation">
                <button type="button" id="practicePrevious"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i><span>Previous</span></button>
                <button type="button" id="practiceNext" class="is-primary"><span>Next Sentence</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
            </div>
        </div>
    </div>
</section>

<section class="section section-cream">
    <div class="container">
        <div class="dark-cta wf-surface-dark" data-wf-surface="dark">
            <div><h2>Need personal speaking guidance?</h2><p>Practise daily, then book a counselling call for teacher support.</p></div>
            <a class="btn btn-primary" href="admission.php?source=practice-room">Book Free Counselling</a>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
