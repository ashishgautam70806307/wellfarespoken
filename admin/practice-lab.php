<?php require_once __DIR__ . '/_header.php';
ensure_schema_updates();
$mode = $_GET['mode'] ?? 'questions';
$allowedModes = ['categories','lessons','questions','mistakes','settings','attempts'];
if (!in_array($mode, $allowedModes, true)) $mode = 'questions';

if ($mode === 'settings' && !admin_can('settings.manage')) { admin_require_permission('settings.manage'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? '')) {
    if (($_POST['action'] ?? '') === 'delete_item') {
        $table = safe_admin_table((string)($_POST['table'] ?? ''));
        $id = (int)($_POST['id'] ?? 0);
        if ($table && $id > 0 && in_array($table, ['practice_categories','practice_lessons','practice_questions','practice_common_mistakes'], true)) {
            db()->prepare("UPDATE `$table` SET status_deleted=1 WHERE id=?")->execute([$id]);
            flash('success', 'Item deleted successfully.');
        }
        redirect('practice-lab.php?mode=' . $mode);
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'save_category') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['category_name'] ?? '');
        $slug = strtolower(trim($_POST['slug'] ?: preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
        if ($name) {
            if ($id) {
                db()->prepare('UPDATE practice_categories SET category_name=?, slug=?, description=?, icon=?, sort_order=?, published=? WHERE id=?')->execute([$name, $slug, trim($_POST['description'] ?? ''), trim($_POST['icon'] ?? ''), (int)($_POST['sort_order'] ?? 0), $_POST['published'] ?? 'Yes', $id]);
            } else {
                db()->prepare('INSERT INTO practice_categories (category_name, slug, description, icon, sort_order, published) VALUES (?,?,?,?,?,?)')->execute([$name, $slug, trim($_POST['description'] ?? ''), trim($_POST['icon'] ?? ''), (int)($_POST['sort_order'] ?? 0), $_POST['published'] ?? 'Yes']);
            }
            flash('success', 'Practice category saved.');
        }
        redirect('practice-lab.php?mode=categories');
    }
    if ($action === 'save_lesson') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['lesson_title'] ?? '');
        if ($title) {
            $data = [(int)($_POST['category_id'] ?? 0), $title, trim($_POST['lesson_type'] ?? 'tense'), trim($_POST['level'] ?? ''), trim($_POST['tense_name'] ?? ''), trim($_POST['short_description'] ?? ''), trim($_POST['instructions'] ?? ''), (int)($_POST['sort_order'] ?? 0), $_POST['published'] ?? 'Yes'];
            if ($id) {
                $data[] = $id;
                db()->prepare('UPDATE practice_lessons SET category_id=?, lesson_title=?, lesson_type=?, level=?, tense_name=?, short_description=?, instructions=?, sort_order=?, published=? WHERE id=?')->execute($data);
            } else {
                db()->prepare('INSERT INTO practice_lessons (category_id, lesson_title, lesson_type, level, tense_name, short_description, instructions, sort_order, published) VALUES (?,?,?,?,?,?,?,?,?)')->execute($data);
            }
            flash('success', 'Practice lesson saved.');
        }
        redirect('practice-lab.php?mode=lessons');
    }
    if ($action === 'save_question') {
        $id = (int)($_POST['id'] ?? 0);
        $question = trim($_POST['question_text'] ?? '');
        $lessonId = (int)($_POST['lesson_id'] ?? 0);
        $lessonStmt = db()->prepare('SELECT category_id FROM practice_lessons WHERE id=? LIMIT 1');
        $lessonStmt->execute([$lessonId]);
        $categoryId = (int)(($lessonStmt->fetch()['category_id'] ?? 0));
        if ($question && $lessonId) {
            $data = [$categoryId, $lessonId, trim($_POST['question_type'] ?? 'fill_blank'), $question, trim($_POST['option_a'] ?? ''), trim($_POST['option_b'] ?? ''), trim($_POST['option_c'] ?? ''), trim($_POST['option_d'] ?? ''), trim($_POST['correct_answer'] ?? ''), trim($_POST['sample_answer'] ?? ''), trim($_POST['accepted_answers'] ?? ''), trim($_POST['answer_match_mode'] ?? 'smart'), trim($_POST['answer_help'] ?? ''), trim($_POST['explanation'] ?? ''), trim($_POST['tense_name'] ?? ''), trim($_POST['level'] ?? ''), (int)($_POST['sort_order'] ?? 0), $_POST['published'] ?? 'Yes'];
            if ($id) {
                $data[] = $id;
                db()->prepare('UPDATE practice_questions SET category_id=?, lesson_id=?, question_type=?, question_text=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_answer=?, sample_answer=?, accepted_answers=?, answer_match_mode=?, answer_help=?, explanation=?, tense_name=?, level=?, sort_order=?, published=? WHERE id=?')->execute($data);
            } else {
                db()->prepare('INSERT INTO practice_questions (category_id, lesson_id, question_type, question_text, option_a, option_b, option_c, option_d, correct_answer, sample_answer, accepted_answers, answer_match_mode, answer_help, explanation, tense_name, level, sort_order, published) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute($data);
            }
            flash('success', 'Practice question saved.');
        }
        redirect('practice-lab.php?mode=questions');
    }
    if ($action === 'save_mistake') {
        $id = (int)($_POST['id'] ?? 0);
        $wrong = trim($_POST['wrong_pattern'] ?? '');
        if ($wrong) {
            $data = [$wrong, trim($_POST['correct_pattern'] ?? ''), trim($_POST['explanation'] ?? ''), trim($_POST['example_sentence'] ?? ''), $_POST['published'] ?? 'Yes'];
            if ($id) {
                $data[] = $id;
                db()->prepare('UPDATE practice_common_mistakes SET wrong_pattern=?, correct_pattern=?, explanation=?, example_sentence=?, published=? WHERE id=?')->execute($data);
            } else {
                db()->prepare('INSERT INTO practice_common_mistakes (wrong_pattern, correct_pattern, explanation, example_sentence, published) VALUES (?,?,?,?,?)')->execute($data);
            }
            flash('success', 'Common mistake saved.');
        }
        redirect('practice-lab.php?mode=mistakes');
    }
    if ($action === 'save_settings') {
        admin_require_permission('settings.manage');
        foreach (['practice_enabled','local_mode_enabled','browser_voice_enabled','ai_enabled','ai_correction_enabled','ai_fallback_enabled','ai_provider','openai_model','ai_daily_limit','ai_timeout_seconds','ai_temperature','ai_system_prompt','free_daily_limit','practice_intro_note'] as $key) {
            save_practice_setting($key, trim($_POST[$key] ?? ''));
        }
        practice_purge_legacy_ai_secret();
        flash('success', 'Practice settings saved.');
        redirect('practice-lab.php?mode=settings');
    }
}

$edit = null;
if (isset($_GET['edit'], $_GET['table'])) {
    $table = safe_admin_table($_GET['table']);
    if ($table) {
        $stmt = db()->prepare("SELECT * FROM `$table` WHERE id=? LIMIT 1");
        $stmt->execute([(int)$_GET['edit']]);
        $edit = $stmt->fetch() ?: null;
    }
}
$cats = db()->query('SELECT * FROM practice_categories WHERE status_deleted=0 ORDER BY sort_order ASC, id ASC')->fetchAll();
$lessons = db()->query('SELECT l.*, c.category_name FROM practice_lessons l LEFT JOIN practice_categories c ON c.id=l.category_id WHERE l.status_deleted=0 ORDER BY l.sort_order ASC, l.id DESC')->fetchAll();
$questions = db()->query('SELECT q.*, l.lesson_title FROM practice_questions q LEFT JOIN practice_lessons l ON l.id=q.lesson_id WHERE q.status_deleted=0 ORDER BY q.id DESC LIMIT 80')->fetchAll();
$mistakes = db()->query('SELECT * FROM practice_common_mistakes WHERE status_deleted=0 ORDER BY id DESC')->fetchAll();
$attempts = db()->query('SELECT a.*, q.question_text FROM practice_attempts a LEFT JOIN practice_questions q ON q.id=a.question_id ORDER BY a.id DESC LIMIT 100')->fetchAll();
?>
<div class="admin-top"><div><h1>Free AI English Practice Lab</h1><p>Build the correct practice feature for spoken English: local database practice first, browser voice second, optional AI later.</p><span class="ai-status-pill"><?= e(practice_ai_status_label()) ?></span></div><div class="admin-actions"><a class="btn btn-primary" href="../free-ai-english-practice.php" target="_blank">Open Practice Page</a></div></div>
<?php if ($msg = flash('success')): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
<div class="admin-tabs">
    <?php $practiceTabs=['questions'=>'Questions','lessons'=>'Lessons','categories'=>'Categories','mistakes'=>'Common Mistakes','attempts'=>'Attempts']; if(admin_can('settings.manage')) $practiceTabs['settings']='Settings'; foreach ($practiceTabs as $key=>$label): ?>
        <a class="<?= $mode===$key?'active':'' ?>" href="practice-lab.php?mode=<?= e($key) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</div>
<?php if ($mode === 'categories'): ?>
<div class="grid-2"><div class="panel-card"><h2><?= $edit ? 'Edit' : 'Add' ?> Category</h2><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save_category"><input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? '')) ?>"><label>Category Name<input name="category_name" value="<?= e($edit['category_name'] ?? '') ?>" required></label><label>Slug<input name="slug" value="<?= e($edit['slug'] ?? '') ?>"></label><label>Icon<input name="icon" value="<?= e($edit['icon'] ?? '') ?>" placeholder="🧠"></label><label>Sort Order<input type="number" name="sort_order" value="<?= e((string)($edit['sort_order'] ?? 0)) ?>"></label><label class="full">Description<textarea name="description"><?= e($edit['description'] ?? '') ?></textarea></label><label>Published<select name="published"><option <?= (($edit['published'] ?? 'Yes')==='Yes')?'selected':'' ?>>Yes</option><option <?= (($edit['published'] ?? '')==='No')?'selected':'' ?>>No</option></select></label><button class="btn btn-primary">Save Category</button></form></div><div class="panel-card table-wrap"><table><thead><tr><th>Name</th><th>Published</th><th>Action</th></tr></thead><tbody><?php foreach($cats as $row): ?><tr><td><strong><?= e($row['icon'].' '.$row['category_name']) ?></strong><br><small><?= e($row['slug']) ?></small></td><td><span class="badge"><?= e($row['published']) ?></span></td><td><div class="table-actions"><a class="btn btn-sm btn-soft" href="practice-lab.php?mode=categories&table=practice_categories&edit=<?= e((string)$row['id']) ?>">Edit</a><form method="post" class="inline-form confirm-action" data-confirm="Delete this item?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete_item"><input type="hidden" name="table" value="practice_categories"><input type="hidden" name="id" value="<?= e((string)$row['id']) ?>"><button class="btn btn-sm btn-danger" type="submit">Delete</button></form></div></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php elseif ($mode === 'lessons'): ?>
<div class="grid-2"><div class="panel-card"><h2><?= $edit ? 'Edit' : 'Add' ?> Lesson</h2><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save_lesson"><input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? '')) ?>"><label>Category<select name="category_id"><?php foreach($cats as $cat): ?><option value="<?= e((string)$cat['id']) ?>" <?= ((int)($edit['category_id'] ?? 0)===(int)$cat['id'])?'selected':'' ?>><?= e($cat['category_name']) ?></option><?php endforeach; ?></select></label><label>Lesson Title<input name="lesson_title" value="<?= e($edit['lesson_title'] ?? '') ?>" required></label><label>Type<select name="lesson_type"><?php foreach(['tense','situation','correction','voice','translation','interview'] as $t): ?><option <?= (($edit['lesson_type'] ?? '')===$t)?'selected':'' ?>><?= e($t) ?></option><?php endforeach; ?></select></label><label>Level<input name="level" value="<?= e($edit['level'] ?? '') ?>"></label><label>Tense Name<input name="tense_name" value="<?= e($edit['tense_name'] ?? '') ?>"></label><label>Sort Order<input type="number" name="sort_order" value="<?= e((string)($edit['sort_order'] ?? 0)) ?>"></label><label class="full">Short Description<textarea name="short_description"><?= e($edit['short_description'] ?? '') ?></textarea></label><label class="full">Instructions<textarea name="instructions"><?= e($edit['instructions'] ?? '') ?></textarea></label><label>Published<select name="published"><option <?= (($edit['published'] ?? 'Yes')==='Yes')?'selected':'' ?>>Yes</option><option <?= (($edit['published'] ?? '')==='No')?'selected':'' ?>>No</option></select></label><button class="btn btn-primary">Save Lesson</button></form></div><div class="panel-card table-wrap"><table><thead><tr><th>Lesson</th><th>Type</th><th>Action</th></tr></thead><tbody><?php foreach($lessons as $row): ?><tr><td><strong><?= e($row['lesson_title']) ?></strong><br><small><?= e($row['category_name'].' • '.$row['level']) ?></small></td><td><?= e($row['lesson_type']) ?></td><td><div class="table-actions"><a class="btn btn-sm btn-soft" href="practice-lab.php?mode=lessons&table=practice_lessons&edit=<?= e((string)$row['id']) ?>">Edit</a><form method="post" class="inline-form confirm-action" data-confirm="Delete this item?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete_item"><input type="hidden" name="table" value="practice_lessons"><input type="hidden" name="id" value="<?= e((string)$row['id']) ?>"><button class="btn btn-sm btn-danger" type="submit">Delete</button></form></div></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php elseif ($mode === 'questions'): ?>
<div class="grid-2"><div class="panel-card"><h2><?= $edit ? 'Edit' : 'Add' ?> Question</h2><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save_question"><input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? '')) ?>"><label>Lesson<select name="lesson_id"><?php foreach($lessons as $lessonRow): ?><option value="<?= e((string)$lessonRow['id']) ?>" <?= ((int)($edit['lesson_id'] ?? 0)===(int)$lessonRow['id'])?'selected':'' ?>><?= e($lessonRow['lesson_title']) ?></option><?php endforeach; ?></select></label><label>Question Type<select name="question_type"><?php foreach(['fill_blank','conversion','situation','correction','voice','translation','mcq'] as $t): ?><option <?= (($edit['question_type'] ?? '')===$t)?'selected':'' ?>><?= e($t) ?></option><?php endforeach; ?></select></label><label class="full">Question Text<textarea name="question_text" required><?= e($edit['question_text'] ?? '') ?></textarea></label><label>Option A<input name="option_a" value="<?= e($edit['option_a'] ?? '') ?>"></label><label>Option B<input name="option_b" value="<?= e($edit['option_b'] ?? '') ?>"></label><label>Option C<input name="option_c" value="<?= e($edit['option_c'] ?? '') ?>"></label><label>Option D<input name="option_d" value="<?= e($edit['option_d'] ?? '') ?>"></label><label class="full">Correct Answer<textarea name="correct_answer" placeholder="Main teacher answer"><?= e($edit['correct_answer'] ?? '') ?></textarea></label><label class="full">Accepted Answers <small>One answer per line or use ||. Example: Isn't that Radha? || Is that not Radha?</small><textarea name="accepted_answers" placeholder="Isn't that Radha?&#10;Is that not Radha?"><?= e($edit['accepted_answers'] ?? '') ?></textarea></label><label>Answer Match Mode<select name="answer_match_mode"><?php foreach(['smart'=>'Smart Match','strict'=>'Strict Exact','contains_keywords'=>'Keywords Allowed'] as $mk=>$mv): ?><option value="<?= e($mk) ?>" <?= (($edit['answer_match_mode'] ?? 'smart')===$mk)?'selected':'' ?>><?= e($mv) ?></option><?php endforeach; ?></select></label><label class="full">Sample / Natural Answer<textarea name="sample_answer" placeholder="Best natural spoken answer shown to student"><?= e($edit['sample_answer'] ?? '') ?></textarea></label><label class="full">Answer Help / Hint<textarea name="answer_help" placeholder="Small hint shown after checking, optional"><?= e($edit['answer_help'] ?? '') ?></textarea></label><label class="full">Explanation<textarea name="explanation"><?= e($edit['explanation'] ?? '') ?></textarea></label><label>Tense Name<input name="tense_name" value="<?= e($edit['tense_name'] ?? '') ?>"></label><label>Level<input name="level" value="<?= e($edit['level'] ?? '') ?>"></label><label>Sort Order<input type="number" name="sort_order" value="<?= e((string)($edit['sort_order'] ?? 0)) ?>"></label><label>Published<select name="published"><option <?= (($edit['published'] ?? 'Yes')==='Yes')?'selected':'' ?>>Yes</option><option <?= (($edit['published'] ?? '')==='No')?'selected':'' ?>>No</option></select></label><button class="btn btn-primary">Save Question</button></form></div><div class="panel-card table-wrap"><table><thead><tr><th>Question</th><th>Lesson</th><th>Action</th></tr></thead><tbody><?php foreach($questions as $row): ?><tr><td><strong><?= e((strlen($row['question_text']) > 70 ? substr($row['question_text'], 0, 70) . '...' : $row['question_text'])) ?></strong><br><small><?= e($row['question_type'].' • '.$row['level']) ?></small></td><td><?= e($row['lesson_title'] ?? '-') ?></td><td><div class="table-actions"><a class="btn btn-sm btn-soft" href="practice-lab.php?mode=questions&table=practice_questions&edit=<?= e((string)$row['id']) ?>">Edit</a><form method="post" class="inline-form confirm-action" data-confirm="Delete this item?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete_item"><input type="hidden" name="table" value="practice_questions"><input type="hidden" name="id" value="<?= e((string)$row['id']) ?>"><button class="btn btn-sm btn-danger" type="submit">Delete</button></form></div></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php elseif ($mode === 'mistakes'): ?>
<div class="grid-2"><div class="panel-card"><h2><?= $edit ? 'Edit' : 'Add' ?> Common Mistake</h2><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save_mistake"><input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? '')) ?>"><label>Wrong Pattern<input name="wrong_pattern" value="<?= e($edit['wrong_pattern'] ?? '') ?>" required></label><label>Correct Pattern<input name="correct_pattern" value="<?= e($edit['correct_pattern'] ?? '') ?>"></label><label class="full">Explanation<textarea name="explanation"><?= e($edit['explanation'] ?? '') ?></textarea></label><label class="full">Example Sentence<textarea name="example_sentence"><?= e($edit['example_sentence'] ?? '') ?></textarea></label><label>Published<select name="published"><option <?= (($edit['published'] ?? 'Yes')==='Yes')?'selected':'' ?>>Yes</option><option <?= (($edit['published'] ?? '')==='No')?'selected':'' ?>>No</option></select></label><button class="btn btn-primary">Save Mistake</button></form></div><div class="panel-card table-wrap"><table><thead><tr><th>Wrong</th><th>Correct</th><th>Action</th></tr></thead><tbody><?php foreach($mistakes as $row): ?><tr><td><?= e($row['wrong_pattern']) ?></td><td><?= e($row['correct_pattern']) ?></td><td><div class="table-actions"><a class="btn btn-sm btn-soft" href="practice-lab.php?mode=mistakes&table=practice_common_mistakes&edit=<?= e((string)$row['id']) ?>">Edit</a><form method="post" class="inline-form confirm-action" data-confirm="Delete this item?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete_item"><input type="hidden" name="table" value="practice_common_mistakes"><input type="hidden" name="id" value="<?= e((string)$row['id']) ?>"><button class="btn btn-sm btn-danger" type="submit">Delete</button></form></div></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php elseif ($mode === 'settings'): ?>
<div class="grid-2">
    <div class="panel-card">
        <h2>Core Practice Settings</h2>
        <p class="muted-text">Keep local mode ON so the practice feature never depends only on AI.</p>
        <form method="post" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="save_settings">
            <label>Practice Enabled<select name="practice_enabled"><option <?= practice_setting('practice_enabled','Yes')==='Yes'?'selected':'' ?>>Yes</option><option <?= practice_setting('practice_enabled','Yes')==='No'?'selected':'' ?>>No</option></select></label>
            <label>Local Mode<select name="local_mode_enabled"><option <?= practice_setting('local_mode_enabled','Yes')==='Yes'?'selected':'' ?>>Yes</option><option <?= practice_setting('local_mode_enabled','Yes')==='No'?'selected':'' ?>>No</option></select></label>
            <label>Browser Voice<select name="browser_voice_enabled"><option <?= practice_setting('browser_voice_enabled','Yes')==='Yes'?'selected':'' ?>>Yes</option><option <?= practice_setting('browser_voice_enabled','Yes')==='No'?'selected':'' ?>>No</option></select></label>
            <label>Free Local Daily Limit<input type="number" name="free_daily_limit" value="<?= e(practice_setting('free_daily_limit','20')) ?>"></label>
            <label class="full">Intro Note<textarea name="practice_intro_note"><?= e(practice_setting('practice_intro_note','')) ?></textarea></label>
            <h2 class="full">Optional AI Enhancement</h2>
            <label>AI Enabled<select name="ai_enabled"><option <?= practice_setting('ai_enabled','No')==='No'?'selected':'' ?>>No</option><option <?= practice_setting('ai_enabled','No')==='Yes'?'selected':'' ?>>Yes</option></select></label>
            <label>AI Correction<select name="ai_correction_enabled"><option <?= practice_setting('ai_correction_enabled','Yes')==='Yes'?'selected':'' ?>>Yes</option><option <?= practice_setting('ai_correction_enabled','Yes')==='No'?'selected':'' ?>>No</option></select></label>
            <label>Fallback Local Mode<select name="ai_fallback_enabled"><option <?= practice_setting('ai_fallback_enabled','Yes')==='Yes'?'selected':'' ?>>Yes</option><option <?= practice_setting('ai_fallback_enabled','Yes')==='No'?'selected':'' ?>>No</option></select></label>
            <label>AI Provider<input name="ai_provider" value="<?= e(practice_setting('ai_provider','openai')) ?>"></label>
            <div class="full alert alert-info"><strong>OpenAI secret:</strong> <?= practice_ai_api_key() !== '' ? 'Configured in server .env' : 'Not configured' ?>. API keys are never stored or edited from this Admin page.</div>
            <label>OpenAI Model<input name="openai_model" value="<?= e(practice_setting('openai_model','gpt-4o-mini')) ?>"></label>
            <label>AI Daily Limit<input type="number" name="ai_daily_limit" value="<?= e(practice_setting('ai_daily_limit','10')) ?>"></label>
            <label>Timeout Seconds<input type="number" name="ai_timeout_seconds" value="<?= e(practice_setting('ai_timeout_seconds','18')) ?>"></label>
            <label>Temperature<input name="ai_temperature" value="<?= e(practice_setting('ai_temperature','0.2')) ?>"></label>
            <div class="full"><small class="help">AI endpoint is server-controlled and restricted to the OPENAI_ALLOWED_HOSTS allowlist.</small></div>
            <label class="full">AI Coach System Prompt<textarea name="ai_system_prompt" rows="4"><?= e(practice_setting('ai_system_prompt','')) ?></textarea></label>
            <button class="btn btn-primary">Save Settings</button>
        </form>
    </div>
    <div class="panel-card">
        <h2>Safe AI Rules</h2>
        <div class="visual-list admin-checklist">
            <div><span>Local questions stay active</span><b>Never breaks</b></div>
            <div><span>API key storage</span><b>Server .env only</b></div>
            <div><span>Daily AI limit</span><b><?= e(practice_setting('ai_daily_limit','10')) ?></b></div>
            <div><span>AI used today</span><b><?= e((string)practice_today_ai_used()) ?></b></div>
            <div><span>Fallback if AI fails</span><b><?= e(practice_setting('ai_fallback_enabled','Yes')) ?></b></div>
            <div><span>Current AI status</span><b><?= e(practice_ai_status_label()) ?></b></div>
        </div>
        <p class="muted-text">For your institute website, this is the correct advanced setup: local practice first, optional AI second. Visitors always get feedback; AI only improves the feedback when available.</p><p class="muted-text"><strong>Important:</strong> The feature is free in local mode. Real AI correction needs a server-configured API key, but the visitor experience will not break if the key is empty, API is off, or limit is reached.</p>
    </div>
</div>
<?php else: ?>
<div class="panel-card table-wrap"><div class="toolbar"><h2>Latest Practice Attempts</h2><p>Use this later to understand weak areas and convert practice users into admission enquiries.</p></div><table><thead><tr><th>Date</th><th>Question</th><th>Student Answer</th><th>Teacher Answer</th><th>Score</th><th>Result</th><th>Feedback</th></tr></thead><tbody><?php foreach($attempts as $row): ?><tr><td><?= e($row['created_at']) ?></td><td><?= e((strlen($row['question_text'] ?? '-') > 60 ? substr($row['question_text'] ?? '-', 0, 60) . '...' : ($row['question_text'] ?? '-'))) ?></td><td><?= e((strlen($row['user_answer'] ?? '-') > 60 ? substr($row['user_answer'] ?? '-', 0, 60) . '...' : ($row['user_answer'] ?? '-'))) ?></td><td><?= e((strlen($row['correct_answer'] ?? '-') > 60 ? substr($row['correct_answer'] ?? '-', 0, 60) . '...' : ($row['correct_answer'] ?? '-'))) ?></td><td><?= e((string)$row['score']) ?></td><td><span class="badge <?= !empty($row['is_correct']) ? 'badge-green' : 'badge-contacted' ?>"><?= !empty($row['is_correct']) ? 'Correct' : 'Improve' ?></span><br><small><?= e($row['match_type'] ?? '') ?></small></td><td><?= e((strlen($row['local_feedback'] ?? '-') > 80 ? substr($row['local_feedback'] ?? '-', 0, 80) . '...' : ($row['local_feedback'] ?? '-'))) ?></td></tr><?php endforeach; ?><?php if(!$attempts): ?><tr><td colspan="7" class="empty-state">No attempts yet.</td></tr><?php endif; ?></tbody></table></div>
<?php endif; ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
