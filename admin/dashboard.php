<?php
$admin_page_final_styles = ['assets/css/phase147-student-accounts.css', 'assets/css/phase159-admin-weekly-papers.css', 'assets/css/phase161-upcoming-performance.css'];
require_once __DIR__ . '/_header.php';
student_account_ensure_schema();

// Phase 46: dashboard uses prepared statements only and never crashes when optional module tables are missing.
function dashboard_table_exists(string $table): bool
{
    $safe = preg_replace('/[^A-Za-z0-9_]/', '', $table);
    if ($safe === '') return false;
    try {
        $stmt = db()->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
        $stmt->execute([$safe]);
        return (int)$stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        try {
            $stmt = db()->query('SHOW TABLES LIKE ' . db()->quote($safe));
            return (bool)$stmt->fetchColumn();
        } catch (Throwable $e2) {
            return false;
        }
    }
}

function dashboard_count(string $table, string $where = '', array $params = []): int
{
    $safe = preg_replace('/[^A-Za-z0-9_]/', '', $table);
    if ($safe === '' || !dashboard_table_exists($safe)) return 0;
    try {
        $sql = 'SELECT COUNT(*) AS c FROM `' . $safe . '`' . ($where !== '' ? ' WHERE ' . $where : '');
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (int)($stmt->fetchColumn() ?: 0);
    } catch (Throwable $e) {
        return 0;
    }
}

function dashboard_count_available(string $table, array $statusColumns = ['status_deleted'], array $publishedColumns = []): int
{
    $safe = preg_replace('/[^A-Za-z0-9_]/', '', $table);
    if ($safe === '' || !dashboard_table_exists($safe)) return 0;

    $columns = [];
    try {
        $stmtCols = db()->prepare('SHOW COLUMNS FROM `' . $safe . '`');
        $stmtCols->execute();
        foreach ($stmtCols->fetchAll() as $c) {
            $columns[] = $c['Field'];
        }
    } catch (Throwable $e) {
        return dashboard_count($safe);
    }

    $where = [];
    $params = [];
    foreach ($statusColumns as $col) {
        if (in_array($col, $columns, true)) {
            $where[] = "(`$col` IS NULL OR `$col` = 0)";
        }
    }
    foreach ($publishedColumns as $col) {
        if (in_array($col, $columns, true)) {
            $where[] = "(`$col` IS NULL OR `$col` = '' OR `$col` IN ('Yes','Y','1'))";
        }
    }

    return dashboard_count($safe, implode(' AND ', $where), $params);
}

function dashboard_first_count(array $candidates): int
{
    foreach ($candidates as $item) {
        $table = $item[0] ?? '';
        if (dashboard_table_exists($table)) {
            return dashboard_count_available($table, $item[1] ?? ['status_deleted'], $item[2] ?? []);
        }
    }
    return 0;
}

function dashboard_rows(string $table, string $orderBy = 'id DESC', int $limit = 6): array
{
    $safe = preg_replace('/[^A-Za-z0-9_]/', '', $table);
    if ($safe === '' || !dashboard_table_exists($safe)) return [];
    $orderBy = preg_replace('/[^A-Za-z0-9_,.\s`-]/', '', $orderBy) ?: 'id DESC';
    $limit = max(1, min(50, $limit));
    try {
        $stmt = db()->prepare('SELECT * FROM `' . $safe . '` ORDER BY ' . $orderBy . ' LIMIT ' . $limit);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

// Make optional module tables available before counting.
if (function_exists('material_ensure_schema')) material_ensure_schema();
if (function_exists('weekly_test_ensure_schema')) weekly_test_ensure_schema();

$enquiries = dashboard_count_available('enquiries');
$today = dashboard_table_exists('enquiries') ? dashboard_count('enquiries', 'DATE(created_at)=CURDATE()') : 0;
$courses = dashboard_count_available('courses', ['status_deleted'], ['published']);
$reviews = dashboard_count_available('testimonials', ['status_deleted'], ['published']);
$gallery = dashboard_count_available('gallery_images', ['status_deleted'], ['published']);
$batches = dashboard_count_available('batch_timings', ['status_deleted'], ['published']);
$faqs = dashboard_count_available('faqs', ['status_deleted'], ['published']);
$navs = dashboard_count_available('nav_menus', ['status_deleted'], ['published']);
$students = dashboard_count_available('students');
$studentActive = dashboard_count('students', "status_deleted=0 AND published='Yes'");
$studentInactive = dashboard_count('students', "status_deleted=0 AND published='No'");
$studentNeverLogin = dashboard_count('students', 'status_deleted=0 AND last_login_at IS NULL');
$studentRecentLogin = dashboard_count('students', 'status_deleted=0 AND last_login_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
$admissions = dashboard_count_available('admissions');
$admissionDue = dashboard_count('admissions', 'status_deleted=0 AND (total_fee-discount_amount-paid_amount)>0');
$materials = dashboard_first_count([
    ['translation_pairs', ['status_deleted'], ['published']],
    ['material_sentence_pairs', ['status_deleted'], ['published']],
    ['roadmap_items', ['status_deleted'], ['published']]
]);
$weeklyTests = dashboard_count_available('weekly_tests');
$weeklyAttempts = dashboard_count('weekly_test_attempts', 'COALESCE(status_deleted,0)=0');
$weeklyPendingChecks = dashboard_count('weekly_test_attempts', "COALESCE(status_deleted,0)=0 AND status='submitted'");
$weeklyQuestions = dashboard_count('weekly_test_questions', "status_deleted=0");
$dashboardUpcomingPapers = [];
if (admin_can('tests.manage') && function_exists('weekly_test_fetch_tests')) {
    try {
        $dashboardUpcomingPapers = array_slice(weekly_test_fetch_tests('upcoming'), 0, 6);
    } catch (Throwable $e) {
        $dashboardUpcomingPapers = [];
    }
}
$dashboardUpcomingPerformanceTest = $dashboardUpcomingPapers[0] ?? null;
$dashboardUpcomingPerformance = ['attempts'=>0,'checked'=>0,'pending'=>0];
$dashboardUpcomingTopThree = [];
if (admin_can('tests.manage') && $dashboardUpcomingPerformanceTest) {
    try {
        $performanceTestId = (int)($dashboardUpcomingPerformanceTest['id'] ?? 0);
        if ($performanceTestId > 0) {
            $performanceStmt = db()->prepare("SELECT COUNT(*) attempts, SUM(status='checked') checked, SUM(status='submitted') pending FROM weekly_test_attempts WHERE COALESCE(status_deleted,0)=0 AND test_id=?");
            $performanceStmt->execute([$performanceTestId]);
            $dashboardUpcomingPerformance = array_merge($dashboardUpcomingPerformance, $performanceStmt->fetch() ?: []);

            $winnerStmt = db()->prepare("SELECT rank_no,student_name,score,total_marks FROM weekly_test_winners WHERE test_id=? ORDER BY rank_no ASC,id ASC LIMIT 3");
            $winnerStmt->execute([$performanceTestId]);
            $dashboardUpcomingTopThree = $winnerStmt->fetchAll();
            if (!$dashboardUpcomingTopThree) {
                $rankStmt = db()->prepare("SELECT COALESCE(NULLIF(s.full_name,''),NULLIF(a.guest_name,''),'Student') student_name, COALESCE(a.admin_score,a.auto_score,0) score, COALESCE(a.total_marks,0) total_marks FROM weekly_test_attempts a LEFT JOIN students s ON s.id=a.student_id WHERE COALESCE(a.status_deleted,0)=0 AND a.test_id=? AND a.status='checked' ORDER BY score DESC, COALESCE(a.submitted_at,a.started_at) ASC,a.id ASC LIMIT 3");
                $rankStmt->execute([$performanceTestId]);
                foreach ($rankStmt->fetchAll() as $index => $row) {
                    $row['rank_no'] = $index + 1;
                    $dashboardUpcomingTopThree[] = $row;
                }
            }
        }
    } catch (Throwable $e) {
        $dashboardUpcomingTopThree = [];
    }
}
$roadmapItems = dashboard_count_available('roadmap_items', ['status_deleted'], ['published']);
$faculty = dashboard_count_available('faculty_members', [], ['published']);
$recent = dashboard_rows('enquiries', 'id DESC', 6);

$dashboardRbacReady = admin_rbac_ready();
$dashboardOwnerId = $dashboardRbacReady ? admin_primary_owner_id() : 0;
$dashboardIsOwner = $dashboardRbacReady ? admin_is_primary_owner() : false;
$dashboardRoleKey = $dashboardRbacReady ? admin_role_key() : 'migration_pending';
$dashboardRoleName = 'Migration Pending';
$dashboardAdminTotal = dashboard_count('admins');
$dashboardActiveAdmins = dashboard_count('admins', "published='Yes'");
$dashboardSuperAdmins = 0;
if ($dashboardRbacReady) {
    try {
        $roleStmt = db()->prepare('SELECT COALESCE(r.role_name,\'Unassigned\') FROM admins a LEFT JOIN admin_roles r ON r.id=a.role_id WHERE a.id=? LIMIT 1');
        $roleStmt->execute([(int)($_SESSION['admin_id'] ?? 0)]);
        $dashboardRoleName = (string)($roleStmt->fetchColumn() ?: 'Unassigned');
        $dashboardSuperAdmins = (int)db()->query("SELECT COUNT(*) FROM admins a JOIN admin_roles r ON r.id=a.role_id WHERE r.role_key='super_admin' AND a.published='Yes'")->fetchColumn();
    } catch (Throwable $e) {
        $dashboardRoleName = 'Unknown';
    }
}
?>
<div class="admin-top">
    <div><h1>Dashboard</h1><p>Welcome, <?= e($_SESSION['admin_name'] ?? 'Admin') ?>. Manage enquiries, students, spoken practice content, weekly tests and website settings from one clean panel.</p></div>
    <div class="admin-actions"><?php if(admin_can('enquiries.manage')):?><a class="btn btn-primary" href="enquiries.php">View Enquiries</a><?php endif;?><?php if(admin_can('materials.manage')):?><a class="btn btn-soft" href="materials.php">Study Materials</a><?php endif;?><?php if(admin_can('tests.manage')):?><a class="btn btn-soft" href="weekly-tests.php">Weekly Tests</a><a class="btn btn-soft" href="weekly-tests.php?type=upcoming#paper-board"><i class="fa-solid fa-file-pdf"></i> Offline Papers</a><?php endif;?><a class="btn btn-soft" href="../index.php" target="_blank">Open Website</a></div>
</div>

<section class="wf150-security-card" aria-label="Administrator access security">
    <div>
        <span class="dash-mini">Security & Access Control</span>
        <h2><?= $dashboardRbacReady ? ($dashboardIsOwner ? 'Protected institute owner.' : 'Role-based staff access is active.') : 'Database security migration is pending.' ?></h2>
        <p>Signed in as <strong><?= e($dashboardRoleName) ?></strong>. <?= $dashboardIsOwner ? 'Only this protected owner can create administrator accounts or change roles. A second Super Admin cannot be assigned from the Admin panel.' : ($dashboardRbacReady ? 'Your access is limited to the permissions assigned to this role. Administrator/role management is owner-only.' : 'Until the security migration is completed, business modules remain locked to prevent legacy privilege bypass.') ?></p>
        <div class="admin-actions">
            <?php if ($dashboardIsOwner): ?>
                <a class="btn btn-primary" href="admin-users.php"><i class="fa-solid fa-user-shield"></i> Admin Accounts</a>
                <a class="btn btn-soft" href="roles.php"><i class="fa-solid fa-shield-halved"></i> Roles</a>
                <a class="btn btn-soft" href="audit-log.php"><i class="fa-solid fa-clock-rotate-left"></i> Audit</a>
            <?php elseif (!$dashboardRbacReady): ?>
                <a class="btn btn-primary" href="system-check.php"><i class="fa-solid fa-database"></i> Complete DB Upgrade</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="wf150-security-metrics">
        <div><b><?= e((string)$dashboardAdminTotal) ?></b><span>Admin records</span></div>
        <div><b><?= e((string)$dashboardActiveAdmins) ?></b><span>Active staff</span></div>
        <div><b><?= $dashboardRbacReady ? e((string)$dashboardSuperAdmins) : '—' ?></b><span>Super Admin</span></div>
        <div><b><?= $dashboardRbacReady ? ($dashboardIsOwner ? 'Owner' : 'Staff') : 'Pending' ?></b><span>Your access</span></div>
        <?php if ($dashboardRbacReady && $dashboardSuperAdmins !== 1): ?><div class="wf150-security-warning">Security warning: expected exactly 1 active Super Admin. Run the Phase 150 owner-lock migration.</div><?php endif; ?>
    </div>
</section>
<?php
$dashDirectorName = trim(app_setting('director_name','')) ?: 'Institute Director';
$dashDirectorRole = trim(app_setting('director_designation','')) ?: 'Director & Spoken English Mentor';
$dashDirectorPhoto = site_asset_url(app_setting('director_photo',''));
$dashLogo = site_asset_url(app_setting('site_logo',''));
?>
<?php if(admin_can('students.manage')): ?>
<section class="panel-card wf147-dashboard-account-control">
    <div class="wf147-dashboard-account-copy">
        <span class="dash-mini">Student Account Control</span>
        <h2>Manage every student login from one secure place.</h2>
        <p>Activate or suspend accounts, reset forgotten passwords, force sign-out, update learning settings and review test/practice history.</p>
        <div class="admin-actions"><a class="btn btn-primary" href="students.php"><i class="fa-solid fa-users-gear"></i> Open Student Accounts</a><a class="btn btn-soft" href="students.php?login=never"><i class="fa-solid fa-user-clock"></i> Never Logged In</a></div>
    </div>
    <div class="wf147-dashboard-account-metrics">
        <a href="students.php"><b><?= e((string)$students) ?></b><span>Total</span></a>
        <a href="students.php?status=Yes"><b><?= e((string)$studentActive) ?></b><span>Active</span></a>
        <a href="students.php?status=No"><b><?= e((string)$studentInactive) ?></b><span>Inactive</span></a>
        <a href="students.php?login=recent"><b><?= e((string)$studentRecentLogin) ?></b><span>7-Day Login</span></a>
        <a href="students.php?login=never"><b><?= e((string)$studentNeverLogin) ?></b><span>Never Login</span></a>
    </div>
</section>
<?php endif; ?>

<?php if(admin_can('settings.manage')): ?>
<div class="panel-card director-dashboard-preview">
    <div class="director-dashboard-media">
        <?php if($dashDirectorPhoto !== ''): ?><img src="../<?= e($dashDirectorPhoto) ?>" alt="<?= e($dashDirectorName) ?>">
        <?php elseif($dashLogo !== ''): ?><img src="../<?= e($dashLogo) ?>" alt="<?= e(app_setting('site_name', APP_NAME)) ?>">
        <?php else: ?><span><?= e(mb_substr($dashDirectorName,0,1)) ?></span><?php endif; ?>
    </div>
    <div>
        <span class="dash-mini">About Page Director</span>
        <h2><?= e($dashDirectorName) ?></h2>
        <p><?= e($dashDirectorRole) ?></p>
    </div>
    <div class="admin-actions"><a class="btn btn-primary" href="settings.php#director-settings">Manage Director Profile</a><a class="btn btn-soft" href="../about.php" target="_blank">View About Page</a></div>
</div>
<?php endif; ?>
<?php if(admin_can('tests.manage')): ?>
<section class="panel-card wf161-dashboard-performance" id="upcoming-test-performance">
    <div class="wf161-dashboard-performance-inner">
        <div>
            <span class="dash-mini">Upcoming Test Performance</span>
            <h2><?= $dashboardUpcomingPerformanceTest ? e((string)($dashboardUpcomingPerformanceTest['title'] ?? 'Upcoming Test')) : 'Top 10, score bands and rank tracking' ?></h2>
            <p><?= $dashboardUpcomingPerformanceTest ? 'Open the performance board to see marks 0–10 distribution, Top 10 standings, Top 3 and rapid-repeat security.' : 'Create an Upcoming Test first. This card will automatically show participation and Top 3 standings.' ?></p>
            <div class="wf161-dashboard-metrics">
                <span><b><?= e((string)(int)($dashboardUpcomingPerformance['attempts'] ?? 0)) ?></b> students</span>
                <span><b><?= e((string)(int)($dashboardUpcomingPerformance['checked'] ?? 0)) ?></b> checked</span>
                <span><b><?= e((string)(int)($dashboardUpcomingPerformance['pending'] ?? 0)) ?></b> pending</span>
                <span><b><?= e((string)weekly_test_upcoming_gap_hours()) ?>h</b> anti-repeat gap</span>
            </div>
            <div class="admin-actions">
                <a class="btn btn-primary" href="upcoming-test-performance.php<?= $dashboardUpcomingPerformanceTest ? '?test_id='.e((string)$dashboardUpcomingPerformanceTest['id']) : '' ?>"><i class="fa-solid fa-ranking-star"></i> Open Performance Board</a>
            </div>
        </div>
        <div class="wf161-dashboard-top3" aria-label="Upcoming Test Top 3 preview">
            <?php if (!$dashboardUpcomingTopThree): ?>
                <div class="wf161-dashboard-empty-ranks">Top 3 names will appear after Admin checks student copies.</div>
            <?php else: ?>
                <?php foreach ($dashboardUpcomingTopThree as $rankRow): ?>
                <a href="upcoming-test-performance.php?test_id=<?= e((string)($dashboardUpcomingPerformanceTest['id'] ?? 0)) ?>">
                    <i>#<?= e((string)($rankRow['rank_no'] ?? '')) ?></i>
                    <b><?= e((string)($rankRow['student_name'] ?? 'Student')) ?></b>
                    <small><?= e(number_format((float)($rankRow['score'] ?? 0),1)) ?> / <?= e(number_format((float)($rankRow['total_marks'] ?? 0),1)) ?></small>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="panel-card wf159-offline-papers" id="offline-test-papers">
    <div class="wf159-paper-head">
        <div>
            <span class="dash-mini">Upcoming Test • Offline Mode</span>
            <h2>Batch-wise Question Papers & Answer Keys</h2>
            <p>Print a student paper for learners without a phone, or open the confidential Admin answer key. The paper uses the same uploaded Upcoming Test questions and marks.</p>
        </div>
        <div class="wf159-paper-head-actions">
            <a class="btn btn-primary" href="weekly-tests.php?type=upcoming#paper-board"><i class="fa-solid fa-list-check"></i> Manage Upcoming Tests</a>
            <a class="btn btn-soft" href="weekly-tests.php?type=upcoming#answer-sheet"><i class="fa-solid fa-file-arrow-up"></i> Upload Questions</a>
        </div>
    </div>
    <?php if (!$dashboardUpcomingPapers): ?>
        <div class="wf159-paper-empty">
            <i class="fa-solid fa-file-circle-plus"></i>
            <div><b>No Upcoming Test paper yet.</b><span>Create an Upcoming Test and upload its Excel/CSV question sheet. Offline paper buttons will appear here automatically.</span></div>
            <a class="btn btn-primary btn-sm" href="weekly-tests.php?type=upcoming#setup">Create Upcoming Test</a>
        </div>
    <?php else: ?>
        <div class="wf159-paper-grid">
        <?php foreach ($dashboardUpcomingPapers as $paper):
            $paperId = (int)($paper['id'] ?? 0);
            $paperReady = weekly_test_ready_reason($paper);
            $paperStatus = $paperReady === 'ready' ? 'Published' : ($paperReady === 'scheduled_later' ? 'Scheduled' : ($paperReady === 'expired' ? 'Closed' : 'Pending'));
            $paperBatch = trim((string)(($paper['batch_label'] ?? '') ?: ($paper['batch_name'] ?? '') ?: 'Common Batch'));
            $paperTiming = trim((string)($paper['batch_timing'] ?? ''));
            $paperDays = trim((string)($paper['batch_days'] ?? ''));
        ?>
            <article class="wf159-paper-card status-<?= e(strtolower($paperStatus)) ?>">
                <div class="wf159-paper-card-top">
                    <span class="wf159-paper-status"><?= e($paperStatus) ?></span>
                    <span class="wf159-paper-count"><i class="fa-solid fa-list-ol"></i> <?= e((string)($paper['question_count'] ?? 0)) ?> Q</span>
                </div>
                <h3><?= e((string)($paper['title'] ?? 'Upcoming Weekly Test')) ?></h3>
                <p><i class="fa-solid fa-users"></i> <?= e($paperBatch) ?></p>
                <div class="wf159-paper-meta">
                    <span><b><?= e((string)($paper['duration_minutes'] ?? 30)) ?></b> min</span>
                    <span><b><?= e((string)($paper['total_marks'] ?? 0)) ?></b> marks</span>
                    <span><b><?= e($paperTiming !== '' ? $paperTiming : 'Flexible') ?></b><?= $paperDays !== '' ? '<small>'.e($paperDays).'</small>' : '<small>Timing</small>' ?></span>
                </div>
                <div class="wf159-paper-actions">
                    <a class="btn btn-primary btn-sm" target="_blank" rel="noopener" href="weekly-test-offline-paper.php?id=<?= e((string)$paperId) ?>&mode=paper&autoprint=1"><i class="fa-solid fa-file-pdf"></i><span>Student Paper / PDF</span></a>
                    <a class="btn btn-soft btn-sm" target="_blank" rel="noopener" href="weekly-test-offline-paper.php?id=<?= e((string)$paperId) ?>&mode=answer-key"><i class="fa-solid fa-key"></i><span>Answer Key</span></a>
                    <a class="btn btn-soft btn-sm" href="weekly-tests.php?type=upcoming&test_id=<?= e((string)$paperId) ?>#question-bank"><i class="fa-solid fa-pen-to-square"></i><span>Manage Questions</span></a>
                </div>
            </article>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php endif; ?>
<div class="grid-4 admin-dashboard-links">
    <?php if(admin_can('enquiries.manage')): ?><a class="card dash-card dash-link" href="enquiries.php"><span class="dash-mini">Open</span><strong><?= e((string)$enquiries) ?></strong><p>Total Enquiries</p></a>
    <a class="card dash-card dash-link" href="enquiries.php?status=New"><span class="dash-mini">Today</span><strong><?= e((string)$today) ?></strong><p>Today’s Enquiries</p></a><?php endif; ?>
    <?php if(admin_can('admissions.manage')): ?><a class="card dash-card dash-link" href="admissions.php"><span class="dash-mini">CRM</span><strong><?= e((string)$admissions) ?></strong><p>Admissions</p></a>
    <a class="card dash-card dash-link" href="admissions.php"><span class="dash-mini">Fee</span><strong><?= e((string)$admissionDue) ?></strong><p>Fee Due</p></a><?php endif; ?>
    <?php if(admin_can('students.manage')): ?><a class="card dash-card dash-link" href="students.php"><span class="dash-mini">Manage</span><strong><?= e((string)$students) ?></strong><p>Student Accounts</p></a><?php endif; ?>
    <?php if(admin_can('courses.manage')): ?><a class="card dash-card dash-link" href="courses.php"><span class="dash-mini">Edit</span><strong><?= e((string)$courses) ?></strong><p>Published Courses</p></a><?php endif; ?>
    <?php if(admin_can('materials.manage')): ?><a class="card dash-card dash-link" href="materials.php"><span class="dash-mini">Practice</span><strong><?= e((string)$materials) ?></strong><p>Practice Sentences</p></a><?php endif; ?>
    <?php if(admin_can('tests.manage')): ?><a class="card dash-card dash-link" href="weekly-tests.php"><span class="dash-mini">Tests</span><strong><?= e((string)$weeklyTests) ?></strong><p>Weekly Tests</p></a>
    <a class="card dash-card dash-link" href="weekly-tests.php#question-bank"><span class="dash-mini">Questions</span><strong><?= e((string)$weeklyQuestions) ?></strong><p>Weekly Question Bank</p></a>
    <a class="card dash-card dash-link" href="weekly-tests.php#student-copies"><span class="dash-mini">Copies</span><strong><?= e((string)$weeklyAttempts) ?></strong><p>Student Test Copies</p></a>
    <a class="card dash-card dash-link" href="weekly-tests.php#student-copies"><span class="dash-mini">Check</span><strong><?= e((string)$weeklyPendingChecks) ?></strong><p>Pending Review</p></a><?php endif; ?>
    <?php if(admin_can('content.manage')): ?><a class="card dash-card dash-link" href="testimonials.php"><span class="dash-mini">Review</span><strong><?= e((string)$reviews) ?></strong><p>Published Reviews</p></a>
    <a class="card dash-card dash-link" href="gallery.php"><span class="dash-mini">Media</span><strong><?= e((string)$gallery) ?></strong><p>Gallery Photos</p></a>
    <a class="card dash-card dash-link" href="faqs.php"><span class="dash-mini">Help</span><strong><?= e((string)$faqs) ?></strong><p>Published FAQs</p></a>
    <a class="card dash-card dash-link" href="nav-menus.php"><span class="dash-mini">Menu</span><strong><?= e((string)$navs) ?></strong><p>Menu Links</p></a>
    <a class="card dash-card dash-link" href="faculty.php"><span class="dash-mini">Team</span><strong><?= e((string)$faculty) ?></strong><p>Faculty Profiles</p></a><?php endif; ?>
    <?php if(admin_can('batches.manage')): ?><a class="card dash-card dash-link" href="batches.php"><span class="dash-mini">Timing</span><strong><?= e((string)$batches) ?></strong><p>Batch Timings</p></a><?php endif; ?>
    <?php if(admin_can('roadmap.manage')): ?><a class="card dash-card dash-link" href="roadmap.php"><span class="dash-mini">Roadmap</span><strong><?= e((string)$roadmapItems) ?></strong><p>Roadmap Practice</p></a><?php endif; ?>
</div>
<?php if(admin_can('enquiries.manage')): ?>
<br>
<div class="panel-card">
    <div class="toolbar"><div><h2 style="margin:0;color:var(--navy)">Recent Enquiries</h2><p style="margin:4px 0 0;color:var(--muted)">Follow up quickly from the admin panel.</p></div><a class="btn btn-sm btn-dark" href="enquiries.php">Open All</a></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Student</th><th>Phone</th><th>Course</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($recent as $row): ?>
                <tr>
                    <td data-label="Student"><strong><?= e($row['name'] ?? '-') ?></strong></td>
                    <td data-label="Phone"><a href="tel:<?= e($row['phone'] ?? '') ?>"><?= e($row['phone'] ?? '-') ?></a></td>
                    <td data-label="Course"><?= e($row['course_interest'] ?? '-') ?></td>
                    <td data-label="Status"><span class="badge <?= e(badge_class_for_status($row['enquiry_status'] ?? 'New')) ?>"><?= e($row['enquiry_status'] ?? 'New') ?></span></td>
                    <td data-label="Date"><?= e($row['created_at'] ?? '-') ?></td>
                    <td data-label="Action"><div class="table-actions"><a class="btn btn-sm btn-soft" href="enquiry-view.php?id=<?= e((string)($row['id'] ?? 0)) ?>">View</a><?php if(!empty($row['phone'])): ?><a class="btn btn-sm btn-green" target="_blank" href="https://wa.me/<?= e(preg_replace('/\D+/', '', $row['phone'])) ?>">WhatsApp</a><?php endif; ?></div></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$recent): ?><tr><td colspan="6" class="empty-state">No enquiries yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
