<?php
require_once __DIR__ . '/includes/functions.php';
ensure_schema_updates();
weekly_test_ensure_schema();

$page_title = 'Student Test Center | ' . app_setting('site_name', APP_NAME);
$meta_description = 'Basic practice, previous paper practice, scheduled weekly exams and student results in one clear test center.';
$page_styles = ['assets/css/phase130-weekly-test.css'];
$page_scripts = ['assets/js/phase130-weekly-test.js'];
$lightweight_layout = true;
$csrf = csrf_token();

$testPools = [
    'basic' => weekly_test_fetch_tests('basic'),
    'previous' => weekly_test_fetch_tests('previous'),
    'upcoming' => weekly_test_fetch_tests('upcoming'),
];
$student = is_student() ? fetch_current_student() : null;
$studentAttempts = $student ? weekly_test_fetch_attempts_for_student((int)$student['id'], 20) : [];
$latestAttempt = $studentAttempts[0] ?? null;

function wf129_test_preferred(array $tests): ?array
{
    foreach ($tests as $test) {
        if ((int)($test['ready_now'] ?? 0) === 1) return $test;
    }
    foreach ($tests as $test) {
        if (strtolower((string)($test['status'] ?? '')) === 'active') return $test;
    }
    return $tests[0] ?? null;
}

function wf129_test_status(?array $test, string $type): array
{
    if (!$test) return ['No paper', 'is-empty'];
    if ((int)($test['ready_now'] ?? 0) === 1) {
        return [$type === 'upcoming' ? 'Exam open' : 'Available now', 'is-ready'];
    }
    $starts = trim((string)($test['starts_at'] ?? ''));
    if ($starts !== '' && strtotime($starts) > time()) return ['Opens ' . date('d M, h:i A', strtotime($starts)), 'is-scheduled'];
    if (strtolower((string)($test['status'] ?? '')) === 'active') return ['Check schedule', 'is-scheduled'];
    return ['Not published', 'is-empty'];
}

$preferred = [];
foreach ($testPools as $type => $tests) $preferred[$type] = wf129_test_preferred($tests);

$cards = [
    'basic' => [
        'eyebrow' => 'Start here',
        'title' => 'Basic Test',
        'text' => 'Easy daily-use questions for confidence and regular practice.',
        'icon' => 'fa-solid fa-seedling',
        'button' => 'Start Basic Test',
    ],
    'previous' => [
        'eyebrow' => 'Practice old paper',
        'title' => 'Previous Test',
        'text' => 'Repeat an earlier paper and improve weak answers.',
        'icon' => 'fa-solid fa-clock-rotate-left',
        'button' => 'Open Previous Test',
    ],
    'upcoming' => [
        'eyebrow' => 'Official schedule',
        'title' => 'Upcoming Test',
        'text' => 'Login, check eligibility and give the scheduled weekly exam.',
        'icon' => 'fa-solid fa-calendar-check',
        'button' => 'Check Upcoming Test',
    ],
];

require_once __DIR__ . '/includes/header.php';
?>
<section class="wf129-test-hero">
    <div class="container wf129-test-hero-inner">
        <div>
            <span class="wf-section-kicker"><i class="fa-solid fa-clipboard-check"></i> Student Test Center</span>
            <h1>Choose one test and start with confidence.</h1>
            <p>Basic, previous and upcoming tests in one clear process.</p>
        </div>
        <div class="wf129-test-profile <?= $student ? 'is-verified' : '' ?>">
            <span><i class="fa-solid <?= $student ? 'fa-user-check' : 'fa-user' ?>"></i></span>
            <div><small><?= $student ? 'Verified student' : 'Guest practice' ?></small><b><?= e($student ? (string)$student['full_name'] : 'Basic & previous available') ?></b></div>
        </div>
    </div>
</section>

<section class="wf129-test-page">
    <div class="container">
        <div class="wf129-test-flow" aria-label="Test process">
            <span><b>1</b><i class="fa-solid fa-hand-pointer"></i><em>Choose</em></span>
            <span><b>2</b><i class="fa-solid fa-circle-check"></i><em>Check</em></span>
            <span><b>3</b><i class="fa-solid fa-pen-to-square"></i><em>Give Test</em></span>
            <span><b>4</b><i class="fa-solid fa-chart-column"></i><em>Result</em></span>
        </div>

        <header class="wf129-test-heading">
            <div><span class="wf-section-kicker">Select test type</span><h2>What do you want to do today?</h2></div>
            <p>One card, one action.</p>
        </header>

        <div class="wf129-test-card-grid">
            <?php foreach ($cards as $type => $card):
                $test = $preferred[$type];
                [$statusText, $statusClass] = wf129_test_status($test, $type);
                $requiresLogin = $type === 'upcoming';
            ?>
                <article class="wf129-test-card type-<?= e($type) ?>" data-test-card="<?= e($type) ?>">
                    <div class="wf129-test-card-top">
                        <span class="wf129-test-card-icon"><i class="<?= e($card['icon']) ?>"></i></span>
                        <span class="wf129-test-status <?= e($statusClass) ?>"><i class="fa-solid fa-circle"></i><?= e($statusText) ?></span>
                    </div>
                    <small><?= e($card['eyebrow']) ?></small>
                    <h3><?= e($card['title']) ?></h3>
                    <p><?= e($card['text']) ?></p>
                    <div class="wf129-test-meta">
                        <span><i class="fa-regular fa-clock"></i><b><?= e((string)($test['duration_minutes'] ?? 0)) ?></b> min</span>
                        <span><i class="fa-solid fa-list-ol"></i><b><?= e((string)($test['question_count'] ?? 0)) ?></b> questions</span>
                    </div>
                    <?php if ($requiresLogin && !$student): ?>
                        <a class="wf-btn wf-btn-primary wf129-test-card-action" href="student-auth.php?redirect=weekly-test.php%3Ftype%3Dupcoming"><span>Login for Test</span></a>
                    <?php else: ?>
                        <button class="wf-btn wf-btn-primary wf129-test-card-action" type="button" data-select-test="<?= e($type) ?>" <?= !$test ? 'disabled' : '' ?>><span><?= e($card['button']) ?></span></button>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <section class="wf129-test-setup" id="wfTestSetup" hidden aria-live="polite">
            <header>
                <div class="wf129-test-selected-icon"><i id="wfSelectedIcon" class="fa-solid fa-seedling"></i></div>
                <div><span id="wfSelectedType">Basic Test</span><h2 id="wfSelectedTitle">Choose a test paper</h2><p id="wfSelectedMeta">Paper details will appear here.</p></div>
                <button type="button" id="wfCloseTestSetup" aria-label="Close test setup"><i class="fa-solid fa-xmark"></i></button>
            </header>

            <div class="wf129-test-setup-grid">
                <label class="wf129-field wf129-test-paper-field">
                    <span><i class="fa-solid fa-file-circle-check"></i> Test Paper</span>
                    <select id="wfTestPaper"></select>
                </label>
                <?php if (!$student): ?>
                    <label class="wf129-field"><span><i class="fa-solid fa-user"></i> Student Name</span><input id="wfGuestName" maxlength="100" autocomplete="name" placeholder="Enter your name"></label>
                    <label class="wf129-field"><span><i class="fa-solid fa-mobile-screen-button"></i> Mobile Number</span><input id="wfGuestPhone" maxlength="10" inputmode="numeric" autocomplete="tel" placeholder="10 digit mobile"></label>
                <?php else: ?>
                    <div class="wf129-test-verified"><i class="fa-solid fa-user-shield"></i><div><small>Verified student</small><b><?= e((string)$student['full_name']) ?></b></div></div>
                <?php endif; ?>
            </div>

            <div class="wf129-test-safety">
                <span><i class="fa-solid fa-floppy-disk"></i><b>Auto-save</b></span>
                <span><i class="fa-solid fa-hourglass-half"></i><b>Server timer</b></span>
                <span><i class="fa-solid fa-lock"></i><b>Secure result</b></span>
            </div>

            <footer>
                <p id="wfTestMessage">Choose an available test paper.</p>
                <button class="wf-btn wf-btn-primary" id="wfStartTest" type="button" disabled><span>Start Test</span></button>
            </footer>
        </section>

        <?php if ($student): ?>
            <section class="wf129-test-results" id="my-results">
                <header><div><span class="wf-section-kicker">My progress</span><h2>Test history and results</h2></div><span><?= e((string)count($studentAttempts)) ?> attempts</span></header>
                <?php if ($studentAttempts): ?>
                    <div class="wf129-test-result-list">
                        <?php foreach ($studentAttempts as $attempt):
                            $score = $attempt['admin_score'] ?? $attempt['auto_score'] ?? null;
                            $status = weekly_test_status_badge((string)($attempt['status'] ?? ''));
                        ?>
                            <a href="<?= e(weekly_test_result_url($attempt)) ?>">
                                <span class="wf129-result-icon"><i class="fa-solid fa-chart-simple"></i></span>
                                <div><small><?= e(ucfirst((string)($attempt['test_type'] ?? 'test'))) ?> test</small><b><?= e((string)($attempt['test_title'] ?? 'Test result')) ?></b><em><?= e($status) ?></em></div>
                                <strong><?= $score !== null ? e((string)$score) : '—' ?><small>/<?= e((string)($attempt['total_marks'] ?? '—')) ?></small></strong>
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="wf129-test-empty"><i class="fa-solid fa-chart-line"></i><h3>No result yet</h3><p>Complete a basic or weekly test. Your result will appear here.</p></div>
                <?php endif; ?>
            </section>
        <?php else: ?>
            <aside class="wf129-test-login-note"><span><i class="fa-solid fa-user-lock"></i></span><div><h2>Save every result</h2><p>Student login se weekly exam, result history aur teacher feedback ek place par milta hai.</p></div><a class="wf-btn wf-btn-secondary" href="student-auth.php?redirect=weekly-test.php%3Ftype%3Dupcoming"><span>Student Login</span></a></aside>
        <?php endif; ?>
    </div>
</section>

<script id="wfWeeklyTestData" type="application/json"><?= json_encode([
    'csrf' => $csrf,
    'isStudent' => (bool)$student,
    'pools' => $testPools,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
