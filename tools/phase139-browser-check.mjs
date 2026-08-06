#!/usr/bin/env node
import { spawn } from 'node:child_process';
import { mkdtemp, rm, writeFile, readFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';
import process from 'node:process';

const root = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const phpPort = Number(process.env.PHASE139_PHP_PORT || 18775);
const chromePort = Number(process.env.PHASE139_CHROME_PORT || 19232);
const base = `http://127.0.0.1:${phpPort}`;
const rootFileUrl = new URL(`file://${root.endsWith('/') ? root : root + '/'}`);
const results = [];
const consoleIssues = [];
const networkIssues = [];
const phpLog = [];
let currentRoute = '';
let php;
let chrome;
let tempDir;

const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));
const check = (name, ok, details = '') => results.push({ name, status: ok ? 'PASS' : 'FAIL', details: String(details || '') });

async function waitForHttp(url, timeout = 15000) {
  const start = Date.now();
  while (Date.now() - start < timeout) {
    try {
      const response = await fetch(url, { redirect: 'manual' });
      if (response.status > 0) return;
    } catch {}
    await sleep(150);
  }
  throw new Error(`Timed out waiting for ${url}`);
}

class CDP {
  constructor(url) {
    this.ws = new WebSocket(url);
    this.id = 0;
    this.pending = new Map();
    this.listeners = new Map();
    this.ready = new Promise((resolve, reject) => {
      this.ws.addEventListener('open', resolve, { once: true });
      this.ws.addEventListener('error', reject, { once: true });
    });
    this.ws.addEventListener('message', event => {
      const message = JSON.parse(event.data);
      if (message.id) {
        const pending = this.pending.get(message.id);
        if (!pending) return;
        this.pending.delete(message.id);
        if (message.error) pending.reject(new Error(message.error.message || JSON.stringify(message.error)));
        else pending.resolve(message.result || {});
        return;
      }
      const handlers = this.listeners.get(message.method) || [];
      for (const handler of [...handlers]) handler(message.params || {});
    });
  }
  async send(method, params = {}) {
    await this.ready;
    const id = ++this.id;
    const promise = new Promise((resolve, reject) => this.pending.set(id, { resolve, reject }));
    this.ws.send(JSON.stringify({ id, method, params }));
    return promise;
  }
  on(method, handler) {
    const handlers = this.listeners.get(method) || [];
    handlers.push(handler);
    this.listeners.set(method, handlers);
    return () => this.listeners.set(method, (this.listeners.get(method) || []).filter(item => item !== handler));
  }
  once(method, timeout = 15000) {
    return new Promise((resolve, reject) => {
      const timer = setTimeout(() => { off(); reject(new Error(`Timed out waiting for ${method}`)); }, timeout);
      const off = this.on(method, params => { clearTimeout(timer); off(); resolve(params); });
    });
  }
  close() { this.ws.close(); }
}

async function evaluate(client, expression) {
  const response = await client.send('Runtime.evaluate', {
    expression,
    awaitPromise: true,
    returnByValue: true,
    userGesture: true,
  });
  if (response.exceptionDetails) throw new Error(response.exceptionDetails.text || 'Runtime evaluation failed');
  return response.result?.value;
}

async function setViewport(client, width, height, mobile = false) {
  await client.send('Emulation.setDeviceMetricsOverride', {
    width, height, deviceScaleFactor: 1, mobile,
    screenWidth: width, screenHeight: height,
  });
  await client.send('Emulation.setTouchEmulationEnabled', { enabled: mobile, maxTouchPoints: mobile ? 5 : 1 });
}

function localAssetPath(reference, fromFile = '') {
  const clean = String(reference || '').split('#')[0].split('?')[0];
  if (!clean || /^(?:https?:|data:|mailto:|tel:|javascript:|#)/i.test(clean)) return null;
  const baseDir = fromFile ? path.dirname(fromFile) : root;
  const resolved = path.resolve(baseDir, clean.replace(/^\/+/, ''));
  return resolved.startsWith(root) ? resolved : null;
}

function rewriteCssUrls(css, cssFile) {
  return css.replace(/url\((['"]?)([^)'"\s]+)\1\)/g, (full, quote, value) => {
    if (/^(?:data:|https?:|#)/i.test(value)) return full;
    const resolved = path.resolve(path.dirname(cssFile), value);
    return `url("${new URL(`file://${resolved}`).href}")`;
  });
}

async function buildBrowserHtml(route) {
  const response = await fetch(`${base}/${route}`);
  let html = await response.text();
  const stylePattern = /<link\b([^>]*?)href=["']([^"']+)["']([^>]*)>/gi;
  const styleReplacements = [];
  for (const match of html.matchAll(stylePattern)) {
    if (!/rel=["']stylesheet["']/i.test(match[0])) continue;
    const file = localAssetPath(match[2]);
    if (!file) {
      styleReplacements.push([match[0], '']);
      continue;
    }
    try {
      const css = rewriteCssUrls(await readFile(file, 'utf8'), file);
      styleReplacements.push([match[0], `<style data-source="${path.basename(file)}">${css}</style>`]);
    } catch {
      styleReplacements.push([match[0], '']);
    }
  }
  for (const [from, to] of styleReplacements) html = html.replace(from, to);

  const scriptPattern = /<script\b([^>]*?)src=["']([^"']+)["']([^>]*)><\/script>/gi;
  const scriptReplacements = [];
  for (const match of html.matchAll(scriptPattern)) {
    const file = localAssetPath(match[2]);
    if (!file) {
      scriptReplacements.push([match[0], '']);
      continue;
    }
    try {
      const js = await readFile(file, 'utf8');
      scriptReplacements.push([match[0], `<script data-source="${path.basename(file)}">${js.replace(/<\/script/gi, '<\\/script')}</script>`]);
    } catch {
      scriptReplacements.push([match[0], '']);
    }
  }
  for (const [from, to] of scriptReplacements) html = html.replace(from, to);

  const fileize = value => {
    const file = localAssetPath(value);
    return file ? new URL(`file://${file}`).href : value;
  };
  html = html.replace(/\b(src|poster)=["']([^"']+)["']/gi, (full, attr, value) => `${attr}="${fileize(value)}"`);
  html = html.replace(/\bsrcset=["']([^"']+)["']/gi, (full, value) => {
    const parts = value.split(',').map(part => {
      const [url, descriptor] = part.trim().split(/\s+/, 2);
      return `${fileize(url)}${descriptor ? ` ${descriptor}` : ''}`;
    });
    return `srcset="${parts.join(', ')}"`;
  });

  const fetchFixture = `<script>
  (() => {
    const materialPayload = {success:true, csrf:'fixture-csrf', collection_id:1, unit_id:0, goal:'speak', direction:'hindi_to_english', units:[{id:1,title:'Daily Conversation'}], items:[
      {id:1,hindi:'मैं रोज अंग्रेजी बोलता हूँ।',english:'I speak English every day.',roman:'Main roz English bolta hoon.',topic:'Present Simple',tag:'Daily Speaking',level:'Beginner',explanation:'Use present simple for habits.',sentence_type:'Simple',teacher_hint:'Use the base verb speak.'},
      {id:2,hindi:'मैं तैयार हूँ।',english:'I am ready.',roman:'Main taiyar hoon.',topic:'Is Am Are',tag:'Daily Speaking',level:'Beginner',explanation:'Use am with I.',sentence_type:'Simple',teacher_hint:'Use am with I.'}
    ], count:2};
    const nativeFetch = window.fetch?.bind(window);
    window.fetch = async (input, init = {}) => {
      const url = String(input instanceof Request ? input.url : input);
      if (url.includes('material-practice-list-api.php')) return new Response(JSON.stringify(materialPayload), {status:200, headers:{'Content-Type':'application/json'}});
      if (url.includes('material-practice-api.php')) return new Response(JSON.stringify({success:true,correct:true,message:'Correct answer',score:100}), {status:200, headers:{'Content-Type':'application/json'}});
      if (url.includes('roadmap-progress-api.php')) return new Response(JSON.stringify({success:true,completed_unit_ids:[1]}), {status:200, headers:{'Content-Type':'application/json'}});
      if (url.includes('weekly-test-api.php')) return new Response(JSON.stringify({success:true,attempt_id:901,access_token:'a'.repeat(64),questions:[{id:1}],remaining_seconds:1200}), {status:200, headers:{'Content-Type':'application/json'}});
      if (nativeFetch && /^(?:data:|file:)/.test(url)) return nativeFetch(input, init);
      return new Response(JSON.stringify({success:false,message:'Fixture endpoint not configured.'}), {status:404, headers:{'Content-Type':'application/json'}});
    };
    if (!navigator.serviceWorker) return;
    try { Object.defineProperty(navigator, 'serviceWorker', {value:{register:async()=>({}), addEventListener:()=>{}}, configurable:true}); } catch {}
  })();
  </script>`;
  html = html.replace(/<head([^>]*)>/i, `<head$1><base href="${rootFileUrl.href}">${fetchFixture}`);
  return { html, status: response.status };
}

async function navigate(client, route) {
  currentRoute = route;
  const { html, status } = await buildBrowserHtml(route);
  const loaded = client.once('Page.loadEventFired', 10000).catch(() => null);
  await client.send('Page.navigate', { url: 'about:blank' });
  await loaded;
  const tree = await client.send('Page.getFrameTree');
  const frameId = tree.frameTree?.frame?.id;
  if (!frameId) throw new Error('Main frame unavailable');
  await client.send('Page.setDocumentContent', { frameId, html });
  await sleep(900);
  const body = await evaluate(client, `({
    title: document.title,
    text: document.body ? document.body.innerText.slice(0, 4000) : '',
    ready: document.readyState,
    width: document.documentElement.scrollWidth,
    client: document.documentElement.clientWidth
  })`);
  check(`${route}: HTTP/DOM loaded`, status === 200 && !/Fatal error|Uncaught Error|Warning: Undefined/.test(body.text), `${status}; ${body.title}; ${body.width}/${body.client}`);
  check(`${route}: no horizontal overflow`, body.width <= body.client + 3, `${body.width}/${body.client}`);
  return body;
}

async function runBrowserChecks() {
  tempDir = await mkdtemp(path.join(tmpdir(), 'phase139-chrome-'));
  const prepend = path.join(root, 'tools', 'phase139-fixture-bootstrap.php');
  php = spawn('php', ['-d', `auto_prepend_file=${prepend}`, '-S', `127.0.0.1:${phpPort}`, '-t', root], { cwd: root });
  php.stdout.on('data', data => phpLog.push(String(data)));
  php.stderr.on('data', data => phpLog.push(String(data)));
  await waitForHttp(`${base}/index.php`);

  chrome = spawn('/usr/bin/chromium', [
    '--headless=new', '--no-sandbox', '--disable-gpu', '--disable-dev-shm-usage',
    `--remote-debugging-port=${chromePort}`, `--user-data-dir=${tempDir}`,
    '--disable-background-networking', '--disable-default-apps', '--disable-extensions',
    '--disable-sync', '--metrics-recording-only', '--mute-audio', '--allow-file-access-from-files', '--no-proxy-server', 'about:blank'
  ]);
  chrome.stdout.on('data', () => {});
  chrome.stderr.on('data', () => {});
  await waitForHttp(`http://127.0.0.1:${chromePort}/json/list`);
  const targets = await (await fetch(`http://127.0.0.1:${chromePort}/json/list`)).json();
  const target = targets.find(item => item.type === 'page');
  if (!target?.webSocketDebuggerUrl) throw new Error('Chromium page target unavailable');

  const client = new CDP(target.webSocketDebuggerUrl);
  await client.ready;
  await Promise.all([
    client.send('Page.enable'), client.send('Runtime.enable'), client.send('Network.enable'), client.send('Log.enable')
  ]);
  client.on('Runtime.exceptionThrown', event => { const d=event.exceptionDetails || {}; consoleIssues.push({ type:'exception', route:currentRoute, text:d.exception?.description || d.text || 'Runtime exception', url:d.url || '', line:d.lineNumber ?? null, column:d.columnNumber ?? null, stack:(d.stackTrace?.callFrames || []).slice(0,4) }); });
  client.on('Log.entryAdded', event => {
    const entry = event.entry || {};
    if (['error', 'warning'].includes(entry.level)) consoleIssues.push({ type: entry.level, text: entry.text || '', url: entry.url || '' });
  });
  client.on('Network.responseReceived', event => {
    const response = event.response || {};
    if (response.status >= 400 && response.url?.startsWith(base)) networkIssues.push({ status: response.status, url: response.url });
  });
  client.on('Network.loadingFailed', event => {
    if (!String(event.errorText || '').includes('ERR_ABORTED')) networkIssues.push({ status: 'FAILED', url: event.blockedReason || event.errorText || '' });
  });

  await setViewport(client, 1366, 900, false);
  await navigate(client, 'index.php');
  const home = await evaluate(client, `({
    slides: document.querySelectorAll('[data-home-slide]').length,
    active: Array.from(document.querySelectorAll('[data-home-slide]')).findIndex(x => x.classList.contains('is-active')),
    rows: document.querySelectorAll('.wf127-review-row').length,
    cards: document.querySelectorAll('.wf127-review-card').length,
    animations: Array.from(document.querySelectorAll('.wf127-review-row')).map(x => getComputedStyle(x).animationName),
    h1Color: (() => { const heading=document.querySelector('.wf126-slide.is-active h1, .wf126-slide.is-active h2'); return heading ? getComputedStyle(heading).color : ''; })(),
    h1Visible: !!document.querySelector('.wf126-slide.is-active h1, .wf126-slide.is-active h2')?.getClientRects().length
  })`);
  check('Home: dynamic responsive banners render', home.slides >= 2 && home.active === 0, JSON.stringify(home));
  check('Home: hero heading is visible', home.h1Visible && home.h1Color !== 'rgb(0, 0, 0)', home.h1Color);
  check('Home: reviews rows and cards render', home.rows === 2 && home.cards >= 8, `${home.rows} rows / ${home.cards} cards`);
  check('Home: reviews auto animation is active', home.animations.every(name => name && name !== 'none'), home.animations.join(', '));
  const reviewSizing = await evaluate(client, `Array.from(document.querySelectorAll('.wf127-review-row')).map(row => ({distance:getComputedStyle(row).getPropertyValue('--wf-review-distance-negative').trim(), duration:getComputedStyle(row).animationDuration}))`);
  check('Home: reviews use measured seamless distance', reviewSizing.length === 2 && reviewSizing.every(item => /^-\d+px$/.test(item.distance) && item.duration !== '0s'), JSON.stringify(reviewSizing));
  const footer = await evaluate(client, `({logoVisible:!!document.querySelector('.wf131-footer-logo img')?.getClientRects().length,socials:document.querySelectorAll('.wf131-footer-socials a').length,phone:(document.querySelector('.wf131-footer-contact a[href^="tel:"] b')?.textContent||'').trim(),links:document.querySelectorAll('.wf131-footer-links a').length})`);
  check('Footer: logo, dynamic social icons and useful links render', footer.logoVisible && footer.socials >= 5 && footer.phone !== '' && footer.links >= 12, JSON.stringify(footer));
  const slideChanged = await evaluate(client, `(async () => {
    const before = Array.from(document.querySelectorAll('[data-home-slide]')).findIndex(x => x.classList.contains('is-active'));
    document.querySelector('[data-home-next]')?.click();
    await new Promise(r => setTimeout(r, 400));
    const after = Array.from(document.querySelectorAll('[data-home-slide]')).findIndex(x => x.classList.contains('is-active'));
    return {before, after};
  })()`);
  check('Home: banner next control works', slideChanged.after !== slideChanged.before, JSON.stringify(slideChanged));

  const dropdown = await evaluate(client, `(async () => {
    const trigger = document.querySelector('.wf127-nav-trigger');
    trigger?.click(); await new Promise(r => setTimeout(r, 150));
    const group = trigger?.closest('.wf127-nav-group');
    const panel = group?.querySelector('.wf127-mega-panel');
    const open = trigger?.getAttribute('aria-expanded') === 'true' && panel && getComputedStyle(panel).visibility !== 'hidden';
    document.dispatchEvent(new KeyboardEvent('keydown', {key:'Escape', bubbles:true}));
    await new Promise(r => setTimeout(r, 100));
    return {open, closed: trigger?.getAttribute('aria-expanded') === 'false'};
  })()`);
  check('Navigation: desktop dropdown opens and closes', dropdown.open && dropdown.closed, JSON.stringify(dropdown));

  await navigate(client, 'courses.php');
  const courses = await evaluate(client, `Array.from(document.querySelectorAll('.course-card')).map(card => ({buttons:card.querySelectorAll('.course-actions .wf-btn').length, visible:Array.from(card.querySelectorAll('.course-actions .wf-btn')).every(b => b.getClientRects().length > 0)}))`);
  check('Courses: every card has visible actions', courses.length >= 3 && courses.every(item => item.buttons >= 2 && item.visible), JSON.stringify(courses));
  const courseButtonStyle = await evaluate(client, `Array.from(document.querySelectorAll('.course-actions .wf-btn')).map(button => ({radius:parseFloat(getComputedStyle(button).borderRadius),height:button.getBoundingClientRect().height,after:getComputedStyle(button,'::after').backgroundColor}))`);
  check('Courses: buttons use Student Login CTA structure', courseButtonStyle.length >= 6 && courseButtonStyle.every(item => item.radius >= 20 && item.height >= 44 && item.after !== 'rgba(0, 0, 0, 0)'), JSON.stringify(courseButtonStyle));

  await navigate(client, 'weekly-test.php');
  const testCenter = await evaluate(client, `({cards:document.querySelectorAll('[data-test-card]').length, types:Array.from(document.querySelectorAll('[data-test-card]')).map(x=>x.dataset.testCard), setupHidden:document.querySelector('#wfTestSetup')?.hidden})`);
  check('Weekly Test: Basic/Previous/Upcoming render', testCenter.cards === 3 && ['basic','previous','upcoming'].every(type => testCenter.types.includes(type)), JSON.stringify(testCenter));
  const testSetup = await evaluate(client, `(async () => {
    const link = document.querySelector('[data-select-test="basic"]');
    link?.click(); await new Promise(r => setTimeout(r, 250));
    const form = document.querySelector('#wfTestSetup');
    return {visible:!!form && !form.hidden && getComputedStyle(form).display !== 'none', type:document.querySelector('#wfSelectedTestTypeInput')?.value, options:document.querySelectorAll('#wfTestPaper option').length};
  })()`);
  check('Weekly Test: card opens working setup', testSetup.visible && testSetup.type === 'basic' && testSetup.options >= 1, JSON.stringify(testSetup));
  const startAttempt = await evaluate(client, `(async () => {
    const form=document.querySelector('#wfTestSetup');
    document.querySelector('#wfGuestName').value='Browser Student';
    document.querySelector('#wfGuestPhone').value='9876543210';
    const body=new FormData(form); body.set('action','start'); body.set('test_id',document.querySelector('#wfTestPaper').value); body.set('test_type','basic'); body.set('guest_name','Browser Student'); body.set('guest_phone','9876543210');
    const response=await fetch('weekly-test-api.php',{method:'POST',body,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
    const result=await response.json(); return {status:response.status,success:!!result.success,attempt:Number(result.attempt_id||0),questions:Array.isArray(result.questions)?result.questions.length:0,token:String(result.access_token||'').length,testId:body.get('test_id'),message:String(result.message||'')};
  })()`);
  check('Weekly Test: Basic attempt API actually starts', startAttempt.status === 200 && startAttempt.success && startAttempt.attempt > 0 && startAttempt.questions > 0 && startAttempt.token >= 32, JSON.stringify(startAttempt));

  await navigate(client, 'admission.php?mode=online&batch_id=1');
  const admission = await evaluate(client, `({batch:document.querySelector('#admissionBatch')?.value, course:document.querySelector('#admissionCourse')?.value, hiddenBatch:document.querySelector('input[name="batch_id"]')?.value, fields:document.querySelectorAll('.wf129-admission-form input, .wf129-admission-form select, .wf129-admission-form textarea').length})`);
  check('Admission: online batch auto-selected', admission.hiddenBatch === '1' && admission.batch.includes('Morning Online Batch'), JSON.stringify(admission));
  check('Admission: related course auto-selected', admission.course === 'Basic Spoken English', admission.course);
  const admissionFields = await evaluate(client, `Array.from(document.querySelectorAll('.wf129-admission-form input:not([type=hidden]),.wf129-admission-form select,.wf129-admission-form textarea')).map(field => {const s=getComputedStyle(field),r=field.getBoundingClientRect();return {radius:parseFloat(s.borderRadius),height:r.height,placeholder:s.fontSize,width:r.width,left:r.left,right:r.right,viewport:innerWidth}})`);
  check('Admission: universal fields are smooth and non-overlapping', admissionFields.length >= 6 && admissionFields.every(item => item.radius >= 12 && item.height >= 48 && item.width > 150 && item.left >= 0 && item.right <= item.viewport + 1), JSON.stringify(admissionFields));

  await navigate(client, 'learning-roadmap.php');
  const roadmap = await evaluate(client, `({stages:document.querySelectorAll('.rm126-stage').length, steps:document.querySelectorAll('.rm126-step').length, nodes:document.querySelectorAll('.rm126-node').length, process:document.querySelectorAll('.rm126-how span').length})`);
  check('Roadmap: path, stages and process render', roadmap.stages >= 2 && roadmap.steps >= 3 && roadmap.nodes === roadmap.steps && roadmap.process >= 4, JSON.stringify(roadmap));

  await navigate(client, 'roadmap-lesson.php?id=1');
  const lesson = await evaluate(client, `({tabs:Array.from(document.querySelectorAll('.duo-lesson-tabs button')).map(button=>({height:button.getBoundingClientRect().height,active:button.classList.contains('active')})),shell:document.querySelector('.duo-lesson-shell')?.getBoundingClientRect().width||0,panel:!!document.querySelector('.duo-panel.active')?.getClientRects().length,overflow:document.documentElement.scrollWidth-document.documentElement.clientWidth})`);
  check('Roadmap Lesson: compact three-tab workspace renders', lesson.tabs.length === 3 && lesson.tabs.filter(x=>x.active).length === 1 && lesson.tabs.every(x=>x.height <= 54) && lesson.shell > 700 && lesson.panel && lesson.overflow <= 3, JSON.stringify(lesson));

  await navigate(client, 'student-auth.php?mode=register');
  const register = await evaluate(client, `(() => {const grid=document.querySelector('.auth-grid'),copy=document.querySelector('.auth-copy'),card=document.querySelector('.auth-card'),input=document.querySelector('.auth-form-grid input:not([type=hidden])');const g=grid?.getBoundingClientRect(),c=copy?.getBoundingClientRect(),a=card?.getBoundingClientRect(),s=input?getComputedStyle(input):null;return {grid:g?.width||0,copy:c?.width||0,card:a?.width||0,fields:document.querySelectorAll('.auth-form-grid input:not([type=hidden]),.auth-form-grid select').length,radius:s?parseFloat(s.borderRadius):0,height:input?.getBoundingClientRect().height||0,overflow:document.documentElement.scrollWidth-document.documentElement.clientWidth}})()`);
  check('Student Register: balanced two-panel form and universal inputs', register.fields >= 7 && register.copy > 280 && register.card > register.copy && register.radius >= 12 && register.height >= 48 && register.overflow <= 3, JSON.stringify(register));

  await navigate(client, 'about.php');
  const about = await evaluate(client, `({cards:Array.from(document.querySelectorAll('.about-mini-card-v2')).map(card=>({height:card.getBoundingClientRect().height,gap:getComputedStyle(card).columnGap})),director:!!document.querySelector('.director-detail-grid-v2')?.getClientRects().length})`);
  check('About: cards have consistent professional spacing', about.cards.length >= 3 && about.cards.every(card => card.height >= 150 && parseFloat(card.gap) >= 12) && about.director, JSON.stringify(about));

  await navigate(client, 'contact.php');
  const contact = await evaluate(client, `Array.from(document.querySelectorAll('.contact-mini-card-v2 .wf-btn')).map(button=>({visible:!!button.getClientRects().length,radius:parseFloat(getComputedStyle(button).borderRadius),after:getComputedStyle(button,'::after').backgroundColor,width:button.getBoundingClientRect().width}))`);
  check('Contact: all actions use common Student Login button structure', contact.length >= 3 && contact.every(item => item.visible && item.radius >= 20 && item.after !== 'rgba(0, 0, 0, 0)' && item.width > 140), JSON.stringify(contact));

  await navigate(client, 'spoken-materials.php');
  await sleep(1000);
  const materials = await evaluate(client, `({tabs:document.querySelectorAll('.goal-tab').length, filters:document.querySelectorAll('.material-filter select, .material-filter input, .ajax-filters select, .ajax-filters input').length, cards:document.querySelectorAll('.material-ajax-card').length, error:document.body.innerText.includes('Unable to load')})`);
  check('Materials: practice controls render', materials.tabs >= 4, JSON.stringify(materials));
  check('Materials: practice API populates cards', materials.cards >= 1 && !materials.error, JSON.stringify(materials));

  await navigate(client, 'gallery.php');
  const gallery = await evaluate(client, `(async () => {
    const opener=document.querySelector('[data-gallery-open]'); opener?.click(); await new Promise(r=>setTimeout(r,200));
    const dialog=document.querySelector('#wfGalleryLightbox');
    const open=!!dialog && (dialog.open || dialog.hasAttribute('open'));
    const before=document.querySelector('#wfGalleryCounter')?.textContent;
    document.querySelector('[data-gallery-next]')?.click(); await new Promise(r=>setTimeout(r,100));
    const after=document.querySelector('#wfGalleryCounter')?.textContent;
    return {items:document.querySelectorAll('[data-gallery-open]').length, open, before, after};
  })()`);
  check('Gallery: lightbox opens and next works', gallery.items >= 2 && gallery.open && gallery.before !== gallery.after, JSON.stringify(gallery));

  await setViewport(client, 390, 844, true);
  await navigate(client, 'index.php');
  const mobile = await evaluate(client, `(async () => {
    const topbar=document.querySelector('.wf127-topbar');
    const button=document.querySelector('[data-drawer-open]'); button?.click(); await new Promise(r=>setTimeout(r,180));
    const drawer=document.querySelector('[data-mobile-drawer]');
    return {
      topbarVisible:!!topbar && getComputedStyle(topbar).display !== 'none' && topbar.getClientRects().length > 0,
      drawerOpen:drawer?.getAttribute('aria-hidden') === 'false' || document.documentElement.classList.contains('wf-drawer-open') || document.body.classList.contains('wf-drawer-open'),
      drawerWidth:drawer?.getBoundingClientRect().width || 0,
      overflow:document.documentElement.scrollWidth - document.documentElement.clientWidth
    };
  })()`);
  check('Mobile: topbar remains visible', mobile.topbarVisible, JSON.stringify(mobile));
  check('Mobile: drawer opens in balanced width', mobile.drawerOpen && mobile.drawerWidth > 250 && mobile.drawerWidth <= 390, JSON.stringify(mobile));
  check('Mobile: no horizontal overflow', mobile.overflow <= 3, String(mobile.overflow));
  const mobileMenuButton = await evaluate(client, `(() => {const b=document.querySelector('[data-drawer-open]');const s=b?getComputedStyle(b):null;return {background:s?.backgroundColor||'',borderTop:s?.borderTopWidth||'',borderRight:s?.borderRightWidth||'',width:b?.getBoundingClientRect().width||0}})()`);
  check('Mobile: menu trigger has no background or border', mobileMenuButton.background === 'rgba(0, 0, 0, 0)' && parseFloat(mobileMenuButton.borderTop||'0') === 0 && parseFloat(mobileMenuButton.borderRight||'0') === 0, JSON.stringify(mobileMenuButton));

  await navigate(client, 'courses.php');
  const mobileCourseButtons = await evaluate(client, `Array.from(document.querySelectorAll('.course-actions .wf-btn')).map(button=>{const label=button.querySelector('.wf-btn-label');const br=button.getBoundingClientRect(),lr=label?.getBoundingClientRect();return {text:(label?.textContent||'').trim(),buttonWidth:br.width,labelWidth:lr?.width||0,labelLeft:lr?.left||0,labelRight:lr?.right||0,buttonLeft:br.left,buttonRight:br.right,height:br.height}})`);
  check('Mobile: course button labels are fully visible', mobileCourseButtons.length >= 6 && mobileCourseButtons.every(x=>x.text && x.labelLeft >= x.buttonLeft-1 && x.labelRight <= x.buttonRight-31 && x.height >= 43), JSON.stringify(mobileCourseButtons));

  await navigate(client, 'roadmap-lesson.php?id=3');
  const mobilePractice = await evaluate(client, `(async()=>{const tab=document.querySelector('[data-tab="practice"]');tab?.click();await new Promise(r=>setTimeout(r,160));document.querySelector('#duoStartPractice')?.click();await new Promise(r=>setTimeout(r,120));const choices=Array.from(document.querySelectorAll('.duo-choice'));const tops=[...new Set(choices.map(x=>Math.round(x.getBoundingClientRect().top)))];choices[0]?.click();await new Promise(r=>setTimeout(r,100));const panel=document.querySelector('[data-panel="practice"]');const sound=document.querySelector('.duo-sound-row');const next=document.querySelector('#duoNextQuestion');return {mode:document.querySelector('.duo-lesson-page')?.classList.contains('is-practice-mode'),choices:choices.length,rows:tops.length,panelTop:panel?.getBoundingClientRect().top||0,panelBottom:panel?.getBoundingClientRect().bottom||0,soundHeight:sound?.getBoundingClientRect().height||0,actionVisible:!!next&&!next.hidden&&next.getClientRects().length>0,overflow:document.documentElement.scrollWidth-document.documentElement.clientWidth}})()`);
  check('Mobile Roadmap Practice: options use compact two-column frame', mobilePractice.mode && mobilePractice.choices >= 4 && mobilePractice.rows <= 2 && mobilePractice.soundHeight <= 62 && mobilePractice.actionVisible && mobilePractice.panelBottom <= 844 + 8 && mobilePractice.overflow <= 3, JSON.stringify(mobilePractice));

  await navigate(client, 'weekly-test.php');
  const mobileTestSetup = await evaluate(client, `(async()=>{document.querySelector('[data-select-test="basic"]')?.click();await new Promise(r=>setTimeout(r,180));const form=document.querySelector('#wfTestSetup');const hero=document.querySelector('.wf129-test-hero');const input=document.querySelector('#wfGuestName');return {focused:document.body.classList.contains('wf-test-setup-open'),formVisible:!!form&&!form.hidden,formTop:form?.getBoundingClientRect().top||999,heroDisplay:hero?getComputedStyle(hero).display:'',inputHeight:input?.getBoundingClientRect().height||0,overflow:document.documentElement.scrollWidth-document.documentElement.clientWidth}})()`);
  check('Mobile Weekly Test: selected setup replaces distant card flow', mobileTestSetup.focused && mobileTestSetup.formVisible && mobileTestSetup.heroDisplay === 'none' && mobileTestSetup.formTop < 90 && mobileTestSetup.inputHeight <= 48 && mobileTestSetup.overflow <= 3, JSON.stringify(mobileTestSetup));

  await navigate(client, 'spoken-materials.php');
  await sleep(1100);
  const mobileMaterials = await evaluate(client, `(()=>{const tabs=Array.from(document.querySelectorAll('.goal-tab'));const tabTops=[...new Set(tabs.map(x=>Math.round(x.getBoundingClientRect().top)))];const slide=document.querySelector('.practice-slide.active');const progress=document.querySelector('.practice-progress-card');const actions=Array.from(document.querySelectorAll('.practice-slide.active .practice-action-row .btn'));const actionTops=[...new Set(actions.map(x=>Math.round(x.getBoundingClientRect().top)))];return {tabs:tabs.length,tabRows:tabTops.length,slideHeight:slide?.getBoundingClientRect().height||0,progressHeight:progress?.getBoundingClientRect().height||0,actions:actions.length,actionRows:actionTops.length,overflow:document.documentElement.scrollWidth-document.documentElement.clientWidth}})()`);
  check('Mobile Materials: modes and practice controls stay compact', mobileMaterials.tabs === 4 && mobileMaterials.tabRows === 2 && mobileMaterials.progressHeight <= 95 && mobileMaterials.actions >= 3 && mobileMaterials.actionRows <= 1 && mobileMaterials.overflow <= 3, JSON.stringify(mobileMaterials));

  const localConsole = consoleIssues.filter(item => !/favicon|Failed to load resource.*404|Not allowed to load local resource/i.test(item.text));
  check('Browser: no JavaScript runtime errors', localConsole.length === 0, JSON.stringify(localConsole.slice(0, 8)));
  const applicationNetworkIssues = networkIssues.filter(item => String(item.url).startsWith(base));
  check('Browser: no application HTTP resource failures', applicationNetworkIssues.length === 0, JSON.stringify(applicationNetworkIssues.slice(0, 12)));

  client.close();
}

try {
  await runBrowserChecks();
} catch (error) {
  check('Browser check runner', false, error.stack || error.message);
} finally {
  if (chrome && !chrome.killed) chrome.kill('SIGTERM');
  if (php && !php.killed) php.kill('SIGTERM');
  await sleep(250);
  if (tempDir) await rm(tempDir, { recursive: true, force: true }).catch(() => {});
}

const phpErrors = phpLog.join('').split(/\r?\n/).filter(line => /PHP (Warning|Notice|Fatal)|Uncaught/i.test(line));
check('Fixture PHP: no warnings/notices/fatals', phpErrors.length === 0, phpErrors.slice(0, 12).join('\n'));

const summary = {
  generated_at: new Date().toISOString(),
  base_url: base,
  passed: results.filter(item => item.status === 'PASS').length,
  failed: results.filter(item => item.status === 'FAIL').length,
  checks: results,
  browser_console_issues: consoleIssues,
  network_issues: networkIssues,
  php_errors: phpErrors,
};
const output = path.join(root, 'PHASE139_BROWSER_VALIDATION.json');
await writeFile(output, JSON.stringify(summary, null, 2) + '\n');
for (const item of results) console.log(`${item.status.padEnd(4)} ${item.name}${item.details ? ` — ${item.details}` : ''}`);
console.log(`\n${summary.passed} passed, ${summary.failed} failed`);
console.log(`Report: ${output}`);
process.exitCode = summary.failed === 0 ? 0 : 1;
