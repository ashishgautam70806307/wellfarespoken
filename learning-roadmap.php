<?php
require_once __DIR__ . '/includes/functions.php';
ensure_schema_updates();
roadmap_seed_defaults();

$page_title = 'Learning Roadmap | Spoken English App';
$meta_description = 'Follow a clear spoken English learning path with unlocked levels, practice items and progress tracking.';
$page_styles = ['assets/css/phase126-roadmap.css'];
$lightweight_layout = true;
$page_final_styles = ['assets/css/phase180-old-design-mobile-fix.css'];

$groups = roadmap_fetch_groups_with_units();
$unitsFlat = [];
foreach ($groups as $roadmapGroup) {
    foreach (($roadmapGroup['units'] ?? []) as $roadmapUnit) {
        $unitsFlat[] = $roadmapUnit;
    }
}
$unitIds = array_map(static fn(array $unit): int => (int)($unit['id'] ?? 0), $unitsFlat);
$itemCounts = roadmap_fetch_item_counts($unitIds);
$totalUnits = count($unitsFlat);
$totalItems = array_sum($itemCounts);
$firstUnitId = (int)($unitsFlat[0]['id'] ?? 0);
$roadmapStudentLoggedIn = is_student();
$roadmapServerCompleted = $roadmapStudentLoggedIn ? roadmap_student_completed_ids(current_student_id()) : [];

$groupIconClasses = [
    'fa-solid fa-seedling',
    'fa-solid fa-bolt',
    'fa-solid fa-puzzle-piece',
    'fa-solid fa-clock-rotate-left',
    'fa-solid fa-comments',
];
$unitIconClasses = [
    'meaning' => 'fa-solid fa-language',
    'verb' => 'fa-solid fa-bolt',
    'use' => 'fa-solid fa-puzzle-piece',
    'tense' => 'fa-solid fa-clock-rotate-left',
    'lesson' => 'fa-solid fa-book-open-reader',
];

require_once __DIR__ . '/includes/header.php';
?>
<section class="rm126-page" id="roadmapPath">
    <div class="container rm126-shell">
        <?php if (isset($_GET['locked'])): ?><div class="alert alert-error"><i class="fa-solid fa-lock"></i> Complete the previous level before opening this lesson.</div><?php endif; ?>
        <header class="rm126-hero wf-surface-dark" data-wf-surface="dark">
            <div class="rm126-hero-copy">
                <a class="rm126-back" href="index.php"><i class="fa-solid fa-arrow-left"></i><span>Home</span></a>
                <span class="rm126-eyebrow"><i class="fa-solid fa-route"></i> Learning Roadmap</span>
                <h1><span class="wf141-desktop-copy">Follow the path. Complete one level at a time.</span><span class="wf141-mobile-copy">Learn. Practice. Unlock.</span></h1>
                <p>Current level complete hote hi next level unlock ho jayega.</p>
                <div class="rm126-how" aria-label="Roadmap process">
                    <span><i class="fa-solid fa-book-open-reader"></i><b>Learn</b></span>
                    <i class="fa-solid fa-arrow-right"></i>
                    <span><i class="fa-solid fa-microphone-lines"></i><b>Practice</b></span>
                    <i class="fa-solid fa-arrow-right"></i>
                    <span><i class="fa-solid fa-clipboard-check"></i><b>Complete</b></span>
                    <i class="fa-solid fa-arrow-right"></i>
                    <span><i class="fa-solid fa-lock-open"></i><b>Unlock</b></span>
                </div>
            </div>

            <div class="rm126-progress-card">
                <div class="rm126-progress-ring" id="roadmapProgressRing" style="--progress:0">
                    <div><strong id="roadmapPercent">0%</strong><small>Completed</small></div>
                </div>
                <div class="rm126-progress-copy">
                    <small>Your next action</small>
                    <b id="roadmapNextLabel"><?= $firstUnitId > 0 ? 'Start Level 1' : 'No level available' ?></b>
                    <?php if ($firstUnitId > 0): ?>
                        <a id="roadmapContinueBtn" href="roadmap-lesson.php?id=<?= e((string)$firstUnitId) ?>">Continue <i class="fa-solid fa-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <div class="rm126-summary" aria-label="Roadmap summary">
            <article><i class="fa-solid fa-layer-group"></i><span><b><?= e((string)$totalUnits) ?></b><small>Total levels</small></span></article>
            <article><i class="fa-solid fa-circle-check"></i><span><b id="roadmapDoneCount">0</b><small>Completed</small></span></article>
            <article><i class="fa-solid fa-star"></i><span><b id="roadmapPointCount">0</b><small>Points</small></span></article>
            <article><i class="fa-solid fa-list-check"></i><span><b><?= e((string)$totalItems) ?></b><small>Practice items</small></span></article>
        </div>

        <?php if ($groups): ?>
            <div class="rm126-stages">
                <?php $step = 0; foreach ($groups as $groupIndex => $group):
                    $groupUnits = $group['units'] ?? [];
                    $groupIcon = $groupIconClasses[$groupIndex % count($groupIconClasses)];
                ?>
                    <section class="rm126-stage">
                        <header class="rm126-stage-head">
                            <span class="rm126-stage-icon"><i class="<?= e($groupIcon) ?>"></i></span>
                            <div><small>Stage <?= e((string)($groupIndex + 1)) ?></small><h2><?= e((string)$group['title']) ?></h2></div>
                            <span class="rm126-stage-count"><?= e((string)count($groupUnits)) ?> levels</span>
                        </header>

                        <div class="rm126-path">
                            <?php foreach ($groupUnits as $unit):
                                $step++;
                                $unitId = (int)($unit['id'] ?? 0);
                                $unitType = strtolower(trim((string)($unit['unit_type'] ?? 'lesson')));
                                $unitIcon = $unitIconClasses[$unitType] ?? 'fa-solid fa-book-open-reader';
                                $itemsCount = (int)($itemCounts[$unitId] ?? 0);
                                $points = (int)($unit['reward_points'] ?? 0);
                                $description = trim((string)($unit['subtitle'] ?: $unit['description'] ?? ''));
                                $sideClass = $step % 2 === 1 ? 'is-left' : 'is-right';
                            ?>
                                <article class="rm126-step <?= e($sideClass) ?>" data-unit-id="<?= e((string)$unitId) ?>" data-step="<?= e((string)$step) ?>" data-points="<?= e((string)$points) ?>">
                                    <div class="rm126-node" aria-hidden="true">
                                        <span class="rm126-node-number"><?= e((string)$step) ?></span>
                                        <i class="<?= e($unitIcon) ?> rm126-node-main"></i>
                                        <i class="fa-solid fa-lock rm126-node-lock"></i>
                                        <i class="fa-solid fa-check rm126-node-done"></i>
                                    </div>

                                    <div class="rm126-level-card">
                                        <div class="rm126-level-top">
                                            <span>Level <?= e((string)$step) ?></span>
                                            <span class="rm126-status"><i class="fa-solid fa-lock"></i> Locked</span>
                                        </div>
                                        <h3><?= e((string)$unit['title']) ?></h3>
                                        <?php if ($description !== ''): ?><p><?= e($description) ?></p><?php endif; ?>
                                        <div class="rm126-meta">
                                            <span><i class="fa-solid fa-list-check"></i><?= e((string)$itemsCount) ?> items</span>
                                            <span><i class="fa-solid fa-star"></i><?= e((string)$points) ?> pts</span>
                                            <span><i class="fa-solid fa-signal"></i><?= e((string)($unit['level'] ?: 'Beginner')) ?></span>
                                        </div>
                                        <div class="rm126-level-action">
                                            <a class="rm126-open" href="roadmap-lesson.php?id=<?= e((string)$unitId) ?>"><span>Open Level</span><i class="fa-solid fa-arrow-right"></i></a>
                                            <button class="rm126-locked" type="button" disabled><i class="fa-solid fa-lock"></i><span>Complete previous level</span></button>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="rm126-empty"><i class="fa-solid fa-route"></i><h2>Roadmap is being prepared</h2><p>Please check again later.</p></div>
        <?php endif; ?>

        <details class="rm126-tools">
            <summary><i class="fa-solid fa-gear"></i> Progress settings</summary>
            <div><p><?= $roadmapStudentLoggedIn ? 'Reset karne par aapke student account ka saved roadmap progress clear hoga.' : 'Guest progress sirf is device me save hai; reset karne par local progress clear hoga.' ?></p><button type="button" id="roadmapResetProgress"><i class="fa-solid fa-arrow-rotate-left"></i> Reset progress</button><?php if (is_admin()): ?><a href="admin/roadmap.php"><i class="fa-solid fa-pen-to-square"></i> Manage roadmap</a><?php endif; ?></div>
        </details>
    </div>
</section>

<script>
(function(){
    const STORAGE_KEY = 'wfRoadmapCompleted';
    const studentMode = <?= $roadmapStudentLoggedIn ? 'true' : 'false' ?>;
    let serverCompleted = <?= json_encode(array_values($roadmapServerCompleted)) ?>;
    const csrfToken = <?= json_encode(csrf_token()) ?>;
    const levels = Array.from(document.querySelectorAll('.rm126-step'));
    const total = levels.length;
    const ring = document.getElementById('roadmapProgressRing');
    const percentText = document.getElementById('roadmapPercent');
    const doneCount = document.getElementById('roadmapDoneCount');
    const pointCount = document.getElementById('roadmapPointCount');
    const continueButton = document.getElementById('roadmapContinueBtn');
    const nextLabel = document.getElementById('roadmapNextLabel');

    function completedIds(){
        if (studentMode) return serverCompleted.map(Number).filter(Number.isFinite);
        try {
            const value = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
            return Array.isArray(value) ? value.map(Number).filter(Number.isFinite) : [];
        } catch (error) {
            return [];
        }
    }

    function setStatus(level, state){
        const badge = level.querySelector('.rm126-status');
        const action = level.querySelector('.rm126-open');
        if (!badge || !action) return;
        if (state === 'done') {
            badge.innerHTML = '<i class="fa-solid fa-circle-check"></i> Completed';
            action.innerHTML = '<span>Review Level</span><i class="fa-solid fa-rotate-right"></i>';
        } else if (state === 'current') {
            badge.innerHTML = '<i class="fa-solid fa-circle-play"></i> Current';
            action.innerHTML = '<span>Start Level</span><i class="fa-solid fa-arrow-right"></i>';
        } else {
            badge.innerHTML = '<i class="fa-solid fa-lock"></i> Locked';
        }
    }

    function renderRoadmap(){
        const completed = completedIds();
        let earnedPoints = 0;
        let currentLevel = null;

        levels.forEach((level, index) => {
            const id = Number(level.dataset.unitId || 0);
            const isDone = completed.includes(id);
            const previousDone = index === 0 || completed.includes(Number(levels[index - 1].dataset.unitId || 0));
            const isCurrent = !isDone && previousDone;
            const isLocked = !isDone && !previousDone;

            level.classList.toggle('is-done', isDone);
            level.classList.toggle('is-current', isCurrent);
            level.classList.toggle('is-locked', isLocked);
            setStatus(level, isDone ? 'done' : (isCurrent ? 'current' : 'locked'));

            if (isDone) earnedPoints += Number(level.dataset.points || 0);
            if (!currentLevel && isCurrent) currentLevel = level;
        });

        const validDone = levels.filter(level => completed.includes(Number(level.dataset.unitId || 0))).length;
        const percentage = total > 0 ? Math.round((validDone / total) * 100) : 0;
        if (ring) ring.style.setProperty('--progress', String(percentage));
        if (percentText) percentText.textContent = percentage + '%';
        if (doneCount) doneCount.textContent = String(validDone);
        if (pointCount) pointCount.textContent = String(earnedPoints);

        const target = currentLevel || levels[0] || null;
        if (target) {
            const targetLink = target.querySelector('.rm126-open');
            const step = target.dataset.step || '1';
            if (continueButton && targetLink) continueButton.href = targetLink.href;
            if (nextLabel) nextLabel.textContent = currentLevel ? 'Continue Level ' + step : 'Review Roadmap';
        }
    }

    document.getElementById('roadmapResetProgress')?.addEventListener('click', async function(){
        if (!window.confirm(studentMode ? 'Your roadmap progress will be reset. Continue?' : 'Is device ka roadmap progress reset karna hai?')) return;
        if (studentMode) {
            const form = new URLSearchParams({action:'reset', csrf_token:csrfToken});
            const response = await fetch('roadmap-progress-api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'}, body:form.toString(), credentials:'same-origin'});
            const data = await response.json().catch(() => ({}));
            if (!response.ok || !data.success) { window.alert(data.message || 'Could not reset progress.'); return; }
            serverCompleted = [];
        } else {
            localStorage.removeItem(STORAGE_KEY);
        }
        renderRoadmap();
        window.scrollTo({top:0, behavior:'smooth'});
    });

    renderRoadmap();
})();
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
