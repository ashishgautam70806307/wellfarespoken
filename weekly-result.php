<?php
require_once __DIR__ . '/includes/functions.php';
private_no_store();
ensure_schema_updates();
weekly_test_ensure_schema();

$attemptId = (int)($_GET['attempt_id'] ?? 0);
$resultToken = trim((string)($_GET['token'] ?? ''));
$attempt = $attemptId > 0 ? weekly_test_attempt_detail($attemptId) : null;

if (!$attempt) {
    http_response_code(404);
    $page_title = 'Result Not Found';
    $lightweight_layout = true;
require_once __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container"><div class="card"><h1>Result not found</h1><p>This test result does not exist.</p><a class="btn btn-primary" href="weekly-test.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Test Center</a></div></div></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$isGuestAttempt = empty($attempt['student_id']);
if ($isGuestAttempt) {
    $expectedToken = trim((string)($attempt['result_token'] ?? $attempt['access_token'] ?? ''));
    if ($expectedToken === '' || $resultToken === '' || !hash_equals($expectedToken, $resultToken)) {
        http_response_code(403);
        exit('Result access denied. Please open the result from the same test completion screen.');
    }
} else {
    require_student();
    if ((int)$attempt['student_id'] !== current_student_id()) {
        http_response_code(403);
        exit('Access denied.');
    }
}

$page_title = 'Weekly Test Result | ' . app_setting('site_name', APP_NAME);
$score = $attempt['admin_score'] !== null ? $attempt['admin_score'] : $attempt['auto_score'];
$status = weekly_test_status_badge((string)$attempt['status']);
$canShowExpected = (($attempt['test_type'] ?? 'basic') === 'basic') || (($attempt['status'] ?? '') === 'checked');
$answeredCount = 0;
foreach (($attempt['answers'] ?? []) as $answer) {
    if (trim((string)($answer['answer_text'] ?? '')) !== '') $answeredCount++;
}
require_once __DIR__ . '/includes/header.php';
?>
<section class="section weekly-result-page">
  <div class="container">
    <div class="result-hero card">
      <span class="eyebrow"><i class="fa-solid fa-trophy" aria-hidden="true"></i> Test Result</span>
      <h1><?= e($attempt['test_title']) ?></h1>
      <p>Status: <strong><?= e($status) ?></strong></p>
      <div class="result-score"><strong><?= e($score !== null ? (string)$score : '-') ?></strong><span>/ <?= e((string)($attempt['total_marks'] ?? '-')) ?> Marks</span></div>
      <div class="exam-legend">
        <span><i class="fa-solid fa-list-check" aria-hidden="true"></i> <?= e((string)$answeredCount) ?> answered</span>
        <span><i class="fa-regular fa-clock" aria-hidden="true"></i> <?= e((string)($attempt['submission_reason'] ?? 'submitted')) ?></span>
        <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Secure result</span>
      </div>
      <?php if (!empty($attempt['penalty_marks']) && (float)$attempt['penalty_marks'] > 0): ?>
        <p class="teacher-mini-hint"><b>Security penalty:</b> -<?= e((string)$attempt['penalty_marks']) ?> mark(s).</p>
      <?php endif; ?>
      <?php if (!empty($attempt['admin_note'])): ?><p class="teacher-mini-hint"><b>Teacher note:</b> <?= e($attempt['admin_note']) ?></p><?php endif; ?>
      <?php if (!$canShowExpected): ?><p class="teacher-mini-hint"><i class="fa-solid fa-lock" aria-hidden="true"></i> Correct answers will appear after teacher checking.</p><?php endif; ?>
      <div class="mini-action-row">
        <a class="btn btn-primary" href="weekly-test.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Test Center</a>
        <?php if (!$isGuestAttempt): ?><a class="btn btn-soft" href="student-dashboard.php"><i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Dashboard</a><?php endif; ?>
      </div>
    </div>

    <div class="card">
      <h2><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i> Answer Review</h2>
      <?php foreach (($attempt['answers'] ?? []) as $i => $ans):
          $state = ($ans['is_correct'] ?? 'Review') === 'Yes' ? 'ok' : 'review';
      ?>
        <div class="result-answer-card <?= e($state) ?>">
          <div>
            <b>Q<?= $i + 1 ?>. <?= e((string)$ans['question_type']) ?><?= !empty($ans['topic_name']) ? ' • ' . e($ans['topic_name']) : '' ?></b>
            <span><?= e((string)($ans['marks_awarded'] ?? 0)) ?>/<?= e((string)$ans['marks']) ?> marks</span>
          </div>
          <p><strong>Question:</strong> <?= e($ans['question_text']) ?></p>
          <p><strong>Your Answer:</strong> <?= e(trim((string)$ans['answer_text']) !== '' ? $ans['answer_text'] : 'No answer') ?></p>
          <?php if ($canShowExpected): ?><p><strong>Expected Answer:</strong> <?= e($ans['expected_answer']) ?></p><?php endif; ?>
          <?php if (!empty($ans['admin_note'])): ?><p class="muted"><strong>Teacher Feedback:</strong> <?= e($ans['admin_note']) ?></p><?php endif; ?>
        </div>
      <?php endforeach; ?>
      <?php if (empty($attempt['answers'])): ?><p class="muted">No answers were saved for this attempt.</p><?php endif; ?>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
