<?php
require_once 'config.php';
require_once 'auth.php';
requireLogin();
$admin      = $_SESSION['admin_user'];
$wa_phone   = WA_PHONE;
$masked     = str_repeat('*', max(0, strlen($wa_phone)-4)) . substr($wa_phone, -4);
$otp_on     = FEATURE_WA_OTP  ? 'true' : 'false';
$fallback_on= FEATURE_OTP_FALLBACK ? 'true' : 'false';
$export_on  = FEATURE_EXPORT  ? 'true' : 'false';
$approval_on= FEATURE_APPROVAL? 'true' : 'false';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Approvals</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,sans-serif;background:#f5f5f3;color:#1a1a18;padding:1rem}
.wrap{max-width:960px;margin:0 auto}
.toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;gap:8px;flex-wrap:wrap}
.toolbar-left{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.toolbar-right{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
@media(max-width:600px){.toolbar{flex-direction:column;align-items:stretch}.toolbar-left,.toolbar-right{justify-content:space-between}}
.btn{padding:8px 14px;border:1px solid #ddd;border-radius:8px;font-size:13px;cursor:pointer;background:#fff;color:#1a1a18;white-space:nowrap;-webkit-appearance:none;display:inline-flex;align-items:center;gap:5px;transition:opacity .15s}
.btn:active{opacity:.75}
.btn:disabled{opacity:.45;cursor:default}
.btn-sm{padding:7px 14px;font-size:13px}
.btn-primary{background:#185FA5;color:#fff;border-color:#185FA5}
.btn-approve{background:#0F6E56;color:#fff;border-color:#0F6E56}
.btn-reject{background:#A32D2D;color:#fff;border-color:#A32D2D}
.btn-logout{color:#A32D2D;border-color:#F7C1C1}
.btn-wa{background:#25D366;color:#fff;border-color:#25D366}
.nav-link{font-size:13px;color:#185FA5;text-decoration:none;padding:8px 14px;border:1px solid #B5D4F4;border-radius:8px;background:#E6F1FB;white-space:nowrap}
.badge{display:inline-block;padding:2px 9px;border-radius:999px;font-size:11px;font-weight:600}
.badge-create{background:#EAF3DE;color:#3B6D11}
.badge-update{background:#E6F1FB;color:#185FA5}
.badge-delete{background:#FCEBEB;color:#A32D2D}
.badge-count{background:#FAEEDA;color:#854F0B;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600}
.admin-chip{display:inline-flex;align-items:center;gap:6px;background:#f5f5f3;border:1px solid #e0e0dd;border-radius:999px;padding:4px 10px 4px 4px;font-size:12px;color:#555}
.admin-avatar{width:22px;height:22px;border-radius:50%;background:#185FA5;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:700}

/* OTP banner */
.otp-banner{background:#fff;border:1px solid #e0e0dd;border-radius:12px;padding:14px 16px;margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.otp-banner.verified{border-color:#C0DD97;background:#F6FBF0}
.otp-banner-left{display:flex;align-items:center;gap:10px}
.otp-status-dot{width:10px;height:10px;border-radius:50%;background:#ddd;flex-shrink:0}
.otp-status-dot.ok{background:#3B6D11}
.otp-status-dot.pending{background:#BA7517;animation:pulse 1.2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.otp-banner-text{font-size:13px;color:#555;line-height:1.5}
.otp-banner-text strong{color:#1a1a18;display:block;font-size:14px}
.otp-banner-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.otp-input{padding:9px 12px;border:1.5px solid #ddd;border-radius:8px;font-size:16px;letter-spacing:.2em;width:130px;text-align:center;outline:none;-webkit-appearance:none}
.otp-input:focus{border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.12)}
.otp-timer{font-size:12px;color:#BA7517;font-weight:600;min-width:40px}

/* Pending cards */
.pending-item{background:#fff;border:1px solid #e0e0dd;border-radius:14px;padding:16px;margin-bottom:14px;transition:opacity .3s}
.pending-item.locked{opacity:.6;pointer-events:none;filter:blur(1px)}
.pending-header{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:12px}
.pending-name{font-size:15px;font-weight:700;flex:1;min-width:120px}
.pending-meta{font-size:11px;color:#aaa;margin-top:2px}
.contact-avatar{width:38px;height:38px;border-radius:50%;background:#B5D4F4;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#0C447C;flex-shrink:0}
.diff-section{font-size:10px;font-weight:700;color:#aaa;letter-spacing:.07em;text-transform:uppercase;margin:10px 0 6px;padding-bottom:4px;border-bottom:1px solid #f0f0ee}
.diff-section:first-child{margin-top:0}
.new-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:6px;margin-bottom:6px}
.new-field{font-size:13px;padding:7px 10px;border-radius:8px;border:1px solid #EAF3DE;background:#F6FBF0;line-height:1.4}
.new-field .fl{color:#3B6D11;display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px}
.changed-list{display:flex;flex-direction:column;gap:6px;margin-bottom:6px}
.changed-row{display:grid;grid-template-columns:110px 1fr auto 1fr;align-items:center;gap:8px;background:#fff;border:1.5px solid #B5D4F4;border-radius:9px;padding:9px 12px}
@media(max-width:560px){.changed-row{grid-template-columns:1fr;gap:4px}.arrow{display:none}}
.changed-label{font-size:11px;font-weight:700;color:#185FA5;text-transform:uppercase;letter-spacing:.04em}
.old-val{font-size:13px;color:#A32D2D;background:#FCEBEB;padding:3px 8px;border-radius:5px;word-break:break-all;text-decoration:line-through;opacity:.8}
.new-val{font-size:13px;color:#0F6E56;background:#EAF3DE;padding:3px 8px;border-radius:5px;word-break:break-all;font-weight:600}
.arrow{font-size:18px;color:#aaa;text-align:center}
.unchanged-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:6px}
.unchanged-field{font-size:12px;padding:6px 9px;border-radius:7px;border:1px solid #eee;background:#fafaf8;line-height:1.4}
.unchanged-field .fl{color:#aaa;display:block;font-size:10px;text-transform:uppercase;letter-spacing:.04em;margin-bottom:1px}
.delete-warn{background:#FCEBEB;border:1px solid #F7C1C1;border-radius:10px;padding:12px 14px;font-size:14px;color:#A32D2D;margin:8px 0;line-height:1.7}
.pending-actions{display:flex;gap:8px;align-items:stretch;flex-wrap:wrap;margin-top:12px;padding-top:12px;border-top:1px solid #f0f0ee}
.review-input{padding:9px 11px;border:1px solid #ddd;border-radius:8px;font-size:13px;flex:1;min-width:140px;background:#fff;color:#1a1a18;-webkit-appearance:none;outline:none}
.review-input:focus{border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.1)}
@media(max-width:500px){.pending-actions{flex-direction:column}.pending-actions .btn{width:100%;justify-content:center;padding:12px}.review-input{width:100%}}
.empty{text-align:center;padding:3rem 1rem;color:#888;font-size:14px;background:#fff;border-radius:14px;border:1px solid #eee;line-height:2}
.loading{text-align:center;padding:3rem;color:#888}

/* ── Feature flag panel ── */
.flags-panel{background:#fff;border:1px solid #e0e0dd;border-radius:14px;padding:0;margin-bottom:1.25rem;overflow:hidden}
.flags-panel-header{display:flex;align-items:center;justify-content:space-between;padding:13px 16px;cursor:pointer;user-select:none;-webkit-user-select:none;gap:8px}
.flags-panel-title{font-size:14px;font-weight:700;color:#1a1a18;display:flex;align-items:center;gap:8px}
.flags-panel-title svg{width:16px;height:16px;color:#185FA5;flex-shrink:0}
.flags-panel-body{border-top:1px solid #f0f0ee;padding:14px 16px;display:none}
.flags-panel-body.open{display:block}
.flag-group{margin-bottom:14px}
.flag-group:last-child{margin-bottom:0}
.flag-group-title{font-size:10px;font-weight:700;color:#aaa;letter-spacing:.07em;text-transform:uppercase;margin-bottom:8px;padding-bottom:5px;border-bottom:1px solid #f5f5f3}
.flag-rows{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:6px}
@media(max-width:500px){.flag-rows{grid-template-columns:1fr}}
.flag-row{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 10px;border-radius:8px;background:#fafaf8;border:1px solid #f0f0ee;transition:background .15s}
.flag-row:hover{background:#f5f5f3}
.flag-row-left{display:flex;flex-direction:column;gap:2px;flex:1;min-width:0}
.flag-label{font-size:13px;font-weight:500;color:#1a1a18}
.flag-desc{font-size:11px;color:#aaa}
/* iOS-style toggle switch */
.toggle-wrap{flex-shrink:0}
.toggle{position:relative;width:42px;height:24px;display:inline-block}
.toggle input{opacity:0;width:0;height:0;position:absolute}
.toggle-slider{position:absolute;inset:0;background:#ddd;border-radius:999px;cursor:pointer;transition:background .2s}
.toggle-slider:before{content:'';position:absolute;width:18px;height:18px;left:3px;top:3px;background:#fff;border-radius:50%;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.toggle input:checked + .toggle-slider{background:#185FA5}
.toggle input:checked + .toggle-slider:before{transform:translateX(18px)}
.toggle input:disabled + .toggle-slider{opacity:.5;cursor:default}
.flag-saving{font-size:10px;color:#BA7517;margin-top:2px;display:none}
.flag-saving.show{display:block}
.flags-chevron{transition:transform .2s;color:#888}
.flags-chevron.open{transform:rotate(180deg)}
.save-note{font-size:11px;color:#888;margin-top:10px;padding-top:10px;border-top:1px solid #f0f0ee;line-height:1.6}
.toast{position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%) translateY(20px);padding:10px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:999;opacity:0;transition:all .25s;pointer-events:none;white-space:nowrap;max-width:92vw;text-align:center}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
.toast-success{background:#EAF3DE;color:#3B6D11;border:1px solid #C0DD97}
.toast-error{background:#FCEBEB;color:#A32D2D;border:1px solid #F7C1C1}
.toast-info{background:#E6F1FB;color:#185FA5;border:1px solid #B5D4F4}
</style>
</head>
<body>
<div class="wrap">

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="toolbar-left">
      <span style="font-size:17px;font-weight:700">Approvals</span>
      <span id="totalBadge" class="badge-count">0</span>
    </div>
    <div class="toolbar-right">
      <div class="admin-chip">
        <span class="admin-avatar"><?php echo strtoupper(substr($admin,0,1)); ?></span>
        <?php echo htmlspecialchars($admin); ?>
      </div>
      <button class="btn" onclick="loadPending()">&#8635; Refresh</button>
      <a href="index.php" class="nav-link">&#8592; Contacts</a>
      <a href="logout.php" class="btn btn-logout">&#9866; Logout</a>
    </div>
  </div>

  <?php if(isset($_GET['timeout'])): ?>
  <div style="background:#FAEEDA;color:#854F0B;border:1px solid #FAC775;border-radius:8px;padding:10px 13px;font-size:13px;margin-bottom:1rem">
    &#9888; Session expired. Please login again.
  </div>
  <?php endif; ?>

  <!-- OTP Banner -->
  <div class="otp-banner" id="otpBanner">
    <div class="otp-banner-left">
      <div class="otp-status-dot" id="otpDot"></div>
      <div class="otp-banner-text">
        <strong id="otpTitle">WhatsApp OTP required</strong>
        <span id="otpSubtitle">Verify your identity to approve or reject changes. OTP will be sent to WhatsApp <?php echo $masked; ?></span>
      </div>
    </div>
    <div class="otp-banner-actions" id="otpActions">
      <button class="btn btn-wa" id="sendOtpBtn" onclick="sendOTP()">
        &#9654; Send OTP
      </button>
    </div>
  </div>

  <!-- Feature flags panel -->
  <div class="flags-panel" id="flagsPanel">
    <div class="flags-panel-header" onclick="toggleFlagsPanel()">
      <div class="flags-panel-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
        Feature Flags
        <span id="flagsSummary" style="font-size:11px;font-weight:400;color:#888"></span>
      </div>
      <svg class="flags-chevron" id="flagsChevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
    <div class="flags-panel-body" id="flagsPanelBody">
      <div id="flagsContent"><div class="loading" style="padding:1.5rem">Loading flags...</div></div>
      <div class="save-note">&#9432; Changes take effect immediately. Page reload may be needed for frontend flags (Google Places, Copy Address, Validation).</div>
    </div>
  </div>

  <div id="pendingList"><div class="loading">Loading...</div></div>
</div>

<div class="toast" id="toast"></div>

<script>
// ── Feature flags (from PHP config.php) ─────────────────────
var FF_WA_OTP   = <?php echo $otp_on; ?>;
var FF_EXPORT   = <?php echo $export_on; ?>;
var FF_APPROVAL = <?php echo $approval_on; ?>;

var API    = 'api.php';
var OTP_API= 'otp.php';
var otpVerified = false;
var otpTimer = null;
var pendingAction = null; // {type:'approve'|'reject', pid, note}

var LABELS = {
  first_name:'First name',last_name:'Last name',dob:'Date of birth',gender:'Gender',
  father_name:'Father name',mother_name:'Mother name',mo_no:'Mobile',wp_no:'WhatsApp',
  Home_Town:'Home town',statuz:'Status',
  block_no:'Block no',address_line1:'Address line 1',street_address:'Street address',
  city:'City',state:'State',zip:'Zip',country:'Country',
  Vatan_vilage:'Vatan village',Vatan_block_no:'Vatan block no',
  Vatan_Street_address:'Vatan street',Vatan_address_line1:'Vatan addr line 1',
  Vatan_city:'Vatan city',Vatan_state:'Vatan state',
  Vatan_zip:'Vatan zip',Vatan_country:'Vatan country'
};

var SECTIONS = {
  'Basic':['first_name','last_name','dob','gender','father_name','mother_name','mo_no','wp_no','Home_Town','statuz'],
  'Current address':['block_no','address_line1','street_address','city','state','zip','country'],
  'Vatan address':['Vatan_vilage','Vatan_block_no','Vatan_Street_address','Vatan_address_line1','Vatan_city','Vatan_state','Vatan_zip','Vatan_country']
};

function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function showToast(msg,type){
  type=type||'success';
  var t=document.getElementById('toast');
  t.textContent=msg; t.className='toast toast-'+type+' show';
  setTimeout(function(){t.className='toast';},3500);
}

function xhr(method,url,data,cb){
  var x=new XMLHttpRequest();
  x.open(method,url,true);
  if(data) x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  x.onreadystatechange=function(){
    if(x.readyState!==4) return;
    if(x.status===401||x.responseText.indexOf('login.php')>=0){ window.location.href='login.php'; return; }
    try{ cb(null,JSON.parse(x.responseText)); }
    catch(e){ cb('Error: '+x.responseText.substring(0,200)); }
  };
  x.onerror=function(){cb('Network error');};
  x.send(data||null);
}

function ajaxJSON(method,url,data,cb){
  var x=new XMLHttpRequest();
  x.open(method,url,true);
  if(data) x.setRequestHeader('Content-Type','application/json');
  x.onreadystatechange=function(){
    if(x.readyState!==4) return;
    if(x.status===403){
      try{
        var r=JSON.parse(x.responseText);
        if(r.otp_required){ showOTPPrompt(); return; }
      }catch(e){}
    }
    if(x.status===401||x.responseText.indexOf('login.php')>=0){ window.location.href='login.php'; return; }
    try{ cb(null,JSON.parse(x.responseText)); }
    catch(e){ cb('Error: '+x.responseText.substring(0,200)); }
  };
  x.onerror=function(){cb('Network error');};
  x.send(data?JSON.stringify(data):null);
}

// ── OTP ───────────────────────────────────────────────────────────────────────
function hideBanner(){
  var b=document.getElementById('otpBanner');
  if(b) b.style.display='none';
}

function setOTPVerified(verified){
  // If OTP feature disabled — always treat as verified
  if(!FF_WA_OTP){ otpVerified=true; hideBanner(); return; }
  otpVerified=verified;
  var banner=document.getElementById('otpBanner');
  var dot=document.getElementById('otpDot');
  var title=document.getElementById('otpTitle');
  var sub=document.getElementById('otpSubtitle');
  var actions=document.getElementById('otpActions');

  if(verified){
    banner.className='otp-banner verified';
    dot.className='otp-status-dot ok';
    title.textContent='Identity verified';
    sub.textContent='You can now approve or reject changes.';
    actions.innerHTML='<span style="font-size:12px;color:#3B6D11;font-weight:600">&#10003; OTP verified</span>'+
      '&nbsp;<button class="btn btn-sm" onclick="resetOTP()" style="font-size:11px;padding:4px 8px">Re-verify</button>';
    // Unlock all cards
    var items=document.querySelectorAll('.pending-item');
    for(var i=0;i<items.length;i++) items[i].classList.remove('locked');
  } else {
    banner.className='otp-banner';
    dot.className='otp-status-dot';
    title.textContent='WhatsApp OTP required';
    sub.innerHTML='Verify your identity to approve or reject changes.';
    actions.innerHTML='<button class="btn btn-wa" id="sendOtpBtn" onclick="sendOTP()">&#9654; Send OTP</button>';
    // Lock all cards
    var items=document.querySelectorAll('.pending-item');
    for(var i=0;i<items.length;i++) items[i].classList.add('locked');
  }
}

function sendOTP(){
  var btn=document.getElementById('sendOtpBtn');
  if(btn){ btn.disabled=true; btn.textContent='Sending...'; }
  xhr('POST',OTP_API,'action=send',function(err,res){
    if(err){ showToast(err,'error'); if(btn){btn.disabled=false;btn.innerHTML='&#9654; Send OTP';} return; }
    if(res.success){
      if(res.fallback){
        showToast('WhatsApp failed. OTP: '+res.otp,'error');
      } else {
        showToast('OTP sent to WhatsApp!','info');
      }
      showOTPInput(res.expires_in||300);
    } else {
      showToast(res.message,'error');
      if(btn){btn.disabled=false;btn.innerHTML='&#9654; Send OTP';}
    }
  });
}

function showOTPInput(expiresIn){
  var actions=document.getElementById('otpActions');
  var dot=document.getElementById('otpDot');
  dot.className='otp-status-dot pending';
  document.getElementById('otpTitle').textContent='Enter OTP';
  document.getElementById('otpSubtitle').textContent='Enter the 6-digit OTP sent to your WhatsApp.';

  actions.innerHTML=
    '<input class="otp-input" id="otpInput" placeholder="------" maxlength="6" inputmode="numeric">'+
    '<button class="btn btn-primary btn-sm" onclick="verifyOTP()">Verify</button>'+
    '<button class="btn btn-sm" onclick="sendOTP()">Resend</button>'+
    '<span class="otp-timer" id="otpTimer"></span>';

  document.getElementById('otpInput').focus();
  document.getElementById('otpInput').addEventListener('keydown',function(e){
    if(e.keyCode===13) verifyOTP();
  });

  // Countdown timer
  clearInterval(otpTimer);
  var remaining=expiresIn;
  otpTimer=setInterval(function(){
    remaining--;
    var el=document.getElementById('otpTimer');
    if(el) el.textContent=Math.floor(remaining/60)+':'+(remaining%60<10?'0':'')+remaining%60;
    if(remaining<=0){
      clearInterval(otpTimer);
      if(el) el.textContent='Expired';
      showToast('OTP expired. Please request a new one.','error');
      setOTPVerified(false);
    }
  },1000);
}

function verifyOTP(){
  var input=document.getElementById('otpInput');
  if(!input) return;
  var code=input.value.trim();
  if(code.length!==6){ showToast('Enter 6-digit OTP','error'); return; }
  xhr('POST',OTP_API,'action=verify&otp='+encodeURIComponent(code),function(err,res){
    if(err){ showToast(err,'error'); return; }
    if(res.success){
      clearInterval(otpTimer);
      setOTPVerified(true);
      showToast('OTP verified! You can now approve or reject.','success');
      // Replay pending action if any
      if(pendingAction){
        var a=pendingAction; pendingAction=null;
        if(a.type==='approve') doApprove(a.pid);
        else doReject(a.pid);
      }
    } else {
      showToast(res.message,'error');
      input.value=''; input.focus();
    }
  });
}

function resetOTP(){
  otpVerified=false;
  // Clear server-side OTP session
  xhr('POST',OTP_API,'action=send',function(){});
  setOTPVerified(false);
}

function showOTPPrompt(){
  showToast('Please verify OTP first','error');
  window.scrollTo({top:0,behavior:'smooth'});
  if(!otpVerified) sendOTP();
}

// Check OTP status on load
if(!FF_WA_OTP){
  // OTP disabled — hide banner, unlock cards immediately
  setOTPVerified(true);
} else {
  xhr('POST',OTP_API,'action=status',function(err,res){
    if(!err && res.verified) setOTPVerified(true);
    else setOTPVerified(false);
  });
}

// ── Load pending ──────────────────────────────────────────────────────────────
function loadPending(){
  document.getElementById('pendingList').innerHTML='<div class="loading">Loading...</div>';
  ajaxJSON('GET',API+'?action=pending_list',null,function(err,res){
    if(err){ document.getElementById('pendingList').innerHTML='<div class="empty">'+esc(err)+'</div>'; return; }
    if(!res.success||!res.data||!res.data.length){
      document.getElementById('pendingList').innerHTML='<div class="empty">&#10003; No pending approvals<br><span style="font-size:13px;color:#aaa">All caught up!</span></div>';
      document.getElementById('totalBadge').textContent='0'; return;
    }
    document.getElementById('totalBadge').textContent=res.data.length;
    var html='';
    for(var i=0;i<res.data.length;i++) html+=renderItem(res.data[i]);
    document.getElementById('pendingList').innerHTML=html;
    if(!otpVerified){
      var items=document.querySelectorAll('.pending-item');
      for(var i=0;i<items.length;i++) items[i].classList.add('locked');
    }
  });
}

function initials(fn,ln){ return ((fn||'')[0]+(ln||'')[0]).toUpperCase(); }

function renderItem(item){
  var newData=item.change_data||{};
  var oldData=item.current_data||{};
  var type=item.change_type;
  var typeLabel={create:'New contact',update:'Edit contact',delete:'Delete contact'}[type]||type;
  var typeCls={create:'badge-create',update:'badge-update',delete:'badge-delete'}[type]||'';
  var fn=newData.first_name||oldData.first_name||'';
  var ln=newData.last_name||oldData.last_name||'';
  var dt=item.requested_at?new Date(item.requested_at).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}):'';

  var body='';

  if(type==='delete'){
    body='<div class="delete-warn">&#9888;&nbsp; Request to permanently delete:<br>'+
      '<strong style="font-size:15px">'+esc(fn)+' '+esc(ln)+'</strong>'+
      (oldData.mo_no?'<br>Mobile: '+esc(oldData.mo_no):'')+
      (oldData.city?' &nbsp;|&nbsp; City: '+esc(oldData.city):'')+
      '<br><span style="font-size:12px;opacity:.7">Contact ID: '+item.contact_id+'</span></div>';

  } else if(type==='create'){
    for(var sec in SECTIONS){
      var sf=SECTIONS[sec]; var sh='';
      for(var i=0;i<sf.length;i++){
        var f=sf[i]; var val=newData[f]!==undefined?String(newData[f]||''):'';
        if(!val||val==='0') continue;
        sh+='<div class="new-field"><span class="fl">'+(LABELS[f]||f)+'</span>'+esc(val)+'</div>';
      }
      if(sh) body+='<div class="diff-section">'+sec+'</div><div class="new-grid">'+sh+'</div>';
    }

  } else if(type==='update'){
    var changedHtml=''; var unchangedBySec={};
    for(var sec in SECTIONS){
      var sf=SECTIONS[sec];
      for(var i=0;i<sf.length;i++){
        var f=sf[i];
        var ov=oldData[f]!==undefined?String(oldData[f]||''):'';
        var nv=newData[f]!==undefined?String(newData[f]||''):ov;
        if(ov==='0') ov=''; if(nv==='0') nv='';
        var isChanged=(newData[f]!==undefined)&&(nv!==ov);
        if(isChanged){
          changedHtml+='<div class="changed-row">'+
            '<div class="changed-label">'+(LABELS[f]||f)+'</div>'+
            '<div class="old-val">'+(ov||'<em style="opacity:.5">empty</em>')+'</div>'+
            '<div class="arrow">&#8594;</div>'+
            '<div class="new-val">'+(nv||'<em style="opacity:.5">empty</em>')+'</div>'+
          '</div>';
        } else if(ov){
          if(!unchangedBySec[sec]) unchangedBySec[sec]='';
          unchangedBySec[sec]+='<div class="unchanged-field"><span class="fl">'+(LABELS[f]||f)+'</span>'+esc(ov)+'</div>';
        }
      }
    }
    if(changedHtml) body+='<div class="diff-section" style="color:#185FA5;border-color:#B5D4F4">&#9998; Changed fields</div><div class="changed-list">'+changedHtml+'</div>';
    var hasU=false; for(var s in unchangedBySec){ if(unchangedBySec[s]){hasU=true;break;} }
    if(hasU){
      body+='<details style="margin-top:8px"><summary style="font-size:12px;color:#888;cursor:pointer;padding:4px 0;-webkit-user-select:none;user-select:none">Show unchanged fields</summary>';
      for(var s in unchangedBySec){ if(!unchangedBySec[s]) continue; body+='<div class="diff-section" style="margin-top:8px">'+s+'</div><div class="unchanged-grid">'+unchangedBySec[s]+'</div>'; }
      body+='</details>';
    }
    if(!changedHtml&&!hasU) body='<div style="color:#888;font-size:13px;padding:8px 0">No field changes detected.</div>';
  }

  return '<div class="pending-item" id="pi-'+item.id+'">'+
    '<div class="pending-header">'+
      '<span class="contact-avatar">'+initials(fn,ln)+'</span>'+
      '<div><div class="pending-name">'+esc(fn)+' '+esc(ln)+'</div><div class="pending-meta">'+dt+'</div></div>'+
      '<span class="badge '+typeCls+'">'+typeLabel+'</span>'+
    '</div>'+
    body+
    '<div class="pending-actions">'+
      '<input class="review-input" id="note-'+item.id+'" placeholder="Review note (optional)">'+
      '<button class="btn btn-approve btn-sm" id="abtn-'+item.id+'" onclick="doApprove('+item.id+')">&#10003; Approve</button>'+
      '<button class="btn btn-reject btn-sm" id="rbtn-'+item.id+'" onclick="doReject('+item.id+')">&#10007; Reject</button>'+
    '</div>'+
  '</div>';
}

function doApprove(pid){
  if(!otpVerified){ pendingAction={type:'approve',pid:pid}; showOTPPrompt(); return; }
  var note=document.getElementById('note-'+pid).value;
  var btn=document.getElementById('abtn-'+pid);
  btn.disabled=true; btn.textContent='Approving...';
  ajaxJSON('POST',API+'?action=approve',{pending_id:pid,review_note:note},function(err,res){
    btn.disabled=false; btn.innerHTML='&#10003; Approve';
    if(err){showToast(err,'error');return;}
    if(res.success){showToast('Approved!','success');fadeRemove(pid);}
    else showToast(res.message,'error');
  });
}

function doReject(pid){
  if(!otpVerified){ pendingAction={type:'reject',pid:pid}; showOTPPrompt(); return; }
  var note=document.getElementById('note-'+pid).value;
  var btn=document.getElementById('rbtn-'+pid);
  btn.disabled=true; btn.textContent='Rejecting...';
  ajaxJSON('POST',API+'?action=reject',{pending_id:pid,review_note:note},function(err,res){
    btn.disabled=false; btn.innerHTML='&#10007; Reject';
    if(err){showToast(err,'error');return;}
    if(res.success){showToast('Rejected','error');fadeRemove(pid);}
    else showToast(res.message,'error');
  });
}

function fadeRemove(pid){
  var el=document.getElementById('pi-'+pid);
  if(el){el.style.opacity='0';setTimeout(function(){el.remove();updateCount();},300);}
  else updateCount();
}

function updateCount(){
  var n=document.querySelectorAll('[id^="pi-"]').length;
  document.getElementById('totalBadge').textContent=n;
  if(!n) document.getElementById('pendingList').innerHTML='<div class="empty">&#10003; No pending approvals<br><span style="font-size:13px;color:#aaa">All caught up!</span></div>';
}

loadPending();

// ── Feature Flags Panel ───────────────────────────────────────
var FLAGS_API = 'flags.php';
var flagsLoaded = false;

function toggleFlagsPanel(){
  var body = document.getElementById('flagsPanelBody');
  var chev = document.getElementById('flagsChevron');
  var isOpen = body.classList.contains('open');
  body.classList.toggle('open', !isOpen);
  chev.classList.toggle('open', !isOpen);
  if(!isOpen && !flagsLoaded) loadFlags();
}

function loadFlags(){
  var content = document.getElementById('flagsContent');
  content.innerHTML = '<div class="loading" style="padding:1.5rem">Loading...</div>';
  var xhr = new XMLHttpRequest();
  xhr.open('POST', FLAGS_API, true);
  xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  xhr.onreadystatechange = function(){
    if(xhr.readyState !== 4) return;
    try{
      var res = JSON.parse(xhr.responseText);
      if(res.success){ flagsLoaded=true; renderFlags(res.flags); }
      else content.innerHTML = '<div style="color:#A32D2D;padding:1rem">'+esc(res.message)+'</div>';
    } catch(e){
      content.innerHTML = '<div style="color:#A32D2D;padding:1rem">Parse error</div>';
    }
  };
  xhr.send('action=list');
}

function renderFlags(flags){
  // Group flags
  var groups = {};
  for(var i=0; i<flags.length; i++){
    var f = flags[i];
    if(!groups[f.group]) groups[f.group] = [];
    groups[f.group].push(f);
  }

  var on=0, off=0;
  for(var i=0;i<flags.length;i++){ if(flags[i].value) on++; else off++; }
  document.getElementById('flagsSummary').textContent = on+' on, '+off+' off';

  var html = '';
  var groupOrder = ['Features','Basic info','Current address','Vatan address'];
  for(var gi=0; gi<groupOrder.length; gi++){
    var grp = groupOrder[gi];
    if(!groups[grp]) continue;
    html += '<div class="flag-group"><div class="flag-group-title">'+grp+'</div><div class="flag-rows">';
    for(var i=0; i<groups[grp].length; i++){
      var f = groups[grp][i];
      var chk = f.value ? ' checked' : '';
      var isValidation = f.key.indexOf('VALIDATE_') === 0;
      var descLabel = isValidation ? (f.value ? 'Required' : 'Optional') : f.desc;
      html += '<div class="flag-row" id="flagrow_'+f.key+'">'+
        '<div class="flag-row-left">'+
          '<div class="flag-label">'+esc(f.label)+'</div>'+
          '<div class="flag-desc" id="flagdesc_'+f.key+'">'+esc(descLabel)+'</div>'+
          '<div class="flag-saving" id="flagsaving_'+f.key+'">Saving...</div>'+
        '</div>'+
        '<div class="toggle-wrap">'+
          '<label class="toggle">'+
            '<input type="checkbox" id="flagtog_'+f.key+'"'+chk+' onchange="toggleFlag(''+f.key+'',this)">'+
            '<span class="toggle-slider"></span>'+
          '</label>'+
        '</div>'+
      '</div>';
    }
    html += '</div></div>';
  }
  document.getElementById('flagsContent').innerHTML = html;
}

function toggleFlag(key, checkbox){
  var newVal = checkbox.checked;
  var saving = document.getElementById('flagsaving_'+key);
  var desc   = document.getElementById('flagdesc_'+key);
  var row    = document.getElementById('flagrow_'+key);
  checkbox.disabled = true;
  if(saving) { saving.textContent='Saving...'; saving.classList.add('show'); }

  var xhr = new XMLHttpRequest();
  xhr.open('POST', FLAGS_API, true);
  xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  xhr.onreadystatechange = function(){
    if(xhr.readyState !== 4) return;
    checkbox.disabled = false;
    if(saving) saving.classList.remove('show');
    try{
      var res = JSON.parse(xhr.responseText);
      if(res.success){
        showToast(res.message, 'success');
        // Update desc label
        if(desc){
          var isValidation = key.indexOf('VALIDATE_') === 0;
          if(isValidation) desc.textContent = newVal ? 'Required' : 'Optional';
        }
        // Update summary count
        var allChecked = document.querySelectorAll('.toggle input:checked').length;
        var allTotal   = document.querySelectorAll('.toggle input').length;
        document.getElementById('flagsSummary').textContent = allChecked+' on, '+(allTotal-allChecked)+' off';
        // Special handling: if OTP toggled, reload OTP banner state
        if(key === 'FEATURE_WA_OTP'){
          setTimeout(function(){ location.reload(); }, 1200);
        }
      } else {
        showToast(res.message || 'Failed to save', 'error');
        checkbox.checked = !newVal; // revert
      }
    } catch(e){
      showToast('Save error', 'error');
      checkbox.checked = !newVal;
    }
  };
  xhr.send('action=toggle&key='+encodeURIComponent(key)+'&value='+(newVal?'true':'false'));
}
</script>
</body>
</html>
