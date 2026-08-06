#!/usr/bin/env python3
from __future__ import annotations
import json, os, re, subprocess
from pathlib import Path

root = Path(__file__).resolve().parents[1]
report: dict[str, object] = {}


def run(cmd: list[str]) -> tuple[int, str]:
    p = subprocess.run(cmd, cwd=root, text=True, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
    return p.returncode, p.stdout.strip()

php_files = sorted(p for p in root.rglob('*.php') if 'vendor' not in p.parts)
php_failures = []
for file in php_files:
    code, out = run(['php', '-l', str(file)])
    if code != 0:
        php_failures.append({'file': str(file.relative_to(root)), 'output': out})
report['php'] = {'count': len(php_files), 'passed': len(php_failures) == 0, 'failures': php_failures}

js_files = sorted(root.rglob('*.js')) + sorted(root.rglob('*.mjs'))
js_failures = []
for file in js_files:
    code, out = run(['node', '--check', str(file)])
    if code != 0:
        js_failures.append({'file': str(file.relative_to(root)), 'output': out})
report['javascript'] = {'count': len(js_files), 'passed': len(js_failures) == 0, 'failures': js_failures}

css_files = sorted(root.rglob('*.css'))
node_css = r'''
const fs=require('fs'); const postcss=require('postcss');
try { postcss.parse(fs.readFileSync(process.argv[1], 'utf8'), {from:process.argv[1]}); }
catch(e) { console.error(e.message); process.exit(1); }
'''
css_failures = []
for file in css_files:
    code, out = run(['node', '-e', node_css, str(file)])
    if code != 0:
        css_failures.append({'file': str(file.relative_to(root)), 'output': out})
report['css'] = {'count': len(css_files), 'passed': len(css_failures) == 0, 'failures': css_failures}

sql_files = sorted((root / 'sql').glob('*.sql'))
sql_text = sql_files[0].read_text(encoding='utf-8', errors='replace') if len(sql_files) == 1 else ''
tables = re.findall(r'CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+`([^`]+)`', sql_text, re.I)
report['database'] = {
    'sql_files': [p.name for p in sql_files],
    'canonical_only': len(sql_files) == 1 and sql_files[0].name == 'wellfare_english_complete.sql',
    'table_count': len(tables),
    'unique_table_count': len(set(tables)),
    'duplicate_tables': sorted({t for t in tables if tables.count(t) > 1}),
    'has_banner_columns': all(x in sql_text for x in ['desktop_image_url','mobile_image_url','content_position','overlay_strength']),
    'has_weekly_security_columns': all(x in sql_text for x in ['access_token','result_token','expires_at','question_snapshot','status_deleted']),
}

asset_refs: set[str] = set()
pattern = re.compile(r'''(?:href|src)=["']([^"'?#]+)(?:[?#][^"']*)?["']''', re.I)
for file in list(root.glob('*.php')) + list((root / 'includes').glob('*.php')):
    text = file.read_text(encoding='utf-8', errors='replace')
    for value in pattern.findall(text):
        if value.startswith(('http://','https://','mailto:','tel:','javascript:','data:','#','<?=','<?php')):
            continue
        if any(ch in value for ch in ['{','}','<','>']):
            continue
        candidate = (file.parent / value).resolve() if value.startswith('../') else (root / value.lstrip('/')).resolve()
        if candidate.suffix.lower() in {'.css','.js','.png','.jpg','.jpeg','.webp','.svg','.ico','.php','.webmanifest'}:
            asset_refs.add(str(candidate))
missing_assets = sorted(str(Path(p).relative_to(root)) if str(p).startswith(str(root)) else p for p in asset_refs if not Path(p).exists())
report['literal_assets'] = {'checked': len(asset_refs), 'missing': missing_assets, 'passed': not missing_assets}

browser_path = root / 'PHASE136_BROWSER_VALIDATION.json'
browser = json.loads(browser_path.read_text()) if browser_path.exists() else {}
report['browser_fixture'] = {'passed': browser.get('failed') == 0, 'passed_count': browser.get('passed', 0), 'failed_count': browser.get('failed', 1)}

page_path = root / 'PHASE136_PAGE_STATUS.json'
page_status = json.loads(page_path.read_text()) if page_path.exists() else {}
report['page_smoke'] = {'passed': page_status.get('failed') == 0, 'passed_count': page_status.get('passed', 0), 'failed_count': page_status.get('failed', 1)}
config_path = root / 'PHASE136_CONFIG_VALIDATION.json'
config_status = json.loads(config_path.read_text()) if config_path.exists() else {}
report['configuration'] = {'passed': config_status.get('failed') == 0, 'passed_count': config_status.get('passed', 0), 'failed_count': config_status.get('failed', 1)}

code, output = run(['php', 'tools/phase136-logic-check.php'])
report['logic'] = {'passed': code == 0, 'output': output}
code, output = run(['php', 'tools/phase136-functional-check.php'])
report['real_database_environment'] = {
    'passed': code == 0,
    'output': output,
    'limitation': None if code == 0 else 'This build container has no pdo_mysql/MySQL server. Run this checker on XAMPP or staging.'
}

report['overall_static_pass'] = all([
    report['php']['passed'], report['javascript']['passed'], report['css']['passed'],
    report['database']['canonical_only'], report['database']['unique_table_count'] == report['database']['table_count'],
    report['literal_assets']['passed'], report['browser_fixture']['passed'], report['page_smoke']['passed'], report['configuration']['passed'], report['logic']['passed'],
])

out = root / 'PHASE136_VALIDATION.json'
out.write_text(json.dumps(report, indent=2, ensure_ascii=False) + '\n', encoding='utf-8')
print(json.dumps({
    'php': report['php']['count'], 'js': report['javascript']['count'], 'css': report['css']['count'],
    'tables': report['database']['table_count'], 'browser_passed': report['browser_fixture']['passed_count'], 'pages_passed': report['page_smoke']['passed_count'], 'config_passed': report['configuration']['passed_count'],
    'overall_static_pass': report['overall_static_pass'],
    'real_db_pass': report['real_database_environment']['passed'],
}, indent=2))
raise SystemExit(0 if report['overall_static_pass'] else 1)
