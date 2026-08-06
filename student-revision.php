<?php
require_once __DIR__ . '/includes/functions.php';
ensure_schema_updates();
material_ensure_schema();
weekly_test_ensure_schema();
require_student();
$student = fetch_current_student();
if (!$student) { redirect('student-auth.php'); }
$studentId = (int)$student['id'];
$wrongAttempts = student_wrong_material_attempts($studentId, 60);
$page_title = 'Revision Room | ' . app_setting('site_name', APP_NAME);
$meta_description = 'Revise wrong spoken English answers and repeat corrected sentences.';
$lightweight_layout = true;
require_once __DIR__ . '/includes/header.php';
?>
<section class="section revision-room-section">
    <div class="container">
        <div class="student-dashboard-hero revision-hero wf-surface-dark">
            <div>
                <span class="eyebrow">Mistake Revision</span>
                <h1>Repeat your wrong answers</h1>
                <p>Wrong answer is not failure. It is your personal practice list. Listen, repeat, and speak again with confidence.</p>
                <div class="student-hero-actions"><a class="btn btn-primary" href="spoken-materials.php?goal=revision">Start Practice Room</a><a class="btn btn-light" href="student-dashboard.php">Dashboard</a></div>
            </div>
        </div>
        <div class="revision-grid">
            <?php if (!$wrongAttempts): ?><div class="card"><h2>No wrong answers yet</h2><p class="muted">Start Practice Room. Any wrong answers will appear here for revision.</p><a class="btn btn-primary" href="spoken-materials.php">Start Practice</a></div><?php endif; ?>
            <?php foreach ($wrongAttempts as $item): ?>
                <article class="card revision-card" data-speak-text="<?= e($item['correct_answer'] ?: $item['english_text']) ?>">
                    <span class="eyebrow"><?= e($item['tense_name'] ?: 'Practice') ?> • <?= e($item['level'] ?: 'Beginner') ?></span>
                    <h2><?= e($item['correct_answer'] ?: $item['english_text']) ?></h2>
                    <p class="muted">Hindi: <?= e($item['hindi_text']) ?></p>
                    <div class="revision-answer-box"><b>Your answer</b><span><?= e($item['user_answer'] ?: '-') ?></span></div>
                    <div class="revision-answer-box correct"><b>Teacher answer</b><span><?= e($item['correct_answer'] ?: $item['english_text']) ?></span></div>
                    <p><?= e($item['feedback'] ?: 'Repeat the teacher answer loudly.') ?></p>
                    <button type="button" class="btn btn-sm btn-soft" data-revision-speak><i class="fa-solid fa-volume-high" aria-hidden="true"></i> Listen & Repeat</button>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<script>
document.addEventListener('click', function(e){
    var btn = e.target.closest('[data-revision-speak]');
    if(!btn || !('speechSynthesis' in window)) return;
    var card = btn.closest('[data-speak-text]');
    var text = card ? card.getAttribute('data-speak-text') : '';
    if(!text) return;
    window.speechSynthesis.cancel();
    var u = new SpeechSynthesisUtterance(text);
    u.lang = 'en-IN'; u.rate = 0.88; u.pitch = 1.03;
    window.speechSynthesis.speak(u);
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
