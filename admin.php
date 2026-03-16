<?php
require_once 'config.php';
require_once 'auth.php';
requireLogin();
// Only super admin can access this page
if (empty($_SESSION['is_super_admin'])) {
    header('Location: approval.php?error=access_denied'); exit;
}
$admin = $_SESSION['admin_user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Admin — Approvers</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,sans-serif;background:#f5f5f3;color:#1a1a18;padding:1rem}
.wrap{max-width:1100px;margin:0 auto}
.toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;gap:8px;flex-wrap:wrap}
.toolbar-left{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.toolbar-right{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
@media(max-width:600px){.toolbar{flex-direction:column;align-items:stretch}.toolbar-left,.toolbar-right{justify-content:space-between}}
.btn{padding:8px 14px;border:1px solid #ddd;border-radius:8px;font-size:13px;cursor:pointer;background:#fff;color:#1a1a18;white-space:nowrap;-webkit-appearance:none;display:inline-flex;align-items:center;gap:5px;transition:opacity .15s}
.btn:active{opacity:.75}.btn:disabled{opacity:.45;cursor:default}
.btn-primary{background:#185FA5;color:#fff;border-color:#185FA5}
.btn-sm{padding:5px 11px;font-size:12px}
.btn-danger{background:#A32D2D;color:#fff;border-color:#A32D2D}
.btn-edit{background:#0F6E56;color:#fff;border-color:#0F6E56}
.btn-logout{color:#A32D2D;border-color:#F7C1C1}
.nav-link{font-size:13px;color:#185FA5;text-decoration:none;padding:8px 14px;border:1px solid #B5D4F4;border-radius:8px;background:#E6F1FB;white-space:nowrap}
.badge{display:inline-block;padding:2px 9px;border-radius:999px;font-size:11px;font-weight:600}
.badge-active{background:#EAF3DE;color:#3B6D11}
.badge-inactive{background:#F1EFE8;color:#5F5E5A}
.badge-approved{background:#EAF3DE;color:#3B6D11}
.badge-rejected{background:#FCEBEB;color:#A32D2D}
.badge-create{background:#EAF3DE;color:#3B6D11}
.badge-update{background:#E6F1FB;color:#185FA5}
.badge-delete{background:#FCEBEB;color:#A32D2D}
.admin-chip{display:inline-flex;align-items:center;gap:6px;background:#f5f5f3;border:1px solid #e0e0dd;border-radius:999px;padding:4px 10px 4px 4px;font-size:12px;color:#555}
.admin-avatar{width:22px;height:22px;border-radius:50%;background:#185FA5;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:700}
/* Tabs */
.tabs{display:flex;gap:0;border-bottom:1px solid #e0e0dd;margin-bottom:1rem}
.tab{padding:10px 18px;font-size:13px;font-weight:500;cursor:pointer;color:#888;border-bottom:2px solid transparent;margin-bottom:-1px;-webkit-user-select:none;user-select:none;transition:color .15s}
.tab.active{color:#185FA5;border-bottom-color:#185FA5}
.tab-content{display:none}.tab-content.active{display:block}
/* Cards */
.card{background:#fff;border:1px solid #e0e0dd;border-radius:12px;overflow:hidden;margin-bottom:1rem}
table{width:100%;border-collapse:collapse;font-size:13px}
thead tr{background:#f5f5f3}
th{padding:10px 12px;text-align:left;font-weight:600;font-size:12px;color:#666;border-bottom:1px solid #e0e0dd;white-space:nowrap}
td{padding:10px 12px;border-bottom:1px solid #f0f0ee;color:#1a1a18;vertical-align:middle}
tr:last-child td{border-bottom:none}
.avatar{width:32px;height:32px;border-radius:50%;background:#B5D4F4;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#0C447C;vertical-align:middle;margin-right:8px}
.stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px;margin-bottom:1rem}
.stat-card{background:#fff;border:1px solid #e0e0dd;border-radius:10px;padding:12px 14px;text-align:center}
.stat-num{font-size:28px;font-weight:700;color:#185FA5}
.stat-lbl{font-size:11px;color:#888;margin-top:2px}
/* Overlay & Modal */
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:flex-end;justify-content:center}
.overlay.open{display:flex}
@media(min-width:641px){.overlay{align-items:center}}
.modal{background:#fff;width:100%;max-width:500px;border-radius:16px 16px 0 0;padding:1.5rem;max-height:90vh;overflow-y:auto}
@media(min-width:641px){.modal{border-radius:16px}}
.modal-handle{width:40px;height:4px;background:#ddd;border-radius:2px;margin:0 auto 1rem;display:block}
@media(min-width:641px){.modal-handle{display:none}}
.modal-title{font-size:16px;font-weight:700;margin-bottom:1rem}
.form-group{display:flex;flex-direction:column;gap:4px;margin-bottom:10px}
.form-label{font-size:12px;color:#666;font-weight:600}
.fi{padding:9px 11px;border:1px solid #ddd;border-radius:8px;font-size:14px;background:#fff;color:#1a1a18;width:100%;outline:none;-webkit-appearance:none}
.fi:focus{border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.1)}
.modal-footer{display:flex;justify-content:flex-end;gap:8px;margin-top:1rem;padding-top:1rem;border-top:1px solid #eee}
.confirm-box{background:#fff;width:100%;max-width:360px;border-radius:16px 16px 0 0;padding:1.5rem}
@media(min-width:641px){.confirm-box{border-radius:16px}}
.confirm-box p{font-size:14px;color:#666;margin:.5rem 0 1.5rem;line-height:1.5}
/* History */
.history-row-approved td{background:#F6FBF0}
.history-row-rejected td{background:#FFF5F5}
.pagination{display:flex;gap:6px;align-items:center;margin-top:1rem;font-size:13px}
.page-info{color:#888;flex:1;text-align:center}
.loading{text-align:center;padding:2rem;color:#888}
.toggle{position:relative;width:38px;height:22px;display:inline-block}
.toggle input{opacity:0;width:0;height:0;position:absolute}
.toggle-slider{position:absolute;inset:0;background:#ddd;border-radius:999px;cursor:pointer;transition:background .2s}
.toggle-slider:before{content:'';position:absolute;width:16px;height:16px;left:3px;top:3px;background:#fff;border-radius:50%;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.toggle input:checked+.toggle-slider{background:#185FA5}
.toggle input:checked+.toggle-slider:before{transform:translateX(16px)}
.toast{position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%) translateY(20px);padding:10px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:999;opacity:0;transition:all .25s;pointer-events:none;max-width:92vw;text-align:center}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
.toast-success{background:#EAF3DE;color:#3B6D11;border:1px solid #C0DD97}
.toast-error{background:#FCEBEB;color:#A32D2D;border:1px solid #F7C1C1}
.empty{text-align:center;padding:2.5rem;color:#888;font-size:13px}
</style>
</head>
<body>
<div class="wrap">

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="toolbar-left">
      <span style="font-size:17px;font-weight:700">Admin Panel</span>
    </div>
    <div class="toolbar-right">
      <div class="admin-chip">
        <span class="admin-avatar"><?php echo strtoupper(substr($admin,0,1)); ?></span>
        <?php echo htmlspecialchars($admin); ?> &nbsp;<span style="color:#3B6D11;font-size:10px;font-weight:700">SUPER ADMIN</span>
      </div>
      <a href="approval.php" class="nav-link">&#10003; Approvals</a>
      <a href="index.php" class="nav-link">&#8592; Contacts</a>
      <a href="logout.php" class="btn btn-logout">&#9866; Logout</a>
    </div>
  </div>

  <!-- Summary stats -->
  <div class="stat-grid" id="statsGrid">
    <div class="stat-card"><div class="stat-num" id="statTotal">—</div><div class="stat-lbl">Total approvers</div></div>
    <div class="stat-card"><div class="stat-num" id="statActive">—</div><div class="stat-lbl">Active</div></div>
    <div class="stat-card"><div class="stat-num" id="statApproved">—</div><div class="stat-lbl">Total approved</div></div>
    <div class="stat-card"><div class="stat-num" id="statRejected">—</div><div class="stat-lbl">Total rejected</div></div>
  </div>

  <!-- Tabs -->
  <div class="tabs">
    <div class="tab active" onclick="switchTab('approvers')">Approvers</div>
    <div class="tab" onclick="switchTab('history')">Approval History</div>
  </div>

  <!-- Approvers Tab -->
  <div class="tab-content active" id="tab-approvers">
    <div style="display:flex;justify-content:flex-end;margin-bottom:10px">
      <button class="btn btn-primary" onclick="openAdd()">+ Add approver</button>
    </div>
    <div class="card">
      <div style="overflow-x:auto">
        <table>
          <thead><tr>
            <th>Approver</th>
            <th>Username</th>
            <th>Mobile</th>
            <th>Status</th>
            <th>Approved</th>
            <th>Rejected</th>
            <th>Last action</th>
            <th>Actions</th>
          </tr></thead>
          <tbody id="approversBody"><tr><td colspan="8" class="loading">Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- History Tab -->
  <div class="tab-content" id="tab-history">
    <div style="display:flex;gap:8px;margin-bottom:10px;flex-wrap:wrap;align-items:center">
      <select class="fi" id="historyFilter" style="width:200px" onchange="loadHistory(1)">
        <option value="">All approvers</option>
      </select>
      <button class="btn btn-sm" onclick="loadHistory(1)">&#8635; Refresh</button>
    </div>
    <div class="card">
      <div style="overflow-x:auto">
        <table>
          <thead><tr>
            <th>Contact</th>
            <th>Change type</th>
            <th>Requested</th>
            <th>Reviewed by</th>
            <th>Action</th>
            <th>Reviewed at</th>
            <th>Note</th>
          </tr></thead>
          <tbody id="historyBody"><tr><td colspan="7" class="loading">Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
    <div class="pagination">
      <button class="btn btn-sm" id="histPrevBtn" onclick="changeHistPage(-1)">&#8592; Prev</button>
      <span class="page-info" id="histPageInfo"></span>
      <button class="btn btn-sm" id="histNextBtn" onclick="changeHistPage(1)">Next &#8594;</button>
    </div>
  </div>

</div>

<!-- Add/Edit Modal -->
<div class="overlay" id="formModal">
  <div class="modal">
    <span class="modal-handle"></span>
    <div class="modal-title" id="modalTitle">Add approver</div>
    <div id="modalBody"></div>
    <div class="modal-footer">
      <button class="btn" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" id="saveBtn" onclick="saveApprover()">Save</button>
    </div>
  </div>
</div>

<!-- Delete confirm -->
<div class="overlay" id="deleteModal">
  <div class="confirm-box">
    <span class="modal-handle"></span>
    <div class="modal-title">Delete approver</div>
    <p>Are you sure? This approver will be removed. Their approval history will be retained.</p>
    <div style="display:flex;gap:8px;justify-content:flex-end">
      <button class="btn" onclick="closeDeleteModal()">Cancel</button>
      <button class="btn btn-danger" onclick="confirmDelete()">Delete</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
var API = 'admin_api.php';
var editId = null;
var deleteId = null;
var histPage = 1;

function showToast(msg,type){
  type=type||'success';
  var t=document.getElementById('toast');
  t.textContent=msg; t.className='toast toast-'+type+' show';
  setTimeout(function(){t.className='toast';},3500);
}
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtDate(v){
  if(!v) return '—';
  return new Date(v).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
}
function initials(name){ var p=String(name||'').split(' '); return (p[0][0]+(p[1]?p[1][0]:'')).toUpperCase(); }

function ajax(method,url,data,cb){
  var x=new XMLHttpRequest();
  x.open(method,url,true);
  if(data) x.setRequestHeader('Content-Type','application/json');
  x.onreadystatechange=function(){
    if(x.readyState!==4) return;
    var raw=x.responseText;
    var js=raw.indexOf('{'); if(js>0) raw=raw.substring(js);
    try{ cb(null,JSON.parse(raw)); } catch(e){ cb('Error: '+x.responseText.substring(0,200)); }
  };
  x.onerror=function(){cb('Network error');};
  x.send(data?JSON.stringify(data):null);
}

function switchTab(name){
  document.querySelectorAll('.tab').forEach(function(t,i){ t.classList.toggle('active', ['approvers','history'][i]===name); });
  document.querySelectorAll('.tab-content').forEach(function(t){ t.classList.remove('active'); });
  document.getElementById('tab-'+name).classList.add('active');
  if(name==='history') loadHistory(1);
}

// ── Load approvers ────────────────────────────────────────────────────────────
function loadApprovers(){
  ajax('GET',API+'?action=list',null,function(err,res){
    if(err||!res.success){ document.getElementById('approversBody').innerHTML='<tr><td colspan="8" style="color:#A32D2D;padding:1rem">'+esc(err||res.message)+'</td></tr>'; return; }
    renderApprovers(res.data);
    updateStats(res.data);
    populateHistoryFilter(res.data);
  });
}

function updateStats(data){
  var active=0, approved=0, rejected=0;
  for(var i=0;i<data.length;i++){
    if(data[i].is_active==1) active++;
    approved += parseInt(data[i].total_approved)||0;
    rejected += parseInt(data[i].total_rejected)||0;
  }
  document.getElementById('statTotal').textContent=data.length;
  document.getElementById('statActive').textContent=active;
  document.getElementById('statApproved').textContent=approved;
  document.getElementById('statRejected').textContent=rejected;
}

function populateHistoryFilter(data){
  var sel=document.getElementById('historyFilter');
  var cur=sel.value;
  sel.innerHTML='<option value="">All approvers</option>';
  for(var i=0;i<data.length;i++){
    sel.innerHTML+='<option value="'+esc(data[i].username)+'">'+esc(data[i].name)+'</option>';
  }
  sel.value=cur;
}

function renderApprovers(data){
  var tbody=document.getElementById('approversBody');
  if(!data.length){ tbody.innerHTML='<tr><td colspan="8" class="empty">No approvers found. Add one above.</td></tr>'; return; }
  var html='';
  for(var i=0;i<data.length;i++){
    var a=data[i];
    var chk=a.is_active==1?' checked':'';
    html+='<tr>'+
      '<td><span class="avatar">'+initials(a.name)+'</span>'+esc(a.name)+'<br><small style="color:#888;font-size:11px">'+esc(a.email)+'</small></td>'+
      '<td><code style="background:#f5f5f3;padding:2px 6px;border-radius:4px;font-size:12px">'+esc(a.username)+'</code></td>'+
      '<td>'+esc(a.mobile||'—')+'</td>'+
      '<td><span class="badge badge-'+(a.is_active==1?'active':'inactive')+'">'+(a.is_active==1?'Active':'Inactive')+'</span></td>'+
      '<td style="color:#0F6E56;font-weight:600">'+(parseInt(a.total_approved)||0)+'</td>'+
      '<td style="color:#A32D2D;font-weight:600">'+(parseInt(a.total_rejected)||0)+'</td>'+
      '<td style="font-size:12px;color:#888">'+fmtDate(a.last_action_at)+'</td>'+
      '<td><div style="display:flex;gap:5px;align-items:center">'+
        '<button class="btn btn-sm btn-edit" onclick="openEdit('+a.id+')">Edit</button>'+
        '<button class="btn btn-sm btn-danger" onclick="openDelete('+a.id+')">Del</button>'+
        '<label class="toggle" title="'+(a.is_active==1?'Deactivate':'Activate')+'">'+
          '<input type="checkbox"'+chk+' onchange="toggleActive('+a.id+',this)">'+
          '<span class="toggle-slider"></span>'+
        '</label>'+
      '</div></td>'+
    '</tr>';
  }
  tbody.innerHTML=html;
}

// ── Add / Edit ─────────────────────────────────────────────────────────────────
function buildForm(vals){
  vals=vals||{};
  return '<div class="form-group"><div class="form-label">Full name *</div>'+
    '<input class="fi" id="fi_name" placeholder="Full name" value="'+esc(vals.name||'')+'"></div>'+
    '<div class="form-group"><div class="form-label">Email *</div>'+
    '<input class="fi" id="fi_email" placeholder="email@example.com" value="'+esc(vals.email||'')+'"></div>'+
    '<div class="form-group"><div class="form-label">Mobile</div>'+
    '<input class="fi" id="fi_mobile" placeholder="+91 9999999999" value="'+esc(vals.mobile||'')+'"></div>'+
    (vals.id?'':'<div class="form-group"><div class="form-label">Username *</div>'+
    '<input class="fi" id="fi_username" placeholder="username" value="'+esc(vals.username||'')+'"></div>')+
    '<div class="form-group"><div class="form-label">'+(vals.id?'New password (leave blank to keep)':'Password * (min 6 chars)')+'</div>'+
    '<input class="fi" id="fi_password" placeholder="••••••" type="password"></div>';
}

function openAdd(){
  editId=null;
  document.getElementById('modalTitle').textContent='Add approver';
  document.getElementById('modalBody').innerHTML=buildForm({});
  document.getElementById('saveBtn').textContent='Create approver';
  document.getElementById('formModal').classList.add('open');
}

function openEdit(id){
  ajax('GET',API+'?action=list',null,function(err,res){
    if(err||!res.success){ showToast('Failed to load','error'); return; }
    var a=null; for(var i=0;i<res.data.length;i++) if(res.data[i].id==id){ a=res.data[i]; break; }
    if(!a){ showToast('Not found','error'); return; }
    editId=id;
    document.getElementById('modalTitle').textContent='Edit approver';
    document.getElementById('modalBody').innerHTML=buildForm(a);
    document.getElementById('saveBtn').textContent='Save changes';
    document.getElementById('formModal').classList.add('open');
  });
}

function closeModal(){ document.getElementById('formModal').classList.remove('open'); }

function saveApprover(){
  var name    =document.getElementById('fi_name').value.trim();
  var email   =document.getElementById('fi_email').value.trim();
  var mobile  =document.getElementById('fi_mobile').value.trim();
  var usernameEl=document.getElementById('fi_username');
  var username= usernameEl ? usernameEl.value.trim() : '';
  var password=document.getElementById('fi_password').value;

  if(!name||!email){ showToast('Name and email are required','error'); return; }
  if(!editId && !username){ showToast('Username is required','error'); return; }

  var btn=document.getElementById('saveBtn');
  btn.disabled=true; btn.textContent='Saving...';

  var data={name:name,email:email,mobile:mobile,password:password};
  if(editId) data.id=editId; else data.username=username;

  ajax('POST',API+'?action='+(editId?'update':'create'),data,function(err,res){
    btn.disabled=false; btn.textContent=editId?'Save changes':'Create approver';
    if(err){ showToast(err,'error'); return; }
    if(res.success){ showToast(editId?'Approver updated!':'Approver created!'); closeModal(); loadApprovers(); }
    else showToast(res.message,'error');
  });
}

// ── Delete ─────────────────────────────────────────────────────────────────────
function openDelete(id){ deleteId=id; document.getElementById('deleteModal').classList.add('open'); }
function closeDeleteModal(){ document.getElementById('deleteModal').classList.remove('open'); }
function confirmDelete(){
  ajax('POST',API+'?action=delete',{id:deleteId},function(err,res){
    if(err){ showToast(err,'error'); return; }
    if(res.success){ showToast('Approver deleted'); closeDeleteModal(); loadApprovers(); }
    else showToast(res.message,'error');
  });
}

// ── Toggle active ──────────────────────────────────────────────────────────────
function toggleActive(id,cb){
  cb.disabled=true;
  ajax('POST',API+'?action=toggle',{id:id,is_active:cb.checked?1:0},function(err,res){
    cb.disabled=false;
    if(err){ showToast(err,'error'); cb.checked=!cb.checked; return; }
    if(res.success){ showToast(res.message); loadApprovers(); }
    else{ showToast(res.message,'error'); cb.checked=!cb.checked; }
  });
}

// ── History ────────────────────────────────────────────────────────────────────
function loadHistory(page){
  histPage=page||1;
  var filter=document.getElementById('historyFilter').value;
  var url=API+'?action=history&page='+histPage+(filter?'&username='+encodeURIComponent(filter):'');
  document.getElementById('historyBody').innerHTML='<tr><td colspan="7" class="loading">Loading...</td></tr>';
  ajax('GET',url,null,function(err,res){
    if(err||!res.success){ document.getElementById('historyBody').innerHTML='<tr><td colspan="7" style="color:#A32D2D;padding:1rem">'+esc(err||res.message)+'</td></tr>'; return; }
    renderHistory(res);
  });
}

function renderHistory(res){
  var tbody=document.getElementById('historyBody');
  document.getElementById('histPageInfo').textContent='Page '+res.page+' of '+res.pages;
  document.getElementById('histPrevBtn').disabled=res.page<=1;
  document.getElementById('histNextBtn').disabled=res.page>=res.pages;
  if(!res.data.length){ tbody.innerHTML='<tr><td colspan="7" class="empty">No history found</td></tr>'; return; }
  var html='';
  for(var i=0;i<res.data.length;i++){
    var r=res.data[i];
    var name=(r.first_name||'')+' '+(r.last_name||'');
    var actCls=r.review_action==='approved'?'badge-approved':'badge-rejected';
    var typeCls={create:'badge-create',update:'badge-update',delete:'badge-delete'}[r.change_type]||'';
    var rowCls=r.review_action==='approved'?'history-row-approved':'history-row-rejected';
    html+='<tr class="'+rowCls+'">'+
      '<td><strong>'+esc(name.trim()||'ID:'+r.contact_id)+'</strong></td>'+
      '<td><span class="badge '+typeCls+'">'+esc(r.change_type)+'</span></td>'+
      '<td style="font-size:12px">'+fmtDate(r.requested_at)+'</td>'+
      '<td>'+
        '<span class="avatar" style="width:26px;height:26px;font-size:10px">'+initials(r.reviewer_name||r.reviewed_by||'?')+'</span>'+
        '<strong>'+esc(r.reviewer_name||r.reviewed_by||'—')+'</strong>'+
        '<br><small style="color:#888;font-size:11px">@'+esc(r.reviewed_by||'')+'</small>'+
      '</td>'+
      '<td><span class="badge '+actCls+'">'+esc(r.review_action||'—')+'</span></td>'+
      '<td style="font-size:12px">'+fmtDate(r.reviewed_at)+'</td>'+
      '<td style="font-size:12px;color:#888">'+esc(r.review_note||'—')+'</td>'+
    '</tr>';
  }
  tbody.innerHTML=html;
}

function changeHistPage(d){ loadHistory(histPage+d); }

loadApprovers();
</script>
</body>
</html>
