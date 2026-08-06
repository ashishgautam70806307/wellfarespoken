#!/usr/bin/env python3
from __future__ import annotations
import http.cookiejar, json, os, re, subprocess, time, urllib.parse, urllib.request
from pathlib import Path

root = Path(__file__).resolve().parents[1]
port = int(os.environ.get('PHASE137_API_PORT', '18778'))
base = f'http://127.0.0.1:{port}/'
log_path = root / 'storage' / 'phase137-weekly-api.log'
log_path.parent.mkdir(parents=True, exist_ok=True)
log_file = log_path.open('w', encoding='utf-8')
process = subprocess.Popen([
    'php', '-d', f'auto_prepend_file={root / "tools" / "phase137-fixture-bootstrap.php"}',
    '-S', f'127.0.0.1:{port}', '-t', str(root)
], cwd=root, stdout=log_file, stderr=subprocess.STDOUT)

class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None

jar = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
no_redirect = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar), NoRedirect())
results = []

def check(name: str, ok: bool, detail: str = '') -> None:
    results.append({'name': name, 'status': 'PASS' if ok else 'FAIL', 'detail': detail})

def request(url: str, data: bytes | None = None, headers: dict[str,str] | None = None, redirect: bool = True):
    req = urllib.request.Request(url, data=data, headers=headers or {})
    try:
        return (opener if redirect else no_redirect).open(req, timeout=12)
    except urllib.error.HTTPError as exc:
        return exc

try:
    for _ in range(80):
        try:
            urllib.request.urlopen(base + 'weekly-test.php?type=basic', timeout=.5).read(20)
            break
        except Exception:
            time.sleep(.1)

    page = request(base + 'weekly-test.php?type=basic')
    html = page.read().decode('utf-8', 'replace')
    csrf_match = re.search(r'name="csrf_token"\s+value="([^"]+)"', html)
    test_match = re.search(r'<option\s+value="(\d+)"[^>]*selected', html)
    if not test_match:
        test_match = re.search(r'<option\s+value="(\d+)"', html)
    csrf = csrf_match.group(1) if csrf_match else ''
    test_id = test_match.group(1) if test_match else ''
    check('Weekly Test page supplies CSRF and paper ID', len(csrf) >= 32 and test_id.isdigit(), f'csrf={len(csrf)}, test_id={test_id}')

    form = urllib.parse.urlencode({
        'csrf_token': csrf,
        'action': 'start',
        'test_type': 'basic',
        'test_id': test_id,
        'guest_name': 'Phase 137 Student',
        'guest_phone': '9876543210',
    }).encode()
    ajax = request(base + 'weekly-test-api.php', form, {
        'Content-Type':'application/x-www-form-urlencoded',
        'X-Requested-With':'XMLHttpRequest',
        'Accept':'application/json',
    })
    payload = json.loads(ajax.read().decode('utf-8', 'replace'))
    check('Basic Test AJAX attempt starts', ajax.status == 200 and payload.get('success') is True and int(payload.get('attempt_id',0)) > 0 and len(payload.get('access_token','')) >= 32 and len(payload.get('questions',[])) > 0, json.dumps({k:payload.get(k) for k in ['success','attempt_id','remaining_seconds']}) )

    # New session for a normal form fallback request.
    jar.clear()
    page = request(base + 'weekly-test.php?type=basic')
    html = page.read().decode('utf-8', 'replace')
    csrf = re.search(r'name="csrf_token"\s+value="([^"]+)"', html).group(1)
    test_id = (re.search(r'<option\s+value="(\d+)"[^>]*selected', html) or re.search(r'<option\s+value="(\d+)"', html)).group(1)
    form = urllib.parse.urlencode({
        'csrf_token': csrf,
        'action': 'start',
        'test_type': 'basic',
        'test_id': test_id,
        'guest_name': 'Native Student',
        'guest_phone': '9876543211',
    }).encode()
    native = request(base + 'weekly-test-api.php', form, {'Content-Type':'application/x-www-form-urlencoded'}, redirect=False)
    location = native.headers.get('Location','')
    check('Basic Test normal POST fallback redirects to exam room', native.status == 303 and 'weekly-exam-room.php?attempt_id=' in location and '&token=' in location, f'{native.status} {location}')
finally:
    process.terminate()
    try: process.wait(timeout=3)
    except subprocess.TimeoutExpired: process.kill()
    log_file.close()

failed = [row for row in results if row['status'] == 'FAIL']
for row in results:
    print(f"{row['status']:<4} {row['name']} — {row['detail']}")
report = {'generated_at': time.strftime('%Y-%m-%dT%H:%M:%S%z'), 'passed': len(results)-len(failed), 'failed': len(failed), 'checks': results}
(root / 'PHASE137_WEEKLY_API_VALIDATION.json').write_text(json.dumps(report, indent=2), encoding='utf-8')
raise SystemExit(1 if failed else 0)
