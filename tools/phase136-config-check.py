#!/usr/bin/env python3
from __future__ import annotations
import json, os, subprocess
from pathlib import Path

root = Path(__file__).resolve().parents[1]
config = root / 'includes' / 'config.php'
code = r'''
$_SERVER['HTTP_HOST'] = getenv('CASE_HTTP_HOST') ?: '';
$_SERVER['SERVER_NAME'] = getenv('CASE_SERVER_NAME') ?: '';
if (getenv('CASE_X_FORWARDED_HOST')) $_SERVER['HTTP_X_FORWARDED_HOST'] = getenv('CASE_X_FORWARDED_HOST');
require %s;
echo json_encode([
  'runtime'=>APP_RUNTIME_ENV,
  'host'=>app_runtime_host(),
  'db_host'=>DB_HOST,
  'db_port'=>DB_PORT,
  'db_name'=>DB_NAME,
  'db_user'=>DB_USER,
  'has_password'=>DB_PASS !== '',
]);
''' % json.dumps(str(config))

base_env = {k:v for k,v in os.environ.items() if not (k.startswith('DB_') or k.startswith('APP_') or k in {'TRUST_PROXY_HEADERS','CASE_HTTP_HOST','CASE_SERVER_NAME','CASE_X_FORWARDED_HOST'})}

cases = [
    ('localhost auto', {'CASE_HTTP_HOST':'localhost:8080'}, {'runtime':'local','db_name':'wellfare_english','db_user':'root'}),
    ('127.0.0.1 auto', {'CASE_HTTP_HOST':'127.0.0.1:8080'}, {'runtime':'local','db_name':'wellfare_english','db_user':'root'}),
    ('live domain auto', {'CASE_HTTP_HOST':'spoken.example.com'}, {'runtime':'live','db_name':'u790281974_wellfarespoken','db_user':'u790281974_wellfarespoken'}),
    ('forced live', {'CASE_HTTP_HOST':'localhost','APP_RUNTIME_MODE':'live'}, {'runtime':'live','db_name':'u790281974_wellfarespoken'}),
    ('live profile override', {'CASE_HTTP_HOST':'spoken.example.com','DB_LIVE_NAME':'custom_live','DB_LIVE_USER':'custom_user','DB_LIVE_PASS':'secret'}, {'runtime':'live','db_name':'custom_live','db_user':'custom_user','has_password':True}),
    ('untrusted proxy ignored', {'CASE_HTTP_HOST':'spoken.example.com','CASE_X_FORWARDED_HOST':'localhost','TRUST_PROXY_HEADERS':'false'}, {'runtime':'live','host':'spoken.example.com'}),
    ('trusted proxy host', {'CASE_HTTP_HOST':'internal.local','CASE_X_FORWARDED_HOST':'localhost','TRUST_PROXY_HEADERS':'true'}, {'runtime':'local','host':'localhost'}),
    ('production CLI hint', {'APP_ENV':'production'}, {'runtime':'live','db_name':'u790281974_wellfarespoken'}),
]
results=[]
for name, extra, expected in cases:
    env=base_env.copy(); env.update(extra)
    p=subprocess.run(['php','-d','display_errors=0','-r',code],cwd=root,env=env,text=True,stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    try: data=json.loads(p.stdout)
    except Exception: data={'raw':p.stdout,'stderr':p.stderr}
    ok=p.returncode==0 and all(data.get(k)==v for k,v in expected.items())
    results.append({'name':name,'status':'PASS' if ok else 'FAIL','expected':expected,'actual':data,'stderr':p.stderr.strip()})

summary={'passed':sum(r['status']=='PASS' for r in results),'failed':sum(r['status']=='FAIL' for r in results),'cases':results}
(root/'PHASE136_CONFIG_VALIDATION.json').write_text(json.dumps(summary,indent=2,ensure_ascii=False)+'\n',encoding='utf-8')
for r in results: print(f"{r['status']:<4} {r['name']}: {r['actual']}")
print(f"\n{summary['passed']} passed, {summary['failed']} failed")
raise SystemExit(0 if summary['failed']==0 else 1)
