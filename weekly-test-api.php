<?php
ob_start();
ini_set('display_errors', '0');
require_once __DIR__ . '/includes/functions.php';
weekly_test_ensure_schema();
while (ob_get_level() > 1) { @ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private, max-age=0');

function weekly_api_wants_json(): bool
{
    $requestedWith = strtolower(trim((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')));
    $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
    return $requestedWith === 'xmlhttprequest' || str_contains($accept, 'application/json');
}

function jt(array $payload, int $code = 200): never
{
    $action = trim((string)($_POST['action'] ?? $_GET['action'] ?? ''));
    if ($action === 'start' && !weekly_api_wants_json()) {
        if (!empty($payload['success']) && !empty($payload['attempt_id']) && !empty($payload['access_token'])) {
            $url = 'weekly-exam-room.php?attempt_id=' . rawurlencode((string)$payload['attempt_id'])
                . '&token=' . rawurlencode((string)$payload['access_token']);
            header('Location: ' . $url, true, 303);
            exit;
        }
        if (!empty($payload['result_url'])) {
            header('Location: ' . safe_local_redirect((string)$payload['result_url'], 'weekly-test.php#my-results'), true, 303);
            exit;
        }
        $type = strtolower(trim((string)($_POST['test_type'] ?? 'basic')));
        if (!in_array($type, ['basic', 'previous', 'upcoming'], true)) $type = 'basic';
        flash('error', trim((string)($payload['message'] ?? 'Test could not start.')) ?: 'Test could not start.');
        header('Location: weekly-test.php?type=' . rawurlencode($type) . '#wfTestSetup', true, 303);
        exit;
    }

    http_response_code($code);
    if (ob_get_length()) @ob_clean();
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function weekly_api_attempt(int $attemptId, string $token): ?array
{
    if ($attemptId <= 0 || strlen($token) < 32) return null;
    return weekly_test_fetch_attempt_record($attemptId, $token, false);
}

function weekly_api_assert_owner(array $attempt): void
{
    if (!empty($attempt['student_id'])) {
        if (!is_student() || (int)$attempt['student_id'] !== current_student_id()) {
            jt(['success'=>false, 'message'=>'Invalid student attempt.'], 403);
        }
        $stmt = db()->prepare("SELECT published, status_deleted FROM students WHERE id=? LIMIT 1");
        $stmt->execute([(int)$attempt['student_id']]);
        $student = $stmt->fetch();
        if (!$student || ($student['published'] ?? 'No') !== 'Yes' || (int)($student['status_deleted'] ?? 0) !== 0) {
            jt(['success'=>false, 'message'=>'Student account is not active.'], 403);
        }
    }
}

function weekly_api_snapshot(array &$attempt): array
{
    return weekly_test_attempt_snapshot($attempt);
}

function weekly_api_sanitized_answers(string $json): array
{
    if (strlen($json) > 160000) jt(['success'=>false, 'message'=>'Answer data is too large.'], 413);
    $data = json_decode($json, true);
    if (!is_array($data) || count($data) > 500) jt(['success'=>false, 'message'=>'Invalid test data.']);
    $answers = [];
    foreach ($data as $qid => $answer) {
        $qid = (int)$qid;
        if ($qid <= 0) continue;
        $answer = trim((string)$answer);
        if (mb_strlen($answer) > 5000) $answer = mb_substr($answer, 0, 5000);
        $answers[$qid] = $answer;
    }
    return $answers;
}

try {
    $action = trim((string)($_POST['action'] ?? $_GET['action'] ?? ''));
    $schemaStatus = weekly_test_schema_status();
    if (!($schemaStatus['ready'] ?? false)) {
        jt(['success'=>false, 'message'=>'Weekly Test database upgrade is incomplete. Import sql/wellfare_english_complete.sql once.'], 503);
    }

    if ($action === 'list') {
        $type = trim((string)($_GET['type'] ?? ''));
        if ($type !== '' && !in_array($type, ['basic','previous','upcoming'], true)) $type = '';
        jt(['success'=>true, 'tests'=>weekly_test_fetch_tests($type ?: null), 'is_student'=>is_student()]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') jt(['success'=>false, 'message'=>'Invalid request.'], 405);
    if (!csrf_validate($_POST['csrf_token'] ?? '')) jt(['success'=>false, 'message'=>'Session expired. Refresh the page once.'], 419);

    if ($action === 'warning') {
        $attemptId = (int)($_POST['attempt_id'] ?? 0);
        $token = trim((string)($_POST['access_token'] ?? ''));
        $attempt = weekly_api_attempt($attemptId, $token);
        if (!$attempt || ($attempt['status'] ?? '') !== 'started') jt(['success'=>false, 'message'=>'Invalid or closed attempt.'], 403);
        weekly_api_assert_owner($attempt);

        $allowed = [
            'Tab or app switch detected', 'Window focus changed', 'Fullscreen exited',
            'Fullscreen permission denied', 'Keyboard shortcut blocked', 'Candidate reported an exam issue'
        ];
        $message = trim((string)($_POST['message'] ?? ''));
        if (!in_array($message, $allowed, true)) $message = 'Unrecognized activity';
        $line = date('Y-m-d H:i:s') . ' - ' . $message . "\n";
        $stmt = db()->prepare("UPDATE weekly_test_attempts
                               SET warning_count=COALESCE(warning_count,0)+1,
                                   activity_log=RIGHT(CONCAT(COALESCE(activity_log,''), ?), 60000)
                               WHERE id=? AND access_token=? AND status='started'");
        $stmt->execute([$line, $attemptId, $token]);
        if ($stmt->rowCount() !== 1) jt(['success'=>false, 'message'=>'Warning could not be saved.'], 409);

        $s = db()->prepare("SELECT a.warning_count, t.test_type, t.penalty_after_warnings,
                                   t.penalty_per_warning, t.warning_limit, t.auto_submit_on_warning_limit
                            FROM weekly_test_attempts a JOIN weekly_tests t ON t.id=a.test_id
                            WHERE a.id=? AND a.access_token=? LIMIT 1");
        $s->execute([$attemptId, $token]);
        $row = $s->fetch() ?: [];
        $count = (int)($row['warning_count'] ?? 0);
        $limit = max(1, (int)($row['warning_limit'] ?? 3));
        if ($count >= $limit) {
            db()->prepare("UPDATE weekly_test_attempts SET suspicious_flag='Yes' WHERE id=? AND access_token=?")
                ->execute([$attemptId, $token]);
        }
        $penaltyActive = (($row['test_type'] ?? 'basic') !== 'basic') && (($row['penalty_after_warnings'] ?? 'Yes') === 'Yes');
        $deduct = ($penaltyActive && $count > 1) ? (($count - 1) * max(0, (float)($row['penalty_per_warning'] ?? 1))) : 0;
        $autoSubmit = (($row['test_type'] ?? 'basic') !== 'basic')
            && (($row['auto_submit_on_warning_limit'] ?? 'Yes') === 'Yes') && $count >= $limit;
        jt(['success'=>true, 'message'=>'Warning saved', 'warning_count'=>$count,
            'penalty_active'=>$penaltyActive, 'penalty_preview'=>$deduct, 'should_auto_submit'=>$autoSubmit]);
    }

    if ($action === 'timing') {
        $attemptId = (int)($_POST['attempt_id'] ?? 0);
        $token = trim((string)($_POST['access_token'] ?? ''));
        $attempt = weekly_api_attempt($attemptId, $token);
        if (!$attempt || ($attempt['status'] ?? '') !== 'started') jt(['success'=>false, 'message'=>'Invalid timing request.'], 403);
        weekly_api_assert_owner($attempt);
        $payload = trim((string)($_POST['timing_json'] ?? '{}'));
        if (strlen($payload) > 2000 || !is_array(json_decode($payload, true))) jt(['success'=>false, 'message'=>'Invalid timing data.']);
        $line = date('Y-m-d H:i:s') . ' ' . $payload . "\n";
        db()->prepare("UPDATE weekly_test_attempts
                       SET timing_log=RIGHT(CONCAT(COALESCE(timing_log,''), ?), 60000)
                       WHERE id=? AND access_token=? AND status='started'")
            ->execute([$line, $attemptId, $token]);
        jt(['success'=>true, 'message'=>'Timing saved']);
    }

    if ($action === 'start') {
        $testId = (int)($_POST['test_id'] ?? 0);
        $type = trim((string)($_POST['test_type'] ?? 'basic'));
        if (!in_array($type, ['basic','previous','upcoming'], true)) $type = 'basic';
        if (!security_rate_limit('weekly-test-start:' . $type, 25, 300)) {
            jt(['success'=>false, 'message'=>'Too many test start requests. Please wait a few minutes.'], 429);
        }
        $test = null;
        if ($testId > 0) {
            $s = db()->prepare("SELECT * FROM weekly_tests WHERE id=? AND status_deleted=0 LIMIT 1");
            $s->execute([$testId]);
            $test = $s->fetch() ?: null;
            if (!$test) jt(['success'=>false, 'message'=>'The selected test paper is no longer available. Refresh the page and choose again.'], 404);
        } else {
            $test = weekly_test_default_by_type($type);
        }
        if (!$test) jt(['success'=>false, 'message'=>'No test found. Ask admin to create a test.']);
        $actualType = strtolower(trim((string)($test['test_type'] ?? '')));
        if (!in_array($actualType, ['basic','previous','upcoming'], true) || $actualType !== $type) {
            jt(['success'=>false, 'message'=>'Selected test type does not match this paper. Refresh the page and try again.'], 409);
        }
        $isOfficialExam = $actualType === 'upcoming';
        if (($isOfficialExam || ($test['requires_login'] ?? 'No') === 'Yes') && !is_student()) {
            jt(['success'=>false, 'login_required'=>true, 'message'=>'Student login is required for the weekly exam.']);
        }
        $activeStudent = null;
        if (is_student()) {
            $studentCheck = db()->prepare("SELECT id, full_name, phone, published, status_deleted FROM students WHERE id=? LIMIT 1");
            $studentCheck->execute([current_student_id()]);
            $activeStudent = $studentCheck->fetch();
            if (!$activeStudent || ($activeStudent['published'] ?? 'No') !== 'Yes' || (int)($activeStudent['status_deleted'] ?? 0) !== 0) {
                student_session_logout();
                jt(['success'=>false, 'login_required'=>true, 'message'=>'Student account is not active. Please login again.'], 403);
            }
        }
        if (strtolower((string)($test['status'] ?? '')) !== 'active' || ($test['published'] ?? 'Yes') !== 'Yes') {
            jt(['success'=>false, 'message'=>'This test is not published yet.']);
        }
        $now = time();
        if (!empty($test['starts_at']) && strtotime((string)$test['starts_at']) > $now) {
            jt(['success'=>false, 'message'=>'This test will open at the scheduled time.']);
        }
        if (!empty($test['ends_at']) && strtotime((string)$test['ends_at']) < $now) {
            jt(['success'=>false, 'message'=>'This test window is closed.']);
        }

        $studentId = is_student() ? current_student_id() : null;
        if ($isOfficialExam && $studentId) {
            $batchEligibility = weekly_test_student_batch_eligibility($studentId, $test);
            if (empty($batchEligibility['allowed'])) {
                jt(['success'=>false, 'message'=>(string)($batchEligibility['message'] ?? 'This test is not assigned to your batch.')], 403);
            }
        }
        $duration = max(1, min(240, (int)($test['duration_minutes'] ?? 30)));
        $startTransactionOpen = false;
        if (($test['test_type'] ?? '') === 'upcoming' && $studentId) {
            $pdo = db();
            $pdo->beginTransaction();
            $startTransactionOpen = true;
            // Every concurrent start for the same official paper waits on this row lock.
            $lock = $pdo->prepare("SELECT id FROM weekly_tests WHERE id=? AND status_deleted=0 FOR UPDATE");
            $lock->execute([(int)$test['id']]);
            if (!$lock->fetchColumn()) {
                $pdo->rollBack();
                $startTransactionOpen = false;
                jt(['success'=>false, 'message'=>'The selected test paper is no longer available.'], 404);
            }

            // Serialize official-test starts for this student as well as this paper.
            // This prevents two different Upcoming Test start requests from racing each other.
            $studentLock = $pdo->prepare("SELECT id FROM students WHERE id=? AND status_deleted=0 FOR UPDATE");
            $studentLock->execute([$studentId]);
            if (!$studentLock->fetchColumn()) {
                $pdo->rollBack();
                $startTransactionOpen = false;
                jt(['success'=>false, 'login_required'=>true, 'message'=>'Student account is not available. Please login again.'], 403);
            }

            $chk = $pdo->prepare("SELECT * FROM weekly_test_attempts
                                  WHERE COALESCE(status_deleted,0)=0 AND test_id=? AND student_id=?
                                    AND status IN ('started','submitted','checked') ORDER BY id DESC LIMIT 1 FOR UPDATE");
            $chk->execute([(int)$test['id'], $studentId]);
            $existing = $chk->fetch();
            if ($existing && in_array($existing['status'], ['submitted','checked'], true)) {
                $pdo->commit();
                $startTransactionOpen = false;
                jt(['success'=>false, 'message'=>'You have already submitted this weekly exam. Open My Results.']);
            }
            if ($existing && $existing['status'] === 'started') {
                $accessToken = trim((string)($existing['access_token'] ?? '')) ?: bin2hex(random_bytes(32));
                $resultToken = trim((string)($existing['result_token'] ?? '')) ?: bin2hex(random_bytes(32));
                $pdo->prepare("UPDATE weekly_test_attempts SET access_token=?, result_token=? WHERE id=?")
                    ->execute([$accessToken, $resultToken, (int)$existing['id']]);
                $existing['access_token'] = $accessToken;
                $existing['result_token'] = $resultToken;
                $existing['duration_minutes'] = (int)($test['duration_minutes'] ?? 30);
                $existing['total_questions'] = (int)($test['total_questions'] ?? 30);
                $existing['shuffle_options'] = (string)($test['shuffle_options'] ?? 'Yes');
                $existing['shuffle_questions'] = (string)($test['shuffle_questions'] ?? 'Yes');
                $existing['title'] = (string)($test['title'] ?? 'Weekly Test');
                $existing['test_type'] = (string)($test['test_type'] ?? 'upcoming');
                $remaining = weekly_attempt_remaining_seconds($existing);
                $pdo->commit();
                $startTransactionOpen = false;

                if ($remaining <= 0) {
                    $finalized = weekly_test_finalize_attempt((int)$existing['id'], $accessToken, [], 'timer_expired');
                    jt(['success'=>false,
                        'message'=>$finalized['success'] ? 'Your exam time is over. Saved answers were submitted. Open My Results.' : $finalized['message'],
                        'result_url'=>$finalized['result_url'] ?? weekly_test_result_url($existing)],
                        $finalized['success'] ? 200 : 500);
                }
                $snapshot = weekly_api_snapshot($existing);
                $safe = array_map(function($q){ unset($q['expected']); return $q; }, $snapshot);
                jt(['success'=>true, 'attempt_id'=>(int)$existing['id'], 'access_token'=>$accessToken,
                    'resumed'=>true, 'remaining_seconds'=>$remaining,
                    'test'=>['id'=>(int)$test['id'], 'title'=>$test['title'], 'type'=>$test['test_type'],
                            'duration'=>$duration, 'instructions'=>$test['instructions']],
                    'questions'=>$safe, 'server_time'=>time()]);
            }

            $eligibility = weekly_test_upcoming_eligibility($studentId, (int)$test['id']);
            if (empty($eligibility['allowed'])) {
                $pdo->rollBack();
                $startTransactionOpen = false;
                jt([
                    'success'=>false,
                    'message'=>(string)($eligibility['message'] ?? 'This Upcoming Test is temporarily locked.'),
                    'available_at'=>$eligibility['available_at'] ?? null,
                    'wait_seconds'=>(int)($eligibility['wait_seconds'] ?? 0),
                ], 409);
            }
        }

        $questions = weekly_test_fetch_questions((int)$test['id'], 500);
        $questions = weekly_test_order_questions($questions, $test, null);
        $questions = array_slice($questions, 0, max(1, (int)($test['total_questions'] ?: 30)));
        if (!$questions) {
            if (!empty($startTransactionOpen) && db()->inTransaction()) db()->rollBack();
            jt(['success'=>false, 'message'=>'This test has no active questions.']);
        }

        $guestName = $studentId
            ? (trim((string)($activeStudent['full_name'] ?? 'Student')) ?: 'Student')
            : (trim((string)($_POST['guest_name'] ?? 'Guest Student')) ?: 'Guest Student');
        $guestPhone = $studentId
            ? weekly_test_clean_phone((string)($activeStudent['phone'] ?? ''))
            : weekly_test_clean_phone((string)($_POST['guest_phone'] ?? ''));
        if (!$studentId) {
            if (strlen($guestPhone) !== 10) jt(['success'=>false, 'message'=>'Enter a valid 10 digit mobile number.']);
            if (mb_strlen($guestName) < 2 || mb_strlen($guestName) > 100) jt(['success'=>false, 'message'=>'Enter a valid student name.']);
        }

        $snapshot = weekly_test_create_snapshot($questions, $test);
        $totalMarks = array_sum(array_map(fn($q)=>(float)$q['marks'], $snapshot));
        $accessToken = bin2hex(random_bytes(32));
        $resultToken = bin2hex(random_bytes(32));
        $orderIds = array_map(fn($q)=>(int)$q['id'], $snapshot);
        $stmt = db()->prepare("INSERT INTO weekly_test_attempts
            (test_id,student_id,guest_name,guest_phone,canonical_phone,started_at,expires_at,status,total_marks,
             access_token,result_token,question_order,question_snapshot)
            VALUES (?,?,?,?,?,NOW(),DATE_ADD(NOW(), INTERVAL ? MINUTE),'started',?,?,?,?,?)");
        $stmt->execute([(int)$test['id'], $studentId, $guestName, $guestPhone, weekly_test_clean_phone($guestPhone),
                        $duration, $totalMarks, $accessToken, $resultToken,
                        json_encode($orderIds), json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        $attemptId = (int)db()->lastInsertId();
        if (!empty($startTransactionOpen) && db()->inTransaction()) {
            db()->commit();
            $startTransactionOpen = false;
        }
        $safe = array_map(function($q){ unset($q['expected']); return $q; }, $snapshot);
        jt(['success'=>true, 'attempt_id'=>$attemptId, 'access_token'=>$accessToken,
            'remaining_seconds'=>$duration * 60,
            'test'=>['id'=>(int)$test['id'], 'title'=>$test['title'], 'type'=>$test['test_type'],
                    'duration'=>$duration, 'instructions'=>$test['instructions']],
            'questions'=>$safe, 'server_time'=>time()]);
    }

    if ($action === 'cancel_attempt') {
        $attemptId = (int)($_POST['attempt_id'] ?? 0);
        $token = trim((string)($_POST['access_token'] ?? ''));
        $attempt = weekly_api_attempt($attemptId, $token);
        if (!$attempt || ($attempt['status'] ?? '') !== 'started') jt(['success'=>false, 'message'=>'Invalid cancel request.'], 403);
        weekly_api_assert_owner($attempt);
        $line = date('Y-m-d H:i:s') . " - Candidate cancelled the test\n";
        $stmt = db()->prepare("UPDATE weekly_test_attempts SET status='cancelled', submitted_at=NOW(),
                               submission_reason='student_cancelled', activity_log=RIGHT(CONCAT(COALESCE(activity_log,''), ?),60000)
                               WHERE id=? AND access_token=? AND status='started'");
        $stmt->execute([$line, $attemptId, $token]);
        jt(['success'=>true, 'message'=>'Test cancelled.']);
    }

    if ($action === 'autosave' || $action === 'submit') {
        $attemptId = (int)($_POST['attempt_id'] ?? 0);
        $token = trim((string)($_POST['access_token'] ?? ''));
        $answers = weekly_api_sanitized_answers((string)($_POST['answers_json'] ?? '[]'));
        $attempt = weekly_api_attempt($attemptId, $token);
        if (!$attempt) jt(['success'=>false, 'message'=>'Attempt not found.'], 403);
        weekly_api_assert_owner($attempt);
        if (($attempt['status'] ?? '') !== 'started') jt(['success'=>false, 'message'=>'This test is already closed.'], 409);

        $snapshot = weekly_api_snapshot($attempt);
        if (!$snapshot) jt(['success'=>false, 'message'=>'Question snapshot is unavailable.'], 500);
        $allowedIds = array_fill_keys(array_map(fn($q)=>(int)$q['id'], $snapshot), true);
        $answers = array_intersect_key($answers, $allowedIds);
        $expired = weekly_attempt_remaining_seconds($attempt) <= 0
            || (!empty($attempt['expires_at']) && strtotime((string)$attempt['expires_at']) <= time());

        if ($action === 'autosave' && !$expired) {
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $lockedAttempt = weekly_test_fetch_attempt_record($attemptId, $token, true);
                if (!$lockedAttempt || ($lockedAttempt['status'] ?? '') !== 'started') {
                    $pdo->rollBack();
                    jt(['success'=>false, 'message'=>'This test is already closed.'], 409);
                }
                $ins = $pdo->prepare("INSERT INTO weekly_test_answers
                    (attempt_id,question_id,answer_text,is_correct,marks_awarded,admin_note)
                    VALUES (?,?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE answer_text=VALUES(answer_text), is_correct=VALUES(is_correct),
                                            marks_awarded=VALUES(marks_awarded), admin_note=VALUES(admin_note)");
                $saved = 0;
                foreach ($snapshot as $q) {
                    $qid = (int)$q['id'];
                    if (!array_key_exists($qid, $answers)) continue;
                    $answer = $answers[$qid];
                    $match = weekly_test_match_answer($answer, (string)($q['expected'] ?? ''));
                    $ins->execute([$attemptId, $qid, $answer, $match['is_correct'], null, $match['note']]);
                    $saved++;
                }
                $pdo->prepare("UPDATE weekly_test_attempts SET last_saved_at=NOW() WHERE id=? AND access_token=? AND status='started'")
                    ->execute([$attemptId, $token]);
                $pdo->commit();
                jt(['success'=>true, 'message'=>'Saved ' . $saved . ' answer(s).',
                    'remaining_seconds'=>weekly_attempt_remaining_seconds($lockedAttempt)]);
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $e;
            }
        }

        $reason = $expired ? 'timer_expired' : 'manual_submit';
        $finalized = weekly_test_finalize_attempt($attemptId, $token, $answers, $reason);
        if (empty($finalized['success'])) jt($finalized, 500);
        jt([
            'success'=>true,
            'message'=>$finalized['message'],
            'auto_score'=>$finalized['auto_score'] ?? 0,
            'penalty_marks'=>$finalized['penalty_marks'] ?? 0,
            'saved'=>$finalized['saved'] ?? 0,
            'expired'=>$expired,
            'already_closed'=>$finalized['already_closed'] ?? false,
            'result_url'=>$finalized['result_url'] ?? '',
        ]);
    }

    jt(['success'=>false, 'message'=>'Unknown action.'], 400);
} catch (Throwable $e) {
    if (isset($startTransactionOpen) && $startTransactionOpen) {
        try {
            $rollbackDb = db();
            if ($rollbackDb->inTransaction()) $rollbackDb->rollBack();
        } catch (Throwable $rollbackError) {
            error_log('[weekly-test-api-rollback] ' . $rollbackError->getMessage());
        }
    }
    error_log('[weekly-test-api] ' . $e->__toString());
    jt(['success'=>false, 'message'=>'Server error. Open Admin > System Check once.'], 500);
}
