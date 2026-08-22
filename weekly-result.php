<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/error-pages.php';
private_no_store();
ensure_schema_updates();
weekly_test_ensure_schema();

$attemptId = max(0, (int)($_GET['attempt_id'] ?? 0));
$resultToken = trim((string)($_GET['token'] ?? ''));
$attempt = $attemptId > 0 ? weekly_test_attempt_detail($attemptId) : null;
$lightweight_layout = true;
$page_final_styles = ['assets/css/phase158-test-results.css'];
$page_title = 'Weekly Test Result | ' . app_setting('site_name', APP_NAME);
$meta_description = 'Secure weekly-test score, attempt details and answer review.';

if (!$attempt) { wf_show_error_page(404); }


$isGuestAttempt = empty($attempt['student_id']);
if ($isGuestAttempt) {
    $expectedToken = trim((string)($attempt['result_token'] ?? $attempt['access_token'] ?? ''));
    if ($expectedToken === '' || $resultToken === '' || !hash_equals($expectedToken, $resultToken)) {
        wf_show_error_page(403);
    }
} else {
    require_student();
    if ((int)($attempt['student_id'] ?? 0) !== current_student_id()) {
        wf_show_error_page(403);
    }
}

// If Admin reopened an accidental Final Submit, the old result URL must not behave like a final result.
// Send the same logged-in student straight back to the preserved attempt/question snapshot.
if (strtolower(trim((string)($attempt['status'] ?? ''))) === 'started') {
    $resumeToken = trim((string)($attempt['access_token'] ?? ''));
    if ($resumeToken !== '') {
        header('Location: weekly-exam-room.php?attempt_id=' . (int)$attempt['id'] . '&token=' . rawurlencode($resumeToken), true, 303);
        exit;
    }
}

$score = $attempt['admin_score'] !== null ? $attempt['admin_score'] : $attempt['auto_score'];
$totalMarks = (float)($attempt['total_marks'] ?? 0);
$percentage = ($score !== null && $totalMarks > 0)
    ? max(0, min(100, (int)round(((float)$score / $totalMarks) * 100)))
    : null;
$statusKey = strtolower(trim((string)($attempt['status'] ?? '')));
$status = weekly_test_status_badge($statusKey);
$canShowExpected = weekly_test_expected_answers_releasable($attempt);
$answerReleaseNote = weekly_test_answer_release_note($attempt);
$answers = is_array($attempt['answers'] ?? null) ? $attempt['answers'] : [];
$answeredCount = 0;
$correctCount = 0;
$wrongCount = 0;
$reviewCount = 0;
foreach ($answers as $answer) {
    if (trim((string)($answer['answer_text'] ?? '')) !== '') $answeredCount++;
    $correctState = strtolower(trim((string)($answer['is_correct'] ?? 'review')));
    if ($correctState === 'yes') $correctCount++;
    elseif ($correctState === 'no') $wrongCount++;
    else $reviewCount++;
}

$dateValue = trim((string)((($attempt['submitted_at'] ?? '') ?: ($attempt['started_at'] ?? '') ?: ($attempt['created_at'] ?? ''))));
$attemptTimestamp = $dateValue !== '' ? strtotime($dateValue) : false;
$startedTimestamp = !empty($attempt['started_at']) ? strtotime((string)$attempt['started_at']) : false;
$submittedTimestamp = !empty($attempt['submitted_at']) ? strtotime((string)$attempt['submitted_at']) : false;
$durationSeconds = ($startedTimestamp && $submittedTimestamp && $submittedTimestamp >= $startedTimestamp)
    ? ($submittedTimestamp - $startedTimestamp)
    : 0;
if ((int)($attempt['reopen_count'] ?? 0) > 0 && !empty($attempt['first_submitted_at']) && !empty($attempt['reopened_at'])) {
    $firstSubmittedTs = strtotime((string)$attempt['first_submitted_at']);
    $reopenedTs = strtotime((string)$attempt['reopened_at']);
    if ($startedTimestamp && $firstSubmittedTs && $reopenedTs && $submittedTimestamp
        && $firstSubmittedTs >= $startedTimestamp && $submittedTimestamp >= $reopenedTs) {
        $durationSeconds = ($firstSubmittedTs - $startedTimestamp) + ($submittedTimestamp - $reopenedTs);
    }
}
$durationText = $durationSeconds > 0
    ? ((int)floor($durationSeconds / 60) . 'm ' . ($durationSeconds % 60) . 's')
    : 'Not available';
$studentName = trim((string)(($attempt['student_name'] ?? '') ?: ($attempt['guest_name'] ?? 'Student')));
$testType = ucfirst((string)($attempt['test_type'] ?? 'Test')) . ' Test';
$statusClass = $statusKey === 'checked' ? 'is-checked' : ($statusKey === 'submitted' ? 'is-pending' : 'is-progress');

require_once __DIR__ . '/includes/header.php';
?>
<section class="wf145-result-page">
    <div class="container wf145-result-shell">
        <nav class="wf145-result-breadcrumb" aria-label="Result navigation">
            <a href="weekly-test.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Test Center</a>
            <span><?= e($testType) ?></span>
        </nav>

        <article class="wf145-result-hero wf-surface-dark <?= e($statusClass) ?>" data-wf-surface="dark">
            <div class="wf145-result-copy">
                <div class="wf145-result-badges"><span><?= e($testType) ?></span><strong><?= e($status) ?></strong></div>
                <h1><?= e((string)($attempt['test_title'] ?? 'Weekly Test Result')) ?></h1>
                <p><?= e($studentName !== '' ? $studentName : 'Student') ?> completed this attempt on <?= $attemptTimestamp ? e(date('d M Y', $attemptTimestamp)) : 'an unavailable date' ?> at <?= $attemptTimestamp ? e(date('h:i A', $attemptTimestamp)) : 'an unavailable time' ?>.</p>
                <div class="wf145-result-actions">
                    <a class="wf-btn wf-btn-gold" href="weekly-test.php"><span class="wf-btn-label"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i><span>Test Center</span></span></a>
                    <?php if (!$isGuestAttempt): ?><a class="wf-btn wf-btn-secondary" href="student-dashboard.php"><span class="wf-btn-label"><i class="fa-solid fa-gauge-high" aria-hidden="true"></i><span>Dashboard</span></span></a><?php endif; ?>
                    <?php if (!$isGuestAttempt): ?><a class="wf145-result-logout" href="student-logout.php"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Logout</a><?php endif; ?>
                </div>
            </div>
            <div class="wf145-score-orbit" style="--score:<?= e((string)($percentage ?? 0)) ?>" aria-label="<?= $percentage !== null ? e((string)$percentage) . ' percent' : 'Percentage pending' ?>">
                <div><strong><?= $percentage !== null ? e((string)$percentage) . '%' : '—' ?></strong><span><?= $score !== null ? e((string)$score) : '—' ?> / <?= e((string)($attempt['total_marks'] ?? '—')) ?> marks</span></div>
            </div>
        </article>

        <div class="wf145-result-stat-grid" aria-label="Attempt summary">
            <article><i class="fa-solid fa-list-check" aria-hidden="true"></i><div><strong><?= e((string)$answeredCount) ?>/<?= e((string)count($answers)) ?></strong><span>Answered</span></div></article>
            <article><i class="fa-solid fa-circle-check" aria-hidden="true"></i><div><strong><?= e((string)$correctCount) ?></strong><span>Correct</span></div></article>
            <article><i class="fa-solid fa-clock" aria-hidden="true"></i><div><strong><?= e($durationText) ?></strong><span>Time used</span></div></article>
            <article><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><div><strong><?= e(ucwords(str_replace('_', ' ', (string)($attempt['submission_reason'] ?? 'submitted')))) ?></strong><span>Submission</span></div></article>
        </div>

        <?php if ((!empty($attempt['penalty_marks']) && (float)$attempt['penalty_marks'] > 0) || !empty($attempt['admin_note']) || !$canShowExpected): ?>
            <div class="wf145-result-notices">
                <?php if (!empty($attempt['penalty_marks']) && (float)$attempt['penalty_marks'] > 0): ?><p class="is-warning"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><span><b>Security penalty:</b> -<?= e((string)$attempt['penalty_marks']) ?> mark(s).</span></p><?php endif; ?>
                <?php if (!empty($attempt['admin_note'])): ?><p><i class="fa-solid fa-comment-dots" aria-hidden="true"></i><span><b>Teacher note:</b> <?= e((string)$attempt['admin_note']) ?></span></p><?php endif; ?>
                <?php if (!$canShowExpected): ?><p><i class="fa-solid fa-lock" aria-hidden="true"></i><span><?= e($answerReleaseNote !== '' ? $answerReleaseNote : 'Master answers are not released yet.') ?></span></p><?php endif; ?>
            </div>
        <?php endif; ?>

        <section class="wf145-answer-review" aria-labelledby="answerReviewTitle">
            <header class="wf145-answer-review-head">
                <div><span class="wf-section-kicker">Question by question</span><h2 id="answerReviewTitle">Answer Review</h2><p>Check what you answered and where another practice round may help.</p></div>
                <div class="wf145-review-summary"><span class="is-correct"><b><?= e((string)$correctCount) ?></b> correct</span><span class="is-wrong"><b><?= e((string)$wrongCount) ?></b> wrong</span><span class="is-review"><b><?= e((string)$reviewCount) ?></b> review</span></div>
            </header>

            <?php if ($answers): ?>
                <div class="wf145-answer-list">
                    <?php foreach ($answers as $index => $answer):
                        $answerState = strtolower(trim((string)($answer['is_correct'] ?? 'review')));
                        $stateClass = $answerState === 'yes' ? 'is-correct' : ($answerState === 'no' ? 'is-wrong' : 'is-review');
                        $stateLabel = $answerState === 'yes' ? 'Correct' : ($answerState === 'no' ? 'Incorrect' : 'Teacher review');
                        $answerText = trim((string)($answer['answer_text'] ?? ''));
                    ?>
                        <article class="wf145-answer-card <?= e($stateClass) ?>">
                            <header><span>Q<?= e((string)($index + 1)) ?></span><div><b>Question</b><small><?= e((string)($answer['marks_awarded'] ?? 0)) ?>/<?= e((string)($answer['marks'] ?? 0)) ?> marks</small></div><strong><?= e($stateLabel) ?></strong></header>
                            <h3><?= e((string)($answer['question_text'] ?? '')) ?></h3>
                            <div class="wf145-answer-comparison">
                                <div><span>Your answer</span><p><?= e($answerText !== '' ? $answerText : 'No answer submitted') ?></p></div>
                                <?php if ($canShowExpected): ?>
                                    <?php $acceptedAnswers = weekly_test_split_expected_answers((string)($answer['expected_answer'] ?? '')); ?>
                                    <div class="wf158-expected-answer"><span>Accepted answer<?= count($acceptedAnswers) === 1 ? '' : 's' ?></span>
                                        <?php if ($acceptedAnswers): ?><div class="wf158-answer-variants"><?php foreach ($acceptedAnswers as $acceptedAnswer): ?><p><?= e($acceptedAnswer) ?></p><?php endforeach; ?></div><?php else: ?><p>No master answer uploaded.</p><?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($answer['admin_note'])): ?><p class="wf145-answer-note"><i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i><span><?= e((string)$answer['admin_note']) ?></span></p><?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="wf145-result-empty"><i class="fa-solid fa-inbox" aria-hidden="true"></i><h3>No answers were saved</h3><p>This attempt does not contain answer records.</p></div>
            <?php endif; ?>
        </section>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
