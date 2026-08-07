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
if ($roadmapStudentLoggedIn) {
    $roadmapAccess = roadmap_student_unit_access(current_student_id(), $unitId);
    if (empty($roadmapAccess['allowed'])) {
        redirect('learning-roadmap.php?locked=1#roadmapPath');
    }
}

$page_title = ($unit ? $unit['title'] : 'Lesson') . ' | Learning Roadmap';
$meta_description = 'Learn and practice this roadmap lesson.';
$lightweight_layout = true;
$page_styles = ['assets/css/phase130-roadmap-lesson.css'];
$skip_phase139_mobile_learning_script = true;
$page_late_styles = ['assets/css/phase143-roadmap-practice.css'];
$page_scripts = ['assets/js/phase143-roadmap-practice.js'];
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

        <div class="duo-lesson-header wf-surface-dark" data-wf-surface="dark">
            <span class="duo-label"><?= e($unit['group_title'] ?? 'LESSON') ?></span>
            <h1><?= e($unit['title'] ?? 'Lesson') ?></h1>
            <p><?= e((string)(($unit['description'] ?? '') ?: ($unit['subtitle'] ?? ''))) ?></p><?php if ($smartSourceTitle !== ''): ?><?php endif; ?>
        </div>

        <div class="duo-lesson-tabs" role="tablist" aria-label="Lesson steps">
            <button class="active" type="button" role="tab" aria-selected="true" data-tab="learn"><i class="fa-solid fa-book-open-reader" aria-hidden="true"></i><span>Learn</span></button>
            <button type="button" role="tab" aria-selected="false" data-tab="practice"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i><span>Practice</span></button>
            <button type="button" role="tab" aria-selected="false" data-tab="finish"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Finish</span></button>
        </div>

        <div class="duo-panel active" role="tabpanel" data-panel="learn">
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

        <div class="duo-panel" role="tabpanel" data-panel="practice">
            <div class="duo-exercise">
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
                <div class="duo-voice-guide">
                    <label for="duoVoiceGuide"><input type="checkbox" id="duoVoiceGuide" checked><span><i class="fa-solid fa-volume-high" aria-hidden="true"></i> Voice guide</span></label>
                    <small id="duoVoiceStatus" aria-live="polite">Question and answer feedback will be spoken.</small>
                </div>
                <div class="duo-answer-grid" id="duoAnswerGrid"></div>
                <div class="duo-result-box" id="duoResultBox" hidden></div>
                <button class="duo-check-btn" type="button" id="duoStartPractice">START</button>
                <button class="duo-big-green" type="button" id="duoNextQuestion" hidden>CONTINUE</button>
            </div>
        </div>

        <div class="duo-panel" role="tabpanel" data-panel="finish">
            <div class="duo-finish-card">
                <h2>Level complete?</h2>
                <p>Finish this lesson to save progress and unlock the next step.</p>
                <button class="duo-big-green completeLevel" type="button">COMPLETE LEVEL</button>
                <?php if ($prevId): ?><a class="duo-small-link" href="roadmap-lesson.php?id=<?= e((string)$prevId) ?>">Previous lesson</a><?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
$roadmapPracticeConfig = [
    'storageKey' => 'wfRoadmapCompleted',
    'studentMode' => $roadmapStudentLoggedIn,
    'serverCompleted' => array_values($roadmapServerCompleted),
    'csrfToken' => csrf_token(),
    'unitId' => $unitId,
    'nextId' => $nextId,
    'allLessonIds' => array_map(static fn(array $roadmapUnit): int => (int)$roadmapUnit['id'], $unitsFlat),
    'items' => array_slice($practiceRows, 0, $practiceLimit),
];
?>
<script type="application/json" id="roadmapPracticeConfig"><?= json_encode($roadmapPracticeConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
