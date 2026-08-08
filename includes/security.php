<?php
/** Shared HTTP security headers, private-cache controls and rate limiting. */

function app_security_headers(): void
{
    if (headers_sent()) return;
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Permitted-Cross-Domain-Policies: none');
    header('Permissions-Policy: camera=(), geolocation=(), microphone=(self)');
    header("Content-Security-Policy: frame-ancestors 'self'; base-uri 'self'; object-src 'none'; form-action 'self'");
    header('Cross-Origin-Opener-Policy: same-origin-allow-popups');
    if (function_exists('app_request_is_https') && app_request_is_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
app_security_headers();

function private_no_store(): void
{
    if (headers_sent()) return;
    header('Cache-Control: no-store, no-cache, must-revalidate, private, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('X-Robots-Tag: noindex, nofollow');
}

function client_ip(): string
{
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    if (defined('TRUST_PROXY_HEADERS') && TRUST_PROXY_HEADERS) {
        $forwarded = trim(explode(',', (string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''))[0] ?? '');
        if (filter_var($forwarded, FILTER_VALIDATE_IP)) $ip = $forwarded;
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
}

function security_rate_file(string $key): string
{
    $dir = dirname(__DIR__) . '/storage/rate-limits';
    if (!is_dir($dir)) @mkdir($dir, 0750, true);
    return $dir . '/' . hash('sha256', $key . '|' . client_ip()) . '.json';
}

function security_rate_is_auth_key(string $key): bool
{
    return str_starts_with($key, 'admin-login:')
        || str_starts_with($key, 'admin-mfa:')
        || str_starts_with($key, 'student-auth:')
        || str_starts_with($key, 'admin-setup:');
}

function security_rate_limit(string $key, int $maxAttempts = 30, int $windowSeconds = 60): bool
{
    $maxAttempts = max(1, $maxAttempts);
    $windowSeconds = max(1, $windowSeconds);
    $bucket = hash('sha256', $key . '|' . client_ip());
    $now = time();

    // Preferred storage: MySQL. This works across PHP workers/servers and is not disabled by a read-only filesystem.
    try {
        if (table_exists('security_rate_limits')) {
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('SELECT bucket_key, attempts, window_started_at, blocked_until FROM security_rate_limits WHERE bucket_key=? FOR UPDATE');
                $stmt->execute([$bucket]);
                $row = $stmt->fetch();
                if (!$row) {
                    $pdo->prepare('INSERT INTO security_rate_limits (bucket_key, attempts, window_started_at, updated_at) VALUES (?,1,NOW(),NOW())')->execute([$bucket]);
                    $pdo->commit();
                    return true;
                }

                $blockedUntil = !empty($row['blocked_until']) ? strtotime((string)$row['blocked_until']) : 0;
                if ($blockedUntil > $now) {
                    $pdo->commit();
                    return false;
                }

                $startedAt = strtotime((string)($row['window_started_at'] ?? '')) ?: $now;
                $attempts = max(0, (int)($row['attempts'] ?? 0));
                if (($startedAt + $windowSeconds) <= $now) {
                    $pdo->prepare('UPDATE security_rate_limits SET attempts=1, window_started_at=NOW(), blocked_until=NULL, updated_at=NOW() WHERE bucket_key=?')->execute([$bucket]);
                    $pdo->commit();
                    return true;
                }

                if ($attempts >= $maxAttempts) {
                    $remaining = max(1, ($startedAt + $windowSeconds) - $now);
                    $blockedAt = date('Y-m-d H:i:s', $now + $remaining);
                    $pdo->prepare('UPDATE security_rate_limits SET blocked_until=?, updated_at=NOW() WHERE bucket_key=?')->execute([$blockedAt, $bucket]);
                    $pdo->commit();
                    return false;
                }

                $pdo->prepare('UPDATE security_rate_limits SET attempts=attempts+1, updated_at=NOW() WHERE bucket_key=?')->execute([$bucket]);
                $pdo->commit();
                return true;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $e;
            }
        }
    } catch (Throwable $e) {
        error_log('[rate-limit-db] ' . $e->getMessage());
    }

    // Compatibility fallback for installations that have not imported the Phase 148 migration yet.
    // Authentication endpoints fail CLOSED if this fallback cannot be locked/written.
    $file = security_rate_file($key);
    $handle = @fopen($file, 'c+');
    if (!$handle) return !security_rate_is_auth_key($key);
    try {
        if (!flock($handle, LOCK_EX)) return !security_rate_is_auth_key($key);
        rewind($handle);
        $data = json_decode(stream_get_contents($handle) ?: '[]', true);
        $times = is_array($data) ? $data : [];
        $cutoff = $now - $windowSeconds;
        $times = array_values(array_filter(array_map('intval', $times), static fn($ts) => $ts >= $cutoff));
        if (count($times) >= $maxAttempts) return false;
        $times[] = $now;
        ftruncate($handle, 0);
        rewind($handle);
        $written = fwrite($handle, json_encode($times));
        fflush($handle);
        if ($written === false) return !security_rate_is_auth_key($key);
        return true;
    } finally {
        @flock($handle, LOCK_UN);
        @fclose($handle);
    }
}

function security_rate_limit_clear(string $key): void
{
    $bucket = hash('sha256', $key . '|' . client_ip());
    try {
        if (table_exists('security_rate_limits')) {
            db()->prepare('DELETE FROM security_rate_limits WHERE bucket_key=?')->execute([$bucket]);
        }
    } catch (Throwable $e) {
        error_log('[rate-limit-clear] ' . $e->getMessage());
    }
    $file = security_rate_file($key);
    if (is_file($file)) @unlink($file);
}

