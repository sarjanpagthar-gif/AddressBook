<?php
// api.php — Contacts CRUD + Approval

// Include config + auth FIRST so session_start() runs before any output
require_once 'config.php';
require_once 'auth.php';

// Now safe to send headers
ini_set('display_errors', 0);  // OFF — errors must not corrupt JSON output
error_reporting(0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$action = isset($_GET['action']) ? $_GET['action'] : '';
switch ($action) {
    case 'list':         handleList();        break;
    case 'create':       handleCreate();      break;
    case 'update':       handleUpdate();      break;
    case 'delete':       handleDelete();      break;
    case 'pending_list': handlePendingList(); break;
    case 'approve':      handleApprove();     break;
    case 'reject':       handleReject();      break;
    case 'export':       handleExport();      break;
    case 'audit_log':       handleAuditLog();       break;
    case 'surname_suggest':  handleSurnameSuggest();  break;
    case 'hometown_suggest': handleHometownSuggest(); break;
    case 'mobile_check':     handleMobileCheck();     break;
    default:
        echo json_encode(['success'=>false,'message'=>'Invalid action: '.$action]);
}

// ── Helpers ───────────────────────────────────────────────────────────────────
// ── Check if mobile number already exists ────────────────────────────────────
function isMobileDuplicate($db, $mo_no, $exclude_id = 0) {
    if (empty($mo_no)) return false;
    // Clean number — digits only for comparison
    $clean = preg_replace('/[^0-9]/', '', $mo_no);
    if (strlen($clean) < 8) return false;

    $stmt = $db->prepare(
        "SELECT id, first_name, last_name FROM contacts
         WHERE REGEXP_REPLACE(mo_no, '[^0-9]', '') = ?
         AND id != ?
         LIMIT 1"
    );
    if (!$stmt) {
        // Fallback if REGEXP_REPLACE not supported (MySQL < 8)
        $stmt = $db->prepare(
            "SELECT id, first_name, last_name FROM contacts
             WHERE mo_no = ? AND id != ?
             LIMIT 1"
        );
        if (!$stmt) return false;
        $stmt->bind_param('si', $mo_no, $exclude_id);
    } else {
        $stmt->bind_param('si', $clean, $exclude_id);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: false;
}

function getBody() {
    $raw  = file_get_contents('php://input');
    if (!$raw) $raw = '{}';
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
function s($d,$k,$def='') { return isset($d[$k]) && $d[$k]!==null ? (string)$d[$k] : $def; }
function n($d,$k,$def=0)  { return isset($d[$k]) && $d[$k]!==null ? (int)$d[$k]    : $def; }
function dt($d,$k) {
    $v = isset($d[$k]) ? trim((string)$d[$k]) : '';
    if ($v===''||$v==='0000-00-00'||$v==='null') return '';
    // Convert DD-MM-YYYY to YYYY-MM-DD if needed
    if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $v, $m)) return $m[3].'-'.$m[2].'-'.$m[1];
    return $v;
}

// ── LIST ──────────────────────────────────────────────────────────────────────
function handleList() {
    $db     = getDB();
    $search = '%'.(isset($_GET['search']) ? $_GET['search'] : '').'%';
    $page   = max(1,(int)(isset($_GET['page'])  ? $_GET['page']  : 1));
    $limit  = max(1,(int)(isset($_GET['limit']) ? $_GET['limit'] : 10));
    $offset = ($page-1)*$limit;
    $all    = isset($_GET['show_all']) && $_GET['show_all']=='1';

    $where  = $all
        ? "WHERE (first_name LIKE ? OR last_name LIKE ? OR mo_no LIKE ? OR city LIKE ? OR Home_Town LIKE ?)"
        : "WHERE approval_status='approved' AND statuz='active' AND (first_name LIKE ? OR last_name LIKE ? OR mo_no LIKE ? OR city LIKE ? OR Home_Town LIKE ?)";

    $stmt = $db->prepare("SELECT COUNT(*) AS total FROM contacts $where");
    if (!$stmt) { echo json_encode(['success'=>false,'message'=>$db->error]); return; }
    $stmt->bind_param('sssss',$search,$search,$search,$search,$search);
    $stmt->execute();
    $total = (int)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $stmt = $db->prepare("SELECT * FROM contacts $where ORDER BY id DESC LIMIT ? OFFSET ?");
    if (!$stmt) { echo json_encode(['success'=>false,'message'=>$db->error]); return; }
    $stmt->bind_param('sssssii',$search,$search,$search,$search,$search,$limit,$offset);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $db->close();
    echo json_encode(['success'=>true,'data'=>$rows,'total'=>$total,'page'=>$page,'pages'=>(int)ceil($total/$limit)]);
}

// ── CREATE ────────────────────────────────────────────────────────────────────
// Columns inserted (27 total):
//  1. first_name        s
//  2. last_name         s
//  3. statuz            hardcoded 'pending'
//  4. approval_status   hardcoded 'pending'
//  5. owner_id          i
//  6. dob               s  (nullable string)
//  7. gender            s
//  8. father_name       s
//  9. mother_name       s
// 10. Home_Town         s
// 11. mo_no             s
// 12. wp_no             s
// 13. block_no          s
// 14. address_line1     s
// 15. street_address    s
// 16. city              s
// 17. state             s
// 18. zip               i
// 19. country           s
// 20. Vatan_vilage      s
// 21. Vatan_block_no    s
// 22. Vatan_Street_address s
// 23. Vatan_address_line1  s
// 24. Vatan_city        s
// 25. Vatan_state       s
// 26. Vatan_zip         i
// 27. Vatan_country     s
//
// ? params = 25 (cols 1,2,5-27 — cols 3+4 hardcoded)
// types    = s s i s s s s s s s s s s s s i s s s s s s s i s  (25 chars)
//            1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5
function handleCreate() {
    $db = getDB();
    $d  = getBody();
    if (empty($d['first_name'])) {
        echo json_encode(['success'=>false,'message'=>'First name is required']); return;
    }

    // Check for duplicate mobile number
    $mo_no = s($d,'mo_no');
    if (!empty($mo_no)) {
        $dup = isMobileDuplicate($db, $mo_no, 0);
        if ($dup) {
            $db->close();
            echo json_encode([
                'success' => false,
                'message' => 'Mobile number already exists for ' . $dup['first_name'] . ' ' . $dup['last_name'] . ' (ID: ' . $dup['id'] . ')',
                'duplicate_mobile' => true,
                'existing_contact' => $dup['first_name'] . ' ' . $dup['last_name']
            ]);
            return;
        }
    }

    $sql = "INSERT INTO `contacts`
        (`first_name`,`last_name`,`statuz`,`approval_status`,`owner_id`,
         `dob`,`gender`,`father_name`,`mother_name`,
         `Home_Town`,`mo_no`,`wp_no`,
         `block_no`,`address_line1`,`street_address`,`city`,`state`,`zip`,`country`,
         `Vatan_vilage`,`Vatan_block_no`,`Vatan_Street_address`,`Vatan_address_line1`,
         `Vatan_city`,`Vatan_state`,`Vatan_zip`,`Vatan_country`)
        VALUES
        (?,?,'pending','pending',?,
         ?,?,?,?,
         ?,?,?,
         ?,?,?,?,?,?,?,
         ?,?,?,?,
         ?,?,?,?)";

    // Count ?-marks above = 2 + 1 + 4 + 3 + 7 + 4 + 4 = 25  ✓
    $stmt = $db->prepare($sql);
    if (!$stmt) { echo json_encode(['success'=>false,'message'=>'Prepare failed: '.$db->error]); return; }

    $p1  = s($d,'first_name');
    $p2  = s($d,'last_name');
    $p3  = n($d,'owner_id');         // i
    $p4  = dt($d,'dob');             // s (null ok)
    $p5  = s($d,'gender');
    $p6  = s($d,'father_name');
    $p7  = s($d,'mother_name');
    $p8  = s($d,'Home_Town');
    $p9  = s($d,'mo_no');
    $p10 = s($d,'wp_no');
    $p11 = s($d,'block_no');
    $p12 = s($d,'address_line1');
    $p13 = s($d,'street_address');
    $p14 = s($d,'city');
    $p15 = s($d,'state');
    $p16 = n($d,'zip');              // i
    $p17 = s($d,'country');
    $p18 = s($d,'Vatan_vilage');
    $p19 = s($d,'Vatan_block_no');
    $p20 = s($d,'Vatan_Street_address');
    $p21 = s($d,'Vatan_address_line1');
    $p22 = s($d,'Vatan_city');
    $p23 = s($d,'Vatan_state');
    $p24 = n($d,'Vatan_zip');        // i
    $p25 = s($d,'Vatan_country');

    // types: p1=s p2=s p3=i p4=s p5=s p6=s p7=s p8=s p9=s p10=s
    //        p11=s p12=s p13=s p14=s p15=s p16=i p17=s
    //        p18=s p19=s p20=s p21=s p22=s p23=s p24=i p25=s
    // = s s i s s s s s s s  s  s  s  s  s  i  s  s  s  s  s  s  s  i  s  = 25 chars
    $types = 'ssissssssssssssisssssssis';  // 25 chars verified
    // Verify: strlen = 25
    if (strlen($types) !== 25) {
        echo json_encode(['success'=>false,'message'=>'Internal: types length '.strlen($types).' != 25']); return;
    }

    $stmt->bind_param($types,
        $p1,$p2,$p3,$p4,$p5,$p6,$p7,$p8,$p9,$p10,
        $p11,$p12,$p13,$p14,$p15,$p16,$p17,
        $p18,$p19,$p20,$p21,$p22,$p23,$p24,$p25
    );

    if ($stmt->execute()) {
        $new_id = (int)$db->insert_id;
        if (FEATURE_APPROVAL) {
            // Approval ON — queue as pending
            logPending($db, $new_id, 'create', $d);
            $stmt->close(); $db->close();
            echo json_encode(['success'=>true,'id'=>$new_id,'message'=>'Contact submitted for approval']);
        } else {
            // Approval OFF — approve immediately
            $upd = $db->prepare("UPDATE contacts SET approval_status='approved', statuz='active' WHERE id=?");
            $upd->bind_param('i',$new_id); $upd->execute(); $upd->close();
            $stmt->close(); $db->close();
            echo json_encode(['success'=>true,'id'=>$new_id,'message'=>'Contact saved successfully']);
        }
    } else {
        $err = $stmt->error;
        $stmt->close(); $db->close();
        echo json_encode(['success'=>false,'message'=>'Execute failed: '.$err]);
    }
}

// ── UPDATE ────────────────────────────────────────────────────────────────────
function handleUpdate() {
    $db = getDB();
    $d  = getBody();
    if (empty($d['id'])) { echo json_encode(['success'=>false,'message'=>'ID required']); return; }
    $id = n($d,'id');

    // Check for duplicate mobile number (exclude current contact)
    $mo_no_upd = s($d,'mo_no');
    if (!empty($mo_no_upd)) {
        $dup = isMobileDuplicate($db, $mo_no_upd, $id);
        if ($dup) {
            $db->close();
            echo json_encode([
                'success' => false,
                'message' => 'Mobile number already exists for ' . $dup['first_name'] . ' ' . $dup['last_name'] . ' (ID: ' . $dup['id'] . ')',
                'duplicate_mobile' => true,
                'existing_contact' => $dup['first_name'] . ' ' . $dup['last_name']
            ]);
            return;
        }
    }

    if (FEATURE_APPROVAL) {
        // Approval ON — queue as pending
        logPending($db, $id, 'update', $d);
        $stmt = $db->prepare("UPDATE `contacts` SET `approval_status`='pending' WHERE `id`=?");
        if (!$stmt) { echo json_encode(['success'=>false,'message'=>$db->error]); return; }
        $stmt->bind_param('i',$id);
        $stmt->execute(); $stmt->close(); $db->close();
        echo json_encode(['success'=>true,'message'=>'Change submitted for approval']);
    } else {
        // Approval OFF — apply changes directly
        applyUpdate($db, $id, $d);
        $db->close();
        echo json_encode(['success'=>true,'message'=>'Contact updated successfully']);
    }
}

// ── DELETE ────────────────────────────────────────────────────────────────────
function handleDelete() {
    $db = getDB();
    $d  = getBody();
    $id = n($d,'id');
    if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required']); return; }

    if (FEATURE_APPROVAL) {
        // Approval ON — queue as pending
        logPending($db, $id, 'delete', ['id'=>$id]);
        $stmt = $db->prepare("UPDATE `contacts` SET `approval_status`='pending' WHERE `id`=?");
        if (!$stmt) { echo json_encode(['success'=>false,'message'=>$db->error]); return; }
        $stmt->bind_param('i',$id);
        $stmt->execute(); $stmt->close(); $db->close();
        echo json_encode(['success'=>true,'message'=>'Delete request submitted for approval']);
    } else {
        // Approval OFF — delete immediately
        $stmt = $db->prepare("DELETE FROM `contacts` WHERE `id`=?");
        if (!$stmt) { echo json_encode(['success'=>false,'message'=>$db->error]); return; }
        $stmt->bind_param('i',$id);
        $stmt->execute(); $stmt->close(); $db->close();
        echo json_encode(['success'=>true,'message'=>'Contact deleted successfully']);
    }
}

// ── PENDING LIST ──────────────────────────────────────────────────────────────
function handlePendingList() {
    try {
        $db = getDB();

        // Use safe column list — audit columns added later via approvers_schema.sql
        $cols = "p.id, p.contact_id, p.change_type, p.change_data,
                 p.requested_at, p.reviewed_at, p.review_note, c.approval_status";

        // Check if new audit columns exist, add them if so
        $colCheck = $db->query("SHOW COLUMNS FROM contacts_pending LIKE 'reviewed_by'");
        if ($colCheck && $colCheck->num_rows > 0) {
            $cols .= ", p.reviewed_by, p.reviewer_name, p.review_action";
        }

        $stmt = $db->prepare(
            "SELECT $cols
             FROM contacts_pending p
             LEFT JOIN contacts c ON c.id = p.contact_id
             WHERE c.approval_status = 'pending' AND p.reviewed_at IS NULL
             ORDER BY p.requested_at DESC"
        );
        if (!$stmt) {
            $err = $db->error;
            if (strpos($err, "contacts_pending") !== false) {
                echo json_encode(['success'=>true,'data'=>[],'message'=>'contacts_pending table not found - run schema.sql']);
            } else {
                echo json_encode(['success'=>false,'message'=>'Prepare failed: '.$err]);
            }
            return;
        }
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($rows as &$row) {
            $row['change_data'] = json_decode($row['change_data'], true);
            $cid = (int)$row['contact_id'];
            $cs  = $db->prepare("SELECT * FROM contacts WHERE id = ?");
            if ($cs) {
                $cs->bind_param('i', $cid);
                $cs->execute();
                $row['current_data'] = $cs->get_result()->fetch_assoc() ?: [];
                $cs->close();
            } else {
                $row['current_data'] = [];
            }
        }

        $db->close();
        echo json_encode(['success'=>true,'data'=>$rows]);

    } catch (Throwable $e) {
        echo json_encode(['success'=>false,'message'=>'pending_list error: '.$e->getMessage()]);
    }
}

// ── APPROVE ───────────────────────────────────────────────────────────────────
function handleApprove() {
    requireLogin();
    requireOTP();
    $db   = getDB();
    $d    = getBody();
    $pid  = n($d,'pending_id');
    $note = s($d,'review_note');

    $stmt = $db->prepare("SELECT * FROM contacts_pending WHERE id=?");
    $stmt->bind_param('i',$pid); $stmt->execute();
    $pending = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$pending) { echo json_encode(['success'=>false,'message'=>'Not found']); return; }

    $cid  = (int)$pending['contact_id'];
    $type = $pending['change_type'];
    $data = json_decode($pending['change_data'], true);

    if ($type === 'delete') {
        $stmt = $db->prepare("DELETE FROM contacts WHERE id=?");
        $stmt->bind_param('i',$cid); $stmt->execute(); $stmt->close();
    } elseif ($type === 'create') {
        $stmt = $db->prepare("UPDATE contacts SET approval_status='approved',statuz='active' WHERE id=?");
        $stmt->bind_param('i',$cid); $stmt->execute(); $stmt->close();
    } elseif ($type === 'update') {
        applyUpdate($db, $cid, $data);
        $stmt = $db->prepare("UPDATE contacts SET approval_status='approved' WHERE id=?");
        $stmt->bind_param('i',$cid); $stmt->execute(); $stmt->close();
    }

    // Record who approved and when
    $now          = date('Y-m-d H:i:s');
    $reviewer     = isset($_SESSION['admin_user']) ? $_SESSION['admin_user'] : 'admin';
    $reviewer_name= isset($_SESSION['approver_name']) ? $_SESSION['approver_name'] : $reviewer;

    // Check if audit columns exist before using them
    $hasAudit = false;
    $ac = $db->query("SHOW COLUMNS FROM contacts_pending LIKE 'reviewed_by'");
    if ($ac && $ac->num_rows > 0) $hasAudit = true;

    if ($hasAudit) {
        $stmt = $db->prepare(
            "UPDATE contacts_pending
             SET reviewed_at=?, review_note=?, reviewed_by=?, reviewer_name=?, review_action='approved'
             WHERE id=?"
        );
        $stmt->bind_param('ssssi',$now,$note,$reviewer,$reviewer_name,$pid);
    } else {
        $stmt = $db->prepare("UPDATE contacts_pending SET reviewed_at=?, review_note=? WHERE id=?");
        $stmt->bind_param('ssi',$now,$note,$pid);
    }
    $stmt->execute(); $stmt->close();

    // Save audit info on the contact itself (if not deleted)
    if ($type !== 'delete') {
        $cc = $db->query("SHOW COLUMNS FROM contacts LIKE 'approved_by'");
        if ($cc && $cc->num_rows > 0) {
            $stmt = $db->prepare("UPDATE contacts SET approved_by=?, approved_by_name=?, approved_at=? WHERE id=?");
            $stmt->bind_param('sssi',$reviewer,$reviewer_name,$now,$cid);
            $stmt->execute(); $stmt->close();
        }
    }

    // Keep reviewed record for history — only delete unreviewed pending records
    $stmt = $db->prepare("DELETE FROM contacts_pending WHERE contact_id=? AND id!=? AND reviewed_at IS NULL");
    $stmt->bind_param('ii',$cid,$pid); $stmt->execute(); $stmt->close();
    $db->close();
    echo json_encode(['success'=>true,'message'=>'Approved']);
}

// ── REJECT ────────────────────────────────────────────────────────────────────
function handleReject() {
    requireLogin();
    requireOTP();
    $db   = getDB();
    $d    = getBody();
    $pid  = n($d,'pending_id');
    $note = s($d,'review_note');

    $stmt = $db->prepare("SELECT * FROM contacts_pending WHERE id=?");
    $stmt->bind_param('i',$pid); $stmt->execute();
    $pending = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$pending) { echo json_encode(['success'=>false,'message'=>'Not found']); return; }

    $cid  = (int)$pending['contact_id'];
    $type = $pending['change_type'];

    if ($type === 'create') {
        $stmt = $db->prepare("DELETE FROM contacts WHERE id=?");
        $stmt->bind_param('i',$cid); $stmt->execute(); $stmt->close();
    } else {
        $stmt = $db->prepare("UPDATE contacts SET approval_status='approved' WHERE id=?");
        $stmt->bind_param('i',$cid); $stmt->execute(); $stmt->close();
    }
    // Record who rejected and when
    $now          = date('Y-m-d H:i:s');
    $reviewer     = isset($_SESSION['admin_user']) ? $_SESSION['admin_user'] : 'admin';
    $reviewer_name= isset($_SESSION['approver_name']) ? $_SESSION['approver_name'] : $reviewer;

    $hasAuditR = false;
    $ar = $db->query("SHOW COLUMNS FROM contacts_pending LIKE 'reviewed_by'");
    if ($ar && $ar->num_rows > 0) $hasAuditR = true;

    if ($hasAuditR) {
        $stmt = $db->prepare(
            "UPDATE contacts_pending
             SET reviewed_at=?, review_note=?, reviewed_by=?, reviewer_name=?, review_action='rejected'
             WHERE id=?"
        );
        $stmt->bind_param('ssssi',$now,$note,$reviewer,$reviewer_name,$pid);
    } else {
        $stmt = $db->prepare("UPDATE contacts_pending SET reviewed_at=?, review_note=? WHERE id=?");
        $stmt->bind_param('ssi',$now,$note,$pid);
    }
    $stmt->execute(); $stmt->close();

    // Keep reviewed record for history — only delete unreviewed pending records
    $stmt = $db->prepare("DELETE FROM contacts_pending WHERE contact_id=? AND id!=? AND reviewed_at IS NULL");
    $stmt->bind_param('ii',$cid,$pid); $stmt->execute(); $stmt->close();
    $db->close();
    echo json_encode(['success'=>true,'message'=>'Rejected']);
}

// ── EXPORT ────────────────────────────────────────────────────────────────────
function handleExport() {
    $db  = getDB();
    $d   = getBody();
    $ids = isset($d['ids']) && is_array($d['ids']) ? array_map('intval',$d['ids']) : [];
    if (empty($ids)) {
        $stmt = $db->prepare("SELECT * FROM contacts WHERE approval_status='approved' AND statuz='active' ORDER BY id DESC");
        $stmt->execute();
    } else {
        $ph   = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("SELECT * FROM contacts WHERE id IN ($ph)");
        $stmt->bind_param(str_repeat('i',count($ids)), ...$ids);
        $stmt->execute();
    }
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close(); $db->close();
    echo json_encode(['success'=>true,'data'=>$rows]);
}

// ── AUDIT LOG (recent approvals/rejections with approver info) ───────────────
function handleAuditLog() {
    $db    = getDB();
    $page  = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
    $limit = 20;
    $offset= ($page-1)*$limit;

    $stmt = $db->prepare(
        "SELECT p.id, p.contact_id, p.change_type, p.requested_at,
                p.reviewed_at, p.reviewed_by, p.reviewer_name,
                p.review_note, p.review_action,
                c.first_name, c.last_name, c.mo_no, c.city
         FROM contacts_pending p
         LEFT JOIN contacts c ON c.id = p.contact_id
         WHERE p.reviewed_at IS NOT NULL
         ORDER BY p.reviewed_at DESC LIMIT ? OFFSET ?"
    );
    if (!$stmt) { echo json_encode(['success'=>false,'message'=>$db->error]); return; }
    $stmt->bind_param('ii',$limit,$offset);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $count = $db->query("SELECT COUNT(*) as t FROM contacts_pending WHERE reviewed_at IS NOT NULL");
    $total = $count ? (int)$count->fetch_assoc()['t'] : 0;
    $db->close();

    echo json_encode([
        'success' => true,
        'data'    => $rows,
        'total'   => $total,
        'pages'   => (int)ceil($total/$limit),
        'page'    => $page,
    ]);
}

// ── SURNAME SUGGEST ──────────────────────────────────────────────────────────────
function handleSurnameSuggest() {
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    if ($q === '') { echo json_encode(['success'=>true,'data'=>[]]); return; }

    $db   = getDB();
    $like = '%' . $db->real_escape_string($q) . '%';

    // Distinct last_names that contain the search string, ordered by frequency
    $stmt = $db->prepare(
        "SELECT last_name, COUNT(*) AS cnt
         FROM contacts
         WHERE last_name LIKE ? AND last_name <> ''
         GROUP BY last_name
         ORDER BY cnt DESC, last_name ASC
         LIMIT 10"
    );
    if (!$stmt) { echo json_encode(['success'=>false,'message'=>$db->error]); return; }
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $db->close();

    echo json_encode(['success'=>true, 'data'=>$rows]);
}

// ── MOBILE CHECK (live duplicate check while typing) ─────────────────────────
function handleMobileCheck() {
    $mo  = isset($_GET['mo']) ? trim($_GET['mo']) : '';
    $eid = isset($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : 0;
    if (empty($mo)) { echo json_encode(['success'=>true,'available'=>true]); return; }

    $db  = getDB();
    $dup = isMobileDuplicate($db, $mo, $eid);
    $db->close();

    if ($dup) {
        echo json_encode([
            'success'   => true,
            'available' => false,
            'message'   => 'Already used by ' . $dup['first_name'] . ' ' . $dup['last_name'],
        ]);
    } else {
        echo json_encode(['success'=>true,'available'=>true]);
    }
}

// ── HOMETOWN SUGGEST ─────────────────────────────────────────────────────────
function handleHometownSuggest() {
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    if ($q === '') { echo json_encode(['success'=>true,'data'=>[]]); return; }

    $db   = getDB();
    $like = '%' . $db->real_escape_string($q) . '%';

    $stmt = $db->prepare(
        "SELECT Home_Town, COUNT(*) AS cnt
         FROM contacts
         WHERE Home_Town LIKE ? AND Home_Town <> ''
         GROUP BY Home_Town
         ORDER BY cnt DESC, Home_Town ASC
         LIMIT 10"
    );
    if (!$stmt) { echo json_encode(['success'=>false,'message'=>$db->error]); return; }
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $db->close();

    echo json_encode(['success'=>true, 'data'=>$rows]);
}

// ── INTERNAL: log pending change ──────────────────────────────────────────────
function logPending($db, $contact_id, $change_type, $data) {
    $json = json_encode($data);
    $stmt = $db->prepare("INSERT INTO contacts_pending (contact_id,change_type,change_data) VALUES (?,?,?)");
    if (!$stmt) return;
    $stmt->bind_param('iss',$contact_id,$change_type,$json);
    $stmt->execute(); $stmt->close();
}

// ── INTERNAL: apply update fields ─────────────────────────────────────────────
// SET params (26) + WHERE id (1) = 27 total
// types: s s s i s s s s s s s s s s s s i s s s s s s s i s i = 27 chars
function applyUpdate($db, $contact_id, $d) {
    $sql = "UPDATE `contacts` SET
        `first_name`=?,`last_name`=?,`statuz`=?,`owner_id`=?,
        `dob`=?,`gender`=?,`father_name`=?,`mother_name`=?,
        `Home_Town`=?,`mo_no`=?,`wp_no`=?,
        `block_no`=?,`address_line1`=?,`street_address`=?,`city`=?,`state`=?,`zip`=?,`country`=?,
        `Vatan_vilage`=?,`Vatan_block_no`=?,`Vatan_Street_address`=?,`Vatan_address_line1`=?,
        `Vatan_city`=?,`Vatan_state`=?,`Vatan_zip`=?,`Vatan_country`=?
        WHERE id=?";
    $stmt = $db->prepare($sql);
    if (!$stmt) return;

    $p1 =s($d,'first_name');          $p2 =s($d,'last_name');
    $p3 =s($d,'statuz','active');      $p4 =n($d,'owner_id');
    $p5 =dt($d,'dob');                 $p6 =s($d,'gender');
    $p7 =s($d,'father_name');          $p8 =s($d,'mother_name');
    $p9 =s($d,'Home_Town');            $p10=s($d,'mo_no');
    $p11=s($d,'wp_no');
    $p12=s($d,'block_no');             $p13=s($d,'address_line1');
    $p14=s($d,'street_address');       $p15=s($d,'city');
    $p16=s($d,'state');                $p17=n($d,'zip');
    $p18=s($d,'country');
    $p19=s($d,'Vatan_vilage');         $p20=s($d,'Vatan_block_no');
    $p21=s($d,'Vatan_Street_address'); $p22=s($d,'Vatan_address_line1');
    $p23=s($d,'Vatan_city');           $p24=s($d,'Vatan_state');
    $p25=n($d,'Vatan_zip');            $p26=s($d,'Vatan_country');
    $p27=$contact_id;

    $types = 'sssissssssssssssisssssssisi'; // 27 chars: p1-3=s,p4=i,p5-16=s,p17=i,p18-24=s,p25=i,p26=s,p27=i

    $stmt->bind_param($types,
        $p1,$p2,$p3,$p4,$p5,$p6,$p7,$p8,$p9,$p10,$p11,
        $p12,$p13,$p14,$p15,$p16,$p17,$p18,
        $p19,$p20,$p21,$p22,$p23,$p24,$p25,$p26,$p27
    );
    $stmt->execute(); $stmt->close();
}
