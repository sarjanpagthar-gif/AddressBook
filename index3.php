<?php // index.php ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Contacts</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,sans-serif;background:#f5f5f3;color:#1a1a18;padding:1rem}
.wrap{max-width:1200px;margin:0 auto}

/* Toolbar */
.toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;gap:8px;flex-wrap:wrap}
.toolbar-left{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.toolbar-right{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.search-box{padding:8px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;background:#fff;color:#1a1a18;width:180px;-webkit-appearance:none}
@media(max-width:480px){.search-box{width:100%;flex:1}}

/* Buttons */
.btn{padding:8px 14px;border:1px solid #ddd;border-radius:8px;font-size:13px;cursor:pointer;background:#fff;color:#1a1a18;transition:background .15s;white-space:nowrap;-webkit-appearance:none;display:inline-flex;align-items:center;gap:4px}
.btn:active{opacity:.8}
.btn:disabled{opacity:.4;cursor:default}
.btn-primary{background:#185FA5;color:#fff;border-color:#185FA5}
.btn-amber{background:#BA7517;color:#fff;border-color:#BA7517}
.btn-sm{padding:5px 10px;font-size:12px}
.btn-danger{background:#A32D2D;color:#fff;border-color:#A32D2D}
.btn-edit{background:#0F6E56;color:#fff;border-color:#0F6E56}
.nav-link{font-size:13px;color:#185FA5;text-decoration:none;padding:8px 14px;border:1px solid #B5D4F4;border-radius:8px;background:#E6F1FB;white-space:nowrap}

/* Badges */
.badge{display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:500}
.badge-active{background:#EAF3DE;color:#3B6D11}
.badge-inactive{background:#F1EFE8;color:#5F5E5A}
.badge-male{background:#E6F1FB;color:#185FA5}
.badge-female{background:#FBEAF0;color:#993556}
.badge-other{background:#F1EFE8;color:#5F5E5A}
.badge-count{background:#EAF3DE;color:#3B6D11;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:500}
.badge-sel{background:#185FA5;color:#fff;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:500;display:none}

/* Desktop table */
.table-card{background:#fff;border:1px solid #e0e0dd;border-radius:12px;overflow:hidden;display:block}
.table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
table{width:100%;border-collapse:collapse;font-size:13px}
thead tr{background:#f5f5f3}
th{padding:10px 12px;text-align:left;font-weight:500;font-size:12px;color:#666;border-bottom:1px solid #e0e0dd;white-space:nowrap}
td{padding:10px 12px;border-bottom:1px solid #f0f0ee;color:#1a1a18;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr.selected td{background:#EAF3DE}
.avatar{width:30px;height:30px;border-radius:50%;background:#B5D4F4;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#0C447C;vertical-align:middle;margin-right:6px;flex-shrink:0}
.actions{display:flex;gap:5px}
.phone-links{display:flex;align-items:center;gap:5px;flex-wrap:nowrap}
.call-link{display:inline-flex;align-items:center;gap:3px;padding:3px 7px;border-radius:6px;font-size:12px;font-weight:500;text-decoration:none;white-space:nowrap;border:1px solid}
.call-link.tel{background:#EAF3DE;color:#0F6E56;border-color:#C0DD97}
.call-link.tel:hover{background:#C0DD97}
.call-link.wa{background:#E6FBF0;color:#075E54;border-color:#9FE1CB}
.call-link.wa:hover{background:#9FE1CB}
.call-link svg{width:12px;height:12px;flex-shrink:0}

/* Mobile card list (hidden on desktop) */
.card-list{display:none}
.contact-card{background:#fff;border:1px solid #e0e0dd;border-radius:12px;padding:14px;margin-bottom:10px;position:relative}
.contact-card-header{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.contact-card-name{font-size:15px;font-weight:500;flex:1}
.contact-card-body{display:grid;grid-template-columns:1fr 1fr;gap:6px}
.contact-card-field{font-size:12px}
.contact-card-field span{color:#888;display:block;font-size:11px}
.contact-card-actions{display:flex;gap:8px;margin-top:10px;padding-top:10px;border-top:1px solid #f0f0ee}
.contact-card-actions .btn{flex:1;justify-content:center}
.card-checkbox{position:absolute;top:14px;right:14px}

/* Show cards on mobile, hide table */
@media(max-width:640px){
  .table-card{display:none}
  .card-list{display:block}
  .toolbar-right{width:100%}
  .toolbar{flex-direction:column;align-items:stretch}
  .toolbar-left{justify-content:space-between}
  .toolbar-right{display:grid;grid-template-columns:1fr 1fr;gap:8px}
  .toolbar-right .search-box{grid-column:1/-1}
  .toolbar-right .nav-link{text-align:center;justify-content:center}
}

/* Overlay & Modal */
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:flex-end;justify-content:center}
.overlay.open{display:flex}
@media(min-width:641px){.overlay{align-items:center}}
.modal{background:#fff;width:100%;max-width:660px;border-radius:16px 16px 0 0;padding:1.25rem;max-height:90vh;overflow-y:auto;-webkit-overflow-scrolling:touch}
@media(min-width:641px){.modal{border-radius:16px;max-height:88vh}}
.modal-handle{width:40px;height:4px;background:#ddd;border-radius:2px;margin:0 auto 1rem;display:block}
@media(min-width:641px){.modal-handle{display:none}}
.modal-title{font-size:16px;font-weight:600;margin-bottom:1rem}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
@media(max-width:480px){.form-grid{grid-template-columns:1fr}}
.form-group{display:flex;flex-direction:column;gap:4px}
.form-label{font-size:12px;color:#666;font-weight:500}
.fi{padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;background:#fff;color:#1a1a18;width:100%;outline:none;-webkit-appearance:none;appearance:none}
.fi:focus{border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.12)}
.fi.error{border-color:#A32D2D!important;box-shadow:0 0 0 3px rgba(163,45,45,.1)!important}
.field-error{font-size:11px;color:#A32D2D;margin-top:3px;display:none;align-items:center;gap:3px}
.field-error.show{display:flex}
.field-error svg{width:11px;height:11px;flex-shrink:0}
.form-label .req{color:#A32D2D;margin-left:2px}
.pac-container{border-radius:10px!important;border:1px solid #ddd!important;box-shadow:0 8px 24px rgba(0,0,0,.12)!important;font-family:system-ui,sans-serif!important;margin-top:4px!important;z-index:9999!important}
.pac-item{padding:8px 12px!important;font-size:13px!important;cursor:pointer!important;border-top:1px solid #f5f5f3!important}
.pac-item:hover{background:#f5f5f3!important}
.pac-item-query{font-weight:600!important;color:#1a1a18!important}
.pac-matched{font-weight:700!important;color:#185FA5!important}
.pac-icon{display:none!important}
.addr-autocomplete-wrap{position:relative}
.addr-clear{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#aaa;font-size:16px;padding:2px 4px;display:none;-webkit-appearance:none}
.addr-clear.show{display:block}
.sec{font-size:11px;font-weight:600;color:#888;letter-spacing:.06em;text-transform:uppercase;grid-column:1/-1;border-top:1px solid #eee;padding-top:12px;margin-top:4px}
.sec.first{border-top:none;padding-top:0;margin-top:0}
.modal-footer{display:flex;justify-content:flex-end;gap:8px;margin-top:1rem;padding-top:1rem;border-top:1px solid #eee}
@media(max-width:480px){.modal-footer{flex-direction:column-reverse}.modal-footer .btn{width:100%;justify-content:center;padding:12px}}

/* Confirm modal */
.confirm-box{background:#fff;width:100%;max-width:380px;border-radius:16px 16px 0 0;padding:1.25rem}
@media(min-width:641px){.confirm-box{border-radius:16px}}
.confirm-box p{font-size:14px;color:#666;margin:.5rem 0 1.25rem;line-height:1.5}
.confirm-footer{display:flex;gap:8px;justify-content:flex-end}
@media(max-width:480px){.confirm-footer{flex-direction:column-reverse}.confirm-footer .btn{width:100%;justify-content:center;padding:12px}}

/* Pagination */
.pagination{display:flex;gap:8px;align-items:center;margin-top:1rem;font-size:13px}
.page-info{color:#888;flex:1;text-align:center}

/* Toast */
.toast{position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%) translateY(20px);padding:10px 20px;border-radius:10px;font-size:13px;font-weight:500;z-index:999;opacity:0;transition:all .25s;pointer-events:none;white-space:nowrap;max-width:90vw;text-align:center}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
.toast-success{background:#EAF3DE;color:#3B6D11;border:1px solid #C0DD97}
.toast-error{background:#FCEBEB;color:#A32D2D;border:1px solid #F7C1C1}
.toast-info{background:#E6F1FB;color:#185FA5;border:1px solid #B5D4F4}
.loading{text-align:center;padding:2.5rem;color:#888;font-size:13px}
.note{font-size:12px;color:#854F0B;background:#FAEEDA;padding:8px 12px;border-radius:8px;margin-bottom:1rem;line-height:1.5}
</style>
<!-- Google Places API — replace YOUR_API_KEY below -->
<script>
var GOOGLE_API_KEY = 'YOUR_GOOGLE_API_KEY';
</script>
</head>
<body>
<div class="wrap">

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="toolbar-left">
      <span style="font-size:16px;font-weight:600">Contacts</span>
      <span id="totalBadge" class="badge-count">0</span>
      <span id="selBadge" class="badge-sel">0 selected</span>
    </div>
    <div class="toolbar-right">
      <input class="search-box" placeholder="Search..." id="searchInput">
      <button class="btn btn-amber" id="exportBtn" onclick="exportSelected()" disabled>&#8595; Export</button>
      <button class="btn btn-primary" onclick="openAdd()">+ Add</button>
      <a href="login.php" class="nav-link">&#10003; Approvals</a>
    </div>
  </div>

  <!-- Desktop table -->
  <div class="table-card">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th style="width:36px"><input type="checkbox" id="checkAll" onchange="toggleAll(this)"></th>
            <th style="min-width:160px">Name</th>
            <th style="min-width:120px">Phone</th>
            <th style="min-width:100px">Home town</th>
            <th style="min-width:220px">Current address</th>
            <th style="min-width:220px">Vatan address</th>
            <th style="width:80px">Status</th>
            <th style="width:90px">Actions</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <tr><td colspan="8" class="loading">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Mobile card list -->
  <div class="card-list" id="cardList">
    <div class="loading">Loading...</div>
  </div>

  <!-- Pagination -->
  <div class="pagination">
    <button class="btn btn-sm" id="prevBtn" onclick="changePage(-1)">&#8592; Prev</button>
    <span class="page-info" id="pageInfo"></span>
    <button class="btn btn-sm" id="nextBtn" onclick="changePage(1)">Next &#8594;</button>
  </div>

</div>

<!-- Add/Edit Modal -->
<div class="overlay" id="formModal">
  <div class="modal">
    <span class="modal-handle"></span>
    <div class="modal-title" id="modalTitle">Add contact</div>
    <div id="modalNote"></div>
    <div id="modalBody"></div>
    <div class="modal-footer">
      <button class="btn" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" id="saveBtn" onclick="saveContact()">Submit for approval</button>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="overlay" id="deleteModal">
  <div class="confirm-box">
    <span class="modal-handle"></span>
    <div class="modal-title">Delete contact</div>
    <p>This will submit a delete request for approval. The contact will be removed once approved.</p>
    <div class="confirm-footer">
      <button class="btn" onclick="closeDeleteModal()">Cancel</button>
      <button class="btn btn-danger" onclick="confirmDelete()">Submit delete request</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
var API = 'api.php';
var editId = null;
var deleteId = null;
var currentPage = 1;
var searchTimer = null;
var selectedIds = {};
var allData = [];

var FIELDS = [
  {id:'first_name',  label:'First name',    ph:'First name',        required:true,  minLen:2, maxLen:50,  errMsg:'First name is required (min 2 chars)'},
  {id:'last_name',   label:'Last name',     ph:'Last name',         required:true,  minLen:2, maxLen:50,  errMsg:'Last name is required (min 2 chars)'},
  {id:'dob',         label:'DOB (DD-MM-YYYY)', ph:'15-08-1993',     required:false, pattern:'dob',        errMsg:'Enter valid date as DD-MM-YYYY'},
  {id:'gender',      label:'Gender',        type:'select', opts:['','male','female','other'], optLabels:['— Select —','Male','Female','Other'], required:true, errMsg:'Please select gender'},
  {id:'father_name', label:'Father name',   ph:'Father name',       required:true,  minLen:2, maxLen:100, errMsg:'Father name is required (min 2 chars)'},
  {id:'mother_name', label:'Mother name',   ph:'Mother name',       required:true,  minLen:2, maxLen:100, errMsg:'Mother name is required (min 2 chars)'},
  {id:'mo_no',       label:'Mobile no',     ph:'+91 9999999999',    required:true,  pattern:'phone',      errMsg:'Enter valid 10-digit mobile number'},
  {id:'wp_no',       label:'WhatsApp no',   ph:'+91 9999999999',    required:false, pattern:'phone',      errMsg:'Enter valid 10-digit WhatsApp number'},
  {id:'Home_Town',   label:'Home town',     ph:'Home town',         required:true,  minLen:2, maxLen:100, errMsg:'Home town is required'},
  {id:'statuz',      label:'Status',        type:'select', opts:['active','inactive'], optLabels:['Active','Inactive'], required:true, errMsg:'Please select status'},
  {sec:'Current address'},
  {id:'block_no',             label:'Block no',       ph:'Block no',                  required:false, maxLen:50},
  {id:'address_line1',        label:'Address line 1', ph:'Address line 1',            required:true,  minLen:3, maxLen:255, errMsg:'Address line 1 is required', addrLine:true},
  {id:'street_address',       label:'Street address', ph:'Type to search address...', required:true,  minLen:3, maxLen:255, errMsg:'Street address is required', geocode:true},
  {id:'city',                 label:'City',           ph:'City',                      required:true,  minLen:2, maxLen:100, errMsg:'City is required'},
  {id:'state',                label:'State',          ph:'State',                     required:true,  minLen:2, maxLen:100, errMsg:'State is required'},
  {id:'zip',                  label:'Zip',            ph:'Zip',                       required:true,  pattern:'zip', isInt:true, errMsg:'Enter valid 6-digit zip code'},
  {id:'country',              label:'Country',        ph:'Country',                   required:true,  minLen:2, maxLen:100, errMsg:'Country is required'},
  {sec:'Vatan address'},
  {id:'Vatan_vilage',         label:'Village',        ph:'Village',                   required:true,  minLen:2, maxLen:150, errMsg:'Village is required'},
  {id:'Vatan_block_no',       label:'Block no',       ph:'Block no',                  required:false, maxLen:50},
  {id:'Vatan_Street_address', label:'Street address', ph:'Type to search address...', required:true,  minLen:3, maxLen:255, errMsg:'Vatan street address is required', geocode:true},
  {id:'Vatan_address_line1',  label:'Address line 1', ph:'Address line 1',            required:true,  minLen:3, maxLen:255, errMsg:'Vatan address line 1 is required'},
  {id:'Vatan_city',           label:'City',           ph:'City',                      required:true,  minLen:2, maxLen:100, errMsg:'Vatan city is required'},
  {id:'Vatan_state',          label:'State',          ph:'State',                     required:true,  minLen:2, maxLen:100, errMsg:'Vatan state is required'},
  {id:'Vatan_zip',            label:'Zip',            ph:'Zip',                       required:true,  pattern:'zip', isInt:true, errMsg:'Enter valid 6-digit zip code'},
  {id:'Vatan_country',        label:'Country',        ph:'Country',                   required:true,  minLen:2, maxLen:100, errMsg:'Vatan country is required'}
];

function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function fmtDOB(v){ if(!v||v==='0000-00-00') return '—'; var p=v.substring(0,10).split('-'); return p.length===3?p[2]+'-'+p[1]+'-'+p[0]:v; }
function dobToMySQL(v){ if(!v||v.trim()==='') return ''; var p=v.trim().split('-'); if(p.length===3&&p[2].length===4) return p[2]+'-'+(p[1].length<2?'0':'')+p[1]+'-'+(p[0].length<2?'0':'')+p[0]; return ''; }
function dobToDisplay(v){ if(!v||v==='0000-00-00') return ''; var p=v.substring(0,10).split('-'); return p.length===3?p[2]+'-'+p[1]+'-'+p[0]:''; }

function showToast(msg,type){
  type=type||'success';
  var t=document.getElementById('toast');
  t.textContent=msg; t.className='toast toast-'+type+' show';
  setTimeout(function(){t.className='toast';},3500);
}

function ajax(method,url,data,cb){
  var xhr=new XMLHttpRequest();
  xhr.open(method,url,true);
  if(data) xhr.setRequestHeader('Content-Type','application/json');
  xhr.onreadystatechange=function(){
    if(xhr.readyState!==4) return;
    try{ cb(null,JSON.parse(xhr.responseText)); }
    catch(e){ cb('Error: '+xhr.responseText.substring(0,150)); }
  };
  xhr.onerror=function(){cb('Network error');};
  xhr.send(data?JSON.stringify(data):null);
}

function loadContacts(){
  var search=document.getElementById('searchInput').value;
  var url=API+'?action=list&page='+currentPage+'&limit=10&search='+encodeURIComponent(search);
  ajax('GET',url,null,function(err,res){
    if(err){showToast(err,'error');return;}
    if(!res.success){showToast(res.message,'error');return;}
    allData=res.data;
    renderTable(res.data);
    renderCards(res.data);
    document.getElementById('totalBadge').textContent=res.total;
    document.getElementById('pageInfo').textContent='Page '+res.page+' of '+res.pages;
    document.getElementById('prevBtn').disabled=res.page<=1;
    document.getElementById('nextBtn').disabled=res.page>=res.pages;
  });
}

function initials(c){ return ((c.first_name||'')[0]+(c.last_name||'')[0]).toUpperCase(); }

// Clean phone number — strip spaces, dashes, brackets for tel: links
function cleanPhone(p){
  return String(p||'').replace(/[\s\-().+]/g,'');
}
// Build phone links cell — call + WhatsApp
function phoneLinks(mo, wp){
  var html='';
  var callNo = mo||wp;
  var waNo   = wp||mo;
  if(!callNo) return '—';
  // call link
  html += '<a class="call-link tel" href="tel:+'+cleanPhone(callNo)+'" onclick="event.stopPropagation()">'+
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 11a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 14.92z"/></svg>'+
    'Call</a>';
  // whatsapp link
  var waNum = cleanPhone(waNo);
  if(waNum.length < 10) waNum = waNum; // keep as is
  html += '<a class="call-link wa" href="https://wa.me/'+waNum+'" target="_blank" onclick="event.stopPropagation()">'+
    '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>'+
    'Chat</a>';
  return '<div class="phone-links">'+html+'</div>';
}

// Build concatenated address string
function fullAddr(c, prefix) {
  var p = prefix || '';
  var parts = [];
  var blk  = p ? c[p+'block_no']       : c.block_no;
  var adr1 = p ? c[p+'address_line1']  : c.address_line1;
  var str  = p ? c[p+'Street_address'] : c.street_address;
  var vill = p === 'Vatan_' ? c.Vatan_vilage : null;
  var city = p ? c[p+'city']           : c.city;
  var st   = p ? c[p+'state']          : c.state;
  var zip  = p ? c[p+'zip']            : c.zip;
  var ctry = p ? c[p+'country']        : c.country;
  if(vill) parts.push(vill);
  if(blk)  parts.push(blk);
  if(adr1) parts.push(adr1);
  if(str)  parts.push(str);
  if(city) parts.push(city);
  if(st)   parts.push(st);
  if(zip && zip != 0)  parts.push(zip);
  if(ctry) parts.push(ctry);
  return parts.join(', ');
}

// Desktop table
function renderTable(data){
  var tbody=document.getElementById('tableBody');
  if(!data.length){tbody.innerHTML='<tr><td colspan="10" class="loading">No approved contacts found</td></tr>';updateSelUI();return;}
  var html='';
  for(var i=0;i<data.length;i++){
    var c=data[i];
    var chk=selectedIds[c.id]?' checked':'';
    var sel=selectedIds[c.id]?' selected':'';
    var curAddr=fullAddr(c,'');
    var vatAddr=fullAddr(c,'Vatan_');
    html+='<tr class="'+sel+'">' +
      '<td><input type="checkbox" class="row-cb" value="'+c.id+'"'+chk+' onchange="toggleRowCb(this,'+c.id+')"></td>'+
      '<td><span class="avatar">'+initials(c)+'</span>'+esc(c.first_name)+' '+esc(c.last_name)+'</td>'+
      '<td>'+phoneLinks(c.mo_no,c.wp_no)+'</td>'+
      '<td>'+esc(c.Home_Town||'—')+'</td>'+
      '<td style="white-space:normal;font-size:12px;color:#444;max-width:220px">'+esc(curAddr||'—')+'</td>'+
      '<td style="white-space:normal;font-size:12px;color:#444;max-width:220px">'+esc(vatAddr||'—')+'</td>'+
      '<td><span class="badge badge-'+c.statuz+'">'+c.statuz+'</span></td>'+
      '<td><div class="actions">'+
        '<button class="btn btn-sm btn-edit" onclick="openEdit('+c.id+')">Edit</button>'+
        '<button class="btn btn-sm btn-danger" onclick="openDelete('+c.id+')">Del</button>'+
      '</div></td></tr>';
  }
  tbody.innerHTML=html;
  updateSelUI();
}

// Mobile cards
function renderCards(data){
  var list=document.getElementById('cardList');
  if(!data.length){list.innerHTML='<div class="loading">No approved contacts found</div>';return;}
  var html='';
  for(var i=0;i<data.length;i++){
    var c=data[i];
    var chk=selectedIds[c.id]?' checked':'';
    var curAddr=fullAddr(c,'');
    var vatAddr=fullAddr(c,'Vatan_');
    html+='<div class="contact-card">'+
      '<input type="checkbox" class="card-checkbox row-cb" value="'+c.id+'"'+chk+' onchange="toggleRowCb(this,'+c.id+')">'+
      '<div class="contact-card-header">'+
        '<span class="avatar" style="width:38px;height:38px;font-size:13px">'+initials(c)+'</span>'+
        '<div class="contact-card-name">'+esc(c.first_name)+' '+esc(c.last_name)+'</div>'+
        '<span class="badge badge-'+c.statuz+'">'+c.statuz+'</span>'+
      '</div>'+
      '<div class="contact-card-body">'+
        (c.mo_no||c.wp_no?'<div class="contact-card-field" style="grid-column:1/-1"><span>Contact</span>'+phoneLinks(c.mo_no,c.wp_no)+'</div>':'')+
        (c.Home_Town?'<div class="contact-card-field"><span>Home town</span>'+esc(c.Home_Town)+'</div>':'')+
        (curAddr?'<div class="contact-card-field" style="grid-column:1/-1"><span>Current address</span><span style="font-size:12px;color:#444">'+esc(curAddr)+'</span></div>':'')+
        (vatAddr?'<div class="contact-card-field" style="grid-column:1/-1"><span>Vatan address</span><span style="font-size:12px;color:#444">'+esc(vatAddr)+'</span></div>':'')+
        (c.city?'<div class="contact-card-field"><span>City</span>'+esc(c.city)+'</div>':'')+
      '</div>'+
      '<div class="contact-card-actions">'+
        '<button class="btn btn-edit" onclick="openEdit('+c.id+')">Edit</button>'+
        '<button class="btn btn-danger" onclick="openDelete('+c.id+')">Delete</button>'+
      '</div>'+
    '</div>';
  }
  list.innerHTML=html;
}

function changePage(d){currentPage+=d;loadContacts();}
document.getElementById('searchInput').addEventListener('input',function(){
  clearTimeout(searchTimer);
  searchTimer=setTimeout(function(){currentPage=1;loadContacts();},350);
});

function toggleAll(cb){
  for(var i=0;i<allData.length;i++){if(cb.checked) selectedIds[allData[i].id]=true; else delete selectedIds[allData[i].id];}
  var cbs=document.querySelectorAll('.row-cb');
  for(var j=0;j<cbs.length;j++){cbs[j].checked=cb.checked;}
  updateSelUI();
}
function toggleRowCb(cb,id){
  if(cb.checked) selectedIds[id]=true; else delete selectedIds[id];
  updateSelUI();
}
function updateSelUI(){
  var n=Object.keys(selectedIds).length;
  var sb=document.getElementById('selBadge');
  sb.textContent=n+' selected'; sb.style.display=n?'inline':'none';
  document.getElementById('exportBtn').disabled=(n===0);
  var all=allData.length>0;
  for(var i=0;i<allData.length;i++){if(!selectedIds[allData[i].id]){all=false;break;}}
  var ca=document.getElementById('checkAll'); if(ca) ca.checked=all;
}

function buildForm(values){
  values=values||{};
  var html='<div class="form-grid">';
  var firstSec=true;
  for(var i=0;i<FIELDS.length;i++){
    var f=FIELDS[i];
    if(f.sec!==undefined){html+='<div class="sec">'+f.sec+'</div>';firstSec=false;continue;}
    if(firstSec){html+='<div class="sec first">Basic info</div>';firstSec=false;}
    var val=values[f.id]!==undefined?esc(String(values[f.id]||'')):'';
    if(f.id==='dob') val=esc(dobToDisplay(values.dob||''));
    if(f.isInt&&val==='0') val='';
    var reqStar=f.required?'<span class="req">*</span>':'';
    html+='<div class="form-group" id="fg_'+f.id+'">'+'<div class="form-label">'+f.label+reqStar+'</div>';
    if(f.type==='select'){
      html+='<select class="fi" id="fi_'+f.id+'">';
      for(var j=0;j<f.opts.length;j++){
        var s2=(f.opts[j]===(values[f.id]||''))?'selected':'';
        html+='<option value="'+f.opts[j]+'"'+(s2?' selected':'')+'>'+f.optLabels[j]+'</option>';
      }
      html+='</select>';
    } else {
      if(f.geocode){
        var prefix=f.id==='street_address'?'':f.id==='Vatan_Street_address'?'Vatan_':'';
        html+='<div class="addr-autocomplete-wrap">'+
          '<input class="fi" id="fi_'+f.id+'" placeholder="'+esc(f.ph||'')+'" value="'+val+'" autocomplete="new-password" data-geocode="1" data-prefix="'+prefix+'">'+
          '<button type="button" class="addr-clear" id="clr_'+f.id+'" onclick="clearAddrField(\'' +f.id+ '\')">&#215;</button>'+
        '</div>';
      } else {
        html+='<input class="fi" id="fi_'+f.id+'" placeholder="'+esc(f.ph||'')+'" value="'+val+'">';
      }
    }
    html+='<div class="field-error" id="err_'+f.id+'"><svg viewBox="0 0 12 12" fill="currentColor"><circle cx="6" cy="6" r="6" opacity=".15"/><text x="6" y="9" text-anchor="middle" font-size="8" font-weight="700" fill="#A32D2D">!</text></svg><span id="errtxt_'+f.id+'"></span></div>';
    html+='</div>';
  }
  html+='</div>';
  return html;
}

function openAdd(){
  editId=null;
  document.getElementById('modalTitle').textContent='Add contact';
  document.getElementById('modalNote').innerHTML='';
  document.getElementById('modalBody').innerHTML=buildForm({statuz:'active'});
  document.getElementById('saveBtn').disabled=false;
  document.getElementById('saveBtn').textContent='Submit for approval';
  document.getElementById('formModal').classList.add('open');
  setTimeout(attachLiveValidation, 50);
}

function openEdit(id){
  ajax('GET',API+'?action=list&show_all=1&page=1&limit=10000',null,function(err,res){
    if(err||!res.success){showToast('Failed to load','error');return;}
    var c=null;
    for(var i=0;i<res.data.length;i++){if(res.data[i].id==id){c=res.data[i];break;}}
    if(!c){showToast('Not found','error');return;}
    editId=id;
    document.getElementById('modalTitle').textContent='Edit contact';
    document.getElementById('modalNote').innerHTML='<div class="note">Changes will be submitted for approval before going live.</div>';
    document.getElementById('modalBody').innerHTML=buildForm(c);
    document.getElementById('saveBtn').disabled=false;
    document.getElementById('saveBtn').textContent='Submit for approval';
    document.getElementById('formModal').classList.add('open');
    setTimeout(attachLiveValidation, 50);
  });
}

function closeModal(){document.getElementById('formModal').classList.remove('open');}

// ── Validation helpers ───────────────────────────────────────────────────────
function showFieldError(id, msg){
  var el=document.getElementById('fi_'+id);
  var err=document.getElementById('err_'+id);
  var txt=document.getElementById('errtxt_'+id);
  if(el) el.classList.add('error');
  if(err) err.classList.add('show');
  if(txt) txt.textContent=msg||'This field is required';
}
function clearFieldError(id){
  var el=document.getElementById('fi_'+id);
  var err=document.getElementById('err_'+id);
  if(el) el.classList.remove('error');
  if(err) err.classList.remove('show');
}
function clearAllErrors(){
  for(var i=0;i<FIELDS.length;i++){
    if(FIELDS[i].id) clearFieldError(FIELDS[i].id);
  }
}
function validatePhone(v){
  var d=v.replace(/[\s\-().+]/g,'');
  return d.length>=10&&/^[0-9]+$/.test(d);
}
function validateDOB(v){
  if(!v||v.trim()==='') return true; // optional
  return /^\d{2}-\d{2}-\d{4}$/.test(v.trim());
}
function validateZip(v){
  return /^[0-9]{4,10}$/.test(String(v).replace(/\s/g,''));
}
function validateField(f, val){
  if(f.required && (!val||val.trim()==='')) return f.errMsg||'This field is required';
  if(val && val.trim()!==''){
    if(f.minLen && val.trim().length < f.minLen) return (f.errMsg||'Minimum '+f.minLen+' characters required');
    if(f.maxLen && val.trim().length > f.maxLen) return 'Maximum '+f.maxLen+' characters allowed';
    if(f.pattern==='phone' && !validatePhone(val)) return f.errMsg||'Enter valid phone number';
    if(f.pattern==='dob'   && !validateDOB(val))   return f.errMsg||'Enter valid date DD-MM-YYYY';
    if(f.pattern==='zip'   && !validateZip(val))    return f.errMsg||'Enter valid zip code';
  }
  return null; // valid
}

// Live validation — attach on each input
function attachLiveValidation(){
  for(var i=0;i<FIELDS.length;i++){
    (function(f){
      if(!f.id) return;
      var el=document.getElementById('fi_'+f.id);
      if(!el) return;
      el.addEventListener('blur',function(){
        var val=f.isInt?String(parseInt(el.value)||0):el.value;
        if(f.id==='dob') val=el.value;
        var err=validateField(f,val);
        if(err) showFieldError(f.id,err); else clearFieldError(f.id);
      });
      el.addEventListener('input',function(){
        if(el.classList.contains('error')){
          var val=f.isInt?String(parseInt(el.value)||0):el.value;
          if(f.id==='dob') val=el.value;
          var err=validateField(f,val);
          if(!err) clearFieldError(f.id);
        }
      });
    })(FIELDS[i]);
  }
}

function saveContact(){
  clearAllErrors();
  var data={};
  var firstErrorId=null;
  var hasError=false;

  for(var i=0;i<FIELDS.length;i++){
    var f=FIELDS[i];
    if(f.sec!==undefined||!f.id) continue;
    var el=document.getElementById('fi_'+f.id);
    if(!el) continue;

    var val='';
    if(f.isInt) val=String(parseInt(el.value)||0);
    else if(f.id==='dob') val=el.value||'';
    else val=el.value||'';

    // Validate
    var errMsg=validateField(f, val);
    if(errMsg){
      showFieldError(f.id, errMsg);
      if(!firstErrorId) firstErrorId=f.id;
      hasError=true;
    }

    // Store value
    if(f.isInt) data[f.id]=parseInt(val)||0;
    else if(f.id==='dob') data[f.id]=dobToMySQL(val);
    else data[f.id]=val;
  }

  if(hasError){
    // Scroll to first error
    var firstEl=document.getElementById('fi_'+firstErrorId);
    if(firstEl) firstEl.scrollIntoView({behavior:'smooth',block:'center'});
    showToast('Please fix the errors below','error');
    return;
  }

  var btn=document.getElementById('saveBtn');
  btn.disabled=true; btn.textContent='Submitting...';
  var isEdit=(editId!==null);
  if(isEdit) data.id=editId;
  ajax('POST',API+'?action='+(isEdit?'update':'create'),data,function(err,res){
    btn.disabled=false; btn.textContent='Submit for approval';
    if(err){showToast(err,'error');return;}
    if(res.success){showToast(isEdit?'Change submitted!':'Contact submitted!','info');closeModal();loadContacts();}
    else showToast(res.message||'Error','error');
  });
}

function openDelete(id){deleteId=id;document.getElementById('deleteModal').classList.add('open');}
function closeDeleteModal(){document.getElementById('deleteModal').classList.remove('open');}
function confirmDelete(){
  ajax('POST',API+'?action=delete',{id:deleteId},function(err,res){
    if(err){showToast(err,'error');return;}
    if(res.success){showToast('Delete request submitted','info');closeDeleteModal();loadContacts();}
    else showToast(res.message,'error');
  });
}

function exportSelected(){
  var ids=[];
  for(var k in selectedIds) ids.push(parseInt(k));
  ajax('POST',API+'?action=export',{ids:ids},function(err,res){
    if(err||!res.success){showToast('Export failed','error');return;}
    var cols=['id','first_name','last_name','gender','dob','father_name','mother_name','mo_no','wp_no','Home_Town','statuz','block_no','address_line1','street_address','city','state','zip','country','Vatan_vilage','Vatan_block_no','Vatan_Street_address','Vatan_address_line1','Vatan_city','Vatan_state','Vatan_zip','Vatan_country'];
    var hdrs=['ID','First Name','Last Name','Gender','DOB','Father Name','Mother Name','Mobile','WhatsApp','Home Town','Status','Block No','Address Line 1','Street','City','State','Zip','Country','Vatan Village','Vatan Block','Vatan Street','Vatan Addr 1','Vatan City','Vatan State','Vatan Zip','Vatan Country'];
    var rows=[hdrs];
    for(var i=0;i<res.data.length;i++){var r=res.data[i];rows.push(cols.map(function(c){return r[c]||'';}));}
    var wb=XLSX.utils.book_new();
    var ws=XLSX.utils.aoa_to_sheet(rows);
    ws['!cols']=hdrs.map(function(){return{wch:18};});
    XLSX.utils.book_append_sheet(wb,ws,'Contacts');
    XLSX.writeFile(wb,'contacts_'+new Date().toISOString().slice(0,10)+'.xlsx');
    showToast('Exported '+res.data.length+' contacts');
  });
}

loadContacts();

// ── Google Places Autocomplete ─────────────────────────────────────────────
var googleLoaded = false;

function loadGoogleMaps(){
  if(googleLoaded || !GOOGLE_API_KEY || GOOGLE_API_KEY==='YOUR_GOOGLE_API_KEY') return;
  googleLoaded = true;
  var s = document.createElement('script');
  s.src = 'https://maps.googleapis.com/maps/api/js?key='+GOOGLE_API_KEY+'&libraries=places&callback=initAutocomplete';
  s.async = true; s.defer = true;
  document.head.appendChild(s);
}

// Called by Google Maps API once loaded
function initAutocomplete(){
  attachAutocompletesToForm();
}

// Attach autocomplete to all geocode fields currently in DOM
function attachAutocompletesToForm(){
  if(typeof google === 'undefined' || !google.maps || !google.maps.places) return;
  var inputs = document.querySelectorAll('[data-geocode="1"]');
  for(var i=0; i<inputs.length; i++){
    setupPlacesInput(inputs[i]);
  }
}

function setupPlacesInput(input){
  if(input._acAttached) return;
  input._acAttached = true;
  var prefix = input.getAttribute('data-prefix')||'';

  var ac = new google.maps.places.Autocomplete(input, {
    types: ['geocode','establishment'],
    componentRestrictions: {country: 'in'},  // restrict to India — remove for worldwide
    fields: ['address_components','formatted_address','geometry','name']
  });

  // Show/hide clear button
  input.addEventListener('input', function(){
    var clr = document.getElementById('clr_'+input.id);
    if(clr) clr.className = 'addr-clear' + (input.value ? ' show' : '');
  });

  ac.addListener('place_changed', function(){
    var place = ac.getPlace();
    if(!place || !place.address_components) return;

    // Parse address components
    var comps = {};
    for(var i=0; i<place.address_components.length; i++){
      var c = place.address_components[i];
      var types = c.types;
      if(types.indexOf('street_number')>=0)      comps.street_number   = c.long_name;
      if(types.indexOf('route')>=0)              comps.route           = c.long_name;
      if(types.indexOf('sublocality_level_1')>=0||types.indexOf('sublocality')>=0) comps.sublocality = c.long_name;
      if(types.indexOf('locality')>=0)           comps.city            = c.long_name;
      if(types.indexOf('administrative_area_level_1')>=0) comps.state  = c.long_name;
      if(types.indexOf('postal_code')>=0)        comps.zip             = c.long_name;
      if(types.indexOf('country')>=0)            comps.country         = c.long_name;
      if(types.indexOf('premise')>=0)            comps.premise         = c.long_name;
    }

    // Build street address from components
    var street = '';
    if(place.name && place.name !== comps.route) street = place.name;
    if(comps.street_number) street = (street ? street+', ' : '') + comps.street_number;
    if(comps.route)         street = (street ? street+', ' : '') + comps.route;
    if(!street && place.formatted_address) street = place.formatted_address.split(',')[0];

    // Fill street address field
    input.value = street || place.formatted_address || '';

    // Fill sibling address fields using prefix
    // prefix='' means current address, prefix='Vatan_' means Vatan address
    function fill(fieldId, val){
      var el = document.getElementById('fi_'+fieldId);
      if(el && val) el.value = val;
    }

    if(prefix === ''){
      fill('address_line1',  comps.sublocality || comps.premise || '');
      fill('city',           comps.city   || '');
      fill('state',          comps.state  || '');
      fill('zip',            comps.zip    || '');
      fill('country',        comps.country|| '');
    } else if(prefix === 'Vatan_'){
      fill('Vatan_address_line1',  comps.sublocality || comps.premise || '');
      fill('Vatan_city',           comps.city   || '');
      fill('Vatan_state',          comps.state  || '');
      fill('Vatan_zip',            comps.zip    || '');
      fill('Vatan_country',        comps.country|| '');
    }

    // Show clear button
    var clr = document.getElementById('clr_'+input.id);
    if(clr) clr.className = 'addr-clear show';
  });
}

function clearAddrField(fieldId){
  var el = document.getElementById('fi_'+fieldId);
  if(el) el.value = '';
  var clr = document.getElementById('clr_'+fieldId);
  if(clr) clr.className = 'addr-clear';
}

// Load Google Maps when modal opens
var _origOpenAdd = openAdd;
openAdd = function(){
  _origOpenAdd();
  loadGoogleMaps();
  setTimeout(attachAutocompletesToForm, 300);
};

var _origOpenEdit = openEdit;
openEdit = function(id){
  _origOpenEdit(id);
  loadGoogleMaps();
  setTimeout(attachAutocompletesToForm, 600);
};
</script>
</body>
</html>
