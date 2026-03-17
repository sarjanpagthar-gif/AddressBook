<?php
require_once __DIR__ . '/config.php';

// Build JS validation flags object from PHP config
$vflags = json_encode([
  'first_name'       => VALIDATE_FIRST_NAME,
  'last_name'        => VALIDATE_LAST_NAME,
  'dob'              => VALIDATE_DOB,
  'gender'           => VALIDATE_GENDER,
  'father_name'      => VALIDATE_FATHER_NAME,
  'mother_name'      => VALIDATE_MOTHER_NAME,
  'mo_no'            => VALIDATE_MOBILE,
  'wp_no'            => VALIDATE_WHATSAPP,
  'Home_Town'        => VALIDATE_HOME_TOWN,
  'block_no'         => VALIDATE_BLOCK_NO,
  'address_line1'    => VALIDATE_ADDRESS_LINE1,
  'street_address'   => VALIDATE_STREET_ADDRESS,
  'city'             => VALIDATE_CITY,
  'state'            => VALIDATE_STATE,
  'zip'              => VALIDATE_ZIP,
  'country'          => VALIDATE_COUNTRY,
  'Vatan_vilage'     => VALIDATE_VATAN_VILLAGE,
  'Vatan_block_no'   => VALIDATE_VATAN_BLOCK_NO,
  'Vatan_Street_address' => VALIDATE_VATAN_STREET,
  'Vatan_address_line1'  => VALIDATE_VATAN_ADDR1,
  'Vatan_city'       => VALIDATE_VATAN_CITY,
  'Vatan_state'      => VALIDATE_VATAN_STATE,
  'Vatan_zip'        => VALIDATE_VATAN_ZIP,
  'Vatan_country'    => VALIDATE_VATAN_COUNTRY,
]);

$export_on    = FEATURE_EXPORT         ? 'true' : 'false';
$approval_on  = FEATURE_APPROVAL       ? 'true' : 'false';
// Column visibility flags — derived from feature flags
$phone_col_on  = FEATURE_PHONE_VISIBILITY ? 'true' : 'false'; // true=column visible, false=column hidden
$col_addr_on   = 'true'; // current address always shown
$col_vatan_on  = 'true'; // vatan address always shown
$col_status_on = 'true'; // status always shown
$col_export_on = FEATURE_EXPORT ? 'true' : 'false'; // checkbox col only if export on
$gplaces_on  = FEATURE_GOOGLE_PLACES  ? 'true' : 'false';
$copyaddr_on = FEATURE_COPY_ADDR      ? 'true' : 'false';
$gpsfill_on  = FEATURE_GPS_FILL       ? 'true' : 'false';
?>
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
.phone-masked{display:inline-flex;align-items:center;gap:5px;font-size:13px;color:#888;letter-spacing:.08em;cursor:pointer;padding:3px 8px;border-radius:6px;background:#f5f5f3;border:1px solid #e0e0dd;user-select:none;-webkit-user-select:none}
.phone-masked:hover{background:#e8e8e5}
.phone-masked svg{width:12px;height:12px;flex-shrink:0;color:#aaa}
.btn-phone-toggle{background:#fff;border:1px solid #ddd;color:#555}
.phones-hidden .phone-col-th,
.phones-hidden .phone-col-td{ display:none!important }
.phones-hidden .phone-col-card{ display:none!important }
.btn-phone-toggle.phones-visible{background:#E6F1FB;border-color:#B5D4F4;color:#185FA5}
.call-link{display:inline-flex;align-items:center;gap:3px;padding:3px 7px;border-radius:6px;font-size:12px;font-weight:500;text-decoration:none;white-space:nowrap;border:1px solid}
.call-link.tel{background:#EAF3DE;color:#0F6E56;border-color:#C0DD97}
.call-link.tel:hover{background:#C0DD97}
.call-link.wa{background:#E6FBF0;color:#075E54;border-color:#9FE1CB}
.call-link.wa:hover{background:#9FE1CB}
.call-link svg{width:12px;height:12px;flex-shrink:0}
/* Home town pill — same shape as call-link but transparent */
.hometown-pill{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:6px;font-size:12px;font-weight:500;white-space:nowrap;border:1px solid #ddd;background:transparent;color:#555}
.hometown-pill svg{width:12px;height:12px;flex-shrink:0;color:#888}
/* Phone + hometown row in accordion */
.acc-contact-row{display:flex;align-items:center;flex-wrap:wrap;gap:6px;margin-bottom:10px}

/* Mobile accordion card list (hidden on desktop) */
.card-list{display:none}

/* Compact accordion row */
.acc-card{background:#fff;border:1px solid #e0e0dd;border-radius:12px;margin-bottom:8px;overflow:hidden;transition:box-shadow .15s}
.acc-card.open{box-shadow:0 4px 16px rgba(0,0,0,.09)}

/* Collapsed header row — always visible */
.acc-header{display:flex;align-items:center;gap:10px;padding:11px 13px;cursor:pointer;-webkit-user-select:none;user-select:none;position:relative}
.acc-avatar{width:36px;height:36px;border-radius:50%;background:#B5D4F4;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#0C447C;flex-shrink:0}
.acc-name{font-size:14px;font-weight:600;color:#1a1a18;flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.acc-meta{font-size:11px;color:#888;white-space:nowrap}
.acc-pills{display:flex;align-items:center;gap:5px;flex-wrap:wrap;margin-top:3px}
.acc-pill{display:inline-flex;align-items:center;gap:3px;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:500;text-decoration:none;white-space:nowrap;border:1px solid;-webkit-appearance:none;cursor:pointer;background:transparent}
.acc-pill.tel{color:#0F6E56;border-color:#C0DD97}
.acc-pill.town{color:#185FA5;border-color:#B5D4F4}
.acc-pill svg{width:10px;height:10px;flex-shrink:0}
.acc-chevron{width:16px;height:16px;flex-shrink:0;color:#aaa;transition:transform .22s}
.acc-card.open .acc-chevron{transform:rotate(180deg)}
.acc-cb{flex-shrink:0}

/* Expanded body — hidden by default */
.acc-body{display:none;border-top:1px solid #f0f0ee;padding:12px 13px;display:none}
.acc-card.open .acc-body{display:block}

/* Fields inside expanded body */
.acc-fields{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px}
.acc-field{font-size:12px;color:#1a1a18}
.acc-field-label{font-size:10px;font-weight:600;color:#aaa;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px}
.acc-field.full{grid-column:1/-1}
.acc-actions{display:flex;gap:8px}
.acc-actions .btn{flex:1;justify-content:center;padding:9px 10px}

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
/* Sur name autocomplete */
.sn-wrap{position:relative}
.sn-dropdown{position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #ddd;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:500;max-height:220px;overflow-y:auto;display:none;margin-top:3px}
.sn-dropdown.open{display:block}
.sn-item{padding:9px 12px;font-size:13px;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:8px;border-bottom:1px solid #f5f5f3;color:#1a1a18}
.sn-item:last-child{border-bottom:none}
.sn-item:hover,.sn-item.active{background:#E6F1FB}
.sn-item mark{background:none;color:#185FA5;font-weight:700;padding:0}
.sn-item-count{font-size:11px;color:#aaa;white-space:nowrap;flex-shrink:0}
.sn-new{color:#0F6E56;font-weight:600}
.sn-new svg{flex-shrink:0}
.sn-empty{padding:9px 12px;font-size:12px;color:#aaa;font-style:italic}
.mobile-dup-warn{font-size:11px;color:#A32D2D;margin-top:3px;display:none;align-items:center;gap:4px;background:#FCEBEB;border:1px solid #F7C1C1;border-radius:6px;padding:4px 8px}
.mobile-dup-warn.show{display:flex}
.mobile-ok{font-size:11px;color:#0F6E56;margin-top:3px;display:none;align-items:center;gap:4px}
.mobile-ok.show{display:flex}
.sn-spinner{display:inline-block;width:12px;height:12px;border:2px solid #ddd;border-top-color:#185FA5;border-radius:50%;animation:snSpin .6s linear infinite;margin-right:6px;vertical-align:middle}
@keyframes snSpin{to{transform:rotate(360deg)}}
.copy-addr-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:#E6F1FB;color:#185FA5;border:1px solid #B5D4F4;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;-webkit-appearance:none;margin-bottom:4px;transition:background .15s}
.gps-btn{width:100%;padding:10px 14px;background:#0F766E;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .15s;-webkit-appearance:none;margin-bottom:8px}
.gps-btn:hover:not(:disabled){background:#0D6560}
.gps-btn:active:not(:disabled){opacity:.9}
.gps-btn:disabled{background:#aaa;cursor:not-allowed}
.gps-btn.gps-ok{background:#15803D}
.gps-spinner{width:14px;height:14px;border:2px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:gpsspin .7s linear infinite;flex-shrink:0}
@keyframes gpsspin{to{transform:rotate(360deg)}}
.gps-coord-strip{display:none;align-items:center;gap:6px;background:#f5f5f3;border-radius:7px;padding:7px 10px;margin-bottom:10px;font-size:11px;color:#888;grid-column:1/-1}
.gps-coord-strip.show{display:flex}
.gps-coord-val{font-family:monospace;font-size:11px;color:#1a1a18;font-weight:600}
.gps-error{display:none;align-items:center;gap:7px;background:#FCEBEB;border:1px solid #F7C1C1;border-radius:7px;padding:8px 10px;margin-bottom:8px;font-size:12px;color:#A32D2D;grid-column:1/-1}
.gps-error.show{display:flex}
.fi.gps-filled{background:#F0FDFC;border-color:#5EEAD4!important}
.copy-addr-btn:hover{background:#B5D4F4}
.copy-addr-btn svg{width:13px;height:13px;flex-shrink:0}
.copy-addr-done{color:#0F6E56;font-size:12px;font-weight:600;display:none;align-items:center;gap:4px;padding:7px 0}
.copy-addr-done.show{display:inline-flex}
</style>
<!-- Google Places API — controlled by FEATURE_GOOGLE_PLACES in config.php -->
<script>
var GOOGLE_API_KEY = '<?php echo defined("GOOGLE_API_KEY") ? GOOGLE_API_KEY : "YOUR_GOOGLE_API_KEY"; ?>';
var FF_EXPORT        = <?php echo $export_on; ?>;
// Column visibility (driven by feature flags — matches approval screen toggles)
var COL_EXPORT        = <?php echo $col_export_on; ?>;  // checkbox/select col
var FF_PHONE_COL = <?php echo $phone_col_on; ?>; // true = Phone column shown, false = hidden
var FF_APPROVAL      = <?php echo $approval_on; ?>;
var FF_GOOGLE_PLACES = <?php echo $gplaces_on; ?>;
var FF_COPY_ADDR    = <?php echo $copyaddr_on; ?>;
var FF_GPS_FILL     = <?php echo $gpsfill_on; ?>;
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


      <?php if(FEATURE_EXPORT): ?><button class="btn btn-amber" id="exportBtn"  onclick="exportSelected()" disabled>&#8595; Export</button><?php endif; ?>
      <button class="btn btn-primary" onclick="openAdd()">+ Add</button>
      <a href="login.php" class="nav-link">&#10003; Approvals</a>
    </div>
  </div>

  <!-- Desktop table -->
  <div class="table-card">
    <div class="table-wrap">
      <table id="contactsTable">
        <thead>
          <tr>
            <?php if(FEATURE_EXPORT): ?><th style="width:36px"><input type="checkbox" id="checkAll" onchange="toggleAll(this)"></th><?php endif; ?>
            <th style="min-width:160px">Name</th>
            <th style="min-width:120px" id="thPhone">Phone</th>
            <th style="min-width:100px">Home town</th>
            <th style="min-width:220px">Current address</th>
            <th style="min-width:220px">Vatan address</th>
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
    <p id="deleteModalText"><?php if(FEATURE_APPROVAL): ?>This will submit a delete request for approval. The contact will be removed once approved.<?php else: ?>This will permanently delete the contact. This action cannot be undone.<?php endif; ?></p>
    <div class="confirm-footer">
      <button class="btn" onclick="closeDeleteModal()">Cancel</button>
      <button class="btn btn-danger" onclick="confirmDelete()"><?php echo FEATURE_APPROVAL ? "Submit delete request" : "Delete permanently"; ?></button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
var API = 'api.php';

// ── Validation flags injected from PHP config.php ─────────────
var VALIDATION_FLAGS = <?php echo $vflags; ?>;
var editId = null;
var deleteId = null;
var currentPage = 1;
var searchTimer = null;
var selectedIds = {};
var allData = [];

var FIELDS = [
  {id:'first_name',  label:'First name',    ph:'First name',        required:true,  minLen:2, maxLen:50,  errMsg:'First name is required (min 2 chars)'},
  {id:'last_name',   label:'Sur name',      ph:'Sur name',          required:true,  minLen:2, maxLen:50,  errMsg:'Sur name is required (min 2 chars)', surname:true},
  {id:'father_name', label:'Father name',   ph:'Father name',       required:true,  minLen:2, maxLen:100, errMsg:'Father name is required (min 2 chars)'},
  {id:'mother_name', label:'Mother name',   ph:'Mother name',       required:true,  minLen:2, maxLen:100, errMsg:'Mother name is required (min 2 chars)'},
  {id:'dob',         label:'DOB (DD-MM-YYYY)', ph:'15-08-1993',     required:false, pattern:'dob',        errMsg:'Enter valid date as DD-MM-YYYY'},
  {id:'gender',      label:'Gender',        type:'select', opts:['','male','female','other'], optLabels:['— Select —','Male','Female','Other'], required:true, errMsg:'Please select gender'},
  {id:'mo_no',       label:'Mobile no',     ph:'+91 9999999999',    required:true,  pattern:'phone',      errMsg:'Enter valid 10-digit mobile number'},
  {id:'wp_no',       label:'WhatsApp no',   ph:'+91 9999999999',    required:false, pattern:'phone',      errMsg:'Enter valid 10-digit WhatsApp number'},
  {id:'Home_Town',   label:'Home town',     ph:'Home town',         required:true,  minLen:2, maxLen:100, errMsg:'Home town is required', hometown:true},
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

// Apply PHP validation flags to FIELDS — runs after FIELDS is declared
(function(){
  for(var i=0; i<FIELDS.length; i++){
    var f=FIELDS[i];
    if(!f.id) continue;
    if(typeof VALIDATION_FLAGS[f.id] !== 'undefined'){
      f.required = VALIDATION_FLAGS[f.id];
    }
  }
})();

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
// ── Phone show/hide ────────────────────────────────────────────────────────────
// phonesVisible — true = Phone column shown, false = Phone column hidden
// Button always shown on index page; toggle shows/hides entire column
var phonesVisible = FF_PHONE_COL; // start visible if flag ON, hidden if OFF

// Mask: show first 2 + last 2 digits e.g. 98••••••10
function maskPhone(num){
  var d = String(num||'').replace(/[^0-9]/g,'');
  if(d.length >= 4) return d.substr(0,2) + '••••••' + d.substr(-2);
  return '••••••';
}

// SVG icons as plain strings — no nesting issues
var SVG_PHONE = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 11a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .18h3a2 2 0 012 1.72 12.05 12.05 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.05 12.05 0 002.81.7A2 2 0 0122 14.92z"/></svg>';
var SVG_WA    = '<svg viewBox="0 0 24 24" fill="currentColor" width="12" height="12"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a9.15 9.15 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479s1.065 2.875 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347zm-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884zm8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';
var SVG_EYE_OFF = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19M6.34 6.34a3 3 0 104.24 4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
var SVG_EYE_ON  = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';

function buildCallHtml(callNo, waNo){
  var tel = cleanPhone(callNo);
  var wa  = cleanPhone(waNo || callNo);
  return '<div class="phone-links">'+
    '<a class="call-link tel" href="tel:+'+tel+'" onclick="event.stopPropagation()">'+SVG_PHONE+'Call</a>'+
    '<a class="call-link wa" href="https://wa.me/'+wa+'" target="_blank" onclick="event.stopPropagation()">'+SVG_WA+'Chat</a>'+
  '</div>';
}

// phoneLinks — always returns Call/Chat buttons
// Column visibility is handled via show/hide toggle (phonesVisible)
function phoneLinks(mo, wp){
  var callNo = mo || wp;
  var waNo   = wp || mo;
  if(!callNo) return '—';
  return buildCallHtml(callNo, waNo);
}

// Reveal single row on tap
function revealPhone(el){
  var mo = el.getAttribute('data-mo');
  var wp = el.getAttribute('data-wp');
  el.outerHTML = buildCallHtml(mo||wp, wp||mo);
}

// Toggle button — show/hide Phone column (th + td + cards)
function togglePhoneVisibility(){
  phonesVisible = !phonesVisible;

  var btn = document.getElementById('phoneToggleBtn');
  var txt = document.getElementById('phoneToggleTxt');
  var ico = document.getElementById('phoneToggleIcon');
  if(txt) txt.textContent = phonesVisible ? 'Hide phones' : 'Show phones';
  if(btn) btn.classList.toggle('phones-visible', phonesVisible);
  if(ico) ico.innerHTML  = phonesVisible ? SVG_EYE_ON : SVG_EYE_OFF;

  // Re-render so phoneLinks() shows correct state
  renderTable(allData);
  renderCards(allData);
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
  if(!data.length){
    var nc=document.querySelectorAll('thead tr th').length||8;
    tbody.innerHTML='<tr><td colspan="'+nc+'" class="loading">No approved contacts found</td></tr>';
    updateSelUI();return;
  }
  var html='';
  for(var i=0;i<data.length;i++){
    var c=data[i];
    var chk=selectedIds[c.id]?' checked':'';
    var sel=selectedIds[c.id]?' selected':'';
    var curAddr=fullAddr(c,'');
    var vatAddr=fullAddr(c,'Vatan_');
    html+='<tr class="'+sel+'">' +
      (COL_EXPORT?'<td><input type="checkbox" class="row-cb" value="'+c.id+'"'+chk+' onchange="toggleRowCb(this,'+c.id+')"></td>':'')+
      '<td><span class="avatar">'+initials(c)+'</span>'+esc(c.first_name)+' '+esc(c.last_name)+'</td>'+
      (phonesVisible?'<td>'+phoneLinks(c.mo_no,c.wp_no)+'</td>':'')+
      '<td>'+esc(c.Home_Town||'—')+'</td>'+
      '<td style="white-space:normal;font-size:12px;color:#444;max-width:220px">'+esc(curAddr||'—')+'</td>'+
      '<td style="white-space:normal;font-size:12px;color:#444;max-width:220px">'+esc(vatAddr||'—')+'</td>'+
      '<td><div class="actions">'+
        '<button class="btn btn-sm btn-edit" onclick="openEdit('+c.id+')">Edit</button>'+
        '<button class="btn btn-sm btn-danger" onclick="openDelete('+c.id+')">Del</button>'+
      '</div></td></tr>';
  }
  tbody.innerHTML=html;
  updateSelUI();
}

// Mobile accordion cards
function renderCards(data){
  var list=document.getElementById('cardList');
  if(!data.length){list.innerHTML='<div class="loading">No approved contacts found</div>';return;}
  var html='';
  for(var i=0;i<data.length;i++){
    var c=data[i];
    var chk=selectedIds[c.id]?' checked':'';
    var curAddr=fullAddr(c,'');
    var vatAddr=fullAddr(c,'Vatan_');
    var ini=initials(c);
    var fullName=esc(c.first_name)+' '+esc(c.last_name);
    // Compact meta shown in header: phone if visible, else home town
    // Header pills: Home town (left) | Phone (right)
    var townPill = c.Home_Town
      ? '<span class="acc-pill town" onclick="event.stopPropagation()">'+
          '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>'+
          esc(c.Home_Town)+'</span>'
      : '';
    var phonePill = (phonesVisible && c.mo_no)
      ? '<a class="acc-pill tel" href="tel:+'+cleanPhone(c.mo_no)+'" onclick="event.stopPropagation()">'+
          '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 11a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 14.92z"/></svg>'+
          esc(c.mo_no)+'</a>'
      : '';

    html+=
      '<div class="acc-card" id="acc-'+c.id+'">'+

        // ── Collapsed header (always visible) ──
        '<div class="acc-header" onclick="accToggle('+c.id+')">'+
          (COL_EXPORT?'<input type="checkbox" class="acc-cb row-cb" value="'+c.id+'"'+chk+' onclick="event.stopPropagation()" onchange="toggleRowCb(this,'+c.id+')">':'')+
          '<span class="acc-avatar">'+ini+'</span>'+
          '<div style="flex:1;min-width:0">'+
            '<div class="acc-name">'+fullName+'</div>'+
            ((townPill||phonePill)?'<div class="acc-pills" style="justify-content:space-between">'+townPill+phonePill+'</div>':'')+
          '</div>'+
          '<svg class="acc-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>'+
        '</div>'+

        // ── Expanded body (hidden until tapped) ──
        '<div class="acc-body">'+
          // Phone links + Home town pill on same row
          '<div class="acc-contact-row">'+
            (phonesVisible&&(c.mo_no||c.wp_no) ? phoneLinks(c.mo_no,c.wp_no) : '')+
            (c.Home_Town ?
              '<span class="hometown-pill">'+
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">'+
                  '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>'+
                  '<polyline points="9 22 9 12 15 12 15 22"/>'+
                '</svg>'+
                esc(c.Home_Town)+
              '</span>' : '')+
          '</div>'+
          '<div class="acc-fields">'+
            // Current address
            (curAddr?
              '<div class="acc-field full">'+
                '<div class="acc-field-label">Current address</div>'+
                '<div style="color:#444;font-size:12px;line-height:1.5">'+esc(curAddr)+'</div>'+
              '</div>' : '')+
            // Vatan address
            (vatAddr?
              '<div class="acc-field full">'+
                '<div class="acc-field-label">Vatan address</div>'+
                '<div style="color:#444;font-size:12px;line-height:1.5">'+esc(vatAddr)+'</div>'+
              '</div>' : '')+
          '</div>'+
          // Action buttons
          '<div class="acc-actions">'+
            '<button class="btn btn-edit" onclick="openEdit('+c.id+')">&#9998; Edit</button>'+
            '<button class="btn btn-danger" onclick="openDelete('+c.id+')">&#10005; Delete</button>'+
          '</div>'+
        '</div>'+

      '</div>';
  }
  list.innerHTML=html;
}

// Accordion toggle — open one, close others
function accToggle(id){
  var cards=document.querySelectorAll('.acc-card');
  for(var i=0;i<cards.length;i++){
    var card=cards[i];
    if(card.id==='acc-'+id){
      card.classList.toggle('open');
    } else {
      card.classList.remove('open'); // close others
    }
  }
}

function changePage(d){currentPage+=d;loadContacts();
// Apply initial phone column header visibility
setTimeout(function(){
  var th = document.getElementById('thPhone');
  if(th) th.style.display = phonesVisible ? '' : 'none';
}, 100);}
document.getElementById('searchInput').addEventListener('input',function(){
  clearTimeout(searchTimer);
  searchTimer=setTimeout(function(){currentPage=1;loadContacts();},350);
});

function toggleAll(cb){
  if(!COL_EXPORT) return;
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
  if(COL_EXPORT){ sb.textContent=n+' selected'; sb.style.display=n?'inline':'none'; }
  else { sb.style.display='none'; }
  var eb=document.getElementById('exportBtn');
  if(eb){ if(FF_EXPORT && COL_EXPORT) eb.disabled=(n===0); else eb.style.display='none'; }
  var all=allData.length>0;
  for(var i=0;i<allData.length;i++){if(!selectedIds[allData[i].id]){all=false;break;}}
  if(COL_EXPORT){
    var ca=document.getElementById('checkAll'); if(ca) ca.checked=all;
  }
}

function buildForm(values){
  values=values||{};
  // Count optional fields for info note
  var optCount=0;
  for(var i=0;i<FIELDS.length;i++){ if(FIELDS[i].id && !FIELDS[i].required) optCount++; }
  var html='';
  if(optCount>0){
    html+='<div style="font-size:11px;color:#888;background:#f5f5f3;border-radius:7px;padding:6px 10px;margin-bottom:10px">'+
      '<span style="color:#A32D2D;font-weight:700">*</span> Required &nbsp;|&nbsp; Fields without * are optional'+
    '</div>';
  }
  html+='<div class="form-grid">';
  var firstSec=true;
  for(var i=0;i<FIELDS.length;i++){
    var f=FIELDS[i];
    if(f.sec!==undefined){
      // Inject copy button before Vatan address section
      if(f.sec==='Current address' && FF_GPS_FILL){
        // GPS auto-populate button for current address
        html+='<div style="grid-column:1/-1;margin-bottom:4px">'+
          '<button type="button" class="gps-btn" id="gpsFillBtn" onclick="gpsAutoFill()">'+
            '<svg width="15" height="15" viewBox="0 0 18 18" fill="none"><circle cx="9" cy="9" r="3" fill="white"/><path d="M9 1v2.5M9 14.5V17M1 9h2.5M14.5 9H17" stroke="white" stroke-width="1.6" stroke-linecap="round"/><circle cx="9" cy="9" r="7" stroke="white" stroke-width="1.2" stroke-dasharray="2.5 2"/></svg>'+
            'Use my current location'+
          '</button>'+
          '<div class="gps-error" id="gpsError"><svg width="13" height="13" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1a7 7 0 100 14A7 7 0 008 1zm-.75 3.5h1.5v5h-1.5v-5zm0 6h1.5v1.5h-1.5V10.5z"/></svg><span id="gpsErrorTxt"></span></div>'+
          '<div class="gps-coord-strip" id="gpsCoordStrip">'+
            '<svg width="12" height="12" viewBox="0 0 18 18" fill="none"><circle cx="9" cy="9" r="3" fill="#0F766E"/><circle cx="9" cy="9" r="7" stroke="#0F766E" stroke-width="1.2" stroke-dasharray="2.5 2"/></svg>'+
            '<span id="gpsLat" class="gps-coord-val">—</span>'+
            '<span style="color:#ddd">|</span>'+
            '<span id="gpsLng" class="gps-coord-val">—</span>'+
            '<span style="margin-left:4px">Accuracy: <span id="gpsAcc" class="gps-coord-val">—</span></span>'+
          '</div>'+
        '</div>';
      }
      if(f.sec==='Vatan address' && FF_COPY_ADDR){
        html+='<div style="grid-column:1/-1;display:flex;align-items:center;gap:10px;padding:4px 0">'+
          '<button type="button" class="copy-addr-btn" onclick="copyToVatan()">'+
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>'+
            'Copy Current address to Vatan address'+
          '</button>'+
          '<span class="copy-addr-done" id="copyDoneMsg">'+
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg>'+
            'Copied!'+
          '</span>'+
        '</div>';
      }
      html+='<div class="sec">'+f.sec+'</div>';firstSec=false;continue;
    }
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
      } else if(f.surname){
        html+='<div class="sn-wrap">'+
          '<input class="fi" id="fi_'+f.id+'" placeholder="'+esc(f.ph||'')+'" value="'+val+'" autocomplete="off" data-surname="1">'+
          '<div class="sn-dropdown" id="sn_drop_'+f.id+'"></div>'+
        '</div>';
      } else if(f.hometown){
        html+='<div class="sn-wrap">'+
          '<input class="fi" id="fi_'+f.id+'" placeholder="'+esc(f.ph||'')+'" value="'+val+'" autocomplete="off" data-hometown="1">'+
          '<div class="sn-dropdown" id="sn_drop_'+f.id+'"></div>'+
        '</div>';
      } else if(f.id==='mo_no'){
        html+='<input class="fi" id="fi_'+f.id+'" placeholder="'+esc(f.ph||'')+'" value="'+val+'" autocomplete="off">'+
              '<div class="mobile-dup-warn" id="mob_dup_warn"><svg width="11" height="11" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1a7 7 0 100 14A7 7 0 008 1zm-.75 3.5h1.5v5h-1.5v-5zm0 6h1.5v1.5h-1.5V10.5z"/></svg><span id="mob_dup_txt"></span></div>'+
              '<div class="mobile-ok" id="mob_ok"><svg width="11" height="11" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="2 8 6 12 14 4"/></svg><span>Available</span></div>';
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
  document.getElementById('saveBtn').textContent = FF_APPROVAL ? 'Submit for approval' : 'Save contact';
  document.getElementById('formModal').classList.add('open');
  setTimeout(function(){ attachLiveValidation(); initSurnameAC(); initHometownAC(); initMobileCheck(); }, 50);
}

function openEdit(id){
  ajax('GET',API+'?action=list&show_all=1&page=1&limit=10000',null,function(err,res){
    if(err||!res.success){showToast('Failed to load','error');return;}
    var c=null;
    for(var i=0;i<res.data.length;i++){if(res.data[i].id==id){c=res.data[i];break;}}
    if(!c){showToast('Not found','error');return;}
    editId=id;
    document.getElementById('modalTitle').textContent='Edit contact';
    document.getElementById('modalNote').innerHTML = FF_APPROVAL
      ? '<div class="note">Changes will be submitted for approval before going live.</div>'
      : '';
    document.getElementById('modalBody').innerHTML=buildForm(c);
    document.getElementById('saveBtn').disabled=false;
    document.getElementById('saveBtn').textContent = FF_APPROVAL ? 'Submit for approval' : 'Save changes';
    document.getElementById('formModal').classList.add('open');
    setTimeout(function(){ attachLiveValidation(); initSurnameAC(); initHometownAC(); initMobileCheck(); }, 100);
  });
}

function closeModal(){document.getElementById('formModal').classList.remove('open');}

function copyToVatan(){
  var MAP = {
    'block_no'       : 'Vatan_block_no',
    'address_line1'  : 'Vatan_address_line1',
    'street_address' : 'Vatan_Street_address',
    'city'           : 'Vatan_city',
    'state'          : 'Vatan_state',
    'zip'            : 'Vatan_zip',
    'country'        : 'Vatan_country'
  };
  var copied = 0;
  for(var src in MAP){
    var srcEl = document.getElementById('fi_'+src);
    var dstEl = document.getElementById('fi_'+MAP[src]);
    if(srcEl && dstEl){
      dstEl.value = srcEl.value;
      // Clear any existing error on dest field
      clearFieldError(MAP[src]);
      copied++;
    }
  }
  // Also copy village from Home_Town if Vatan_vilage is empty
  var htEl  = document.getElementById('fi_Home_Town');
  var vilEl = document.getElementById('fi_Vatan_vilage');
  if(htEl && vilEl && !vilEl.value && htEl.value){
    vilEl.value = htEl.value;
    clearFieldError('Vatan_vilage');
  }
  // Show success tick
  var msg = document.getElementById('copyDoneMsg');
  if(msg){
    msg.classList.add('show');
    setTimeout(function(){ msg.classList.remove('show'); }, 2500);
  }
  if(copied > 0) showToast('Current address copied to Vatan address', 'success');
}

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
    if(!res.success && res.duplicate_mobile){
      // Highlight mobile field with duplicate error
      showFieldError('mo_no', res.message);
      var mobInp = document.getElementById('fi_mo_no');
      if(mobInp) mobInp.scrollIntoView({behavior:'smooth',block:'center'});
      showToast(res.message, 'error');
      return;
    }
    if(res.success){
      var msg = FF_APPROVAL
        ? (isEdit ? 'Change submitted for approval!' : 'Contact submitted for approval!')
        : (isEdit ? 'Contact updated!'               : 'Contact saved!');
      showToast(msg, FF_APPROVAL ? 'info' : 'success');
      closeModal(); loadContacts();
    }
    else showToast(res.message||'Error','error');
  });
}

function openDelete(id){deleteId=id;document.getElementById('deleteModal').classList.add('open');}
function closeDeleteModal(){document.getElementById('deleteModal').classList.remove('open');}
function confirmDelete(){
  ajax('POST',API+'?action=delete',{id:deleteId},function(err,res){
    if(err){showToast(err,'error');return;}
    if(res.success){showToast(FF_APPROVAL ? 'Delete request submitted for approval' : 'Contact deleted','info');closeDeleteModal();loadContacts();}
    else showToast(res.message,'error');
  });
}

function exportSelected(){
  if(!FF_EXPORT || !COL_EXPORT){ showToast('Export is disabled','error'); return; }
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

// Apply initial phone column header visibility after first render
setTimeout(function(){
  var th = document.getElementById('thPhone');
  if(th) th.style.display = phonesVisible ? '' : 'none';
}, 50);
// ── Sur Name Autocomplete ──────────────────────────────────────────────────────
var snTimer = null;
var snActiveIdx = -1;

function initSurnameAC(){
  var inputs = document.querySelectorAll('[data-surname="1"]');
  for(var i=0;i<inputs.length;i++){
    (function(inp){
      if(inp._snAttached) return;
      inp._snAttached = true;
      var dropId = 'sn_drop_' + inp.id.replace('fi_','');
      var drop   = document.getElementById(dropId);
      if(!drop) return;

      inp.addEventListener('input', function(){
        clearTimeout(snTimer);
        var q = inp.value.trim();
        if(q.length < 1){ snClose(drop); return; }
        snTimer = setTimeout(function(){ snFetch(q, inp, drop); }, 250);
      });

      inp.addEventListener('keydown', function(e){
        var items = drop.querySelectorAll('.sn-item');
        if(!items.length) return;
        if(e.key==='ArrowDown'){
          e.preventDefault();
          snActiveIdx = Math.min(snActiveIdx+1, items.length-1);
          snHighlight(items);
        } else if(e.key==='ArrowUp'){
          e.preventDefault();
          snActiveIdx = Math.max(snActiveIdx-1, 0);
          snHighlight(items);
        } else if(e.key==='Enter' && snActiveIdx>=0){
          e.preventDefault();
          items[snActiveIdx].click();
        } else if(e.key==='Escape'){
          snClose(drop);
        }
      });

      // Close on outside click
      document.addEventListener('mousedown', function(e){
        if(!inp.contains(e.target) && !drop.contains(e.target)) snClose(drop);
      });
    })(inputs[i]);
  }
}

function snFetch(q, inp, drop){
  drop.innerHTML = '<div class="sn-empty"><span class="sn-spinner"></span>Searching...</div>';
  drop.classList.add('open');
  snActiveIdx = -1;

  var xsn = new XMLHttpRequest();
  xsn.open('GET', API+'?action=surname_suggest&q='+encodeURIComponent(q), true);
  xsn.onreadystatechange = function(){
    if(xsn.readyState!==4) return;
    // Strip any PHP warnings before JSON
    var raw = xsn.responseText;
    var js  = raw.indexOf('{');
    if(js>0) raw = raw.substring(js);
    try{
      var res = JSON.parse(raw);
      snRender(res.data||[], q, inp, drop);
    } catch(e){
      drop.innerHTML = '<div class="sn-empty">Error loading suggestions</div>';
    }
  };
  xsn.onerror = function(){
    drop.innerHTML = '<div class="sn-empty">Network error</div>';
  };
  xsn.send();
}

function snRender(list, q, inp, drop){
  var html = '';

  // Case-insensitive highlight — no regex, no escaping issues
  function hilight(name, search){
    var lo  = name.toLowerCase();
    var sl  = search.toLowerCase();
    var idx = lo.indexOf(sl);
    if(idx < 0) return esc(name);
    return esc(name.substring(0, idx)) +
           '<mark>' + esc(name.substring(idx, idx + search.length)) + '</mark>' +
           esc(name.substring(idx + search.length));
  }

  for(var i=0; i<list.length; i++){
    var name  = list[i].last_name;
    var count = list[i].cnt;
    var hi    = hilight(name, q);
    html += '<div class="sn-item" data-val="'+esc(name)+'">'+
      '<span>'+hi+'</span>'+
      '<span class="sn-item-count">'+count+' contact'+(count>1?'s':'')+' </span>'+
    '</div>';
  }

  // "Add new" option — show if no exact match
  var exactMatch = list.some(function(r){ return r.last_name.toLowerCase()===q.toLowerCase(); });
  if(!exactMatch){
    html += '<div class="sn-item sn-new" data-val="'+esc(q)+'">'+
      '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>'+
      '<span>Add &ldquo;'+esc(q)+'&rdquo; as new Sur name</span>'+
    '</div>';
  }

  if(!html){
    html = '<div class="sn-empty">No matches found.</div>';
  }

  drop.innerHTML = html;
  drop.classList.add('open');

  // Attach click handlers
  var items = drop.querySelectorAll('.sn-item');
  for(var j=0; j<items.length; j++){
    (function(item){
      item.addEventListener('mousedown', function(e){
        e.preventDefault();
        inp.value = item.getAttribute('data-val');
        snClose(drop);
        inp.classList.remove('error');
        var errEl = document.getElementById('err_' + inp.id.replace('fi_',''));
        if(errEl) errEl.classList.remove('show');
      });
    })(items[j]);
  }
}

function snHighlight(items){
  for(var i=0;i<items.length;i++) items[i].classList.toggle('active', i===snActiveIdx);
  if(items[snActiveIdx]) items[snActiveIdx].scrollIntoView({block:'nearest'});
}

function snClose(drop){
  if(drop){ drop.classList.remove('open'); drop.innerHTML=''; }
  snActiveIdx = -1;
}

// ── Home Town Autocomplete (reuses sn* helpers) ───────────────────────────────
var htTimer = null;

function initHometownAC(){
  var inputs = document.querySelectorAll('[data-hometown="1"]');
  for(var i=0;i<inputs.length;i++){
    (function(inp){
      if(inp._htAttached) return;
      inp._htAttached = true;
      var dropId = 'sn_drop_' + inp.id.replace('fi_','');
      var drop   = document.getElementById(dropId);
      if(!drop) return;

      inp.addEventListener('input', function(){
        clearTimeout(htTimer);
        var q = inp.value.trim();
        if(q.length < 1){ snClose(drop); return; }
        htTimer = setTimeout(function(){ htFetch(q, inp, drop); }, 250);
      });

      inp.addEventListener('keydown', function(e){
        var items = drop.querySelectorAll('.sn-item');
        if(!items.length) return;
        if(e.key==='ArrowDown'){
          e.preventDefault();
          snActiveIdx = Math.min(snActiveIdx+1, items.length-1);
          snHighlight(items);
        } else if(e.key==='ArrowUp'){
          e.preventDefault();
          snActiveIdx = Math.max(snActiveIdx-1, 0);
          snHighlight(items);
        } else if(e.key==='Enter' && snActiveIdx>=0){
          e.preventDefault();
          items[snActiveIdx].click();
        } else if(e.key==='Escape'){
          snClose(drop);
        }
      });

      document.addEventListener('mousedown', function(e){
        if(!inp.contains(e.target) && !drop.contains(e.target)) snClose(drop);
      });
    })(inputs[i]);
  }
}

function htFetch(q, inp, drop){
  drop.innerHTML = '<div class="sn-empty"><span class="sn-spinner"></span>Searching...</div>';
  drop.classList.add('open');
  snActiveIdx = -1;

  var xht = new XMLHttpRequest();
  xht.open('GET', API+'?action=hometown_suggest&q='+encodeURIComponent(q), true);
  xht.onreadystatechange = function(){
    if(xht.readyState!==4) return;
    var raw = xht.responseText;
    var js  = raw.indexOf('{');
    if(js>0) raw = raw.substring(js);
    try{
      var res = JSON.parse(raw);
      htRender(res.data||[], q, inp, drop);
    } catch(e){
      drop.innerHTML = '<div class="sn-empty">Error loading suggestions</div>';
    }
  };
  xht.onerror = function(){ drop.innerHTML = '<div class="sn-empty">Network error</div>'; };
  xht.send();
}

function htRender(list, q, inp, drop){
  var html = '';

  function hilight(name, search){
    var lo  = name.toLowerCase();
    var sl  = search.toLowerCase();
    var idx = lo.indexOf(sl);
    if(idx < 0) return esc(name);
    return esc(name.substring(0,idx)) +
           '<mark>' + esc(name.substring(idx, idx+search.length)) + '</mark>' +
           esc(name.substring(idx+search.length));
  }

  for(var i=0; i<list.length; i++){
    var name  = list[i].Home_Town;
    var count = list[i].cnt;
    var hi    = hilight(name, q);
    html += '<div class="sn-item" data-val="'+esc(name)+'">'+
      '<span>'+hi+'</span>'+
      '<span class="sn-item-count">'+count+' contact'+(count>1?'s':'')+' </span>'+
    '</div>';
  }

  var exactMatch = list.some(function(r){ return r.Home_Town.toLowerCase()===q.toLowerCase(); });
  if(!exactMatch){
    html += '<div class="sn-item sn-new" data-val="'+esc(q)+'">'+
      '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>'+
      '<span>Add &ldquo;'+esc(q)+'&rdquo; as new Home town</span>'+
    '</div>';
  }

  if(!html) html = '<div class="sn-empty">No matches found.</div>';

  drop.innerHTML = html;
  drop.classList.add('open');

  var items = drop.querySelectorAll('.sn-item');
  for(var j=0; j<items.length; j++){
    (function(item){
      item.addEventListener('mousedown', function(e){
        e.preventDefault();
        inp.value = item.getAttribute('data-val');
        snClose(drop);
        inp.classList.remove('error');
        var errEl = document.getElementById('err_' + inp.id.replace('fi_',''));
        if(errEl) errEl.classList.remove('show');
      });
    })(items[j]);
  }
}

// ── Mobile duplicate live check ──────────────────────────────────────────────
var mobCheckTimer = null;

function initMobileCheck(){
  var inp = document.getElementById('fi_mo_no');
  if(!inp || inp._mobAttached) return;
  inp._mobAttached = true;

  inp.addEventListener('input', function(){
    clearTimeout(mobCheckTimer);
    var warn = document.getElementById('mob_dup_warn');
    var ok   = document.getElementById('mob_ok');
    if(warn) warn.classList.remove('show');
    if(ok)   ok.classList.remove('show');

    var val = inp.value.replace(/[^0-9]/g,'');
    if(val.length < 8) return; // wait for enough digits

    mobCheckTimer = setTimeout(function(){
      var excludeId = editId || 0;
      var xmob = new XMLHttpRequest();
      xmob.open('GET', API+'?action=mobile_check&mo='+encodeURIComponent(inp.value)+'&exclude_id='+excludeId, true);
      xmob.onreadystatechange = function(){
        if(xmob.readyState!==4) return;
        try{
          var raw = xmob.responseText;
          var js  = raw.indexOf('{'); if(js>0) raw=raw.substring(js);
          var res = JSON.parse(raw);
          if(!res.available){
            inp.classList.add('error');
            if(warn){ document.getElementById('mob_dup_txt').textContent = res.message; warn.classList.add('show'); }
            if(ok)   ok.classList.remove('show');
          } else {
            inp.classList.remove('error');
            if(warn) warn.classList.remove('show');
            if(ok)   ok.classList.add('show');
            setTimeout(function(){ if(ok) ok.classList.remove('show'); }, 2000);
          }
        } catch(e){}
      };
      xmob.send();
    }, 500);
  });
}

// ── GPS Auto-fill (Nominatim / OpenStreetMap — free, no API key) ─────────────
function gpsAutoFill(){
  if(!FF_GPS_FILL) return;
  var btn = document.getElementById('gpsFillBtn');
  if(!btn) return;
  if(!navigator.geolocation){
    gpsShowError('Geolocation is not supported by your browser.');
    return;
  }
  gpsSetLoading(true);
  gpsHideError();
  navigator.geolocation.getCurrentPosition(
    gpsOnSuccess,
    gpsOnError,
    {enableHighAccuracy:true, timeout:12000, maximumAge:0}
  );
}

function gpsOnSuccess(pos){
  var lat = pos.coords.latitude;
  var lng = pos.coords.longitude;
  var acc = Math.round(pos.coords.accuracy);

  var latEl = document.getElementById('gpsLat');
  var lngEl = document.getElementById('gpsLng');
  var accEl = document.getElementById('gpsAcc');
  var strip = document.getElementById('gpsCoordStrip');
  if(latEl) latEl.textContent = lat.toFixed(5);
  if(lngEl) lngEl.textContent = lng.toFixed(5);
  if(accEl) accEl.textContent = acc+'m';
  if(strip) strip.classList.add('show');

  // Reverse geocode via Nominatim (OpenStreetMap — free, no API key needed)
  var url = 'https://nominatim.openstreetmap.org/reverse?lat='+lat+'&lon='+lng+'&format=json&addressdetails=1&accept-language=en';
  var xhr = new XMLHttpRequest();
  xhr.open('GET', url, true);
  xhr.setRequestHeader('User-Agent','ContactBook-AddressPicker');
  xhr.onreadystatechange = function(){
    if(xhr.readyState !== 4) return;
    gpsSetLoading(false);
    if(xhr.status === 200){
      try{
        var data = JSON.parse(xhr.responseText);
        if(data && data.address){
          gpsFillFields(data.address);
          gpsSetSuccess();
        } else {
          gpsShowError('Location found but address could not be resolved. Please fill manually.');
          gpsSetSuccess();
        }
      } catch(e){
        gpsShowError('Could not parse address response. Please fill manually.');
      }
    } else {
      gpsShowError('Reverse geocode failed (status '+xhr.status+'). Please fill manually.');
    }
  };
  xhr.onerror = function(){
    gpsSetLoading(false);
    gpsShowError('Network error during reverse geocode. Please fill manually.');
  };
  xhr.send();
}

function gpsOnError(err){
  gpsSetLoading(false);
  var msgs = {
    1:'Location access denied. Please allow location permission and try again.',
    2:'Location unavailable. Make sure GPS is enabled on your device.',
    3:'Location request timed out. Please try again.'
  };
  gpsShowError(msgs[err.code] || 'Could not get your location.');
}

function gpsFillFields(a){
  // Build street from house number + road + suburb/neighbourhood
  var streetParts = [];
  if(a.house_number)  streetParts.push(a.house_number);
  if(a.road)          streetParts.push(a.road);
  if(a.suburb)        streetParts.push(a.suburb);
  if(a.neighbourhood) streetParts.push(a.neighbourhood);
  var street = streetParts.join(', ');

  // Address line 1 = building/area detail
  var addr1Parts = [];
  if(a.amenity)      addr1Parts.push(a.amenity);
  if(a.building)     addr1Parts.push(a.building);
  if(a.quarter)      addr1Parts.push(a.quarter);
  var addr1 = addr1Parts.join(', ') || (a.suburb || a.neighbourhood || '');

  var city    = a.city    || a.town    || a.village || a.county || '';
  var state   = a.state   || '';
  var zip     = a.postcode|| '';
  var country = a.country || '';

  gpsSetField('fi_street_address',  street);
  gpsSetField('fi_address_line1',   addr1);
  gpsSetField('fi_city',            city);
  gpsSetField('fi_state',           state);
  gpsSetField('fi_zip',             zip);
  gpsSetField('fi_country',         country);

  // Clear any validation errors on filled fields
  var filled = ['street_address','address_line1','city','state','zip','country'];
  for(var i=0;i<filled.length;i++){
    if(document.getElementById('fi_'+filled[i]) && document.getElementById('fi_'+filled[i]).value)
      clearFieldError(filled[i]);
  }
}

function gpsSetField(id, value){
  var el = document.getElementById(id);
  if(!el) return;
  el.value = value || '';
  if(value) el.classList.add('gps-filled');
  else el.classList.remove('gps-filled');
  // Clear error if now has value
  if(value && el.classList.contains('error')) el.classList.remove('error');
}

function gpsSetLoading(on){
  var btn = document.getElementById('gpsFillBtn');
  if(!btn) return;
  btn.disabled = on;
  btn.classList.remove('gps-ok');
  if(on){
    btn.innerHTML = '<span class="gps-spinner"></span><span>Getting location…</span>';
  } else {
    btn.innerHTML = '<svg width="15" height="15" viewBox="0 0 18 18" fill="none"><circle cx="9" cy="9" r="3" fill="white"/><path d="M9 1v2.5M9 14.5V17M1 9h2.5M14.5 9H17" stroke="white" stroke-width="1.6" stroke-linecap="round"/><circle cx="9" cy="9" r="7" stroke="white" stroke-width="1.2" stroke-dasharray="2.5 2"/></svg><span>Use my current location</span>';
  }
}

function gpsSetSuccess(){
  var btn = document.getElementById('gpsFillBtn');
  if(!btn) return;
  btn.disabled = false;
  btn.classList.add('gps-ok');
  btn.innerHTML = '<svg width="15" height="15" viewBox="0 0 18 18" fill="none"><circle cx="9" cy="9" r="7.5" fill="rgba(255,255,255,.2)"/><path d="M5 9.5L7.5 12L13 6.5" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Location detected — tap to refresh</span>';
  showToast('Address auto-filled from GPS!','success');
}

function gpsShowError(msg){
  var el  = document.getElementById('gpsError');
  var txt = document.getElementById('gpsErrorTxt');
  if(el)  el.classList.add('show');
  if(txt) txt.textContent = msg;
}

function gpsHideError(){
  var el = document.getElementById('gpsError');
  if(el) el.classList.remove('show');
}

// ── Google Places Autocomplete ─────────────────────────────────────────────
var googleLoaded = false;

function loadGoogleMaps(){
  if(!FF_GOOGLE_PLACES) return;
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
