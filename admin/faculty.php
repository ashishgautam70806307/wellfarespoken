<?php require_once __DIR__ . '/_header.php';
ensure_schema_updates();

function faculty_upload_image(array $file): string
{
    $path = secure_image_upload($file, 'faculty', 'faculty', 2 * 1024 * 1024);
    return $path ?? '';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        flash('error', 'Security token expired. Please try again.');
        redirect('faculty.php');
    }
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $currentImage = trim($_POST['current_image_url'] ?? '');
            $uploadedImage = faculty_upload_image($_FILES['faculty_photo'] ?? []);
            $imageUrl = $uploadedImage !== '' ? $uploadedImage : $currentImage;

            $data = [
                trim($_POST['faculty_name'] ?? ''),
                trim($_POST['designation'] ?? ''),
                trim($_POST['experience'] ?? ''),
                trim($_POST['qualification'] ?? ''),
                trim($_POST['short_bio'] ?? ''),
                trim($_POST['full_bio'] ?? ''),
                trim($_POST['expertise'] ?? ''),
                $imageUrl,
                trim($_POST['phone'] ?? ''),
                trim($_POST['email'] ?? ''),
                (int)($_POST['sort_order'] ?? 0),
                ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes'
            ];
            if ($data[0] === '') {
                throw new Exception('Faculty name is required.');
            }
            if ($id > 0) {
                $stmt = db()->prepare("UPDATE faculty_members SET faculty_name=?, designation=?, experience=?, qualification=?, short_bio=?, full_bio=?, expertise=?, image_url=?, phone=?, email=?, sort_order=?, published=? WHERE id=?");
                $data[] = $id;
                $stmt->execute($data);
                flash('success', 'Faculty profile updated.');
            } else {
                $stmt = db()->prepare("INSERT INTO faculty_members (faculty_name, designation, experience, qualification, short_bio, full_bio, expertise, image_url, phone, email, sort_order, published) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute($data);
                flash('success', 'Faculty profile added.');
            }
            redirect('faculty.php');
        }

        if ($action === 'delete') {
            $stmt = db()->prepare("DELETE FROM faculty_members WHERE id=?");
            $stmt->execute([(int)($_POST['id'] ?? 0)]);
            flash('success', 'Faculty profile deleted.');
            redirect('faculty.php');
        }

        if ($action === 'bulk_delete') {
            $ids = array_values(array_filter(array_map('intval', $_POST['ids'] ?? [])));
            if (!$ids) {
                throw new Exception('Please select at least one faculty profile.');
            }
            $in = implode(',', array_fill(0, count($ids), '?'));
            $stmt = db()->prepare("DELETE FROM faculty_members WHERE id IN ($in)");
            $stmt->execute($ids);
            flash('success', count($ids) . ' faculty profiles deleted.');
            redirect('faculty.php');
        }
    } catch (Throwable $e) {
        error_log('[admin-faculty] ' . $e->__toString());
        flash('error', 'Faculty record could not be saved. Check the fields and upload permissions.');
        redirect('faculty.php');
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM faculty_members WHERE id=? LIMIT 1");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

$q = trim((string)($_GET['q'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];
if ($q !== '') {
    $where[] = "(faculty_name LIKE ? OR designation LIKE ? OR experience LIKE ? OR qualification LIKE ? OR expertise LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like, $like, $like, $like);
}
$whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

$totalRows = 0;
$rows = [];
try {
    $cnt = db()->prepare("SELECT COUNT(*) FROM faculty_members" . $whereSql);
    $cnt->execute($params);
    $totalRows = (int)$cnt->fetchColumn();

    $stmt = db()->prepare("SELECT * FROM faculty_members" . $whereSql . " ORDER BY sort_order ASC, id DESC LIMIT {$perPage} OFFSET {$offset}");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    $rows = [];
}
$totalPages = max(1, (int)ceil($totalRows / $perPage));
?>
<div class="admin-top compact-admin-top">
    <div>
        <span class="admin-kicker">Website CMS</span>
        <h1>Faculty Manager</h1>
        <p>Add teachers for homepage moving faculty cards and faculty profile pages.</p>
    </div>
    <div class="admin-actions">
        <a class="btn btn-soft" href="../index.php#faculty" target="_blank">View Faculty</a>
    </div>
</div>

<div class="faculty-admin-layout">
    <section class="card faculty-form-card">
        <h2><?= $edit ? 'Edit Faculty' : 'Add Faculty' ?></h2>
        <form method="post" enctype="multipart/form-data" class="faculty-form-grid">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? 0)) ?>">
            <input type="hidden" name="current_image_url" value="<?= e($edit['image_url'] ?? '') ?>">

            <label>Faculty Name<input name="faculty_name" value="<?= e($edit['faculty_name'] ?? '') ?>" placeholder="Teacher name"></label>
            <label>Designation<input name="designation" value="<?= e($edit['designation'] ?? '') ?>" placeholder="Spoken English Faculty"></label>
            <label>Experience<input name="experience" value="<?= e($edit['experience'] ?? '') ?>" placeholder="7+ Years"></label>
            <label>Qualification<input name="qualification" value="<?= e($edit['qualification'] ?? '') ?>" placeholder="B.Ed, MA, Diploma"></label>

            <label class="wide">Faculty Photo
                <input type="file" name="faculty_photo" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
                <?php if (!empty($edit['image_url'])): ?><small>Current: <?= e($edit['image_url']) ?></small><?php endif; ?>
            </label>

            <label>Phone<input name="phone" value="<?= e($edit['phone'] ?? '') ?>" placeholder="Optional"></label>
            <label>Email<input name="email" value="<?= e($edit['email'] ?? '') ?>" placeholder="Optional"></label>
            <label>Sort Order<input type="number" name="sort_order" value="<?= e((string)($edit['sort_order'] ?? 0)) ?>"></label>
            <label>Status<select name="published"><option value="Yes" <?= (($edit['published'] ?? 'Yes')==='Yes')?'selected':'' ?>>Published</option><option value="No" <?= (($edit['published'] ?? '')==='No')?'selected':'' ?>>Draft</option></select></label>

            <label class="wide">Short Bio<textarea name="short_bio" rows="3" placeholder="Short profile for card"><?= e($edit['short_bio'] ?? '') ?></textarea></label>
            <label class="wide">Full Bio<textarea name="full_bio" rows="4" placeholder="Full profile details page content"><?= e($edit['full_bio'] ?? '') ?></textarea></label>
            <label class="wide">Expertise<textarea name="expertise" rows="3" placeholder="Conversation, Grammar, Interview, Pronunciation"><?= e($edit['expertise'] ?? '') ?></textarea></label>

            <div class="faculty-form-actions wide">
                <button class="btn btn-primary" type="submit"><?= $edit ? 'Update Faculty' : 'Add Faculty' ?></button>
                <?php if ($edit): ?><a class="btn btn-soft" href="faculty.php">Cancel Edit</a><?php endif; ?>
            </div>
        </form>
    </section>

    <section class="card faculty-list-card">
        <div class="faculty-list-head">
            <div>
                <h2>Faculty Profiles</h2>
                <p>Total profiles: <b><?= e((string)$totalRows) ?></b></p>
            </div>
            <form method="get" class="faculty-search-form">
                <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search faculty...">
                <button class="btn btn-soft" type="submit">Search</button>
                <?php if ($q !== ''): ?><a class="btn btn-soft" href="faculty.php">Clear</a><?php endif; ?>
            </form>
        </div>

        <form method="post" onsubmit="return confirm('Delete selected faculty profiles?')">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="bulk_delete">

            <div class="clean-record-table faculty-table-wrap">
                <table class="faculty-table">
                    <thead>
                        <tr>
                            <th style="width:42px"><input type="checkbox" onclick="document.querySelectorAll('.facultyRowCheck').forEach(c=>c.checked=this.checked)"></th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Experience</th>
                            <th>Status</th>
                            <th style="width:170px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $r): $img = site_asset_url($r['image_url'] ?? ''); ?>
                        <tr>
                            <td><input class="facultyRowCheck" type="checkbox" name="ids[]" value="<?= e((string)$r['id']) ?>"></td>
                            <td><?php if ($img): ?><img class="admin-thumb" src="../<?= e($img) ?>" loading="lazy" decoding="async" alt="<?= e($r['faculty_name']) ?>"><?php else: ?><span class="admin-avatar"><?= e(strtoupper(substr($r['faculty_name'],0,1))) ?></span><?php endif; ?></td>
                            <td><b><?= e($r['faculty_name']) ?></b><br><small><?= e($r['qualification']) ?></small></td>
                            <td><?= e($r['designation']) ?></td>
                            <td><?= e($r['experience']) ?></td>
                            <td><span class="admin-mini-pill"><?= e($r['published']) ?></span></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn btn-sm btn-soft" href="faculty.php?edit=<?= e((string)$r['id']) ?>&q=<?= e(urlencode($q)) ?>&page=<?= e((string)$page) ?>">Edit</a>
                                    <form method="post" onsubmit="return confirm('Delete this faculty?')">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= e((string)$r['id']) ?>">
                                        <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$rows): ?><tr><td colspan="7" class="roadmap-empty-records"><b>No faculty profiles found.</b><br>Add a faculty profile from the left form.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="roadmap-record-actions">
                <button class="btn btn-danger" type="submit">Delete Selected</button>
                <span>Showing <?= e((string)count($rows)) ?> of <?= e((string)$totalRows) ?> profiles</span>
            </div>
        </form>

        <?php if ($totalPages > 1): ?>
        <nav class="admin-pagination">
            <?php
            $baseParams = 'q=' . urlencode($q);
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
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
