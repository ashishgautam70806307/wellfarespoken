<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
weekly_test_ensure_schema();
$id = max(0, (int)($_GET['id'] ?? 0));
$stmt = db()->prepare("SELECT t.*, b.batch_name, b.timing, b.days, (SELECT COUNT(*) FROM weekly_test_questions q WHERE q.test_id=t.id AND q.status_deleted=0) question_count, (SELECT COUNT(*) FROM weekly_test_questions q WHERE q.test_id=t.id AND q.status_deleted=0 AND q.published='Yes') active_questions FROM weekly_tests t LEFT JOIN batch_timings b ON b.id=t.batch_id WHERE t.id=? AND COALESCE(t.status_deleted,0)=0 LIMIT 1");
$stmt->execute([$id]);
$paper = $stmt->fetch();
if (!$paper) { flash('error','Batch/test paper not found.'); redirect('weekly-tests.php'); }
$type = $paper['test_type'] ?: 'basic';
$ready = weekly_test_ready_reason($paper);
$attemptStats = db()->prepare("SELECT COUNT(*) attempts, SUM(status IN ('submitted','checked')) submitted, SUM(status='checked') checked, COALESCE(SUM(warning_count),0) warnings FROM weekly_test_attempts WHERE COALESCE(status_deleted,0)=0 AND test_id=?");
$attemptStats->execute([$id]);
$stats = $attemptStats->fetch() ?: ['attempts'=>0,'submitted'=>0,'checked'=>0,'warnings'=>0];
$winners = weekly_test_active_winners($id);
$questions = db()->prepare("SELECT * FROM weekly_test_questions WHERE test_id=? AND status_deleted=0 ORDER BY sort_order ASC, id ASC LIMIT 10");
$questions->execute([$id]);
$qrows=$questions->fetchAll();
require_once __DIR__ . '/_header.php';
?>
<div class="admin-top weekly-admin-head">
  <div><span class="eyebrow">Batch Paper</span><h1><?= e($paper['title']) ?></h1><p><?= e(ucfirst($type)) ?> • <?= e($paper['batch_label'] ?: ($paper['batch_name'] ?: 'Common paper')) ?><?= !empty($paper['timing']) ? ' • '.e($paper['timing']) : '' ?></p></div>
  <div class="admin-actions"><a class="btn btn-soft" href="weekly-tests.php?type=<?= e($type) ?>&test_id=<?= e((string)$id) ?>#setup">Edit Setup</a><a class="btn btn-soft" href="weekly-tests.php?type=<?= e($type) ?>&test_id=<?= e((string)$id) ?>#question-bank">Question Bank</a><?php if($type==='upcoming'): ?><a class="btn btn-soft" target="_blank" href="weekly-test-offline-paper.php?id=<?= e((string)$id) ?>&mode=paper">Offline PDF</a><a class="btn btn-soft" target="_blank" href="weekly-test-offline-paper.php?id=<?= e((string)$id) ?>&mode=answer-key">Answer Key</a><?php endif; ?><a class="btn btn-primary" target="_blank" href="../weekly-test.php">Open Test Page</a></div>
</div>
<div class="weekly-paper-detail-grid">
  <section class="admin-card paper-detail-status <?= $ready==='ready'?'published':'pending' ?>">
    <span class="paper-badge"><?= e($ready==='ready'?'Published / Student Visible':($ready==='scheduled_later'?'Scheduled':($ready==='expired'?'Closed':'Pending')) ) ?></span>
    <h2>Paper Status</h2>
    <p>Frontend par start hone ke liye paper active, questions active aur schedule open hona chahiye.</p>
    <div class="paper-detail-stats"><em><?= e((string)$paper['active_questions']) ?> active questions</em><em><?= e((string)$paper['duration_minutes']) ?> min</em><em><?= e((string)$stats['attempts']) ?> attempts</em><em><?= e((string)$stats['warnings']) ?> warnings</em></div>
    <div class="paper-detail-actions">
      <form class="ajax-admin-form" action="weekly-test-ajax.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="publish_test_now"><input type="hidden" name="test_id" value="<?= e((string)$id) ?>"><button class="btn btn-primary" type="submit">Publish Now</button><span class="ajax-msg"></span></form>
      <form class="ajax-admin-form" action="weekly-test-ajax.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="set_test_pending"><input type="hidden" name="test_id" value="<?= e((string)$id) ?>"><button class="btn btn-soft" type="submit"><?= $type==='upcoming' ? 'Close Entry' : 'Set Pending' ?></button><span class="ajax-msg"></span></form>
      <form class="ajax-admin-form" action="weekly-test-ajax.php" method="post" data-confirm="<?= e($type==='upcoming' ? 'Close new entry and finalize the Top 3 when all active attempts are finished and all submitted copies are checked?' : 'Complete this batch test and publish top 3 winners for 2 days?') ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="complete_batch_test"><input type="hidden" name="test_id" value="<?= e((string)$id) ?>"><button class="btn btn-green" type="submit"><?= $type==='upcoming' ? 'Finalize Top 3' : 'Complete Test' ?></button><span class="ajax-msg"></span></form>
      <form class="ajax-admin-form" action="weekly-test-ajax.php" method="post" data-confirm="Hide/archive this batch paper?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="archive_test_paper"><input type="hidden" name="test_id" value="<?= e((string)$id) ?>"><button class="btn btn-red" type="submit">Delete / Archive</button><span class="ajax-msg"></span></form>
    </div>
  </section>
  <section class="admin-card">
    <h2>Quick Manage</h2>
    <div class="weekly-flow-cards single-paper-flow">
      <a class="weekly-flow-card" href="weekly-tests.php?type=<?= e($type) ?>&test_id=<?= e((string)$id) ?>#setup"><span>01</span><b>Edit setup</b><small>title, batch, timing, status</small></a>
      <a class="weekly-flow-card" href="weekly-tests.php?type=<?= e($type) ?>&test_id=<?= e((string)$id) ?>#upload"><span>02</span><b>Upload sheet</b><small>CSV/XLSX answer sheet</small></a>
      <a class="weekly-flow-card" href="weekly-tests.php?type=<?= e($type) ?>&test_id=<?= e((string)$id) ?>#question-bank"><span>03</span><b>Questions</b><small>edit / active / delete</small></a>
      <a class="weekly-flow-card" href="weekly-tests.php?type=<?= e($type) ?>&test_id=<?= e((string)$id) ?>#student-copies"><span>04</span><b>Student copies</b><small>review attempts</small></a>
      <?php if($type==='upcoming'): ?><a class="weekly-flow-card" target="_blank" href="weekly-test-offline-paper.php?id=<?= e((string)$id) ?>&mode=paper"><span>05</span><b>Offline PDF</b><small>printable batch paper</small></a><?php endif; ?>
    </div>
  </section>
</div>
<?php if($winners): ?><section class="admin-card paper-winner-panel"><h2>Published Winners</h2><div class="weekly-winner-grid"><?php foreach($winners as $w): ?><article class="weekly-winner-card rank-<?= e((string)$w['rank_no']) ?>"><b>#<?= e((string)$w['rank_no']) ?> <?= e($w['student_name']) ?></b><span><?= e((string)$w['score']) ?> / <?= e((string)$w['total_marks']) ?></span><small>Visible until <?= e($w['published_until']) ?></small></article><?php endforeach; ?></div></section><?php endif; ?>
<section class="admin-card"><div class="section-between"><div><h2>Latest Questions</h2><p class="muted small">First 10 rows only. Open question bank to manage all questions.</p></div><a class="btn btn-soft btn-sm" href="weekly-tests.php?type=<?= e($type) ?>&test_id=<?= e((string)$id) ?>#question-bank">Manage All</a></div><div class="table-responsive"><table class="admin-table"><thead><tr><th>#</th><th>Type</th><th>Question</th><th>Answer</th><th>Status</th></tr></thead><tbody><?php foreach($qrows as $q): ?><tr><td><?= e((string)$q['sort_order']) ?></td><td><?= e($q['question_type']) ?></td><td><?= e($q['question_text']) ?></td><td><?= e($q['expected_answer']) ?></td><td><?= e($q['published']) ?></td></tr><?php endforeach; if(!$qrows): ?><tr><td colspan="5">No questions yet.</td></tr><?php endif; ?></tbody></table></div></section>
<script>
document.querySelectorAll('.ajax-admin-form').forEach(form=>{form.addEventListener('submit',e=>{e.preventDefault();const msg=form.querySelector('.ajax-msg');if(msg)msg.textContent='Working...';fetch(form.getAttribute('action'),{method:'POST',body:new FormData(form),cache:'no-store',headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(d=>{if(msg){msg.textContent=d.message||'';msg.className='ajax-msg '+(d.success?'ok':'bad');}if(window.AppUI){window.AppUI.toast(d.success?'success':'error',d.message||'Done');}if(d.success)setTimeout(()=>location.reload(),800);}).catch((err)=>{if(msg){msg.textContent='Server error';msg.className='ajax-msg bad';}if(window.AppUI){window.AppUI.toast('error','Server error. Please try again.');}});});});
</script>
<?php require_once __DIR__ . '/_footer.php'; ?>
