<?php
require_once __DIR__ . '/includes/functions.php';
private_no_store();
ensure_schema_updates();
student_account_ensure_schema();
$mode = (string)($_POST['mode'] ?? ($_GET['mode'] ?? 'login'));
$mode = $mode === 'register' ? 'register' : 'login';
$returnTo = safe_local_redirect((string)($_POST['redirect'] ?? ($_GET['redirect'] ?? 'student-dashboard.php')));
if (is_student()) {
    redirect($returnTo);
}
$page_title = 'Student Login | ' . app_setting('site_name', APP_NAME);
$meta_description = 'Student login and registration for spoken English practice, Hindi to English learning and progress tracking.';
$errors = [];
if (isset($_GET['expired'])) $errors[] = 'Session expired. Please login again.';
if (isset($_GET['inactive'])) $errors[] = 'Your account is inactive. Please contact the institute.';
if (isset($_GET['reset'])) $errors[] = 'Your account password or security settings were updated by the institute. Please login again.';
if (isset($_GET['pending'])) $errors[] = 'Your account is waiting for institute activation.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security check failed. Refresh the page and try again.';
    } else {
        $phone = clean_phone_digits((string)($_POST['phone'] ?? ''));
        if (strlen($phone) > 10 && str_starts_with($phone, '91')) $phone = substr($phone, -10);
        $password = (string)($_POST['password'] ?? '');
        $rateKey = 'student-auth:' . $mode . ':' . $phone;
        $ipRateKey = 'student-auth:ip:' . $mode;
        $phoneAllowed = security_rate_limit($rateKey, $mode === 'register' ? 5 : 10, $mode === 'register' ? 3600 : 900);
        $ipAllowed = security_rate_limit($ipRateKey, $mode === 'register' ? 12 : 40, $mode === 'register' ? 3600 : 900);
        if (!$phoneAllowed || !$ipAllowed) {
            $errors[] = 'Too many requests. Please wait before trying again.';
        }
        if (strlen($phone) !== 10) $errors[] = 'Please enter a valid 10 digit phone number.';
        $passwordError = student_password_error($password);
        if ($passwordError !== '') $errors[] = $passwordError;

        if (!$errors && $mode === 'register') {
            $name = trim((string)($_POST['full_name'] ?? ''));
            $email = strtolower(trim((string)($_POST['email'] ?? '')));
            $confirmPassword = (string)($_POST['confirm_password'] ?? '');
            $level = 'Zero Level'; // Official level is assessment/admin controlled.
            $goal = trim((string)($_POST['target_goal'] ?? ''));
            $trap = trim((string)($_POST['website'] ?? ''));
            $consent = !empty($_POST['account_consent']);
            if ($trap !== '') $errors[] = 'Invalid registration request.';
            if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $errors[] = 'Please enter a valid student name.';
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
            if (!hash_equals($password, $confirmPassword)) $errors[] = 'Password and confirm password do not match.';
            if (!$consent) $errors[] = 'Please confirm that this mobile number belongs to you or is being used with permission.';

            if (!$errors) {
                try {
                    $check = db()->prepare('SELECT id FROM students WHERE phone=? AND status_deleted=0 LIMIT 1');
                    $check->execute([$phone]);
                    if ($check->fetchColumn()) {
                        $errors[] = 'This phone number is already registered. Please login.';
                        $mode = 'login';
                    } else {
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        $published = student_registration_is_open() ? 'Yes' : 'No';
                        if (column_exists('students','identity_status') && column_exists('students','consent_at')) {
                            $stmt = db()->prepare('INSERT INTO students (full_name,phone,email,identity_status,registration_source,consent_at,password_hash,current_level,target_goal,preferred_language,daily_goal_minutes,published) VALUES (?,?,?,?,?,NOW(),?,?,?,?,?,?)');
                            $stmt->execute([$name,$phone,$email ?: null,'Unverified','self',$passwordHash,$level,$goal ?: null,'Hindi',20,$published]);
                        } else {
                            $stmt = db()->prepare('INSERT INTO students (full_name,phone,email,password_hash,current_level,target_goal,preferred_language,daily_goal_minutes,published) VALUES (?,?,?,?,?,?,?,?,?)');
                            $stmt->execute([$name,$phone,$email ?: null,$passwordHash,$level,$goal ?: null,'Hindi',20,$published]);
                        }
                        $studentId=(int)db()->lastInsertId();
                        lifecycle_link_student_registration($studentId,$phone);
                        security_rate_limit_clear($rateKey);
                        if ($published !== 'Yes') {
                            flash('success','Account created. The institute must activate it before you can login.');
                            redirect('student-auth.php?mode=login&pending=1');
                        }
                        $student = ['id'=>$studentId, 'full_name'=>$name, 'password_hash'=>$passwordHash, 'updated_at'=>null, 'published'=>'Yes', 'status_deleted'=>0, 'auth_version'=>1];
                        student_session_login($student);
                        redirect($returnTo === 'student-dashboard.php' ? 'student-dashboard.php?welcome=1' : $returnTo);
                    }
                } catch (Throwable $e) {
                    error_log('[student-register] ' . $e->__toString());
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
                    db()->prepare('UPDATE students SET last_login_at=NOW() WHERE id=?')->execute([(int)$student['id']]);
                    $fresh = db()->prepare('SELECT * FROM students WHERE id=? LIMIT 1');
                    $fresh->execute([(int)$student['id']]);
                    $student = $fresh->fetch() ?: $student;
                    security_rate_limit_clear($rateKey);
                    student_session_login($student);
                    redirect($returnTo);
                }
            } catch (Throwable $e) {
                error_log('[student-login] ' . $e->__toString());
                $errors[] = 'Login failed. Please try again.';
            }
        }
    }
}
$lightweight_layout = true;
$page_styles = ['assets/css/phase149-student-auth.css'];
$page_scripts = ['assets/js/phase149-password-toggle.js'];
require_once __DIR__ . '/includes/header.php';
?>
<section class="section student-auth-section mode-<?= e($mode) ?>">
    <div class="container auth-grid">
        <aside class="auth-copy wf-surface-dark" data-wf-surface="dark" data-reveal>
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
                <div class="auth-support" aria-label="Registration process">
                    <span><i class="fa-solid fa-user-check" aria-hidden="true"></i>Create one secure student account</span>
                    <span><i class="fa-solid fa-route" aria-hidden="true"></i>Open the correct roadmap for your level</span>
                    <span><i class="fa-solid fa-chart-line" aria-hidden="true"></i>Save practice, tests and improvement</span>
                </div>
            <?php else: ?>
                <div class="auth-support" aria-label="Login benefits">
                    <span><i class="fa-solid fa-book-open-reader" aria-hidden="true"></i>Continue your latest lesson</span>
                    <span><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>View previous test and revision</span>
                    <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i>Secure account-based progress</span>
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
                <p><?= $mode === 'register' ? 'Required fields are marked with *. No paid OTP is used right now; the mobile stays marked Unverified until institute staff confirms it.' : 'Use your registered mobile number and password.' ?></p>
            </header>
            <?php if ($errors): ?><div class="alert alert-error" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
            <form method="post" class="form-stack auth-form-grid">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="mode" value="<?= e($mode === 'register' ? 'register' : 'login') ?>">
                <input type="hidden" name="redirect" value="<?= e($returnTo) ?>">
                <?php if ($mode === 'register'): ?>
                    <div class="field full"><label for="studentFullName">Full Name *</label><input id="studentFullName" type="text" name="full_name" required maxlength="100" value="<?= e($_POST['full_name'] ?? '') ?>" placeholder="Enter student name" autocomplete="name"></div>
                    <div class="field"><label for="studentEmail">Email <small>Optional</small></label><input id="studentEmail" type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="student@example.com" autocomplete="email"></div>
                    <div class="field"><label>Starting Level</label><input value="Zero Level / Unassessed" readonly><small class="help">Your official level is set later by assessment or institute staff, so students cannot skip ahead during registration.</small></div>
                    <div class="field full"><label for="studentGoal">Learning Goal</label><input id="studentGoal" type="text" name="target_goal" value="<?= e($_POST['target_goal'] ?? '') ?>" placeholder="Interview, daily conversation or school English"></div>
                <?php endif; ?>
                <div class="field <?= $mode === 'register' ? '' : 'full' ?>"><label for="studentPhone">Phone Number *</label><input id="studentPhone" type="tel" name="phone" required maxlength="10" inputmode="numeric" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="10 digit mobile" autocomplete="tel"></div>
                <div class="field <?= $mode === 'register' ? '' : 'full' ?>"><label for="studentPassword">Password *</label><div class="wf149-password-field"><input id="studentPassword" type="password" name="password" required minlength="8" maxlength="128" autocomplete="<?= $mode === 'register' ? 'new-password' : 'current-password' ?>" placeholder="Minimum 8 characters"><button class="wf149-password-toggle" type="button" data-password-toggle="studentPassword" aria-label="Show password" aria-pressed="false"><i class="fa-solid fa-eye" aria-hidden="true"></i></button></div></div>
                <?php if ($mode === 'register'): ?><div class="field full"><label for="studentConfirmPassword">Confirm Password *</label><div class="wf149-password-field"><input id="studentConfirmPassword" type="password" name="confirm_password" required minlength="8" maxlength="128" autocomplete="new-password" placeholder="Enter password again"><button class="wf149-password-toggle" type="button" data-password-toggle="studentConfirmPassword" aria-label="Show confirm password" aria-pressed="false"><i class="fa-solid fa-eye" aria-hidden="true"></i></button></div></div><input type="text" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px"><div class="field full"><label class="auth-consent"><input type="checkbox" name="account_consent" value="1" required <?= !empty($_POST['account_consent']) ? 'checked' : '' ?>> <span>I confirm this mobile number belongs to me or I have permission to use it for this student account.</span></label></div><?php endif; ?>
                <div class="auth-submit full"><button class="wf-btn wf-btn-primary" type="submit"><span class="wf-btn-label"><i class="fa-solid <?= $mode === 'register' ? 'fa-user-plus' : 'fa-right-to-bracket' ?>"></i><?= $mode === 'register' ? 'Create Student Account' : 'Login to Dashboard' ?></span></button><small><i class="fa-solid fa-shield-halved"></i> Your account information is protected.</small></div>
            </form>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
