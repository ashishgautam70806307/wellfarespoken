#!/usr/bin/env python3
from __future__ import annotations
import json, os, socket, subprocess, time, urllib.error, urllib.request
from pathlib import Path

root = Path(__file__).resolve().parents[1]
port = 18779
base = f'http://127.0.0.1:{port}/'
log_path = root / 'storage' / 'phase139-page-smoke.log'
log_file = log_path.open('w', encoding='utf-8')
process = subprocess.Popen([
    'php', '-d', f'auto_prepend_file={root / "tools" / "phase139-fixture-bootstrap.php"}',
    '-S', f'127.0.0.1:{port}', '-t', str(root)
], cwd=root, stdout=log_file, stderr=subprocess.STDOUT)

routes = [
    ('Home', 'index.php', 200),
    ('Courses', 'courses.php', 200),
    ('Course Detail', 'course-detail.php?id=1', 200),
    ('Online Class', 'online-class.php', 200),
    ('Spoken Materials', 'spoken-materials.php', 200),
    ('Learning Roadmap', 'learning-roadmap.php', 200),
    ('Roadmap Lesson', 'roadmap-lesson.php?id=1', 200),
    ('Basic Test', 'weekly-test.php?type=basic', 200),
    ('Previous Test', 'weekly-test.php?type=previous', 200),
    ('Upcoming Test', 'weekly-test.php?type=upcoming', 200),
    ('Student Login', 'student-auth.php', 200),
    ('Student Register', 'student-auth.php?mode=register', 200),
    ('Admission Batch Preselection', 'admission.php?mode=online&batch_id=1', 200),
    ('About', 'about.php', 200),
    ('Contact', 'contact.php', 200),
    ('Gallery', 'gallery.php', 200),
    ('Reviews', 'reviews.php', 200),
    ('Faculty Profile', 'faculty-profile.php?id=1', 200),
    ('PWA Check', 'pwa-check.php', 200),
    ('Institute Login', 'admin/login.php', 200),
    ('AI Teacher Hidden', 'ai-teacher.php', 302, 'spoken-materials.php'),
    ('Student Dashboard Protected', 'student-dashboard.php', 302, 'student-auth.php'),
    ('Student Revision Protected', 'student-revision.php', 302, 'student-auth.php'),
]

opener = urllib.request.build_opener(urllib.request.HTTPHandler())
results = []
try:
    for _ in range(50):
        try:
            urllib.request.urlopen(base + 'index.php', timeout=1).read(10)
            break
        except Exception:
            time.sleep(.1)
    for route_item in routes:
        label, route, expected, *redirect_hint = route_item
        redirect_hint = redirect_hint[0] if redirect_hint else ''
        request = urllib.request.Request(base + route, method='GET')
        try:
            response = opener.open(request, timeout=8)
            status = response.status
            body = response.read().decode('utf-8', 'replace')
            location = response.headers.get('Location', '')
        except urllib.error.HTTPError as exc:
            status = exc.code
            body = exc.read().decode('utf-8', 'replace')
            location = exc.headers.get('Location', '')
        # urllib follows redirects, so protected routes may arrive at login with 200.
        final_url = response.geturl()
        accepted = status == expected or (expected == 302 and status == 200 and redirect_hint != '' and redirect_hint in final_url)
        has_php_error = any(token in body for token in ['Fatal error', 'Uncaught Error', 'Warning: Undefined', 'Notice: Undefined'])
        results.append({
            'page': label, 'route': route, 'expected_status': expected, 'actual_status': status,
            'final_url': final_url, 'location': location,
            'status': 'PASS' if accepted and not has_php_error else 'FAIL',
            'php_error_in_body': has_php_error,
        })
finally:
    process.terminate()
    try: process.wait(timeout=3)
    except subprocess.TimeoutExpired: process.kill()
    log_file.close()

log_text = log_path.read_text(encoding='utf-8', errors='replace') if log_path.exists() else ''
log_issues = [line for line in log_text.splitlines() if any(x in line for x in ['PHP Warning', 'PHP Notice', 'PHP Fatal', 'Uncaught'])]
summary = {
    'generated_at': time.strftime('%Y-%m-%dT%H:%M:%S%z'),
    'passed': sum(r['status'] == 'PASS' for r in results),
    'failed': sum(r['status'] == 'FAIL' for r in results),
    'pages': results,
    'php_log_issues': log_issues,
    'removed_feature_files': {
        'free-ai-english-practice.php': not (root / 'free-ai-english-practice.php').exists(),
        'practice-quick-api.php': not (root / 'practice-quick-api.php').exists(),
        'practice-session-api.php': not (root / 'practice-session-api.php').exists(),
    }
}
if log_issues: summary['failed'] += 1
out = root / 'PHASE139_PAGE_STATUS.json'
out.write_text(json.dumps(summary, indent=2, ensure_ascii=False) + '\n', encoding='utf-8')
for item in results:
    print(f"{item['status']:<4} {item['page']:<32} {item['actual_status']} {item['route']}")
if log_issues:
    print('FAIL PHP log issues')
    for issue in log_issues[:10]: print('     ' + issue)
print(f"\n{summary['passed']} passed, {summary['failed']} failed")
raise SystemExit(0 if summary['failed'] == 0 else 1)
