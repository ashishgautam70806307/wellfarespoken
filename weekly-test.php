<?php
require_once __DIR__ . '/includes/functions.php';
ensure_schema_updates();
weekly_test_ensure_schema();

$page_title = 'Student Test Center | ' . app_setting('site_name', APP_NAME);
$meta_description = 'Basic practice, previous paper practice, scheduled weekly exams and student results in one clear test center.';
$page_styles = ['assets/css/phase130-weekly-test.css'];
$page_final_styles = ['assets/css/phase146-weekly-test.css'];
$page_scripts = ['assets/js/phase146-weekly-test.js'];
$skip_phase139_learning_css = true;
$skip_phase141_learning_css = true;
$skip_phase142_interaction_css = true;
$skip_phase139_mobile_learning_script = true;
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
$student = is_student() ? fetch_current_student() : null;
if ($student) private_no_store();
$batchAccessError = null;
if ($student && $testSystemError === '' && !empty($testPools['upcoming'])) {
    $studentIdForBatch = (int)($student['id'] ?? 0);
    $eligibleUpcoming = [];
    foreach ($testPools['upcoming'] as $paper) {
        $batchCheck = weekly_test_student_batch_eligibility($studentIdForBatch, $paper);
        if (!empty($batchCheck['allowed'])) {
            $eligibleUpcoming[] = $paper;
        } elseif ($requestedType === 'upcoming' && $requestedTestId > 0 && (int)($paper['id'] ?? 0) === $requestedTestId) {
            $batchAccessError = (string)($batchCheck['message'] ?? 'This test is not assigned to your batch.');
        }
    }
    $testPools['upcoming'] = $eligibleUpcoming;
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
$selectedReady = $selectedTest
    && (int)($selectedTest['ready_now'] ?? 0) === 1
    && strtolower((string)($selectedTest['status'] ?? '')) === 'active'
    && (int)($selectedTest['question_count'] ?? 0) > 0;
$selectedEligibility = null;
if ($selectedReady && $student && $selectedType === 'upcoming' && $selectedTest) {
    $selectedEligibility = weekly_test_upcoming_eligibility((int)$student['id'], (int)$selectedTest['id']);
    if (empty($selectedEligibility['allowed'])) $selectedReady = false;
}
$nativeError = flash('error');
if ($nativeError === null && $batchAccessError !== null) {
    $nativeError = $batchAccessError;
} elseif ($nativeError === null && $invalidRequestedPaper) {
    $nativeError = 'The selected test paper is no longer available for your account. Choose an available paper again.';
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

        <div class="wf145-test-carousel" data-test-carousel>
            <button class="wf145-test-arrow is-prev" type="button" aria-label="Previous test option" data-test-prev><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
            <div class="wf129-test-card-grid" data-test-track>
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
                        <span class="wf129-test-status <?= e($statusClass) ?>"><i class="fa-solid fa-circle" aria-hidden="true"></i><span class="wf146-status-full"><?= e($statusText) ?></span><span class="wf146-status-short"><?= e($mobileStatus) ?></span></span>
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
                        <a class="wf-btn wf-btn-primary wf129-test-card-action <?= !$test ? 'is-disabled' : '' ?>" href="<?= $test ? e($cardUrl) : '#' ?>" aria-disabled="<?= $test ? 'false' : 'true' ?>"><span class="wf-btn-label"><i class="<?= e($card['icon']) ?>"></i><span class="wf139-desktop-action"><?= e($card['button']) ?></span><span class="wf139-mobile-action"><?= e($card['mobile_button']) ?></span></span></a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
            </div>
            <button class="wf145-test-arrow is-next" type="button" aria-label="Next test option" data-test-next><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
            <div class="wf145-test-dots" aria-label="Test option navigation"><span></span><span></span><span></span></div>
        </div>

        <?php
        $selectedRequiresLogin = $selectedTest && ($selectedType === 'upcoming' || strtolower((string)($selectedTest['requires_login'] ?? 'no')) === 'yes');
        $selectedReturnUrl = 'weekly-test.php?type=' . rawurlencode($selectedType) . ($selectedTest ? '&test_id=' . (int)$selectedTest['id'] : '') . '#wfTestSetup';
        ?>
        <?php if ($setupOpen && $selectedRequiresLogin && !$student): ?>
            <section class="wf129-test-setup wf145-test-login-gate is-open" id="wfTestSetupGate" data-test-setup-gate aria-labelledby="wfLoginGateTitle">
                <span class="wf145-test-login-icon"><i class="fa-solid fa-user-shield" aria-hidden="true"></i></span>
                <div><span class="wf-section-kicker">Student login required</span><h2 id="wfLoginGateTitle">Login before starting this official test.</h2><p>Your attempt, timer and result history will be safely linked to your student account.</p></div>
                <a class="wf-btn wf-btn-primary" href="student-auth.php?redirect=<?= e(rawurlencode($selectedReturnUrl)) ?>"><span class="wf-btn-label"><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i><span>Login and Continue</span></span></a>
                <a class="wf145-test-gate-back" href="weekly-test.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Choose another test</a>
            </section>
        <?php else: ?>
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
                            <option value="<?= (int)$paper['id'] ?>"
                                data-title="<?= e((string)$paper['title']) ?>"
                                data-questions="<?= e((string)($paper['question_count'] ?? 0)) ?>"
                                data-duration="<?= e((string)($paper['duration_minutes'] ?? 0)) ?>"
                                data-batch="<?= e((string)($paper['batch_name'] ?? '')) ?>"
                                data-ready="<?= e((string)($paper['ready_now'] ?? 0)) ?>"
                                data-status="<?= e((string)($paper['status'] ?? '')) ?>"
                                <?= $selectedTest && (int)$selectedTest['id'] === (int)$paper['id'] ? 'selected' : '' ?>><?= e((string)$paper['title']) ?> · <?= e((string)($paper['question_count'] ?? 0)) ?>Q · <?= e((string)($paper['duration_minutes'] ?? 0)) ?> min</option>
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
                <p id="wfTestMessage"><?php if (!$selectedTest): ?>No test paper is available.<?php elseif ($selectedEligibility && empty($selectedEligibility['allowed'])): ?><?= e((string)($selectedEligibility['message'] ?? 'This Upcoming Test is temporarily locked.')) ?><?php elseif (!$selectedReady): ?>This paper is not open yet. Check its schedule or ask the institute.<?php else: ?>Ready. Start the selected paper when you are comfortable.<?php endif; ?></p>
                <button class="wf-btn wf-btn-primary" id="wfStartTest" type="submit" <?= $selectedReady ? '' : 'disabled' ?>><span class="wf-btn-label"><i class="fa-solid fa-play"></i>Start Test</span></button>
            </footer>
            <noscript><p class="wf133-noscript-note"><i class="fa-solid fa-circle-info"></i>JavaScript is off. The form will still open the secure test room after submission.</p></noscript>
        </form>
        <?php endif; ?>

        <?php if ($student): ?>
            <section class="wf129-test-results wf145-history-section" id="my-results" aria-labelledby="testHistoryTitle">
                <header class="wf145-history-head"><div><span class="wf-section-kicker">Saved attempts</span><h2 id="testHistoryTitle">Test history and results</h2><p>Every attempt with exact date, time, score and status.</p></div><span><?= e((string)count($studentAttempts)) ?> attempts</span></header>
                <?php if ($studentAttempts): ?>
                    <div class="wf145-history-grid">
                        <?php foreach ($studentAttempts as $index => $attempt):
                            $attemptStatus = strtolower((string)($attempt['status'] ?? ''));
                            $score = $attempt['admin_score'] !== null ? $attempt['admin_score'] : $attempt['auto_score'];
                            $totalMarks = (float)($attempt['total_marks'] ?? 0);
                            $percentage = ($score !== null && $totalMarks > 0) ? max(0, min(100, (int)round(((float)$score / $totalMarks) * 100))) : null;
                            $dateValue = trim((string)((($attempt['submitted_at'] ?? '') ?: ($attempt['started_at'] ?? '') ?: ($attempt['created_at'] ?? ''))));
                            $timeValue = $dateValue !== '' ? strtotime($dateValue) : false;
                            $attemptUrl = in_array($attemptStatus, ['submitted', 'checked'], true)
                                ? weekly_test_result_url($attempt)
                                : ('weekly-exam-room.php?attempt_id=' . (int)$attempt['id'] . '&token=' . rawurlencode((string)($attempt['access_token'] ?? '')));
                            $statusClass = $attemptStatus === 'checked' ? 'is-checked' : ($attemptStatus === 'started' ? 'is-progress' : 'is-pending');
                        ?>
                            <article class="wf145-history-card <?= e($statusClass) ?>">
                                <div class="wf145-history-card-top"><span class="wf145-history-index">#<?= e((string)(count($studentAttempts) - $index)) ?></span><span class="wf145-history-type"><?= e(ucfirst((string)($attempt['test_type'] ?? 'test'))) ?> Test</span><span class="wf145-history-status"><?= e(weekly_test_status_badge((string)($attempt['status'] ?? ''))) ?></span></div>
                                <h3><?= e((string)($attempt['test_title'] ?? 'Test result')) ?></h3>
                                <div class="wf145-history-time"><span><i class="fa-regular fa-calendar" aria-hidden="true"></i><?= $timeValue ? e(date('d M Y', $timeValue)) : 'Date unavailable' ?></span><span><i class="fa-regular fa-clock" aria-hidden="true"></i><?= $timeValue ? e(date('h:i A', $timeValue)) : 'Time unavailable' ?></span></div>
                                <div class="wf145-history-score-row"><div><small>Score</small><strong><?= $score !== null ? e((string)$score) : '—' ?><span>/<?= e((string)($attempt['total_marks'] ?? '—')) ?></span></strong></div><div class="wf145-history-percent" style="--score:<?= e((string)($percentage ?? 0)) ?>"><span><?= $percentage !== null ? e((string)$percentage) . '%' : '—' ?></span></div></div>
                                <a class="wf145-history-action" href="<?= e($attemptUrl) ?>"><span><?= $attemptStatus === 'started' ? 'Resume Test' : 'View Answer Review' ?></span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="wf129-test-empty"><i class="fa-solid fa-chart-line"></i><h3>No result yet</h3><p>Complete a basic or weekly test. Your date, time and result will appear here.</p></div>
                <?php endif; ?>
            </section>
        <?php else: ?>
            <aside class="wf129-test-login-note"><span><i class="fa-solid fa-user-lock"></i></span><div><h2>Save every result</h2><p>Student login se weekly exam, result history aur teacher feedback ek place par milta hai.</p></div><?= wf_button('Student Login', 'student-auth.php?redirect=weekly-test.php%3Ftype%3Dupcoming', 'secondary', 'fa-solid fa-user-graduate') ?></aside>
        <?php endif; ?>
    </div>
</section>


<?php require_once __DIR__ . '/includes/footer.php'; ?>
