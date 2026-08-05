<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Online Classes | ' . app_setting('site_name', APP_NAME);
$meta_description = 'Live online spoken English classes with teacher guidance, speaking practice, recordings, attendance and progress tracking.';
require_once __DIR__ . '/includes/header.php';
$siteName = app_setting('site_name', APP_NAME);
$phone = preg_replace('/[^0-9+]/', '', app_setting('phone', APP_PHONE));
$batches = fetch_batch_timings(6);
?>
<section class="online-class-hero section-soft">
    <div class="container online-class-hero-grid">
        <div class="online-class-copy">
            <span class="eyebrow">Classroom + Online Learning</span>
            <h1>Learn live from anywhere with personal teacher guidance.</h1>
            <p><?= e($siteName) ?> combines live spoken English classes, practical speaking tasks, recordings, weekly tests and progress tracking in one smooth learning journey.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="admission.php">Join an Online Batch</a>
                <a class="btn btn-light" href="tel:<?= e($phone) ?>">Talk to Counsellor</a>
            </div>
            <div class="online-points-grid">
                <div><i class="fa-solid fa-video"></i><span>Live interactive classes</span></div>
                <div><i class="fa-solid fa-microphone-lines"></i><span>Speaking practice rooms</span></div>
                <div><i class="fa-solid fa-clipboard-check"></i><span>Attendance and weekly tests</span></div>
                <div><i class="fa-solid fa-circle-play"></i><span>Recordings and revision</span></div>
            </div>
        </div>
        <div class="online-class-visual card">
            <div class="virtual-class-window">
                <div class="virtual-window-top"><span></span><span></span><span></span><b>LIVE CLASS</b></div>
                <div class="virtual-main-stage">
                    <div class="virtual-main-icon"><i class="fa-solid fa-person-chalkboard"></i></div>
                    <h3>Daily Conversation Practice</h3>
                    <p>Teacher-led spoken English session</p>
                </div>
                <div class="virtual-student-strip"><span><i class="fa-solid fa-user"></i></span><span><i class="fa-solid fa-user"></i></span><span><i class="fa-solid fa-user"></i></span><span><i class="fa-solid fa-user"></i></span></div>
                <div class="virtual-controls"><span><i class="fa-solid fa-microphone"></i></span><span><i class="fa-solid fa-video"></i></span><span><i class="fa-solid fa-hand"></i></span><span class="danger"><i class="fa-solid fa-phone-slash"></i></span></div>
                <div class="virtual-badge score">Speaking score +8</div>
                <div class="virtual-badge saved">Attendance saved</div>
            </div>
        </div>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="section-title center-title"><span class="eyebrow">Online Learning Experience</span><h2>Everything students need to learn consistently</h2><p>A structured system keeps classes interactive, revision simple and progress visible.</p></div>
        <div class="online-advantage-grid">
            <article class="card feature-card"><div class="icon"><i class="fa-regular fa-calendar-check"></i></div><h3>Batch Scheduling</h3><p>Clear class days, time, teacher and joining instructions for every batch.</p></article>
            <article class="card feature-card"><div class="icon"><i class="fa-solid fa-book-open-reader"></i></div><h3>Lesson Continuity</h3><p>Connect live classes with notes, recordings, homework and revision.</p></article>
            <article class="card feature-card"><div class="icon"><i class="fa-solid fa-chart-line"></i></div><h3>Progress Tracking</h3><p>Track attendance, practice completion, weekly tests and improvement.</p></article>
            <article class="card feature-card"><div class="icon"><i class="fa-regular fa-comments"></i></div><h3>Teacher Feedback</h3><p>Get correction, speaking tips and personal recommendations after class.</p></article>
        </div>
    </div>
</section>
<?php if ($batches): ?>
<section class="section section-soft">
    <div class="container">
        <div class="section-head"><div class="section-title"><span class="eyebrow">Available Batches</span><h2>Choose a comfortable learning schedule</h2><p>Batch timings are managed by the institute and may include classroom, online or hybrid learning.</p></div><a class="btn btn-primary" href="admission.php">Check Admission</a></div>
        <div class="batch-grid">
            <?php foreach (array_slice($batches,0,4) as $batch): ?>
                <article class="batch-card"><span><?= e($batch['course_name'] ?: 'Spoken English') ?></span><h3><?= e($batch['batch_name']) ?></h3><p><?= e($batch['timing'] ?: 'Timing available on call') ?></p><small><?= e($batch['days'] ?: 'Flexible days') ?></small><?php if (!empty($batch['seats_note'])): ?><em><?= e($batch['seats_note']) ?></em><?php endif; ?></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<section class="section">
    <div class="container">
        <div class="section-title"><span class="eyebrow">How It Works</span><h2>Your online class journey</h2><p>From admission to feedback, every step is simple and student-friendly.</p></div>
        <div class="online-flow-grid">
            <article class="card step-card"><b>1</b><h3>Register & Select Batch</h3><p>Choose your level, preferred time and online or hybrid learning mode.</p></article>
            <article class="card step-card"><b>2</b><h3>Join Live Session</h3><p>Attend teacher-led classes with speaking activities and doubt solving.</p></article>
            <article class="card step-card"><b>3</b><h3>Practice & Revise</h3><p>Use recordings, notes, assignments and speaking tasks after class.</p></article>
            <article class="card step-card"><b>4</b><h3>Test & Improve</h3><p>Take weekly tests and receive clear feedback for your next goal.</p></article>
        </div>
    </div>
</section>
<section class="section section-soft"><div class="container"><div class="dark-cta"><div><h2>Ready to join your first online spoken English batch?</h2><p>Submit your admission enquiry and our team will guide you with level, timing and class details.</p></div><a class="btn btn-primary" href="admission.php">Start Admission</a></div></div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
