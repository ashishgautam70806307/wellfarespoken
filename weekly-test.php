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

$allowedTypes = ['basic', 'previous', 'upcoming'];
$requestedType = strtolower(trim((string)($_GET['type'] ?? '')));
if (!in_array($requestedType, $allowedTypes, true)) $requestedType = '';
$requestedTestId = max(0, (int)($_GET['test_id'] ?? 0));

$weeklySchema = weekly_test_schema_status();
$testSystemError = '';
$testPools = ['basic' => [], 'previous' => [], 'upcoming' => []];
if (!($weeklySchema['ready'] ?? false)) {
    $testSystemError = 'Weekly Test database upgrade is pending. Institute admin should import sql/wellfare_english_complete.sql once.';
} else {
    try {
        $testPools = [
            'basic' => weekly_test_fetch_tests('basic'),
            'previous' => weekly_test_fetch_tests('previous'),
            'upcoming' => weekly_test_fetch_tests('upcoming'),
        ];
    } catch (Throwable $e) {
        error_log('[weekly-test-page] ' . $e->__toString());
        $testSystemError = 'Weekly Test data could not be loaded. Please contact the institute.';
    }
}
$invalidRequestedPaper = false;
if ($requestedType !== '' && $requestedTestId > 0) {
    $requestedPool = $testPools[$requestedType] ?? [];
    $invalidRequestedPaper = !array_filter($requestedPool, static fn(array $paper): bool => (int)($paper['id'] ?? 0) === $requestedTestId);
    if ($invalidRequestedPaper) {
        $requestedType = '';
        $requestedTestId = 0;
    }
}

$student = is_student() ? fetch_current_student() : null;
if ($student) private_no_store();
$studentName = trim((string)($student['full_name'] ?? $student['student_name'] ?? $student['name'] ?? 'Student'));
$studentAttempts = [];
if ($student && $testSystemError === '') {
    try {
        $studentAttempts = weekly_test_fetch_attempts_for_student((int)$student['id'], 20);
    } catch (Throwable $e) {
        error_log('[weekly-test-attempts] ' . $e->__toString());
    }
}

function wf133_test_preferred(array $tests, int $requestedTestId = 0): ?array
{
    if ($requestedTestId > 0) {
        foreach ($tests as $test) if ((int)($test['id'] ?? 0) === $requestedTestId) return $test;
    }
    foreach ($tests as $test) if ((int)($test['ready_now'] ?? 0) === 1) return $test;
    foreach ($tests as $test) if (strtolower((string)($test['status'] ?? '')) === 'active') return $test;
    return $tests[0] ?? null;
}

function wf133_test_status(?array $test, string $type): array
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
foreach ($testPools as $type => $tests) {
    $preferred[$type] = wf133_test_preferred($tests, $requestedType === $type ? $requestedTestId : 0);
}

$cards = [
    'basic' => [
        'eyebrow' => 'Start here',
        'title' => 'Basic Test',
        'text' => 'Easy daily-use questions for regular practice.',
        'icon' => 'fa-solid fa-seedling',
        'button' => 'Start Basic Test',
        'mobile_button' => 'Basic',
    ],
    'previous' => [
        'eyebrow' => 'Practice old paper',
        'title' => 'Previous Test',
        'text' => 'Repeat an earlier paper and improve weak answers.',
        'icon' => 'fa-solid fa-clock-rotate-left',
        'button' => 'Open Previous Test',
        'mobile_button' => 'Previous',
    ],
    'upcoming' => [
        'eyebrow' => 'Official schedule',
        'title' => 'Upcoming Test',
        'text' => 'Login and give the scheduled weekly exam.',
        'icon' => 'fa-solid fa-calendar-check',
        'button' => 'Check Upcoming Test',
        'mobile_button' => 'Upcoming',
    ],
];

$selectedType = $requestedType !== '' ? $requestedType : 'basic';
$selectedTest = $preferred[$selectedType] ?? null;
$setupOpen = $requestedType !== '';
$nativeError = flash('error');
if ($nativeError === null && $invalidRequestedPaper) {
    $nativeError = 'The selected test paper is no longer available. Choose an available paper again.';
}
if ($nativeError === null && $testSystemError !== '') $nativeError = $testSystemError;

require_once __DIR__ . '/includes/header.php';
?>
<section class="wf129-test-hero wf-surface-dark" data-wf-surface="dark">
    <div class="container wf129-test-hero-inner">
        <div>
            <span class="wf-section-kicker"><i class="fa-solid fa-clipboard-check"></i> Student Test Center</span>
            <h1><span class="wf141-desktop-copy">Choose one test. Follow one simple process.</span><span class="wf141-mobile-copy">Choose your test</span></h1>
            <p>Basic, previous and upcoming tests are managed separately.</p>
        </div>
        <div class="wf129-test-profile <?= $student ? 'is-verified' : '' ?>">
            <span><i class="fa-solid <?= $student ? 'fa-user-check' : 'fa-user' ?>"></i></span>
            <div><small><?= $student ? 'Verified student' : 'Guest practice' ?></small><b><?= e($student ? $studentName : 'Basic & previous available') ?></b></div>
        </div>
    </div>
</section>

<section class="wf129-test-page">
    <div class="container">
        <?php if ($nativeError): ?>
            <div class="wf133-test-alert" role="alert"><i class="fa-solid fa-circle-exclamation"></i><span><?= e($nativeError) ?></span></div>
        <?php endif; ?>

        <div class="wf129-test-flow" aria-label="Test process">
            <span><b>1</b><i class="fa-solid fa-hand-pointer"></i><em>Choose</em></span>
            <span><b>2</b><i class="fa-solid fa-circle-check"></i><em>Check</em></span>
            <span><b>3</b><i class="fa-solid fa-pen-to-square"></i><em>Give Test</em></span>
            <span><b>4</b><i class="fa-solid fa-chart-column"></i><em>Result</em></span>
        </div>

        <header class="wf129-test-heading">
            <div><span class="wf-section-kicker">Select test type</span><h2>What do you want to do today?</h2></div>
            <p>Choose one card to continue.</p>
        </header>

        <div class="wf129-test-card-grid">
            <?php foreach ($cards as $type => $card):
                $test = $preferred[$type];
                [$statusText, $statusClass] = wf133_test_status($test, $type);
                $mobileStatus = $statusClass === 'is-ready' ? 'Open' : ($statusClass === 'is-scheduled' ? 'Soon' : 'Closed');
                $requiresLogin = $type === 'upcoming';
                $cardUrl = 'weekly-test.php?type=' . rawurlencode($type) . ($test ? '&test_id=' . (int)$test['id'] : '') . '#wfTestSetup';
            ?>
                <article class="wf129-test-card type-<?= e($type) ?> <?= $requestedType === $type ? 'is-selected' : '' ?>" data-test-card="<?= e($type) ?>">
                    <div class="wf129-test-card-top">
                        <span class="wf129-test-card-icon"><i class="<?= e($card['icon']) ?>"></i></span>
                        <span class="wf129-test-status <?= e($statusClass) ?>" data-mobile-status="<?= e($mobileStatus) ?>"><i class="fa-solid fa-circle"></i><?= e($statusText) ?></span>
                    </div>
                    <small><?= e($card['eyebrow']) ?></small>
                    <h3><?= e($card['title']) ?></h3>
                    <p><?= e($card['text']) ?></p>
                    <div class="wf129-test-meta">
                        <span><i class="fa-regular fa-clock"></i><b><?= e((string)($test['duration_minutes'] ?? 0)) ?></b> min</span>
                        <span><i class="fa-solid fa-list-ol"></i><b><?= e((string)($test['question_count'] ?? 0)) ?></b> questions</span>
                    </div>
                    <?php if ($requiresLogin && !$student): ?>
                        <a class="wf-btn wf-btn-primary wf129-test-card-action" href="student-auth.php?redirect=<?= e(rawurlencode($cardUrl)) ?>"><span class="wf-btn-label"><i class="fa-solid fa-user-lock"></i><span class="wf139-desktop-action">Login for Test</span><span class="wf139-mobile-action">Login</span></span></a>
                    <?php else: ?>
                        <a class="wf-btn wf-btn-primary wf129-test-card-action <?= !$test ? 'is-disabled' : '' ?>" href="<?= $test ? e($cardUrl) : '#' ?>" data-select-test="<?= e($type) ?>" aria-disabled="<?= $test ? 'false' : 'true' ?>"><span class="wf-btn-label"><i class="<?= e($card['icon']) ?>"></i><span class="wf139-desktop-action"><?= e($card['button']) ?></span><span class="wf139-mobile-action"><?= e($card['mobile_button']) ?></span></span></a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <form class="wf129-test-setup <?= $setupOpen ? 'is-open' : '' ?>" id="wfTestSetup" method="post" action="weekly-test-api.php" <?= $setupOpen ? '' : 'hidden' ?> aria-live="polite">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="start">
            <input type="hidden" name="test_type" id="wfSelectedTestTypeInput" value="<?= e($selectedType) ?>">

            <header>
                <div class="wf129-test-selected-icon"><i id="wfSelectedIcon" class="<?= e($cards[$selectedType]['icon']) ?>"></i></div>
                <div><span id="wfSelectedType"><?= e($cards[$selectedType]['title']) ?></span><h2 id="wfSelectedTitle"><?= e((string)($selectedTest['title'] ?? 'Choose a test paper')) ?></h2><p id="wfSelectedMeta"><?php if ($selectedTest): ?><?= e((string)($selectedTest['question_count'] ?? 0)) ?> questions · <?= e((string)($selectedTest['duration_minutes'] ?? 0)) ?> minutes<?php else: ?>Paper details will appear here.<?php endif; ?></p></div>
                <button class="wf133-icon-control" type="button" id="wfCloseTestSetup" aria-label="Close test setup"><i class="fa-solid fa-xmark"></i></button>
            </header>

            <div class="wf129-test-setup-grid">
                <label class="wf129-field wf129-test-paper-field" for="wfTestPaper">
                    <span><i class="fa-solid fa-file-circle-check"></i> Test Paper</span>
                    <select id="wfTestPaper" name="test_id" required>
                        <?php $selectedPool = $testPools[$selectedType] ?? []; ?>
                        <?php if ($selectedPool): foreach ($selectedPool as $paper): ?>
                            <option value="<?= (int)$paper['id'] ?>" <?= $selectedTest && (int)$selectedTest['id'] === (int)$paper['id'] ? 'selected' : '' ?>><?= e((string)$paper['title']) ?> · <?= e((string)($paper['question_count'] ?? 0)) ?>Q · <?= e((string)($paper['duration_minutes'] ?? 0)) ?> min</option>
                        <?php endforeach; else: ?>
                            <option value="">No paper available</option>
                        <?php endif; ?>
                    </select>
                </label>
                <?php if (!$student): ?>
                    <label class="wf129-field" for="wfGuestName"><span><i class="fa-solid fa-user"></i> Student Name</span><input id="wfGuestName" name="guest_name" maxlength="100" minlength="2" autocomplete="name" placeholder="Enter your name" required></label>
                    <label class="wf129-field" for="wfGuestPhone"><span><i class="fa-solid fa-mobile-screen-button"></i> Mobile Number</span><input id="wfGuestPhone" name="guest_phone" maxlength="10" minlength="10" pattern="[0-9]{10}" inputmode="numeric" autocomplete="tel" placeholder="10 digit mobile" required></label>
                <?php else: ?>
                    <div class="wf129-test-verified"><i class="fa-solid fa-user-shield"></i><div><small>Verified student</small><b><?= e($studentName) ?></b></div></div>
                <?php endif; ?>
            </div>

            <div class="wf129-test-safety">
                <span><i class="fa-solid fa-floppy-disk"></i><b>Auto-save</b></span>
                <span><i class="fa-solid fa-hourglass-half"></i><b>Server timer</b></span>
                <span><i class="fa-solid fa-lock"></i><b>Secure result</b></span>
            </div>

            <footer>
                <p id="wfTestMessage"><?= $selectedTest ? 'Complete the details and start the selected paper.' : 'No test paper is available.' ?></p>
                <button class="wf-btn wf-btn-primary" id="wfStartTest" type="submit" <?= $selectedTest ? '' : 'disabled' ?>><span class="wf-btn-label"><i class="fa-solid fa-play"></i>Start Test</span></button>
            </footer>
            <noscript><p class="wf133-noscript-note"><i class="fa-solid fa-circle-info"></i>JavaScript is off. The form will still open the secure test room after submission.</p></noscript>
        </form>

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
            <aside class="wf129-test-login-note"><span><i class="fa-solid fa-user-lock"></i></span><div><h2>Save every result</h2><p>Student login se weekly exam, result history aur teacher feedback ek place par milta hai.</p></div><?= wf_button('Student Login', 'student-auth.php?redirect=weekly-test.php%3Ftype%3Dupcoming', 'secondary', 'fa-solid fa-user-graduate') ?></aside>
        <?php endif; ?>
    </div>
</section>

<script id="wfWeeklyTestData" type="application/json"><?= json_encode([
    'csrf' => $csrf,
    'isStudent' => (bool)$student,
    'pools' => $testPools,
    'requestedType' => $requestedType,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
