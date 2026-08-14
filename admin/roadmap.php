<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
ensure_schema_updates();
roadmap_seed_defaults();

$tab = $_GET['tab'] ?? 'words';
if (!in_array($tab, ['words','uses','tense'], true)) $tab = 'words';

if (isset($_GET['download_sample'])) {
    $sampleMap = [
        'words' => [
            'filename' => 'roadmap_word_meaning_sample.csv',
            'rows' => [
                ['key','col_1_english_word','col_2_hindi_meaning','col_3_example','col_4_hindi_example','col_5_accepted_variation','col_6_type_tag','example_text_notes','sort_order'],
                ['I','I','main / mai','I am a student.','main ek student hoon.','me','pronoun','Subject pronoun for self.',1],
                ['Book','Book','kitaab','This is my book.','yah meri kitaab hai.','','word','Daily object word.',2],
                ['This','This','yah / ye','This is a pen.','yah ek pen hai.','','demonstrative','Near object word.',3],
            ],
        ],
        'uses' => [
            'filename' => 'roadmap_uses_modal_pattern_sample.csv',
            'rows' => [
                ['key','col_1_rule_structure','col_2_hindi_question','col_3_correct_english_answer','col_4_hindi_example','col_5_accepted_variation','col_6_type_tag','example_text_notes','sort_order'],
                ['Has simple','Subject + has + object','uske paas bike hai.','He has a bike.','uske paas bike hai.','He has one bike.','simple','Use has with he/she/it/singular name.',1],
                ['Have simple','Subject + have + object','mere paas kitaab hai.','I have a book.','mere paas kitaab hai.','I have one book.','simple','Use have with I/we/you/they/plural.',2],
                ['Can ability','Subject + can + verb1','main english bol sakta hoon.','I can speak English.','main english bol sakta hoon.','I am able to speak English.','ability','Can is used for ability.',3],
            ],
        ],
        'tense' => [
            'filename' => 'roadmap_tense_sample.csv',
            'rows' => [
                ['key','col_1_structure','col_2_hindi_question','col_3_correct_english_answer','col_4_hindi_example','col_5_accepted_variation','col_6_type_tag','example_text_notes','sort_order'],
                ['Present Simple','Subject + V1/V5 + object','main roz english bolta hoon.','I speak English every day.','main roz english bolta hoon.','I speak English daily.','present_simple','Habit/routine tense.',1],
                ['Past Simple','Subject + V2 + object','maine kal english padhi.','I studied English yesterday.','maine kal english padhi.','I read English yesterday.','past_simple','Past completed action.',2],
                ['Future Simple','Subject + will + V1 + object','main kal english padhunga.','I will study English tomorrow.','main kal english padhunga.','I shall study English tomorrow.','future_simple','Future action.',3],
            ],
        ],
    ];
    $sample = $sampleMap[$tab];
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $sample['filename'] . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo "ï»¿";
    $out = fopen('php://output', 'w');
    foreach ($sample['rows'] as $row) {
        fputcsv($out, array_map('csv_safe_cell', $row));
    }
    fclose($out);
    exit;
}

$admin_page_final_styles = ['assets/css/phase169-admin-usability.css'];
require_once __DIR__ . '/_header.php';

$typeMap = [
    'words' => ['title' => 'Word Meaning Manager', 'types' => ['meaning'], 'help' => 'Pronouns, this/that, daily word meanings and vocabulary.'],
    'uses' => ['title' => 'Uses / Modal Pattern Manager', 'types' => ['use'], 'help' => 'Has/have, was/were, should, can, could, must, have to, want to etc.'],
    'tense' => ['title' => 'Tense Manager', 'types' => ['tense'], 'help' => '12 tenses rules, structure, examples and practice rows.'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        flash('error', 'Security token expired. Please try again.');
        redirect('roadmap.php?tab=' . urlencode($tab));
    }
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'save_unit') {
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            if ($title === '') throw new Exception('Step title is required.');

            $groupTitle = $tab === 'words' ? 'Foundation' : ($tab === 'uses' ? 'Use-Based English' : 'Tense Mastery');
            $stmt = db()->prepare("SELECT id FROM roadmap_groups WHERE title=? AND status_deleted=0 LIMIT 1");
            $stmt->execute([$groupTitle]);
            $groupId = (int)$stmt->fetchColumn();
            if (!$groupId) {
                $stmt = db()->prepare("INSERT INTO roadmap_groups (title, subtitle, icon, color, sort_order) VALUES (?,?,?,?,?)");
                $stmt->execute([$groupTitle, $typeMap[$tab]['help'], $tab === 'words' ? '📘' : ($tab === 'uses' ? '🧩' : '⏱'), '#1a3565', $tab === 'words' ? 1 : ($tab === 'uses' ? 3 : 4)]);
                $groupId = (int)db()->lastInsertId();
            }

            $unitType = $tab === 'words' ? 'meaning' : ($tab === 'uses' ? 'use' : 'tense');
            $data = [
                $groupId,
                $title,
                trim($_POST['subtitle'] ?? ''),
                trim($_POST['description'] ?? ''),
                $unitType,
                trim($_POST['level'] ?? 'Beginner'),
                trim($_POST['target_url'] ?? ''),
                trim($_POST['icon'] ?? ($tab === 'words' ? '📘' : ($tab === 'uses' ? '🧩' : '⏱'))),
                (int)($_POST['reward_points'] ?? 10),
                (int)($_POST['sort_order'] ?? 0),
                ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes',
            ];
            if ($id > 0) {
                $stmt = db()->prepare("UPDATE roadmap_units SET group_id=?, title=?, subtitle=?, description=?, unit_type=?, level=?, target_url=?, icon=?, reward_points=?, sort_order=?, published=? WHERE id=?");
                $data[] = $id;
                $stmt->execute($data);
                flash('success', 'Step updated.');
            } else {
                $stmt = db()->prepare("INSERT INTO roadmap_units (group_id, title, subtitle, description, unit_type, level, target_url, icon, reward_points, sort_order, published) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute($data);
                flash('success', 'Step created.');
            }
            redirect('roadmap.php?tab=' . urlencode($tab));
        }

        if ($action === 'toggle_unit_status') {
            $id = (int)($_POST['id'] ?? 0);
            $published = ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes';
            if ($id <= 0) throw new Exception('Invalid step selected.');
            $stmt = db()->prepare("UPDATE roadmap_units SET published=? WHERE id=? AND status_deleted=0");
            $stmt->execute([$published, $id]);
            flash('success', $published === 'Yes' ? 'Step published.' : 'Step moved to draft.');
            redirect('roadmap.php?tab=' . urlencode($tab) . '&unit_id=' . $id);
        }

        if ($action === 'delete_unit') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception('Invalid step selected.');
            $stmt = db()->prepare("UPDATE roadmap_units SET status_deleted=1, published='No' WHERE id=? AND status_deleted=0");
            $stmt->execute([$id]);
            flash('success', 'Step deleted safely. Its records are hidden because the topic is deleted.');
            redirect('roadmap.php?tab=' . urlencode($tab));
        }

        if ($action === 'save_item') {
            $id = (int)($_POST['id'] ?? 0);
            $unitId = (int)($_POST['unit_id'] ?? 0);
            if ($unitId <= 0) throw new Exception('Please select step.');
            $data = [
                $unitId,
                trim($_POST['item_key'] ?? ''),
                trim($_POST['col_1'] ?? ''),
                trim($_POST['col_2'] ?? ''),
                trim($_POST['col_3'] ?? ''),
                trim($_POST['col_4'] ?? ''),
                trim($_POST['col_5'] ?? ''),
                trim($_POST['col_6'] ?? ''),
                trim($_POST['example_text'] ?? ''),
                (int)($_POST['sort_order'] ?? 0),
                ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes',
            ];
            if ($id > 0) {
                $stmt = db()->prepare("UPDATE roadmap_items SET unit_id=?, item_key=?, col_1=?, col_2=?, col_3=?, col_4=?, col_5=?, col_6=?, example_text=?, sort_order=?, published=? WHERE id=?");
                $data[] = $id;
                $stmt->execute($data);
                flash('success', 'Record updated.');
            } else {
                $stmt = db()->prepare("INSERT INTO roadmap_items (unit_id, item_key, col_1, col_2, col_3, col_4, col_5, col_6, example_text, sort_order, published) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute($data);
                flash('success', 'Record added.');
            }
            redirect('roadmap.php?tab=' . urlencode($tab) . '&unit_id=' . $unitId);
        }

        if ($action === 'delete_item') {
            $unitId = (int)($_POST['unit_id'] ?? 0);
            $stmt = db()->prepare("UPDATE roadmap_items SET status_deleted=1 WHERE id=?");
            $stmt->execute([(int)($_POST['id'] ?? 0)]);
            flash('success', 'Record deleted.');
            redirect('roadmap.php?tab=' . urlencode($tab) . '&unit_id=' . $unitId);
        }

        if ($action === 'bulk_delete_items') {
            $ids = array_map('intval', $_POST['ids'] ?? []);
            if ($ids) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                $stmt = db()->prepare("UPDATE roadmap_items SET status_deleted=1 WHERE id IN ($in)");
                $stmt->execute($ids);
                flash('success', count($ids) . ' records deleted.');
            }
            redirect('roadmap.php?tab=' . urlencode($tab) . '&unit_id=' . (int)($_POST['unit_id'] ?? 0));
        }

        if ($action === 'import_items') {
            $unitId = (int)($_POST['unit_id'] ?? 0);
            if ($unitId <= 0) throw new Exception('Please select step.');
            if (empty($_FILES['csv_file']['tmp_name'])) throw new Exception('Upload CSV/TXT file.');
            $fh = fopen($_FILES['csv_file']['tmp_name'], 'r');
            if (!$fh) throw new Exception('Could not read file.');
            $stmt = db()->prepare("INSERT INTO roadmap_items (unit_id, item_key, col_1, col_2, col_3, col_4, col_5, col_6, example_text, sort_order, published) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $rowNo=0; $added=0;
            while (($row=fgetcsv($fh)) !== false) {
                $rowNo++;
                if (isset($row[0])) { $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$row[0]); }
                if ($rowNo === 1 && preg_match('/key|word|title|question|answer|correct/i', implode(' ', $row))) continue;
                $row = array_pad($row, 9, '');
                $allEmpty = true;
                for ($c=0; $c<8; $c++) { if (trim((string)$row[$c]) !== '') { $allEmpty = false; break; } }
                if ($allEmpty) continue;
                $stmt->execute([$unitId, trim($row[0]), trim($row[1]), trim($row[2]), trim($row[3]), trim($row[4]), trim($row[5]), trim($row[6]), trim($row[7]), (int)($row[8] ?: $rowNo), 'Yes']);
                $added++;
            }
            fclose($fh);
            flash('success', $added . ' rows imported.');
            redirect('roadmap.php?tab=' . urlencode($tab) . '&unit_id=' . $unitId);
        }
    } catch (Throwable $e) {
        error_log('[admin-roadmap] ' . $e->__toString());
        flash('error', 'Roadmap change could not be saved. Check Admin > System Check.');
        redirect('roadmap.php?tab=' . urlencode($tab));
    }
}

$allUnits = roadmap_admin_units();
$units = array_values(array_filter($allUnits, function($u) use ($tab) {
    if ($tab === 'words') return ($u['unit_type'] ?? '') === 'meaning';
    if ($tab === 'uses') return ($u['unit_type'] ?? '') === 'use';
    return ($u['unit_type'] ?? '') === 'tense';
}));

$unitIds = array_map(fn($u) => (int)$u['id'], $units);
$selectedUnitId = (int)($_GET['unit_id'] ?? ($units[0]['id'] ?? 0));
if ($selectedUnitId <= 0 || !in_array($selectedUnitId, $unitIds, true)) {
    $selectedUnitId = (int)($units[0]['id'] ?? 0);
}

$search = trim((string)($_GET['q'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$unitRecordCounts = [];
try {
    if ($unitIds) {
        $in = implode(',', array_fill(0, count($unitIds), '?'));
        $stmtCnt = db()->prepare("SELECT unit_id, COUNT(*) AS total FROM roadmap_items WHERE status_deleted=0 AND unit_id IN ($in) GROUP BY unit_id");
        $stmtCnt->execute($unitIds);
        foreach ($stmtCnt->fetchAll() as $r) {
            $unitRecordCounts[(int)$r['unit_id']] = (int)$r['total'];
        }
    }
} catch (Throwable $e) {}

$where = ["unit_id=?", "status_deleted=0"];
$params = [$selectedUnitId];
if ($search !== '') {
    $where[] = "(item_key LIKE ? OR col_1 LIKE ? OR col_2 LIKE ? OR col_3 LIKE ? OR col_4 LIKE ? OR col_5 LIKE ? OR col_6 LIKE ? OR example_text LIKE ?)";
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like, $like, $like, $like, $like);
}
$totalItems = 0;
$items = [];
try {
    $countStmt = db()->prepare("SELECT COUNT(*) FROM roadmap_items WHERE " . implode(' AND ', $where));
    $countStmt->execute($params);
    $totalItems = (int)$countStmt->fetchColumn();

    $sql = "SELECT * FROM roadmap_items WHERE " . implode(' AND ', $where) . " ORDER BY sort_order ASC, id DESC LIMIT {$perPage} OFFSET {$offset}";
    $listStmt = db()->prepare($sql);
    $listStmt->execute($params);
    $items = $listStmt->fetchAll();
} catch (Throwable $e) {
    $items = [];
    $totalItems = 0;
}
$totalPages = max(1, (int)ceil($totalItems / $perPage));
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
}

$editItem = null;
if (isset($_GET['edit_item'])) {
    try {
        $stmtEdit = db()->prepare("SELECT * FROM roadmap_items WHERE id=? AND status_deleted=0 LIMIT 1");
        $stmtEdit->execute([(int)$_GET['edit_item']]);
        $editItem = $stmtEdit->fetch() ?: null;
    } catch (Throwable $e) {}
}
$editUnit = null;
if (isset($_GET['edit_unit'])) {
    foreach ($units as $u) {
        if ((int)$u['id'] === (int)$_GET['edit_unit']) { $editUnit = $u; break; }
    }
}
?>
<div class="admin-top compact-admin-top roadmap-clean-top">
    <div>
        <span class="admin-kicker">Learning CMS</span>
        <h1>Roadmap Manager</h1>
        <p>Only 3 sections: Word Meaning, Uses Pattern, and Tense. Select one section and manage all related records.</p>
    </div>
    <div class="admin-actions">
        <a class="btn btn-soft" href="../learning-roadmap.php" target="_blank">View Roadmap</a>
    </div>
</div>

<div class="clean-roadmap-tabs">
    <a class="<?= $tab==='words'?'active':'' ?>" href="roadmap.php?tab=words">1. Word Meaning</a>
    <a class="<?= $tab==='uses'?'active':'' ?>" href="roadmap.php?tab=uses">2. Uses / Modal Pattern</a>
    <a class="<?= $tab==='tense'?'active':'' ?>" href="roadmap.php?tab=tense">3. Tense</a>
</div>

<section class="card clean-manager-head">
    <div>
        <h2><?= e($typeMap[$tab]['title']) ?></h2>
        <p><?= e($typeMap[$tab]['help']) ?></p>
    </div>
    <div class="manager-quick-guide">
        <?php if ($tab === 'words'): ?>
            <span>Excel: key, word, Hindi, example, Hindi example, tag</span>
        <?php elseif ($tab === 'uses'): ?>
            <span>Excel: use name, rule, Hindi question, English answer, sentence type</span>
        <?php else: ?>
            <span>Excel: tense, structure, Hindi question, English answer, type</span>
        <?php endif; ?>
    </div>
</section>

<div class="roadmap-create-launchers">
    <details class="roadmap-create-toggle" <?= $editUnit ? 'open' : '' ?>>
        <summary>
            <span class="roadmap-launch-no">1</span>
            <span class="roadmap-launch-copy"><b><?= $editUnit ? 'Edit Step / Topic' : 'Create Step / Topic' ?></b><small>Click to <?= $editUnit ? 'edit this topic' : 'add a new topic' ?></small></span>
            <i class="fa-solid fa-chevron-down"></i>
        </summary>
        <section class="card roadmap-create-body">
        <div class="roadmap-card-head-inline">
            <h2><?= $editUnit ? 'Edit Step' : 'Create Step / Topic' ?></h2>
            <?php if ($editUnit): ?><a class="btn btn-sm btn-soft" href="roadmap.php?tab=<?= e($tab) ?>&unit_id=<?= e((string)$selectedUnitId) ?>">Cancel Edit</a><?php endif; ?>
        </div>
        <form method="post" class="admin-form-grid">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="save_unit">
            <input type="hidden" name="id" value="<?= e((string)($editUnit['id'] ?? 0)) ?>">
            <label>Step / Topic Name<input name="title" value="<?= e($editUnit['title'] ?? '') ?>" placeholder="<?= $tab==='words'?'Basic Words / Pronouns':($tab==='uses'?'Use of Has / Have':'Present Simple') ?>"></label>
            <label>Small Subtitle<input name="subtitle" value="<?= e($editUnit['subtitle'] ?? '') ?>" placeholder="Short topic description"></label>
            <label>Level<input name="level" value="<?= e($editUnit['level'] ?? 'Beginner') ?>"></label>
            <label>Icon<input name="icon" value="<?= e($editUnit['icon'] ?? ($tab==='words'?'📘':($tab==='uses'?'🧩':'⏱'))) ?>"></label>
            <label>Points<input type="number" name="reward_points" value="<?= e((string)($editUnit['reward_points'] ?? 10)) ?>"></label>
            <label>Sort Order<input type="number" name="sort_order" value="<?= e((string)($editUnit['sort_order'] ?? 0)) ?>"></label>
            <label>Status<select name="published"><option value="Yes" <?= (($editUnit['published'] ?? 'Yes')==='Yes')?'selected':'' ?>>Published</option><option value="No" <?= (($editUnit['published'] ?? '')==='No')?'selected':'' ?>>Draft</option></select></label>
            <label class="wide">Description<textarea name="description" rows="3"><?= e($editUnit['description'] ?? '') ?></textarea></label>
            <label class="wide">Target URL optional<input name="target_url" value="<?= e($editUnit['target_url'] ?? '') ?>" placeholder="Optional only"></label>
            <button class="btn btn-primary" type="submit"><?= $editUnit ? 'Update Topic' : 'Create Topic' ?></button>
        </form>

        </section>
    </details>

    <details class="roadmap-create-toggle" <?= $editItem ? 'open' : '' ?>>
        <summary>
            <span class="roadmap-launch-no">2</span>
            <span class="roadmap-launch-copy"><b><?= $editItem ? 'Edit Record' : 'Add Record' ?></b><small>Click to <?= $editItem ? 'edit this record' : 'add one record manually' ?></small></span>
            <i class="fa-solid fa-chevron-down"></i>
        </summary>
        <section class="card roadmap-create-body">
        <h2><?= $editItem ? 'Edit Record' : 'Add Record' ?></h2>
        <form method="post" class="admin-form-grid">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="save_item">
            <input type="hidden" name="id" value="<?= e((string)($editItem['id'] ?? 0)) ?>">
            <label class="wide">Select Topic<select name="unit_id"><?php foreach($units as $u): ?><option value="<?= e((string)$u['id']) ?>" <?= ((int)($editItem['unit_id'] ?? $selectedUnitId)===(int)$u['id'])?'selected':'' ?>><?= e($u['title']) ?></option><?php endforeach; ?></select></label>
            <label>Key / Name<input name="item_key" value="<?= e($editItem['item_key'] ?? '') ?>" placeholder="I / Has Have / Present Simple"></label>
            <label><?= $tab==='words'?'English Word / Subject':'Rule / Structure' ?><input name="col_1" value="<?= e($editItem['col_1'] ?? '') ?>"></label>
            <label><?= $tab==='words'?'Hindi Meaning':'Hindi Question / Meaning' ?><input name="col_2" value="<?= e($editItem['col_2'] ?? '') ?>"></label>
            <label><?= $tab==='words'?'Example':'Correct English Answer / Example' ?><input name="col_3" value="<?= e($editItem['col_3'] ?? '') ?>"></label>
            <label>Hindi Example<input name="col_4" value="<?= e($editItem['col_4'] ?? '') ?>"></label>
            <label>Accepted Variation<input name="col_5" value="<?= e($editItem['col_5'] ?? '') ?>"></label>
            <label>Type Tag<input name="col_6" value="<?= e($editItem['col_6'] ?? ($tab==='uses'?'simple':($tab==='tense'?'simple':'word'))) ?>"></label>
            <label>Sort<input type="number" name="sort_order" value="<?= e((string)($editItem['sort_order'] ?? 0)) ?>"></label>
            <label>Status<select name="published"><option value="Yes" <?= (($editItem['published'] ?? 'Yes')==='Yes')?'selected':'' ?>>Published</option><option value="No" <?= (($editItem['published'] ?? '')==='No')?'selected':'' ?>>Draft</option></select></label>
            <label class="wide">Notes<textarea name="example_text" rows="3"><?= e($editItem['example_text'] ?? '') ?></textarea></label>
            <button class="btn btn-primary" type="submit"><?= $editItem ? 'Update Record' : 'Add Record' ?></button>
        </form>

        </section>
    </details>
</div>

<section class="card roadmap-topic-manager-card">
    <div class="section-title-row roadmap-topic-manager-head">
        <div>
            <h2>Manage Steps / Topics</h2>
            <p>Edit, publish/draft or safely delete created topics. Record counts help you know where Excel data is saved.</p>
        </div>
        <a class="btn btn-soft" href="roadmap.php?tab=<?= e($tab) ?>">+ New Topic</a>
    </div>
    <div class="roadmap-topic-grid">
        <?php foreach($units as $u): $cnt = (int)($unitRecordCounts[(int)$u['id']] ?? 0); $isSelected = ((int)$u['id'] === $selectedUnitId); ?>
            <article class="roadmap-topic-card <?= $isSelected ? 'selected' : '' ?> <?= (($u['published'] ?? 'Yes') === 'Yes') ? 'published' : 'draft' ?>">
                <a class="roadmap-topic-main" href="roadmap.php?tab=<?= e($tab) ?>&unit_id=<?= e((string)$u['id']) ?>">
                    <span class="roadmap-topic-icon"><?= e($u['icon'] ?: ($tab==='words'?'📘':($tab==='uses'?'🧩':'⏱'))) ?></span>
                    <div>
                        <h3><?= e($u['title']) ?></h3>
                        <p><?= e($u['subtitle'] ?: ($u['description'] ?: 'No subtitle added')) ?></p>
                        <div class="roadmap-topic-meta">
                            <span><?= e($u['level'] ?: 'Beginner') ?></span>
                            <span><?= e((string)$cnt) ?> record(s)</span>
                            <span>Sort <?= e((string)$u['sort_order']) ?></span>
                        </div>
                    </div>
                </a>
                <div class="roadmap-topic-actions">
                    <a class="btn btn-sm btn-soft" href="roadmap.php?tab=<?= e($tab) ?>&unit_id=<?= e((string)$u['id']) ?>&edit_unit=<?= e((string)$u['id']) ?>">Edit</a>
                    <form method="post" data-confirm="<?= (($u['published'] ?? 'Yes') === 'Yes') ? 'Move this topic to draft?' : 'Publish this topic?' ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="toggle_unit_status">
                        <input type="hidden" name="id" value="<?= e((string)$u['id']) ?>">
                        <input type="hidden" name="published" value="<?= (($u['published'] ?? 'Yes') === 'Yes') ? 'No' : 'Yes' ?>">
                        <button class="btn btn-sm <?= (($u['published'] ?? 'Yes') === 'Yes') ? 'btn-soft' : 'btn-green' ?>" type="submit"><?= (($u['published'] ?? 'Yes') === 'Yes') ? 'Draft' : 'Publish' ?></button>
                    </form>
                    <form method="post" data-confirm="Delete this topic safely? It will be hidden from admin/frontend, but related records remain in database.">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="delete_unit">
                        <input type="hidden" name="id" value="<?= e((string)$u['id']) ?>">
                        <button class="btn btn-sm btn-red" type="submit">Delete</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (!$units): ?>
            <div class="roadmap-topic-empty">No topic created yet. Use Create Step / Topic form above.</div>
        <?php endif; ?>
    </div>
</section>

<section class="card clean-import-card">
    <h2>Import Excel CSV</h2>
    <form method="post" enctype="multipart/form-data" class="clean-import-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="import_items">
        <label>Topic<select name="unit_id"><?php foreach($units as $u): $cnt = (int)($unitRecordCounts[(int)$u['id']] ?? 0); ?><option value="<?= e((string)$u['id']) ?>" <?= $selectedUnitId===(int)$u['id']?'selected':'' ?>><?= e($u['title']) ?> (<?= e((string)$cnt) ?>)</option><?php endforeach; ?></select></label>
        <label>CSV/TXT File<input type="file" name="csv_file" accept=".csv,.txt"></label>
        <div class="roadmap-import-actions">
            <button class="btn btn-primary" type="submit">Import</button>
            <a class="btn btn-soft" href="roadmap.php?tab=<?= e($tab) ?>&download_sample=1">Download <?= $tab==='words'?'Word Meaning':($tab==='uses'?'Uses / Modal Pattern':'Tense') ?> Sample</a>
        </div>
    </form>
    <p>CSV order: key, col1, col2, col3, col4, col5, type/tag, notes, sort_order. Download sample and keep the same columns.</p>
</section>

<section class="card clean-records-card">
    <div class="section-title-row roadmap-record-head">
        <div>
            <h2>Manage Records</h2>
            <p>Total records in this topic: <b><?= e((string)$totalItems) ?></b>. Search or change topic if Excel data is not visible.</p>
        </div>
        <form method="get" class="clean-topic-filter roadmap-topic-filter">
            <input type="hidden" name="tab" value="<?= e($tab) ?>">
            <select name="unit_id" onchange="this.form.submit()">
                <?php foreach($units as $u): $cnt = (int)($unitRecordCounts[(int)$u['id']] ?? 0); ?>
                    <option value="<?= e((string)$u['id']) ?>" <?= $selectedUnitId===(int)$u['id']?'selected':'' ?>><?= e($u['title']) ?> (<?= e((string)$cnt) ?>)</option>
                <?php endforeach; ?>
            </select>
            <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search word, Hindi, answer, tag...">
            <button class="btn btn-soft" type="submit">Search</button>
            <?php if ($search !== ''): ?><a class="btn btn-soft" href="?tab=<?= e($tab) ?>&unit_id=<?= e((string)$selectedUnitId) ?>">Clear</a><?php endif; ?>
        </form>
    </div>

    <form method="post" data-confirm="Delete selected records?">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="bulk_delete_items">
        <input type="hidden" name="unit_id" value="<?= e((string)$selectedUnitId) ?>">
        <div class="clean-record-table roadmap-record-table">
            <table>
                <thead>
                    <tr>
                        <th style="width:42px"><input type="checkbox" onclick="document.querySelectorAll('.rowCheck').forEach(c=>c.checked=this.checked)"></th>
                        <th>Key</th>
                        <th>Main / Answer</th>
                        <th>Hindi / Question</th>
                        <th>Example / Variation</th>
                        <th>Tag</th>
                        <th>Status</th>
                        <th style="width:92px">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($items as $it): ?>
                    <tr>
                        <td><input class="rowCheck" type="checkbox" name="ids[]" value="<?= e((string)$it['id']) ?>"></td>
                        <td><b><?= e($it['item_key']) ?></b><br><small>#<?= e((string)$it['id']) ?> / Sort <?= e((string)$it['sort_order']) ?></small></td>
                        <td><?= e($it['col_1']) ?></td>
                        <td><?= e($it['col_2']) ?></td>
                        <td><?= e($it['col_3']) ?><?php if(!empty($it['col_5'])): ?><br><small>Alt: <?= e($it['col_5']) ?></small><?php endif; ?></td>
                        <td><span class="admin-mini-pill"><?= e($it['col_6'] ?: '-') ?></span></td>
                        <td><?= e($it['published']) ?></td>
                        <td><a class="btn btn-sm btn-soft" href="?tab=<?= e($tab) ?>&unit_id=<?= e((string)$selectedUnitId) ?>&page=<?= e((string)$page) ?>&q=<?= e(urlencode($search)) ?>&edit_item=<?= e((string)$it['id']) ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$items): ?>
                    <tr><td colspan="8" class="roadmap-empty-records">
                        <b>No records found in selected topic.</b><br>
                        Check the topic dropdown above. Counts are shown beside every topic name, so you can see where Excel data was uploaded.
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="roadmap-record-actions">
            <button class="btn btn-danger" type="submit">Delete Selected</button>
            <span>Showing <?= e((string)count($items)) ?> of <?= e((string)$totalItems) ?> records</span>
        </div>
    </form>

    <?php if ($totalPages > 1): ?>
        <nav class="admin-pagination">
            <?php
            $baseParams = 'tab=' . urlencode($tab) . '&unit_id=' . urlencode((string)$selectedUnitId) . '&q=' . urlencode($search);
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            ?>
            <?php if ($page > 1): ?><a href="?<?= $baseParams ?>&page=<?= e((string)($page-1)) ?>">Prev</a><?php endif; ?>
            <?php for($p=$startPage; $p<=$endPage; $p++): ?>
                <a class="<?= $p===$page?'active':'' ?>" href="?<?= $baseParams ?>&page=<?= e((string)$p) ?>"><?= e((string)$p) ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?><a href="?<?= $baseParams ?>&page=<?= e((string)($page+1)) ?>">Next</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/_footer.php'; ?>
