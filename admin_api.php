<?php
// admin_api.php — Approvers management AJAX API
require_once 'config.php';
require_once 'auth.php';
requireLogin();

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'list':    handleList();    break;
    case 'create':  handleCreate();  break;
    case 'update':  handleUpdate();  break;
    case 'delete':  handleDelete();  break;
    case 'toggle':  handleToggle();  break;
    case 'history': handleHistory(); break;
    default:
        echo json_encode(['success'=>false,'message'=>'Invalid action']);
}

function getBody() {
    $raw = file_get_contents('php://input');
    if (!$raw) $raw = '{}';
    $d = json_decode($raw, true);
    return is_array($d) ? $d : [];
}
function s($d,$k,$def='') { return isset($d[$k]) ? trim((string)$d[$k]) : $def; }
function n($d,$k,$def=0)  { return isset($d[$k]) ? (int)$d[$k] : $def; }

// ── LIST all approvers ────────────────────────────────────────────────────────
function handleList() {
    $db = getDB();
    $stmt = $db->prepare(
        "SELECT a.*,
            (SELECT COUNT(*) FROM contacts_pending p
             WHERE p.reviewed_by = a.username AND p.review_action = 'approved') AS total_approved,
            (SELECT COUNT(*) FROM contacts_pending p
             WHERE p.reviewed_by = a.username AND p.review_action = 'rejected') AS total_rejected,
            (SELECT MAX(p.reviewed_at) FROM contacts_pending p
             WHERE p.reviewed_by = a.username) AS last_action_at
         FROM approvers a ORDER BY a.name ASC"
    );
    if (!$stmt) { echo json_encode(['success'=>false,'message'=>$db->error]); return; }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    // Remove password hash from response
    foreach ($rows as &$r) unset($r['password_hash']);
    $db->close();
    echo json_encode(['success'=>true,'data'=>$rows]);
}

// ── CREATE approver ───────────────────────────────────────────────────────────
function handleCreate() {
    $db = getDB();
    $d  = getBody();

    $name     = s($d,'name');
    $email    = s($d,'email');
    $mobile   = s($d,'mobile');
    $username = s($d,'username');
    $password = s($d,'password');

    if (!$name || !$email || !$username || !$password) {
        echo json_encode(['success'=>false,'message'=>'Name, email, username and password are required']);
        return;
    }
    if (strlen($password) < 6) {
        echo json_encode(['success'=>false,'message'=>'Password must be at least 6 characters']);
        return;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare(
        "INSERT INTO approvers (name,email,mobile,username,password_hash,is_active)
         VALUES (?,?,?,?,?,1)"
    );
    if (!$stmt) { echo json_encode(['success'=>false,'message'=>$db->error]); return; }
    $stmt->bind_param('sssss', $name, $email, $mobile, $username, $hash);

    if ($stmt->execute()) {
        echo json_encode(['success'=>true,'id'=>(int)$db->insert_id,'message'=>'Approver created']);
    } else {
        $err = $stmt->error;
        echo json_encode(['success'=>false,'message'=>strpos($err,'Duplicate')!==false
            ? 'Username already exists' : $err]);
    }
    $stmt->close(); $db->close();
}

// ── UPDATE approver ───────────────────────────────────────────────────────────
function handleUpdate() {
    $db = getDB();
    $d  = getBody();
    $id = n($d,'id');

    if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required']); return; }

    $name    = s($d,'name');
    $email   = s($d,'email');
    $mobile  = s($d,'mobile');
    $password= s($d,'password');

    if (!$name || !$email) {
        echo json_encode(['success'=>false,'message'=>'Name and email are required']);
        return;
    }

    if ($password) {
        if (strlen($password) < 6) {
            echo json_encode(['success'=>false,'message'=>'Password must be at least 6 characters']);
            return;
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE approvers SET name=?,email=?,mobile=?,password_hash=? WHERE id=?");
        if (!$stmt) { echo json_encode(['success'=>false,'message'=>$db->error]); return; }
        $stmt->bind_param('ssssi', $name, $email, $mobile, $hash, $id);
    } else {
        $stmt = $db->prepare("UPDATE approvers SET name=?,email=?,mobile=? WHERE id=?");
        if (!$stmt) { echo json_encode(['success'=>false,'message'=>$db->error]); return; }
        $stmt->bind_param('sssi', $name, $email, $mobile, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success'=>true,'message'=>'Approver updated']);
    } else {
        echo json_encode(['success'=>false,'message'=>$stmt->error]);
    }
    $stmt->close(); $db->close();
}

// ── DELETE approver ───────────────────────────────────────────────────────────
function handleDelete() {
    $db = getDB();
    $d  = getBody();
    $id = n($d,'id');
    if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required']); return; }

    $stmt = $db->prepare("DELETE FROM approvers WHERE id=?");
    if (!$stmt) { echo json_encode(['success'=>false,'message'=>$db->error]); return; }
    $stmt->bind_param('i',$id);
    if ($stmt->execute()) {
        echo json_encode(['success'=>true,'message'=>'Approver deleted']);
    } else {
        echo json_encode(['success'=>false,'message'=>$stmt->error]);
    }
    $stmt->close(); $db->close();
}

// ── TOGGLE active/inactive ────────────────────────────────────────────────────
function handleToggle() {
    $db = getDB();
    $d  = getBody();
    $id = n($d,'id');
    $active = isset($d['is_active']) ? (int)(bool)$d['is_active'] : 1;
    if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required']); return; }

    $stmt = $db->prepare("UPDATE approvers SET is_active=? WHERE id=?");
    if (!$stmt) { echo json_encode(['success'=>false,'message'=>$db->error]); return; }
    $stmt->bind_param('ii',$active,$id);
    if ($stmt->execute()) {
        echo json_encode(['success'=>true,'message'=>$active ? 'Activated' : 'Deactivated']);
    } else {
        echo json_encode(['success'=>false,'message'=>$stmt->error]);
    }
    $stmt->close(); $db->close();
}

// ── HISTORY — audit log of approvals/rejections ───────────────────────────────
function handleHistory() {
    $db = getDB();
    $username = isset($_GET['username']) ? trim($_GET['username']) : '';
    $page     = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
    $limit    = 20;
    $offset   = ($page - 1) * $limit;

    $where = $username ? "WHERE p.reviewed_by = ? AND p.reviewed_at IS NOT NULL"
                       : "WHERE p.reviewed_at IS NOT NULL";

    $countSql = "SELECT COUNT(*) as total FROM contacts_pending p $where";
    $stmt = $db->prepare($countSql);
    if (!$stmt) { echo json_encode(['success'=>false,'message'=>$db->error]); return; }
    if ($username) $stmt->bind_param('s',$username);
    $stmt->execute();
    $total = (int)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $sql = "SELECT p.id, p.contact_id, p.change_type, p.requested_at,
                   p.reviewed_at, p.reviewed_by, p.reviewer_name,
                   p.review_note, p.review_action,
                   c.first_name, c.last_name
            FROM contacts_pending p
            LEFT JOIN contacts c ON c.id = p.contact_id
            $where ORDER BY p.reviewed_at DESC LIMIT ? OFFSET ?";
    $stmt = $db->prepare($sql);
    if (!$stmt) { echo json_encode(['success'=>false,'message'=>$db->error]); return; }
    if ($username) $stmt->bind_param('sii',$username,$limit,$offset);
    else           $stmt->bind_param('ii',$limit,$offset);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close(); $db->close();

    echo json_encode([
        'success' => true,
        'data'    => $rows,
        'total'   => $total,
        'pages'   => (int)ceil($total/$limit),
        'page'    => $page,
    ]);
}
