<?php
$admin_page_final_styles = ['assets/css/phase169-admin-usability.css'];
require_once __DIR__ . '/_header.php';
material_ensure_schema();
$collections = fetch_material_collections(500);
$defaultCollection = material_default_practice_collection_id() ?: ($collections[0]['id'] ?? 0);
$units = fetch_material_units((int)$defaultCollection, 500);
$csrf = csrf_token();
?>
<div class="materials-manager-v2" data-csrf="<?= e($csrf) ?>">
    <div class="toolbar page-toolbar material-head-card">
        <div>
            <span class="eyebrow">Client Friendly CMS</span>
            <h1>Spoken Practice Content Manager</h1>
            <p class="muted-text">Manage spoken English practice content, topics, sentence answers, accepted variations, hints and common mistakes in one place.</p>
        </div>
        <div class="material-head-actions"><button class="btn btn-soft" type="button" id="seedUseLibraryBtn">Load 1000 Use/Tense Sentences</button><a class="btn btn-primary" href="../spoken-materials.php" target="_blank">Open Practice Room</a></div>
    </div>

    <div class="ajax-status material-admin-status" id="materialAdminStatus" hidden></div>

    <div class="material-workflow-grid wf169-material-workflow">
        <button type="button" class="workflow-card active" data-admin-section-btn="sectionCategory"><b>1</b><strong>Create Category</strong><span>Make a folder first</span></button>
        <button type="button" class="workflow-card" data-admin-section-btn="sectionTopic"><b>2</b><strong>Create Topic</strong><span>Select category → add topic</span></button>
        <button type="button" class="workflow-card" data-admin-section-btn="sectionImport"><b>3</b><strong>Upload Excel / CSV</strong><span>Select category + topic</span></button>
        <button type="button" class="workflow-card" data-admin-section-btn="sectionOne"><b>4</b><strong>Add Sentence</strong><span>Quick manual entry</span></button>
        <button type="button" class="workflow-card" data-admin-section-btn="sectionManage"><b>5</b><strong>Manage Records</strong><span>Search, edit or delete</span></button>
    </div>

    <div class="panel-card no-api-engine-card">
        <div>
            <span class="eyebrow">Free Smart Engine</span>
            <h2>Smart checking system</h2>
            <p>Student answers are checked against verified sentences, accepted variations, keywords, common mistakes and teacher hints.</p>
        </div>
        <div class="engine-mini-grid">
            <span>✅ Exact / accepted answers</span>
            <span>✅ Smart close match</span>
            <span>✅ Common mistake feedback</span>
            <span>✅ Browser mic + voice</span>
        </div>
    </div>

    <section class="admin-material-section active" id="sectionCategory">
        <div class="panel-card material-admin-panel wf169-material-step">
            <div class="wf169-step-copy">
                <span class="wf169-step-kicker">Step 1</span>
                <h2>Create a category / folder</h2>
                <p class="muted-text">Create the main folder once. Example: Grammar Practice, Daily Speaking, Interview English. Then choose it from dropdowns in the next steps.</p>
                <div class="wf169-category-chips" id="materialCategoryChips">
                    <?php foreach(array_slice($collections, 0, 8) as $c): ?><span><?= e($c['title']) ?></span><?php endforeach; ?>
                </div>
            </div>
            <form class="ajax-material-form wf169-compact-form" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="action" value="create_category">
                <label>Category Name
                    <input name="collection_title" placeholder="Example: Grammar Practice" required>
                </label>
                <label>Default Level
                    <select name="level"><option>Beginner to Advanced</option><option>Beginner</option><option>Basic</option><option>Intermediate</option><option>Advanced</option></select>
                </label>
                <button class="btn btn-primary" type="submit">Create Category & Continue</button>
            </form>
        </div>
    </section>

    <section class="admin-material-section" id="sectionTopic">
        <div class="panel-card material-admin-panel wf169-material-step">
            <div class="wf169-step-copy">
                <span class="wf169-step-kicker">Step 2</span>
                <h2>Create a topic inside category</h2>
                <p class="muted-text">Choose the category first, then add the actual lesson topic such as is/am/are, Present Simple, Daily Market English or Interview Questions.</p>
            </div>
            <form class="ajax-material-form wf169-compact-form" data-after="reloadLists" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="action" value="create_lesson">
                <label>1. Select Category
                    <select name="collection_id" data-collection-select required>
                        <option value="">Select category</option>
                        <?php foreach($collections as $c): ?><option value="<?= e((string)$c['id']) ?>"><?= e($c['title']) ?></option><?php endforeach; ?>
                    </select>
                </label>
                <label>2. Topic / Tense / Use Name
                    <input name="topic_name" placeholder="Example: Present Simple" required>
                </label>
                <label>Level
                    <select name="level"><option>Beginner</option><option>Basic</option><option>Intermediate</option><option>Advanced</option></select>
                </label>
                <details class="wf169-material-advanced">
                    <summary>Optional teacher instruction</summary>
                    <label>Instruction
                        <textarea name="instructions" rows="3" placeholder="Example: Hindi sentence ko English me bolo, phir type karke check karo."></textarea>
                    </label>
                </details>
                <button class="btn btn-primary" type="submit">Create Topic & Continue</button>
            </form>
        </div>
    </section>

    <section class="admin-material-section" id="sectionImport">
        <div class="panel-card material-admin-panel">
            <div class="section-title-row">
                <div><h2>Bulk upload sentences</h2><p class="muted-text">Upload large sentence lists using Excel, CSV or TXT. Uploaded records become available in the practice room.</p></div>
                <button type="button" class="btn btn-soft" id="downloadSampleCsv">Download Sample CSV</button>
            </div>
            <div class="excel-format-card">
                <b>Recommended columns:</b> <small>For the easy flow, selected Topic is used for every row.</small>
                <code>Hindi Sentence, English Sentence, Topic/Tense, Situation, Level, Accepted Answers, Explanation, Sentence Type, Common Mistakes, Teacher Hint, Match Mode</code>
                <small>CSV/TXT pipe format is supported: Hindi | English | Roman | Topic | Situation | Level | Accepted | Explanation</small>
            </div>
            <form class="ajax-material-form" data-after="reloadLists" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="action" value="import_sentences">
                <div class="grid-3 wf169-select-path">
                    <label>1. Select Category
                        <select name="collection_id" data-collection-select required>
                            <option value="">Select category</option>
                            <?php foreach($collections as $c): ?><option value="<?= e((string)$c['id']) ?>" <?= (int)$defaultCollection===(int)$c['id']?'selected':'' ?>><?= e($c['title']) ?></option><?php endforeach; ?>
                        </select>
                    </label>
                    <label>2. Select Topic
                        <select name="unit_id" data-unit-select data-unit-mode="required" required>
                            <option value="">Select topic</option>
                            <?php foreach($units as $u): ?><option value="<?= e((string)$u['id']) ?>"><?= e($u['title']) ?></option><?php endforeach; ?>
                        </select>
                    </label>
                    <label>Default Level
                        <select name="level"><option>Beginner</option><option>Basic</option><option>Intermediate</option><option>Advanced</option></select>
                    </label>
                </div>
                <input type="hidden" name="topic_name" value="">
                <label>Excel / CSV / TXT file
                    <input type="file" name="sentence_file" accept=".csv,.txt,.xlsx">
                </label>
                <label>Or paste bulk sentences
                    <textarea name="bulk_text" rows="8" placeholder="मैं रोज अंग्रेजी बोलता हूँ। | I speak English every day. | Main roz angrezi bolta hoon | Present Simple | Daily Practice | Beginner"></textarea>
                </label>
                <details class="wf169-material-advanced">
                    <summary>Advanced import option</summary>
                    <label class="wf169-check-label"><input type="checkbox" name="allow_row_topics" value="Yes"> Allow the file Topic/Tense column to route rows into other topics. Keep this off for the easiest one-topic upload.</label>
                </details>
                <button class="btn btn-primary" type="submit">Upload into Selected Topic</button>
            </form>
        </div>
    </section>

    <section class="admin-material-section" id="sectionOne">
        <div class="panel-card material-admin-panel">
            <h2>Add / edit one sentence</h2>
            <p class="muted-text">Save the verified question and answer. Student responses will be checked against this record.</p>
            <form class="ajax-material-form" id="singleSentenceForm" data-after="reloadLists" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="action" value="save_sentence">
                <input type="hidden" name="id" value="">
                <div class="grid-2 wf169-select-path">
                    <label>1. Select Category
                        <select name="collection_id" data-collection-select required>
                            <option value="">Select category</option>
                            <?php foreach($collections as $c): ?><option value="<?= e((string)$c['id']) ?>" <?= (int)$defaultCollection===(int)$c['id']?'selected':'' ?>><?= e($c['title']) ?></option><?php endforeach; ?>
                        </select>
                    </label>
                    <label>2. Select Topic
                        <select name="unit_id" data-unit-select data-unit-mode="required" required>
                            <option value="">Select topic</option>
                            <?php foreach($units as $u): ?><option value="<?= e((string)$u['id']) ?>"><?= e($u['title']) ?></option><?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <input type="hidden" name="tense_name" value="">
                <div class="grid-2">
                    <label>Hindi Sentence / Question
                        <textarea name="hindi_text" rows="4" placeholder="मैं रोज अंग्रेजी बोलता हूँ।" required></textarea>
                    </label>
                    <label>Correct English Answer
                        <textarea name="english_text" rows="4" placeholder="I speak English every day." required></textarea>
                    </label>
                </div>
                <details class="wf169-material-advanced wf169-sentence-advanced">
                    <summary>Advanced / optional sentence settings</summary>
                    <div class="wf169-advanced-body">
                <div class="grid-3">
                    <label>Roman Hindi Optional<input name="roman_text" placeholder="Main roz angrezi bolta hoon"></label>
                    <label>Situation Tag<input name="situation_tag" placeholder="Daily Practice / Interview / Market"></label>
                    <label>Level<select name="level"><option>Beginner</option><option>Basic</option><option>Intermediate</option><option>Advanced</option></select></label>
                </div>
                <div class="grid-3">
                    <label>Sentence Type
                        <select name="sentence_type"><option>Simple</option><option>Negative</option><option>Yes/No Question</option><option>WH Question</option><option>Double Interrogative</option><option>Daily Use</option><option>Speaking</option></select>
                    </label>
                    <label>Match Mode
                        <select name="answer_match_mode"><option value="smart">Smart match</option><option value="strict">Strict exact/accepted</option><option value="contains_keywords">Keyword match</option></select>
                    </label>
                    <label>Common Mistakes<input name="common_mistakes" placeholder="Example: I has, go market, you is"></label>
                </div>
                <label>Accepted answer variations optional
                    <textarea name="accepted_english_answers" rows="3" placeholder="I try to speak English daily&#10;I speak English daily"></textarea>
                    <small class="muted-text">One answer per line. Useful when multiple English answers are correct.</small>
                </label>
                <label>Teacher explanation optional
                    <textarea name="explanation" rows="3" placeholder="Use present simple for daily habits."></textarea>
                </label>
                <label>Teacher hint for wrong answers optional
                    <textarea name="teacher_hint" rows="3" placeholder="With I/You/We/They use base verb. Speak slowly and repeat the correct sentence."></textarea>
                </label>
                    </div>
                </details>
                <div class="action-row"><button class="btn btn-primary" type="submit">Save Sentence</button><button type="button" class="btn btn-soft" id="resetSentenceForm">Clear</button></div>
            </form>
        </div>
    </section>

    <section class="admin-material-section" id="sectionManage">
        <div class="panel-card material-admin-panel">
            <div class="section-title-row"><div><h2>Manage uploaded sentences</h2><p class="muted-text">Search by question, answer, topic, situation or level.</p></div><div class="sentence-manage-actions"><span class="mini-chip" id="sentenceCountChip">0 records</span><button type="button" class="btn btn-sm btn-danger admin-icon-action material-icon-action" id="bulkDeleteSentences" title="Delete selected" aria-label="Delete selected"><span>🗑</span></button></div></div>
            <form id="sentenceSearchForm" class="ajax-filter-row">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="action" value="list_sentences"><input type="hidden" name="page" value="1">
                <label>Category<select name="collection_id" data-collection-select><option value="0">All Categories</option><?php foreach($collections as $c): ?><option value="<?= e((string)$c['id']) ?>"><?= e($c['title']) ?></option><?php endforeach; ?></select></label>
                <label>Topic<select name="unit_id" data-unit-select data-unit-mode="search"><option value="0">All Topics</option><?php foreach($units as $u): ?><option value="<?= e((string)$u['id']) ?>"><?= e($u['title']) ?></option><?php endforeach; ?></select></label>
                <label>Search<input name="q" placeholder="is am are, present, interview, Hindi or English"></label>
                <button type="submit" class="btn btn-primary admin-icon-action material-filter-icon" title="Search" aria-label="Search"><span>🔍</span></button><button type="button" class="btn btn-soft admin-icon-action material-filter-icon" id="resetSentenceSearch" title="Reset" aria-label="Reset"><span>↻</span></button>
            </form>
            <div class="table-wrap sentence-table-wrap">
                <table class="data-table material-record-table">
                    <thead><tr><th class="select-col"><input type="checkbox" id="selectAllSentences" aria-label="Select all"></th><th>Question</th><th>Answer</th><th>Topic / Type</th><th>Situation / Hint</th><th>Level / Mode</th><th class="material-action-head">⚙</th></tr></thead>
                    <tbody id="sentenceTableBody"><tr><td colspan="7" class="muted-text">Loading...</td></tr></tbody>
                </table>
            </div>
            <div class="material-pagination" id="sentencePagination"></div>
        </div>
    </section>
</div>
<script>
(function(){
    const root = document.querySelector('.materials-manager-v2'); if(!root) return;
    const csrf = root.dataset.csrf;
    const status = document.getElementById('materialAdminStatus');
    const api = 'materials-ajax.php';
    function showStatus(msg, ok=true){ if(window.AppUI){ window.AppUI.toast(ok?'success':'error', msg, ok?'Done':'Error'); } if(status){ status.hidden=false; status.className='ajax-status material-admin-status '+(ok?'is-ok':'is-error'); status.textContent=msg; setTimeout(()=>{status.hidden=true;},5000); } }
    function askConfirm(message, title='Confirm action', okText='Continue'){ if(window.AppUI && window.AppUI.confirm){ return window.AppUI.confirm({title:title, message:message, okText:okText, cancelText:'Cancel'}); } return Promise.resolve(confirm(message)); }
    function fd(obj){ const f=new FormData(); Object.keys(obj).forEach(k=>f.append(k,obj[k])); return f; }
    function post(formData){ return fetch(api,{method:'POST',body:formData,headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()); }
    function activate(id){ document.querySelectorAll('[data-admin-section-btn]').forEach(b=>b.classList.toggle('active', b.dataset.adminSectionBtn===id)); document.querySelectorAll('.admin-material-section').forEach(s=>s.classList.toggle('active', s.id===id)); }
    document.querySelectorAll('[data-admin-section-btn]').forEach(btn=>btn.addEventListener('click',()=>activate(btn.dataset.adminSectionBtn)));
    function renderPager(res){
        const pager=document.getElementById('sentencePagination'); if(!pager) return;
        const page=parseInt(res.page||1,10), pages=parseInt(res.pages||1,10), count=parseInt(res.count||0,10);
        if(pages<=1){ pager.innerHTML='<span>Showing '+count+' record(s)</span>'; return; }
        let html='<span>Page '+page+' of '+pages+' • '+count+' records</span>';
        html+='<button type="button" data-page="'+Math.max(1,page-1)+'" '+(page<=1?'disabled':'')+'>‹ Prev</button>';
        for(let i=Math.max(1,page-2); i<=Math.min(pages,page+2); i++){ html+='<button type="button" data-page="'+i+'" class="'+(i===page?'active':'')+'">'+i+'</button>'; }
        html+='<button type="button" data-page="'+Math.min(pages,page+1)+'" '+(page>=pages?'disabled':'')+'>Next ›</button>';
        pager.innerHTML=html;
    }
    function loadSentences(page){
        const form=document.getElementById('sentenceSearchForm'); if(!form) return;
        if(page) form.page.value=page;
        const data=new FormData(form); data.set('csrf_token',csrf); data.set('action','list_sentences');
        post(data).then(res=>{ if(!res.success) throw new Error(res.message||'Could not load'); document.getElementById('sentenceTableBody').innerHTML=res.html; document.getElementById('sentenceCountChip').textContent=(res.count||0)+' records'; renderPager(res); const all=document.getElementById('selectAllSentences'); if(all) all.checked=false; }).catch(e=>showStatus(e.message,false));
    }
    window.loadSentences = loadSentences;
    function addCollectionOption(id, title){
        if(!id) return;
        document.querySelectorAll('[data-collection-select]').forEach(sel=>{
            if(![...sel.options].some(o=>String(o.value)===String(id))){ const opt=document.createElement('option'); opt.value=String(id); opt.textContent=title||('Category #'+id); sel.appendChild(opt); }
        });
        const chips=document.getElementById('materialCategoryChips');
        if(chips && title && ![...chips.children].some(x=>x.textContent.trim()===String(title).trim())){ const chip=document.createElement('span'); chip.textContent=title; chips.prepend(chip); }
    }
    function syncTopicField(form){
        if(!form) return; const unit=form.querySelector('[data-unit-select]'); if(!unit) return;
        const text=unit.selectedIndex>=0 ? unit.options[unit.selectedIndex].textContent.trim() : '';
        if(form.elements.tense_name) form.elements.tense_name.value=unit.value ? text : '';
        if(form.elements.topic_name) form.elements.topic_name.value=unit.value ? text : '';
    }
    function loadUnitsForForm(form, collectionId, selectedUnit){
        if(!form) return Promise.resolve(); const sel=form.querySelector('[data-unit-select]'); if(!sel) return Promise.resolve();
        const mode=sel.dataset.unitMode||'required';
        if(!collectionId || String(collectionId)==='0'){ sel.innerHTML=mode==='search'?'<option value="0">All Topics</option>':'<option value="">Select topic</option>'; syncTopicField(form); return Promise.resolve(); }
        const data=fd({csrf_token:csrf,action:'get_units',collection_id:collectionId});
        return post(data).then(res=>{ if(!res.success) throw new Error(res.message||'Could not load topics');
            const first=mode==='search'?'<option value="0">All Topics</option>':'<option value="">Select topic</option>';
            sel.innerHTML=first+(res.units||[]).map(u=>'<option value="'+u.id+'">'+escapeHtml(u.title)+'</option>').join('');
            if(selectedUnit && [...sel.options].some(o=>String(o.value)===String(selectedUnit))) sel.value=String(selectedUnit);
            syncTopicField(form);
        }).catch(err=>showStatus(err.message,false));
    }
    document.querySelectorAll('.ajax-material-form').forEach(form=>form.addEventListener('submit',function(e){
        e.preventDefault(); syncTopicField(form);
        const btn=form.querySelector('button[type="submit"]'); const old=btn?btn.textContent:''; if(btn){btn.disabled=true;btn.textContent='Saving...';}
        const data=new FormData(form); data.set('csrf_token',csrf);
        post(data).then(res=>{
            if(!res.success) throw new Error(res.message||'Action failed'); showStatus(res.message||'Saved successfully');
            const action=String(data.get('action')||'');
            if(action==='create_category'){ addCollectionOption(res.collection_id,res.collection_title); const topicForm=document.querySelector('#sectionTopic form'); if(topicForm){ topicForm.elements.collection_id.value=String(res.collection_id); } activate('sectionTopic'); }
            if(action==='create_lesson'){
                addCollectionOption(res.collection_id,res.collection_title);
                ['sectionImport','sectionOne'].forEach(id=>{ const f=document.querySelector('#'+id+' form'); if(f){ f.elements.collection_id.value=String(res.collection_id); loadUnitsForForm(f,res.collection_id,res.unit_id); } });
                activate('sectionImport');
            }
            if(form.dataset.after==='reloadLists') loadSentences();
            if(action==='save_sentence'){ const c=form.elements.collection_id.value, u=form.elements.unit_id.value; form.reset(); form.elements.id.value=''; form.elements.collection_id.value=c; loadUnitsForForm(form,c,u); }
        }).catch(err=>showStatus(err.message,false)).finally(()=>{ if(btn){btn.disabled=false;btn.textContent=old;} });
    }));
    const searchForm=document.getElementById('sentenceSearchForm'); if(searchForm){ searchForm.addEventListener('submit',e=>{e.preventDefault();loadSentences();}); searchForm.querySelectorAll('input:not([type="hidden"])').forEach(el=>el.addEventListener('change',()=>loadSentences(1))); }
    document.querySelectorAll('[data-collection-select]').forEach(sel=>sel.addEventListener('change',()=>{ const form=sel.closest('form'); loadUnitsForForm(form,sel.value).then(()=>{ if(form===searchForm) loadSentences(1); }); }));
    document.querySelectorAll('[data-unit-select]').forEach(sel=>sel.addEventListener('change',()=>{ syncTopicField(sel.closest('form')); if(sel.closest('form')===searchForm) loadSentences(1); }));
    document.addEventListener('click',function(e){ const del=e.target.closest('[data-delete-sentence]'); if(del){ askConfirm('Delete this sentence safely? It will be removed from practice records.', 'Delete sentence', 'Delete').then(ok=>{ if(!ok) return; post(fd({csrf_token:csrf,action:'delete_sentence',id:del.dataset.deleteSentence})).then(res=>{ if(!res.success) throw new Error(res.message); showStatus(res.message); loadSentences(); }).catch(err=>showStatus(err.message,false)); }); }
        const edit=e.target.closest('[data-edit-sentence]'); if(edit){ const p=JSON.parse(edit.dataset.pair||'{}'); activate('sectionOne'); const form=document.getElementById('singleSentenceForm'); ['id','collection_id','hindi_text','english_text','roman_text','tense_name','situation_tag','level','explanation','accepted_english_answers','accepted_hindi_answers','sentence_type','common_mistakes','teacher_hint','answer_match_mode'].forEach(k=>{ if(form.elements[k]) form.elements[k].value=p[k]||''; }); loadUnitsForForm(form,p.collection_id,p.unit_id).then(()=>syncTopicField(form)); window.scrollTo({top:0,behavior:'smooth'}); }
    });
    var resetBtn=document.getElementById('resetSentenceForm'); if(resetBtn){ resetBtn.addEventListener('click',function(){ var f=document.getElementById('singleSentenceForm'); f.reset(); f.elements.id.value=''; }); }
    var seedBtn=document.getElementById('seedUseLibraryBtn'); if(seedBtn){ seedBtn.addEventListener('click',function(){ askConfirm('Load ready 1000 use/tense practice sentences? Existing records will not duplicate.', 'Load practice library', 'Load').then(ok=>{ if(!ok) return; const old=seedBtn.textContent; seedBtn.disabled=true; seedBtn.textContent='Loading library...'; post(fd({csrf_token:csrf,action:'seed_use_library'})).then(res=>{ if(!res.success) throw new Error(res.message||'Could not load library'); showStatus(res.message||'Library loaded'); loadSentences(); activate('sectionManage'); }).catch(err=>showStatus(err.message,false)).finally(()=>{seedBtn.disabled=false;seedBtn.textContent=old;}); }); }); }
    var sampleBtn=document.getElementById('downloadSampleCsv'); if(sampleBtn){ sampleBtn.addEventListener('click',function(){ const csv='Hindi Sentence,English Sentence,Topic/Tense,Situation,Level,Accepted Answers,Explanation,Sentence Type,Common Mistakes,Teacher Hint,Match Mode\n"मैं रोज अंग्रेजी बोलता हूँ।","I speak English every day.","Present Simple","Daily Practice","Beginner","I speak English daily","Use present simple for daily habits.","Simple","I speaking English","Use base verb after I/you/we/they.","smart"\n"क्या आप मेरी मदद कर सकते हैं?","Can you help me?","Modal Verb","Polite Speaking","Beginner","Could you help me?","Can you is a polite request.","Yes/No Question","You can help me?","Question starts with Can/Could.","smart"'; const blob=new Blob([csv],{type:'text/csv;charset=utf-8'}); const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='spoken-practice-sample.csv'; a.click(); URL.revokeObjectURL(a.href); }); }
    function escapeHtml(v){ return String(v||'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c])); }

    document.addEventListener('click',function(e){
        const pg=e.target.closest('#sentencePagination button[data-page]');
        if(pg){ loadSentences(parseInt(pg.dataset.page||'1',10)); }
    });
    document.getElementById('selectAllSentences')?.addEventListener('change',function(){
        document.querySelectorAll('.sentence-select').forEach(cb=>cb.checked=this.checked);
    });
    document.getElementById('bulkDeleteSentences')?.addEventListener('click',function(){
        const ids=[...document.querySelectorAll('.sentence-select:checked')].map(cb=>cb.value);
        if(!ids.length){ showStatus('Please select at least one record.', false); return; }
        askConfirm('Delete selected '+ids.length+' record(s)?', 'Delete selected records', 'Delete').then(ok=>{ if(!ok) return;
        const data=fd({csrf_token:csrf, action:'bulk_delete_sentences', ids:ids.join(',')});
        post(data).then(res=>{ if(!res.success) throw new Error(res.message||'Could not delete'); showStatus(res.message||'Deleted.'); loadSentences(); }).catch(e=>showStatus(e.message,false));
        });
    });
    document.getElementById('resetSentenceSearch')?.addEventListener('click',function(){
        const form=document.getElementById('sentenceSearchForm');
        form.reset(); form.page.value=1; loadSentences(1);
    });

    loadSentences();
})();
</script>
<?php require_once __DIR__ . '/_footer.php'; ?>
