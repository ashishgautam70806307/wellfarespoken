<?php
require_once __DIR__ . '/includes/functions.php';
private_no_store();
ensure_schema_updates();
if (is_student()) {
    redirect('student-dashboard.php');
}
$page_title = 'Student Login | ' . app_setting('site_name', APP_NAME);
$meta_description = 'Student login and registration for spoken English practice, Hindi to English learning and progress tracking.';
$errors = [];
$mode = (string)($_POST['mode'] ?? ($_GET['mode'] ?? 'login'));
$mode = $mode === 'register' ? 'register' : 'login';
$returnTo = safe_local_redirect((string)($_POST['redirect'] ?? ($_GET['redirect'] ?? 'student-dashboard.php')));
if (isset($_GET['expired'])) $errors[] = 'Session expired. Please login again.';
if (isset($_GET['inactive'])) $errors[] = 'Your account is inactive. Please contact the institute.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security check failed. Refresh the page and try again.';
    } else {
        $phone = clean_phone_digits((string)($_POST['phone'] ?? ''));
        if (strlen($phone) > 10 && str_starts_with($phone, '91')) $phone = substr($phone, -10);
        $password = (string)($_POST['password'] ?? '');
        $rateKey = 'student-auth:' . $mode . ':' . $phone;
        if (!security_rate_limit($rateKey, $mode === 'register' ? 5 : 10, $mode === 'register' ? 3600 : 900)) {
            $errors[] = 'Too many requests. Please wait before trying again.';
        }
        if (strlen($phone) !== 10) $errors[] = 'Please enter a valid 10 digit phone number.';
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';

        if (!$errors && $mode === 'register') {
            $name = trim((string)($_POST['full_name'] ?? ''));
            $email = strtolower(trim((string)($_POST['email'] ?? '')));
            $confirmPassword = (string)($_POST['confirm_password'] ?? '');
            $level = trim((string)($_POST['current_level'] ?? 'Zero Level')) ?: 'Zero Level';
            $goal = trim((string)($_POST['target_goal'] ?? ''));
            if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $errors[] = 'Please enter a valid student name.';
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
            if (!hash_equals($password, $confirmPassword)) $errors[] = 'Password and confirm password do not match.';
            if (!in_array($level, ['Zero Level','Basic','Intermediate','Advanced'], true)) $level = 'Zero Level';

            if (!$errors) {
                try {
                    $check = db()->prepare('SELECT id FROM students WHERE phone=? AND status_deleted=0 LIMIT 1');
                    $check->execute([$phone]);
                    if ($check->fetchColumn()) {
                        $errors[] = 'This phone number is already registered. Please login.';
                        $mode = 'login';
                    } else {
                        $stmt = db()->prepare('INSERT INTO students (full_name,phone,email,password_hash,current_level,target_goal,preferred_language,daily_goal_minutes,published) VALUES (?,?,?,?,?,?,?,?,?)');
                        $stmt->execute([$name,$phone,$email ?: null,password_hash($password,PASSWORD_DEFAULT),$level,$goal ?: null,'Hindi',20,'Yes']);
                        $student = ['id'=>(int)db()->lastInsertId(), 'full_name'=>$name];
                        security_rate_limit_clear($rateKey);
                        student_session_login($student);
                        redirect($returnTo === 'student-dashboard.php' ? 'student-dashboard.php?welcome=1' : $returnTo);
                    }
                } catch (Throwable $e) {
                    $errors[] = 'Could not create account. Please try again.';
                }
            }
        } elseif (!$errors) {
            try {
                $stmt = db()->prepare('SELECT * FROM students WHERE phone=? AND status_deleted=0 LIMIT 1');
                $stmt->execute([$phone]);
                $student = $stmt->fetch();
                if (!$student || !password_verify($password, (string)$student['password_hash'])) {
                    $errors[] = 'Invalid phone number or password.';
                } elseif (($student['published'] ?? 'No') !== 'Yes') {
                    $errors[] = 'Your account is inactive. Please contact the institute.';
                } else {
                    if (password_needs_rehash((string)$student['password_hash'], PASSWORD_DEFAULT)) {
                        db()->prepare('UPDATE students SET password_hash=? WHERE id=?')->execute([password_hash($password, PASSWORD_DEFAULT), (int)$student['id']]);
                    }
                    security_rate_limit_clear($rateKey);
                    student_session_login($student);
                    db()->prepare('UPDATE students SET last_login_at=NOW() WHERE id=?')->execute([(int)$student['id']]);
                    redirect($returnTo);
                }
            } catch (Throwable $e) {
                $errors[] = 'Login failed. Please try again.';
            }
        }
    }
}
$lightweight_layout = true;
require_once __DIR__ . '/includes/header.php';
?>
<section class="section student-auth-section mode-<?= e($mode) ?>">
    <div class="container auth-grid">
        <aside class="auth-copy" data-reveal>
            <span class="eyebrow"><i class="fa-solid fa-user-graduate"></i> Student Learning Account</span>
            <h1><?= $mode === 'register' ? 'Create your account and begin at the right level.' : 'Continue your learning from where you stopped.' ?></h1>
            <p><?= $mode === 'register' ? 'Register once to save lessons, practice, tests and progress.' : 'Login to open your roadmap, practice history and test results.' ?></p>
            <div class="auth-journey" aria-label="Student account benefits">
                <article><span><i class="fa-solid fa-route"></i></span><div><b>Clear Roadmap</b><small>See the next lesson.</small></div></article>
                <article><span><i class="fa-solid fa-microphone-lines"></i></span><div><b>Daily Practice</b><small>Learn and speak regularly.</small></div></article>
                <article><span><i class="fa-solid fa-clipboard-check"></i></span><div><b>Saved Results</b><small>Track tests and revision.</small></div></article>
            </div>
            <?php if ($mode === 'register'): ?>
                <div class="auth-level-strip">
                    <?php foreach (student_level_steps() as $index => $step): ?>
                        <span><b><?= e((string)($index + 1)) ?></b><?= e($step[0]) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </aside>

        <div class="auth-card card" data-reveal>
            <div class="auth-tabs" role="tablist" aria-label="Student account action">
                <a class="<?= $mode !== 'register' ? 'active' : '' ?>" href="student-auth.php?mode=login<?= $returnTo !== 'student-dashboard.php' ? '&redirect=' . rawurlencode($returnTo) : '' ?>"><i class="fa-solid fa-right-to-bracket"></i>Login</a>
                <a class="<?= $mode === 'register' ? 'active' : '' ?>" href="student-auth.php?mode=register<?= $returnTo !== 'student-dashboard.php' ? '&redirect=' . rawurlencode($returnTo) : '' ?>"><i class="fa-solid fa-user-plus"></i>Register</a>
            </div>
            <header class="auth-form-head">
                <span><?= $mode === 'register' ? 'New Student' : 'Welcome Back' ?></span>
                <h2><?= $mode === 'register' ? 'Create Student Account' : 'Student Login' ?></h2>
                <p><?= $mode === 'register' ? 'Required fields are marked with *.' : 'Use your registered mobile number and password.' ?></p>
            </header>
            <?php if ($errors): ?><div class="alert alert-error" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
            <form method="post" class="form-stack auth-form-grid">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="mode" value="<?= e($mode === 'register' ? 'register' : 'login') ?>">
                <input type="hidden" name="redirect" value="<?= e($returnTo) ?>">
                <?php if ($mode === 'register'): ?>
                    <div class="field full"><label for="studentFullName">Full Name *</label><div class="wf129-input-icon"><i class="fa-solid fa-user"></i><input id="studentFullName" type="text" name="full_name" required maxlength="100" value="<?= e($_POST['full_name'] ?? '') ?>" placeholder="Enter student name" autocomplete="name"></div></div>
                    <div class="field"><label for="studentEmail">Email <small>Optional</small></label><div class="wf129-input-icon"><i class="fa-solid fa-envelope"></i><input id="studentEmail" type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="student@example.com" autocomplete="email"></div></div>
                    <div class="field"><label for="studentLevel">Current English Level</label><select id="studentLevel" name="current_level"><?php $postedLevel = (string)($_POST['current_level'] ?? 'Zero Level'); foreach (['Zero Level','Basic','Intermediate','Advanced'] as $levelOption): ?><option value="<?= e($levelOption) ?>" <?= $postedLevel === $levelOption ? 'selected' : '' ?>><?= e($levelOption) ?></option><?php endforeach; ?></select></div>
                    <div class="field full"><label for="studentGoal">Learning Goal</label><div class="wf129-input-icon"><i class="fa-solid fa-bullseye"></i><input id="studentGoal" type="text" name="target_goal" value="<?= e($_POST['target_goal'] ?? '') ?>" placeholder="Interview, daily conversation or school English"></div></div>
                <?php endif; ?>
                <div class="field <?= $mode === 'register' ? '' : 'full' ?>"><label for="studentPhone">Phone Number *</label><div class="wf129-input-icon"><i class="fa-solid fa-mobile-screen-button"></i><input id="studentPhone" type="tel" name="phone" required maxlength="10" inputmode="numeric" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="10 digit mobile" autocomplete="tel"></div></div>
                <div class="field <?= $mode === 'register' ? '' : 'full' ?>"><label for="studentPassword">Password *</label><div class="wf129-input-icon password-wrap"><i class="fa-solid fa-lock"></i><input id="studentPassword" type="password" name="password" required minlength="8" autocomplete="<?= $mode === 'register' ? 'new-password' : 'current-password' ?>" placeholder="Minimum 8 characters"></div></div>
                <?php if ($mode === 'register'): ?><div class="field full"><label for="studentConfirmPassword">Confirm Password *</label><div class="wf129-input-icon password-wrap"><i class="fa-solid fa-shield-halved"></i><input id="studentConfirmPassword" type="password" name="confirm_password" required minlength="8" autocomplete="new-password" placeholder="Enter password again"></div></div><?php endif; ?>
                <div class="auth-submit full"><button class="wf-btn wf-btn-primary" type="submit"><span class="wf-btn-label"><i class="fa-solid <?= $mode === 'register' ? 'fa-user-plus' : 'fa-right-to-bracket' ?>"></i><?= $mode === 'register' ? 'Create Student Account' : 'Login to Dashboard' ?></span></button><small><i class="fa-solid fa-shield-halved"></i> Your account information is protected.</small></div>
            </form>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
