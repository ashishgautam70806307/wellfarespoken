<?php
require_once __DIR__ . '/error-pages.php';
/** Phase 148 security, identity, lifecycle and payment helpers. */

function phase148_schema_ready(): bool
{
    return table_exists('admin_roles') && table_exists('admin_permissions') && table_exists('security_rate_limits');
}

function admin_count(): int
{
    // Count every administrator row, not only active rows. Setup must never reopen
    // just because all existing accounts were deactivated.
    try { return (int)db()->query("SELECT COUNT(*) FROM admins")->fetchColumn(); }
    catch (Throwable $e) { return 0; }
}

function admin_setup_needed(): bool
{
    // Setup is a true first-install bootstrap only. Any existing administrator row
    // means the public Institute Login must remain a login screen. Legacy/migrated
    // accounts are handled by RBAC/password migration rules, never by reopening setup.
    try {
        if (!table_exists('admins')) return true;
        return admin_count() === 0;
    } catch (Throwable $e) {
        // Never reopen owner creation because of a transient database/query error.
        return false;
    }
}

function admin_rbac_ready(): bool
{
    return table_exists('admin_roles') && table_exists('admin_permissions') && table_exists('admin_role_permissions') && column_exists('admins', 'role_id');
}

function admin_super_role_id(): int
{
    if (!admin_rbac_ready()) return 0;
    try {
        return (int)(db()->query("SELECT id FROM admin_roles WHERE role_key='super_admin' LIMIT 1")->fetchColumn() ?: 0);
    } catch (Throwable $e) { return 0; }
}

function admin_primary_owner_id(): int
{
    if (!admin_rbac_ready()) return 0;
    try {
        $stmt = db()->query("SELECT a.id FROM admins a JOIN admin_roles r ON r.id=a.role_id WHERE r.role_key='super_admin' AND a.published='Yes' ORDER BY a.id ASC LIMIT 1");
        return (int)($stmt->fetchColumn() ?: 0);
    } catch (Throwable $e) { return 0; }
}

function admin_is_primary_owner(int $adminId = 0): bool
{
    $adminId = $adminId > 0 ? $adminId : (int)($_SESSION['admin_id'] ?? 0);
    $ownerId = admin_primary_owner_id();
    return $adminId > 0 && $ownerId > 0 && $adminId === $ownerId;
}

function admin_assert_primary_owner(): void
{
    if (admin_is_primary_owner()) return;
    wf_show_error_page(403);
}

function admin_page_permission(?string $page = null): ?string
{
    $page = basename($page ?? (string)($_SERVER['PHP_SELF'] ?? ''));
    $map = [
        'dashboard.php'=>'dashboard.view',
        'enquiries.php'=>'enquiries.manage','enquiry-view.php'=>'enquiries.manage',
        'admissions.php'=>'admissions.manage','admission-view.php'=>'admissions.manage',
        'students.php'=>'students.manage','student-view.php'=>'students.manage',
        'courses.php'=>'courses.manage','batches.php'=>'batches.manage',
        'materials.php'=>'materials.manage','materials-ajax.php'=>'materials.manage','practice-lab.php'=>'materials.manage',
        'roadmap.php'=>'roadmap.manage',
        'weekly-tests.php'=>'tests.manage','weekly-test-paper.php'=>'tests.manage','weekly-test-offline-paper.php'=>'tests.manage','weekly-test-ajax.php'=>'tests.manage','weekly-student-record.php'=>'tests.manage','upcoming-test-performance.php'=>'tests.manage','weekly-live-students.php'=>'tests.manage',
        'online-classes.php'=>'content.manage',
        'testimonials.php'=>'content.manage','faculty.php'=>'content.manage','videos.php'=>'content.manage','gallery.php'=>'content.manage','faqs.php'=>'content.manage','content.php'=>'content.manage','hero-banners.php'=>'content.manage','form-options.php'=>'content.manage','nav-menus.php'=>'content.manage','seo.php'=>'content.manage',
        'settings.php'=>'settings.manage',
        'system-check.php'=>'system.manage','ui-library.php'=>'system.manage',
        'admin-users.php'=>'admins.manage','roles.php'=>'admins.manage','audit-log.php'=>'admins.manage',
    ];
    return $map[$page] ?? null;
}

function admin_role_key(int $adminId = 0): string
{
    $adminId = $adminId > 0 ? $adminId : (int)($_SESSION['admin_id'] ?? 0);
    if ($adminId <= 0 || !admin_rbac_ready()) return 'legacy_admin';
    try {
        $stmt = db()->prepare('SELECT r.role_key FROM admins a LEFT JOIN admin_roles r ON r.id=a.role_id WHERE a.id=? LIMIT 1');
        $stmt->execute([$adminId]);
        return (string)($stmt->fetchColumn() ?: 'legacy_admin');
    } catch (Throwable $e) { return 'legacy_admin'; }
}

function admin_can(string $permission, int $adminId = 0): bool
{
    $adminId = $adminId > 0 ? $adminId : (int)($_SESSION['admin_id'] ?? 0);
    if ($adminId <= 0) return false;

    // Before RBAC is installed, fail closed for business/admin modules. The only
    // safe compatibility access is Dashboard + System Check so the owner can
    // finish the migration without turning every legacy admin into an all-powerful user.
    if (!admin_rbac_ready()) return in_array($permission, ['dashboard.view','system.manage'], true);

    try {
        $stmt = db()->prepare("SELECT r.role_key FROM admins a JOIN admin_roles r ON r.id=a.role_id AND r.published='Yes' WHERE a.id=? AND a.published='Yes' LIMIT 1");
        $stmt->execute([$adminId]);
        $roleKey = (string)($stmt->fetchColumn() ?: '');
        if ($roleKey === '') return false;

        // Super Admin is a single protected owner identity, not a normal assignable role.
        if ($roleKey === 'super_admin') return admin_is_primary_owner($adminId);
        if ($permission === 'admins.manage') return false;

        $stmt = db()->prepare("SELECT COUNT(*) FROM admins a JOIN admin_roles r ON r.id=a.role_id AND r.published='Yes' JOIN admin_role_permissions rp ON rp.role_id=r.id JOIN admin_permissions p ON p.id=rp.permission_id WHERE a.id=? AND a.published='Yes' AND p.permission_key=?");
        $stmt->execute([$adminId, $permission]);
        return (int)$stmt->fetchColumn() > 0;
    } catch (Throwable $e) { return false; }
}

function admin_table_permission(string $table): string
{
    $map = [
        'courses'=>'courses.manage', 'course_variants'=>'courses.manage',
        'batch_timings'=>'batches.manage',
        'practice_categories'=>'materials.manage', 'practice_lessons'=>'materials.manage',
        'practice_questions'=>'materials.manage', 'practice_common_mistakes'=>'materials.manage',
        'practice_settings'=>'materials.manage', 'material_collections'=>'materials.manage',
        'material_assets'=>'materials.manage', 'material_units'=>'materials.manage', 'translation_pairs'=>'materials.manage',
        'weekly_tests'=>'tests.manage', 'weekly_test_questions'=>'tests.manage',
        'roadmap_groups'=>'roadmap.manage', 'roadmap_units'=>'roadmap.manage', 'roadmap_items'=>'roadmap.manage',
        'site_settings'=>'settings.manage',
        'testimonials'=>'content.manage', 'videos'=>'content.manage', 'gallery_images'=>'content.manage',
        'faqs'=>'content.manage', 'content_blocks'=>'content.manage', 'form_options'=>'content.manage',
        'nav_menus'=>'content.manage', 'hero_banners'=>'content.manage', 'faculty_members'=>'content.manage',
    ];
    return $map[$table] ?? 'system.manage';
}

function admin_require_permission(string $permission): void
{
    if (admin_can($permission)) return;
    http_response_code(403);
    if (str_contains((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') || strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success'=>false,'message'=>'You do not have permission to perform this action.']);
        exit;
    }
    wf_show_error_page(403);
}

function admin_password_change_required(array $admin): bool
{
    if (($admin['must_change_password'] ?? 'No') !== 'Yes') return false;
    $adminId = (int)($admin['id'] ?? 0);
    if ($adminId <= 0) return false;

    // The protected institute owner may carry a stale legacy migration flag from
    // Phase 148/150. The owner already proved the existing password at login, and
    // owner password changes must be performed explicitly from Account Security.
    // Temporary-password enforcement remains active for normal staff accounts.
    if (admin_rbac_ready() && admin_is_primary_owner($adminId)) return false;
    return true;
}

function admin_clear_stale_owner_password_gate(array &$admin): void
{
    if (($admin['must_change_password'] ?? 'No') !== 'Yes') return;
    $adminId = (int)($admin['id'] ?? 0);
    if ($adminId <= 0 || !admin_rbac_ready() || !admin_is_primary_owner($adminId)) return;
    try {
        db()->prepare("UPDATE admins SET must_change_password='No' WHERE id=?")->execute([$adminId]);
        $admin['must_change_password'] = 'No';
        admin_audit_log('admin.owner_legacy_password_gate_cleared','admin',$adminId,'Cleared stale legacy must-change-password flag for the protected institute owner.');
    } catch (Throwable $e) {
        error_log('[admin-owner-password-gate] ' . $e->getMessage());
    }
}

function admin_password_gate_active(): bool
{
    return !empty($_SESSION['admin_password_change_required']);
}

function admin_session_signature(array $admin): string
{
    return hash('sha256', implode('|', [
        (string)($admin['password_hash'] ?? ''),
        (string)($admin['auth_version'] ?? 1),
        (string)($admin['published'] ?? 'No'),
        (string)($admin['role_id'] ?? 0),
    ]));
}

function admin_session_login(array $admin): void
{
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int)$admin['id'];
    $_SESSION['admin_name'] = (string)($admin['name'] ?? 'Admin');
    $_SESSION['admin_auth_signature'] = admin_session_signature($admin);
    $_SESSION['admin_last_activity'] = time();
    $_SESSION['admin_password_change_required'] = admin_password_change_required($admin);
    unset($_SESSION['admin_mfa_pending_id'], $_SESSION['admin_mfa_pending_at']);
}

function admin_invalidate_sessions(int $adminId): void
{
    if ($adminId <= 0 || !column_exists('admins','auth_version')) return;
    db()->prepare('UPDATE admins SET auth_version=auth_version+1, updated_at=NOW() WHERE id=?')->execute([$adminId]);
}

function admin_audit_log(string $eventType, ?string $entityType = null, string|int|null $entityId = null, string $note = ''): void
{
    if (!table_exists('admin_audit_events')) return;
    try {
        $stmt = db()->prepare('INSERT INTO admin_audit_events (admin_id,event_type,entity_type,entity_id,event_note,request_path,ip_address,user_agent) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([
            !empty($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null,
            mb_substr($eventType,0,80), $entityType ? mb_substr($entityType,0,80) : null,
            $entityId !== null ? mb_substr((string)$entityId,0,80) : null,
            $note !== '' ? mb_substr($note,0,4000) : null,
            mb_substr((string)($_SERVER['REQUEST_URI'] ?? ''),0,255), client_ip(),
            mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''),0,500),
        ]);
    } catch (Throwable $e) { error_log('[admin-audit] '.$e->getMessage()); }
}

function admin_request_audit_bootstrap(): void
{
    static $booted = false;
    if ($booted || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !is_admin()) return;
    $booted = true;
    $page = basename((string)($_SERVER['PHP_SELF'] ?? 'admin'));
    $action = preg_replace('/[^a-z0-9_.-]+/i','_', (string)($_POST['action'] ?? 'save')) ?: 'save';
    $entityId = (string)($_POST['id'] ?? $_GET['id'] ?? '');
    register_shutdown_function(static function() use ($page,$action,$entityId): void {
        if (http_response_code() >= 400) return;
        admin_audit_log('request.' . $action, pathinfo($page,PATHINFO_FILENAME), $entityId !== '' ? $entityId : null, 'Administrative POST completed.');
    });
}

function app_secret_key_bytes(): string
{
    static $key = null;
    if (is_string($key)) return $key;

    $configured = defined('APP_SECRET_KEY') ? trim((string)APP_SECRET_KEY) : '';
    if ($configured !== '') {
        $raw = preg_match('/^[a-f0-9]{64,}$/i', $configured) ? @hex2bin(substr($configured, 0, 64)) : false;
        $key = is_string($raw) && strlen($raw) >= 32 ? substr($raw, 0, 32) : hash('sha256', $configured, true);
        return $key;
    }

    // Safe compatibility fallback: keep a generated key in private storage, not in the database/source tree.
    $dir = defined('PRIVATE_STORAGE_PATH') ? PRIVATE_STORAGE_PATH : dirname(__DIR__) . '/storage/private';
    $file = rtrim($dir, '/\\') . '/.app-secret.key';
    try {
        if (!is_dir($dir)) @mkdir($dir, 0750, true);
        if (is_file($file) && is_readable($file)) {
            $stored = trim((string)file_get_contents($file));
            if (preg_match('/^[a-f0-9]{64}$/i', $stored)) {
                $raw = hex2bin($stored);
                if (is_string($raw)) { $key = $raw; return $key; }
            }
        }
        $generated = random_bytes(32);
        $written = @file_put_contents($file, bin2hex($generated), LOCK_EX);
        if ($written === false) throw new RuntimeException('Security encryption key is not configured. Set APP_SECRET_KEY in .env or make private storage writable.');
        @chmod($file, 0600);
        $key = $generated;
        return $key;
    } catch (Throwable $e) {
        if ($e instanceof RuntimeException) throw $e;
        throw new RuntimeException('Security encryption key could not be initialized. Configure APP_SECRET_KEY in .env.');
    }
}

function app_encrypt_secret(string $plain): string
{
    if ($plain === '') return '';
    if (!function_exists('openssl_encrypt')) return $plain; // legacy compatibility on unusual PHP builds
    $iv = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt($plain, 'aes-256-gcm', app_secret_key_bytes(), OPENSSL_RAW_DATA, $iv, $tag);
    if (!is_string($cipher) || $tag === '') throw new RuntimeException('Unable to encrypt the security secret.');
    return 'enc:v1:' . base64_encode($iv . $tag . $cipher);
}

function app_decrypt_secret(string $stored): string
{
    if ($stored === '' || !str_starts_with($stored, 'enc:v1:')) return $stored; // legacy plaintext remains readable for migration
    if (!function_exists('openssl_decrypt')) return '';
    $raw = base64_decode(substr($stored, 7), true);
    if (!is_string($raw) || strlen($raw) < 29) return '';
    $iv = substr($raw, 0, 12); $tag = substr($raw, 12, 16); $cipher = substr($raw, 28);
    try { $key = app_secret_key_bytes(); } catch (Throwable $e) { return ''; }
    $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    return is_string($plain) ? $plain : '';
}

function admin_mfa_secret_plain(string $stored): string
{
    return app_decrypt_secret($stored);
}

function admin_mfa_is_required_for_owner(int $adminId = 0): bool
{
    if (!defined('ADMIN_REQUIRE_OWNER_MFA') || !ADMIN_REQUIRE_OWNER_MFA) return false;
    $adminId = $adminId > 0 ? $adminId : (int)($_SESSION['admin_id'] ?? 0);
    return $adminId > 0 && admin_is_primary_owner($adminId);
}

function admin_mfa_gate_active(): bool
{
    if (!admin_mfa_is_required_for_owner()) return false;
    try {
        $stmt = db()->prepare("SELECT mfa_enabled,mfa_secret FROM admins WHERE id=? AND published='Yes' LIMIT 1");
        $stmt->execute([(int)($_SESSION['admin_id'] ?? 0)]);
        $row = $stmt->fetch() ?: [];
        return ($row['mfa_enabled'] ?? 'No') !== 'Yes' || trim((string)($row['mfa_secret'] ?? '')) === '';
    } catch (Throwable $e) { return true; }
}

// RFC 6238 TOTP helpers. Free authenticator apps can be used; no paid SMS/email provider is required.
function admin_password_error(string $password): string
{
    $length = strlen($password);
    if ($length < 12 || $length > 128) return 'Administrator passwords must be 12–128 characters.';
    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) return 'Administrator passwords must include at least one letter and one number.';
    return '';
}

function admin_mfa_base32_decode(string $secret): string
{
    $alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret=strtoupper(preg_replace('/[^A-Z2-7]/i','',$secret) ?? '');
    $bits='';
    foreach(str_split($secret) as $c){$v=strpos($alphabet,$c); if($v===false) continue; $bits.=str_pad(decbin($v),5,'0',STR_PAD_LEFT);} 
    $out=''; for($i=0;$i+8<=strlen($bits);$i+=8)$out.=chr(bindec(substr($bits,$i,8))); return $out;
}
function admin_mfa_generate_secret(int $length=24): string
{
    $alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $s=''; for($i=0;$i<$length;$i++)$s.=$alphabet[random_int(0,31)]; return $s;
}
function admin_mfa_code(string $secret, ?int $time=null): string
{
    $counter=intdiv($time ?? time(),30); $bin=pack('N2',0,$counter); $hash=hash_hmac('sha1',$bin,admin_mfa_base32_decode($secret),true); $offset=ord($hash[19])&0xf; $num=((ord($hash[$offset])&0x7f)<<24)|((ord($hash[$offset+1])&0xff)<<16)|((ord($hash[$offset+2])&0xff)<<8)|(ord($hash[$offset+3])&0xff); return str_pad((string)($num%1000000),6,'0',STR_PAD_LEFT);
}
function admin_mfa_verify(string $secret,string $code): bool
{
    $code=preg_replace('/\D+/','',$code) ?? ''; if(strlen($code)!==6) return false; $now=time();
    foreach([-1,0,1] as $w){ if(hash_equals(admin_mfa_code($secret,$now+$w*30),$code)) return true; } return false;
}
function admin_mfa_uri(string $email,string $secret): string
{
    $issuer=defined('ADMIN_MFA_ISSUER')?ADMIN_MFA_ISSUER:APP_NAME;
    return 'otpauth://totp/'.rawurlencode($issuer.':'.$email).'?secret='.rawurlencode($secret).'&issuer='.rawurlencode($issuer).'&digits=6&period=30';
}

function student_registration_is_open(): bool
{
    $mode = defined('STUDENT_REGISTRATION_MODE') ? STUDENT_REGISTRATION_MODE : 'open';
    return $mode !== 'approval';
}

function admission_ledger_total(int $admissionId): float
{
    if ($admissionId<=0 || !table_exists('admission_payments')) return 0.0;
    $stmt=db()->prepare("SELECT COALESCE(SUM(CASE WHEN entry_type IN ('Payment','Opening','Adjustment') THEN amount WHEN entry_type='Refund' THEN -amount ELSE 0 END),0) FROM admission_payments WHERE admission_id=?");
    $stmt->execute([$admissionId]); return (float)$stmt->fetchColumn();
}
function admission_recalculate_ledger(int $admissionId): void
{
    if ($admissionId <= 0 || !table_exists('admission_payments')) return;
    $stmt = db()->prepare('SELECT total_fee,discount_amount FROM admissions WHERE id=? LIMIT 1');
    $stmt->execute([$admissionId]);
    $a = $stmt->fetch();
    if (!$a) return;

    $paid = max(0.0, admission_ledger_total($admissionId));
    $due = max(0.0, (float)$a['total_fee'] - (float)$a['discount_amount']);
    if ($due <= 0.0001) $status = 'Paid';
    elseif ($paid <= 0.0001) $status = 'Unpaid';
    elseif ($paid + 0.0001 >= $due) $status = 'Paid';
    else $status = 'Partial';

    $last = db()->prepare("SELECT payment_mode,receipt_no FROM admission_payments WHERE admission_id=? AND entry_type IN ('Payment','Opening','Adjustment') ORDER BY entry_date DESC,id DESC LIMIT 1");
    $last->execute([$admissionId]);
    $lr = $last->fetch() ?: [];
    db()->prepare('UPDATE admissions SET paid_amount=?, payment_status=?, payment_mode=?, receipt_no=? WHERE id=?')
        ->execute([$paid,$status,$lr['payment_mode']??null,$lr['receipt_no']??null,$admissionId]);
}

function admission_add_payment(int $admissionId,string $type,float $amount,string $mode='',string $reference='',string $receipt='',string $note='',?string $entryDate=null): int
{
    $types = ['Payment','Refund','Adjustment','Opening'];
    if (!in_array($type,$types,true)) throw new RuntimeException('Invalid ledger entry type.');
    if ($admissionId <= 0 || $amount <= 0) throw new RuntimeException('A positive payment amount is required.');
    if (!table_exists('admission_payments')) throw new RuntimeException('Payment ledger migration is not installed.');

    $mode = mb_substr(trim($mode),0,80);
    $reference = mb_substr(trim($reference),0,160);
    $receipt = mb_substr(trim($receipt),0,120);
    $note = mb_substr(trim($note),0,4000);
    if ($entryDate !== null && $entryDate !== '') {
        $parsed = strtotime($entryDate);
        if ($parsed === false) throw new RuntimeException('Please enter a valid payment date/time.');
        $entryDate = date('Y-m-d H:i:s',$parsed);
    } else {
        $entryDate = date('Y-m-d H:i:s');
    }
    if ($receipt === '') $receipt = 'WF-'.date('Ymd').'-'.$admissionId.'-'.strtoupper(bin2hex(random_bytes(4)));

    $pdo = db();
    $ownsTransaction = !$pdo->inTransaction();
    if ($ownsTransaction) $pdo->beginTransaction();
    try {
        $lock = $pdo->prepare('SELECT id,status_deleted,total_fee,discount_amount FROM admissions WHERE id=? FOR UPDATE');
        $lock->execute([$admissionId]);
        $admission = $lock->fetch();
        if (!$admission || (int)($admission['status_deleted'] ?? 0) !== 0) throw new RuntimeException('Admission record is not available for payment posting.');

        $currentTotal = admission_ledger_total($admissionId);
        $feeDue = max(0.0, (float)($admission['total_fee'] ?? 0) - (float)($admission['discount_amount'] ?? 0));
        $outstanding = max(0.0, $feeDue - $currentTotal);
        if ($type === 'Refund' && $amount > $currentTotal + 0.0001) {
            throw new RuntimeException('Refund cannot be greater than the net amount received.');
        }
        if ($type === 'Payment' && $feeDue > 0.0001 && $amount > $outstanding + 0.0001) {
            throw new RuntimeException('Payment cannot be greater than the current outstanding balance.');
        }
        if (in_array($type, ['Refund','Adjustment'], true) && mb_strlen($note) < 3) {
            throw new RuntimeException($type . ' entries require a short reason/note for the audit trail.');
        }
        if ($type === 'Payment' && $mode === '') {
            throw new RuntimeException('Choose a payment mode for a payment entry.');
        }

        $stmt = $pdo->prepare('INSERT INTO admission_payments (admission_id,entry_type,amount,payment_mode,reference_no,receipt_no,note,entry_date,admin_id) VALUES (?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$admissionId,$type,$amount,$mode?:null,$reference?:null,$receipt,$note?:null,$entryDate,!empty($_SESSION['admin_id'])?(int)$_SESSION['admin_id']:null]);
        $id = (int)$pdo->lastInsertId();
        admission_recalculate_ledger($admissionId);
        if ($ownsTransaction) $pdo->commit();
        admin_audit_log('payment.add','admission',$admissionId,$type.' '.number_format($amount,2).' receipt '.$receipt);
        return $id;
    } catch (Throwable $e) {
        if ($ownsTransaction && $pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

function admission_payments(int $admissionId): array
{
    if($admissionId<=0 || !table_exists('admission_payments'))return[]; $s=db()->prepare('SELECT p.*,a.name admin_name FROM admission_payments p LEFT JOIN admins a ON a.id=p.admin_id WHERE p.admission_id=? ORDER BY p.entry_date DESC,p.id DESC');$s->execute([$admissionId]);return$s->fetchAll();
}

function lifecycle_resolve_course_id(string $title): int
{
    $title=trim($title); if($title==='')return 0; $s=db()->prepare('SELECT id FROM courses WHERE LOWER(TRIM(title))=LOWER(TRIM(?)) LIMIT 1');$s->execute([$title]);return(int)($s->fetchColumn()?:0);
}
function lifecycle_resolve_batch_id(string $label): int
{
    $label=trim($label); if($label==='')return 0; $s=db()->prepare("SELECT id FROM batch_timings WHERE LOWER(TRIM(batch_name))=LOWER(TRIM(?)) OR LOWER(TRIM(CONCAT(batch_name,' - ',COALESCE(timing,''))))=LOWER(TRIM(?)) LIMIT 1");$s->execute([$label,$label]);return(int)($s->fetchColumn()?:0);
}
function lifecycle_sync_admission(int $admissionId): void
{
    if($admissionId<=0 || !table_exists('student_enrollments'))return;
    $s=db()->prepare('SELECT * FROM admissions WHERE id=? AND status_deleted=0 LIMIT 1');$s->execute([$admissionId]);$a=$s->fetch();if(!$a)return;
    $studentId=(int)($a['student_id']??0);$courseId=(int)($a['course_id']??0);$batchId=(int)($a['batch_id']??0);
    if($studentId<=0)return;
    $status=($a['admission_status']??'')==='Joined'?'Active':((($a['admission_status']??'')==='Cancelled')?'Cancelled':'Pending');
    $joined=$status==='Active'?date('Y-m-d H:i:s'):null;
    $q=db()->prepare('SELECT id,enrollment_status FROM student_enrollments WHERE admission_id=? LIMIT 1');$q->execute([$admissionId]);$enId=(int)($q->fetchColumn()?:0);
    if($enId>0){db()->prepare('UPDATE student_enrollments SET student_id=?,course_id=?,course_title_snapshot=?,enrollment_status=?,joined_at=COALESCE(joined_at,?) WHERE id=?')->execute([$studentId,$courseId?:null,$a['course_interest']?:null,$status,$joined,$enId]);}
    else{db()->prepare('INSERT INTO student_enrollments (student_id,admission_id,course_id,course_title_snapshot,enrollment_status,joined_at) VALUES (?,?,?,?,?,?)')->execute([$studentId,$admissionId,$courseId?:null,$a['course_interest']?:null,$status,$joined]);$enId=(int)db()->lastInsertId();}
    if($batchId>0 && $enId>0 && $status!=='Cancelled'){
        $b=db()->prepare('SELECT batch_name FROM batch_timings WHERE id=? LIMIT 1');$b->execute([$batchId]);$bn=(string)($b->fetchColumn()?:($a['batch_preference']??''));
        db()->prepare("UPDATE student_batch_memberships SET membership_status='Left',left_at=NOW() WHERE enrollment_id=? AND membership_status='Active' AND (batch_id IS NULL OR batch_id<>?)")->execute([$enId,$batchId]);
        $m=db()->prepare("SELECT id FROM student_batch_memberships WHERE enrollment_id=? AND batch_id=? AND membership_status='Active' LIMIT 1");$m->execute([$enId,$batchId]);if(!$m->fetchColumn())db()->prepare('INSERT INTO student_batch_memberships (enrollment_id,student_id,batch_id,batch_name_snapshot,membership_status) VALUES (?,?,?,?,?)')->execute([$enId,$studentId,$batchId,$bn,'Active']);
    }
}
function lifecycle_link_student_registration(int $studentId,string $phone): void
{
    if($studentId<=0 || !column_exists('admissions','student_id'))return;
    // A self-entered phone number is not proof of identity. Never attach an admission
    // to a new learning account until institute staff has manually verified the mobile.
    if (column_exists('students','identity_status')) {
        $identity=db()->prepare('SELECT identity_status FROM students WHERE id=? AND status_deleted=0 LIMIT 1');
        $identity->execute([$studentId]);
        if ((string)($identity->fetchColumn()?:'Unverified') !== 'Verified') return;
    }
    $digits=clean_phone_digits($phone); if(strlen($digits)>10)$digits=substr($digits,-10); if(strlen($digits)!==10)return;
    $s=db()->prepare("SELECT id FROM admissions WHERE status_deleted=0 AND student_id IS NULL AND RIGHT(REPLACE(REPLACE(REPLACE(phone,' ',''),'-',''),'+91',''),10)=? ORDER BY COALESCE(admission_date,DATE(created_at)) DESC,id DESC LIMIT 1");$s->execute([$digits]);$aid=(int)($s->fetchColumn()?:0); if($aid<=0)return;
    db()->prepare('UPDATE admissions SET student_id=? WHERE id=? AND student_id IS NULL')->execute([$studentId,$aid]); lifecycle_sync_admission($aid); student_account_log($studentId,'admission_linked','Admission linked to verified student account','Institute-verified mobile number matched an existing admission.');
}
function lifecycle_convert_enquiry(int $enquiryId): int
{
    if($enquiryId<=0)throw new RuntimeException('Invalid enquiry.'); $pdo=db();$pdo->beginTransaction();
    try{$s=$pdo->prepare('SELECT * FROM enquiries WHERE id=? AND status_deleted=0 FOR UPDATE');$s->execute([$enquiryId]);$e=$s->fetch();if(!$e)throw new RuntimeException('Enquiry not found.');if(!empty($e['converted_admission_id'])){$pdo->commit();return(int)$e['converted_admission_id'];}
        $digits=clean_phone_digits((string)$e['phone']);if(strlen($digits)>10)$digits=substr($digits,-10);$studentId=0;if(strlen($digits)===10){$studentSql=column_exists('students','identity_status') ? "SELECT id FROM students WHERE status_deleted=0 AND identity_status='Verified' AND RIGHT(phone,10)=? LIMIT 1" : "SELECT id FROM students WHERE status_deleted=0 AND RIGHT(phone,10)=? LIMIT 1";$q=$pdo->prepare($studentSql);$q->execute([$digits]);$studentId=(int)($q->fetchColumn()?:0);}
        $courseId=(int)($e['course_id']??0)?:lifecycle_resolve_course_id((string)($e['course_interest']??''));$batchId=(int)($e['batch_id']??0)?:lifecycle_resolve_batch_id((string)($e['preferred_batch']??''));
        $i=$pdo->prepare('INSERT INTO admissions (enquiry_id,student_id,course_id,batch_id,student_name,phone,course_interest,batch_preference,current_level,source_label,admission_status,admin_note,published,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())');$i->execute([$enquiryId,$studentId?:null,$courseId?:null,$batchId?:null,$e['name'],$e['phone'],$e['course_interest']??null,$e['preferred_batch']??null,$e['current_level']??null,$e['lead_source']??'Website','New',$e['admin_note']??null,'Yes']);$aid=(int)$pdo->lastInsertId();
        $pdo->prepare("UPDATE enquiries SET enquiry_status='Converted',converted_admission_id=?,converted_at=NOW() WHERE id=?")->execute([$aid,$enquiryId]);$pdo->commit(); lifecycle_sync_admission($aid);admin_audit_log('enquiry.convert','enquiry',$enquiryId,'Converted to admission #'.$aid);return$aid;
    }catch(Throwable $ex){if($pdo->inTransaction())$pdo->rollBack();throw$ex;}
}

function admin_pagination_state(int $total, int $perPage = 24, string $pageKey = 'page'): array
{
    $perPage = max(10, min(100, $perPage));
    $pages = max(1, (int)ceil(max(0,$total) / $perPage));
    $page = max(1, min($pages, (int)($_GET[$pageKey] ?? 1)));
    return ['page'=>$page,'pages'=>$pages,'per_page'=>$perPage,'offset'=>($page-1)*$perPage,'total'=>$total,'key'=>$pageKey];
}

function admin_pagination_html(array $pager): string
{
    $page=(int)($pager['page']??1);$pages=(int)($pager['pages']??1);$total=(int)($pager['total']??0);$key=(string)($pager['key']??'page');
    if($pages<=1)return $total>0?'<div class="admin-pagination"><span>'.$total.' record(s)</span></div>':'';
    $base=$_GET;unset($base[$key]);$html='<nav class="admin-pagination" aria-label="Pagination"><span>Page '.$page.' of '.$pages.' · '.$total.' records</span><div>';
    $make=function(int $p,string $label,bool $disabled=false)use($base,$key){if($disabled)return '<span class="btn btn-sm btn-soft disabled" aria-disabled="true">'.$label.'</span>';$q=$base;$q[$key]=$p;return '<a class="btn btn-sm btn-soft" href="?'.e(http_build_query($q)).'">'.$label.'</a>';};
    $html.=$make(max(1,$page-1),'‹ Prev',$page<=1);
    for($i=max(1,$page-2);$i<=min($pages,$page+2);$i++){$q=$base;$q[$key]=$i;$html.='<a class="btn btn-sm '.($i===$page?'btn-primary':'btn-soft').'" href="?'.e(http_build_query($q)).'">'.$i.'</a>';}
    $html.=$make(min($pages,$page+1),'Next ›',$page>=$pages);
    return $html.'</div></nav>';
}

function material_private_root(): string
{
    $root=defined('PRIVATE_STORAGE_PATH')?PRIVATE_STORAGE_PATH:(dirname(__DIR__).'/storage/private'); if(!is_dir($root))@mkdir($root,0750,true); return $root;
}
function material_asset_absolute_path(string $stored): ?string
{
    $stored=trim(str_replace('\\','/',$stored)); if($stored==='')return null;
    if(str_starts_with($stored,'private/materials/')){$rel=substr($stored,strlen('private/'));$path=material_private_root().'/'.$rel;}
    elseif(str_starts_with($stored,'assets/uploads/materials/')){$path=dirname(__DIR__).'/'.$stored;}
    else return null;
    $real=realpath($path); if($real===false)return null;
    $allowed=[realpath(material_private_root()),realpath(dirname(__DIR__).'/assets/uploads/materials')];
    foreach($allowed as $base){if($base&&str_starts_with($real,$base.DIRECTORY_SEPARATOR))return$real;} return null;
}
