<?php
require_once __DIR__ . '/includes/functions.php';
private_no_store();
weekly_test_ensure_schema();
require_student();

$attemptId = max(0, (int)($_GET['attempt_id'] ?? 0));
$attempt = $attemptId > 0 ? weekly_test_attempt_detail($attemptId) : null;
if (!$attempt || (int)($attempt['student_id'] ?? 0) !== current_student_id()) {
    http_response_code(404);
    ?><div class="wf158-answer-inline-error">This answer review is not available.</div><?php
    exit;
}

$status = strtolower(trim((string)($attempt['status'] ?? '')));
if (!in_array($status, ['submitted', 'checked'], true)) {
    http_response_code(409);
    ?><div class="wf158-answer-inline-error">Finish the test before opening the answer review.</div><?php
    exit;
}

$answers = is_array($attempt['answers'] ?? null) ? $attempt['answers'] : [];
$showExpected = weekly_test_expected_answers_releasable($attempt);
$releaseNote = weekly_test_answer_release_note($attempt);
?>
<div class="wf158-inline-review">
    <?php if (!$answers): ?>
        <p class="wf158-answer-inline-error">No saved answer rows were found for this attempt.</p>
    <?php else: ?>
        <?php foreach ($answers as $i => $answer):
            $state = strtolower(trim((string)($answer['is_correct'] ?? 'review')));
            $stateClass = $state === 'yes' ? 'is-correct' : ($state === 'no' ? 'is-wrong' : 'is-review');
            $answerText = trim((string)($answer['answer_text'] ?? ''));
            $variants = $showExpected ? weekly_test_split_expected_answers((string)($answer['expected_answer'] ?? '')) : [];
        ?>
            <article class="wf158-inline-answer <?= e($stateClass) ?>">
                <div class="wf158-inline-qhead"><b>Q<?= e((string)($i + 1)) ?></b><span><?= e((string)($answer['marks_awarded'] ?? 0)) ?>/<?= e((string)($answer['marks'] ?? 0)) ?> marks</span></div>
                <h4><?= e((string)($answer['question_text'] ?? 'Question')) ?></h4>
                <div class="wf158-inline-answer-row"><span>Your answer</span><p><?= e($answerText !== '' ? $answerText : 'No answer submitted') ?></p></div>
                <?php if ($showExpected): ?>
                    <div class="wf158-inline-answer-row is-master"><span>Accepted answer<?= count($variants) === 1 ? '' : 's' ?></span>
                        <?php if ($variants): ?>
                            <?php foreach ($variants as $variant): ?><p><?= e($variant) ?></p><?php endforeach; ?>
                        <?php else: ?><p>No master answer uploaded.</p><?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="wf158-inline-answer-lock"><i class="fa-solid fa-lock" aria-hidden="true"></i><span><?= e($releaseNote) ?></span></div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
        <a class="wf158-inline-full-link" href="<?= e(weekly_test_result_url($attempt)) ?>">Open complete result <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    <?php endif; ?>
</div>
