<?php
require_once __DIR__ . '/../includes/functions.php';
private_no_store();
require_admin();
admin_require_permission('tests.manage');
weekly_test_ensure_schema();

$testId = max(0, (int)($_GET['id'] ?? 0));
$mode = strtolower(trim((string)($_GET['mode'] ?? 'paper')));
if (!in_array($mode, ['paper', 'answer-key'], true)) $mode = 'paper';
$autoPrint = (string)($_GET['autoprint'] ?? '') === '1';

$stmt = db()->prepare("SELECT t.*, b.batch_name, b.timing batch_timing, b.days batch_days FROM weekly_tests t LEFT JOIN batch_timings b ON b.id=t.batch_id WHERE t.id=? AND COALESCE(t.status_deleted,0)=0 LIMIT 1");
$stmt->execute([$testId]);
$test = $stmt->fetch();
if (!$test) {
    http_response_code(404);
    echo 'Test paper not found.';
    exit;
}

$limit = max(1, min(200, (int)($test['total_questions'] ?? 30)));
$qStmt = db()->prepare("SELECT * FROM weekly_test_questions WHERE test_id=? AND status_deleted=0 AND published='Yes' ORDER BY sort_order ASC, id ASC LIMIT {$limit}");
$qStmt->execute([$testId]);
$questions = $qStmt->fetchAll();

$siteName = trim((string)app_setting('site_name', APP_NAME));
if ($siteName === '') $siteName = APP_NAME;
$siteLogo = site_asset_url((string)app_setting('site_logo', ''));
if ($siteLogo !== '' && !preg_match('#^https?://#i', $siteLogo)) $siteLogo = '../' . ltrim($siteLogo, '/');
$batchName = trim((string)(($test['batch_label'] ?? '') ?: ($test['batch_name'] ?? '') ?: 'Common Batch'));
$batchTiming = trim((string)($test['batch_timing'] ?? ''));
$batchDays = trim((string)($test['batch_days'] ?? ''));
$schedule = '';
if (!empty($test['starts_at'])) $schedule .= date('d M Y, h:i A', strtotime((string)$test['starts_at']));
if (!empty($test['ends_at'])) $schedule .= ($schedule !== '' ? ' - ' : 'Until ') . date('d M Y, h:i A', strtotime((string)$test['ends_at']));
if ($schedule === '') $schedule = 'Date: __________________';
$totalMarks = 0.0;
foreach ($questions as $q) $totalMarks += (float)($q['marks'] ?? 0);
if ($totalMarks <= 0) $totalMarks = (float)($test['total_marks'] ?? 0);
$paperCode = 'WF-' . strtoupper(substr((string)($test['test_type'] ?? 'TEST'), 0, 3)) . '-' . str_pad((string)$testId, 4, '0', STR_PAD_LEFT);
$title = $mode === 'answer-key' ? 'Answer Key' : 'Offline Question Paper';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title . ' - ' . (string)$test['title']) ?></title>
<style>
:root{--navy:#071d3e;--navy2:#12335d;--gold:#e7ae2f;--ink:#17263a;--muted:#5d6b7c;--line:#d8e0e8;--soft:#f6f8fb;--green:#168759}
*{box-sizing:border-box}html,body{margin:0;padding:0;background:#e9eef5;color:var(--ink);font-family:Inter,"Noto Sans Devanagari","Nirmala UI",Mangal,Arial,sans-serif;-webkit-print-color-adjust:exact;print-color-adjust:exact}.toolbar{position:sticky;top:0;z-index:20;display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 14px;background:#071d3e;color:#fff;box-shadow:0 8px 25px rgba(7,29,62,.18)}.toolbar-left{min-width:0}.toolbar b{display:block;font-size:14px}.toolbar small{display:block;color:#c7d4e7;font-size:11px}.toolbar-actions{display:flex;gap:8px;flex-wrap:wrap}.tool-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:36px;padding:8px 12px;border:0;border-radius:9px;background:#fff;color:#071d3e;font-weight:800;font-size:12px;text-decoration:none;cursor:pointer}.tool-btn.primary{background:var(--gold)}.preview{width:min(210mm,calc(100% - 24px));min-height:297mm;margin:18px auto;background:#fff;box-shadow:0 18px 55px rgba(10,32,58,.16);position:relative;overflow:hidden}.watermark{position:fixed;left:50%;top:51%;z-index:0;transform:translate(-50%,-50%) rotate(-28deg);font-size:46pt;font-weight:900;letter-spacing:.04em;color:rgba(7,29,62,.035);white-space:nowrap;pointer-events:none}.print-head{position:relative;z-index:2;padding:8mm 9mm 4mm;border-bottom:2px solid var(--navy)}.brand-row{display:flex;align-items:center;gap:8px}.brand-logo{width:14mm;height:14mm;object-fit:contain}.brand-copy{min-width:0}.brand-copy h1{margin:0;color:var(--navy);font-size:14pt;line-height:1.05}.brand-copy p{margin:2px 0 0;color:var(--muted);font-size:7.7pt}.paper-chip{margin-left:auto;padding:4px 7px;border-radius:999px;background:#fff4d0;color:#765400;font-size:7pt;font-weight:900;white-space:nowrap}.paper-title{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px;align-items:end;margin-top:4mm}.paper-title h2{margin:0;color:var(--navy);font-size:13pt;line-height:1.15}.paper-title span{font-size:7.5pt;color:var(--muted);font-weight:700;text-align:right}.meta-strip{display:grid;grid-template-columns:1.45fr 1fr .7fr .7fr;gap:4px;margin-top:3mm}.meta-strip div{padding:4px 5px;border:1px solid var(--line);border-radius:5px;background:#fafbfd}.meta-strip b{display:block;color:#33475f;font-size:6.5pt;text-transform:uppercase;letter-spacing:.04em}.meta-strip span{display:block;margin-top:1px;font-size:7.4pt;font-weight:800;overflow-wrap:anywhere}.student-strip{position:relative;z-index:2;display:grid;grid-template-columns:1.25fr 1fr .72fr .7fr;gap:7mm;padding:3mm 9mm;border-bottom:1px solid var(--line);font-size:8pt}.student-strip span{display:block;padding:1.7mm 0;border-bottom:1px solid #65768a}.instructions{position:relative;z-index:2;display:flex;gap:6px;align-items:flex-start;padding:2.2mm 9mm;background:#f7f9fc;border-bottom:1px solid var(--line);font-size:7pt;line-height:1.35;color:#4f6074}.instructions b{color:var(--navy);white-space:nowrap}.question-list{position:relative;z-index:2;padding:2.5mm 9mm 9mm}.q-item{display:grid;grid-template-columns:7mm minmax(0,1fr) 10mm;gap:2mm;align-items:start;min-height:8.2mm;padding:1.25mm 0;border-bottom:1px solid #e3e8ee;break-inside:avoid;page-break-inside:avoid}.q-no{display:grid;place-items:center;width:6mm;height:6mm;border-radius:50%;background:var(--navy);color:#fff;font-size:7pt;font-weight:900}.q-main{min-width:0}.q-text{font-size:8.1pt;line-height:1.25;font-weight:700;color:#17283c;overflow-wrap:anywhere}.q-meta{display:flex;gap:4px;flex-wrap:wrap;margin-top:.7mm}.q-meta span{font-size:5.8pt;color:#6d7a8b}.q-options{display:grid;grid-template-columns:1fr 1fr;gap:1px 8px;margin-top:1mm;font-size:6.7pt;color:#3e4f63}.answer-space{height:4.2mm;margin-top:1mm;border-bottom:1px dotted #8491a1}.answer-key{margin-top:1mm;padding:1mm 1.5mm;border-left:2px solid var(--green);background:#f2fbf7;color:#183d2e;font-size:7pt;line-height:1.25}.answer-key p{margin:.5mm 0}.q-mark{text-align:right;color:#56687d;font-size:6.7pt;font-weight:900;white-space:nowrap}.print-foot{position:relative;z-index:2;display:flex;justify-content:space-between;gap:10px;padding:2mm 9mm 5mm;color:#6f7c8c;font-size:6.5pt}.repeat-page-label{display:none}.empty{padding:25mm;text-align:center;color:#6d7a8b}.screen-note{width:min(210mm,calc(100% - 24px));margin:0 auto 20px;padding:10px 12px;border-radius:10px;background:#fff7dc;color:#6d5412;font-size:12px;box-shadow:0 8px 25px rgba(20,40,65,.08)}
@page{size:A4 portrait;margin:0}
@media print{html,body{background:#fff}.toolbar,.screen-note{display:none!important}.preview{width:210mm;min-height:297mm;margin:0;box-shadow:none;overflow:visible}.watermark{position:fixed}.repeat-page-label{display:flex;position:fixed;z-index:5;left:9mm;right:9mm;bottom:2mm;justify-content:space-between;border-top:1px solid #d8e0e8;padding-top:1mm;color:#6d7a8b;font-size:6pt;background:#fff}.q-item{break-inside:avoid;page-break-inside:avoid}.print-head{padding-top:6mm}.question-list{padding-bottom:8mm}.print-foot{display:none}}
@media(max-width:700px){.toolbar{align-items:flex-start}.toolbar-actions{justify-content:flex-end}.preview{margin:10px auto}.meta-strip{grid-template-columns:1fr 1fr}.student-strip{grid-template-columns:1fr}.paper-title{grid-template-columns:1fr}.paper-title span{text-align:left}.q-options{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="toolbar">
  <div class="toolbar-left"><b><?= e($title) ?></b><small><?= e((string)$test['title']) ?> - <?= e($batchName) ?></small></div>
  <div class="toolbar-actions">
    <?php if ($mode === 'paper'): ?><a class="tool-btn" href="weekly-test-offline-paper.php?id=<?= e((string)$testId) ?>&mode=answer-key" target="_blank">Answer Key</a><?php else: ?><a class="tool-btn" href="weekly-test-offline-paper.php?id=<?= e((string)$testId) ?>&mode=paper" target="_blank">Student Paper</a><?php endif; ?>
    <button class="tool-btn primary" type="button" onclick="window.print()">Save as PDF / Print</button>
    <button class="tool-btn" type="button" onclick="window.close()">Close</button>
  </div>
</div>
<div class="screen-note"><b>PDF:</b> Click <b>Save as PDF / Print</b> and choose <b>Save as PDF</b> in the browser. The layout targets up to about 25 short questions per A4 page; long questions automatically move to the next page instead of being cut.</div>
<main class="preview">
<div class="watermark"><?= e(strtoupper($siteName)) ?></div>
<div class="repeat-page-label"><span><?= e($siteName) ?> - <?= e($batchName) ?></span><span><?= e($paperCode) ?> - <?= e($mode === 'answer-key' ? 'Answer Key' : 'Offline Test') ?></span></div>
<header class="print-head">
  <div class="brand-row">
    <?php if ($siteLogo !== ''): ?><img class="brand-logo" src="<?= e($siteLogo) ?>" alt="<?= e($siteName) ?> logo"><?php endif; ?>
    <div class="brand-copy"><h1><?= e($siteName) ?></h1><p>English Spoken - Offline Weekly Test</p></div>
    <span class="paper-chip"><?= e($mode === 'answer-key' ? 'ADMIN ANSWER KEY' : 'STUDENT QUESTION PAPER') ?></span>
  </div>
  <div class="paper-title"><h2><?= e((string)$test['title']) ?></h2><span><?= e($paperCode) ?><br><?= e(ucfirst((string)($test['test_type'] ?? 'test'))) ?> Test</span></div>
  <div class="meta-strip">
    <div><b>Batch</b><span><?= e($batchName) ?></span></div>
    <div><b>Timing / Days</b><span><?= e(trim($batchTiming . ($batchTiming && $batchDays ? ' - ' : '') . $batchDays) ?: 'As scheduled') ?></span></div>
    <div><b>Time</b><span><?= e((string)($test['duration_minutes'] ?? 30)) ?> min</span></div>
    <div><b>Marks</b><span><?= e(rtrim(rtrim(number_format($totalMarks, 2, '.', ''), '0'), '.')) ?></span></div>
  </div>
</header>
<?php if ($mode === 'paper'): ?>
  <div class="student-strip"><span>Name: ______________________________</span><span>Mobile / Roll: ____________________</span><span>Date: ____________</span><span>Score: ______ / <?= e(rtrim(rtrim(number_format($totalMarks, 2, '.', ''), '0'), '.')) ?></span></div>
<?php endif; ?>
<div class="instructions"><b>Schedule:</b><span><?= e($schedule) ?>. <?= e(trim((string)($test['instructions'] ?? 'Write clear answers in the space below each question.'))) ?></span></div>
<section class="question-list">
<?php if (!$questions): ?><div class="empty">No active questions are available in this paper.</div><?php endif; ?>
<?php foreach ($questions as $index => $q):
  $options = array_values(array_filter([trim((string)($q['option_a'] ?? '')),trim((string)($q['option_b'] ?? '')),trim((string)($q['option_c'] ?? '')),trim((string)($q['option_d'] ?? ''))], static fn($v) => $v !== ''));
  $accepted = weekly_test_split_expected_answers((string)($q['expected_answer'] ?? ''));
?>
<article class="q-item">
  <span class="q-no"><?= e((string)($index + 1)) ?></span>
  <div class="q-main">
    <div class="q-text"><?= e((string)$q['question_text']) ?></div>
    <?php if (!empty($q['topic_name']) || !empty($q['question_type'])): ?><div class="q-meta"><span><?= e((string)($q['topic_name'] ?? '')) ?></span><span><?= e(str_replace('_',' ',(string)($q['question_type'] ?? ''))) ?></span></div><?php endif; ?>
    <?php if ($options): ?><div class="q-options"><?php foreach ($options as $oi => $opt): ?><span><?= e(chr(65 + $oi) . '. ' . $opt) ?></span><?php endforeach; ?></div><?php endif; ?>
    <?php if ($mode === 'answer-key'): ?><div class="answer-key"><?php if ($accepted): ?><?php foreach ($accepted as $ans): ?><p><?= e($ans) ?></p><?php endforeach; ?><?php else: ?><p>No master answer uploaded.</p><?php endif; ?></div><?php else: ?><div class="answer-space" aria-label="Answer writing space"></div><?php endif; ?>
  </div>
  <span class="q-mark"><?= e(rtrim(rtrim(number_format((float)($q['marks'] ?? 1), 2, '.', ''), '0'), '.')) ?> mark<?= (float)($q['marks'] ?? 1) == 1.0 ? '' : 's' ?></span>
</article>
<?php endforeach; ?>
</section>
<footer class="print-foot"><span><?= e($siteName) ?> - <?= e($batchName) ?></span><span><?= e($paperCode) ?> - <?= e($mode === 'answer-key' ? 'Confidential Answer Key' : 'Write neatly and submit to your teacher') ?></span></footer>
</main>
<?php if ($autoPrint): ?><script>window.addEventListener('load',function(){setTimeout(function(){window.print();},450);});</script><?php endif; ?>
</body>
</html>
