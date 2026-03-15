<?php
require_once __DIR__ . '/config.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = isset($_POST['username']) ? trim($_POST['username']) : '';
    $pass = isset($_POST['password']) ? $_POST['password'] : '';

    if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = $user;
        $_SESSION['login_time']      = time();
        header('Location: approval.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Admin Login</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,sans-serif;background:#f5f5f3;color:#1a1a18;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}
.card{background:#fff;border:1px solid #e0e0dd;border-radius:16px;padding:2rem;width:100%;max-width:380px;box-shadow:0 4px 24px rgba(0,0,0,.07)}
.logo{width:48px;height:48px;background:#185FA5;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;color:#fff;font-size:22px;font-weight:700}
.title{text-align:center;font-size:20px;font-weight:700;margin-bottom:.25rem}
.subtitle{text-align:center;font-size:13px;color:#888;margin-bottom:1.75rem}
.form-group{display:flex;flex-direction:column;gap:5px;margin-bottom:1rem}
.form-label{font-size:12px;font-weight:600;color:#555}
.fi{padding:11px 13px;border:1px solid #ddd;border-radius:9px;font-size:14px;background:#fff;color:#1a1a18;width:100%;outline:none;-webkit-appearance:none;transition:border .15s}
.fi:focus{border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.12)}
.btn-login{width:100%;padding:12px;background:#185FA5;color:#fff;border:none;border-radius:9px;font-size:15px;font-weight:600;cursor:pointer;margin-top:.5rem;-webkit-appearance:none;transition:background .15s}
.btn-login:active{background:#0C447C}
.error{background:#FCEBEB;color:#A32D2D;border:1px solid #F7C1C1;border-radius:8px;padding:10px 13px;font-size:13px;margin-bottom:1rem;text-align:center}
.back-link{display:block;text-align:center;margin-top:1.25rem;font-size:13px;color:#185FA5;text-decoration:none}
.password-wrap{position:relative}
.password-wrap .fi{padding-right:42px}
.eye-btn{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#aaa;font-size:16px;padding:4px;-webkit-appearance:none}
</style>
</head>
<body>
<div class="card">
  <div class="logo">A</div>
  <div class="title">Admin Login</div>
  <div class="subtitle">Sign in to manage approvals</div>

  <?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="POST" action="login.php" autocomplete="on">
    <div class="form-group">
      <div class="form-label">Username</div>
      <input class="fi" type="text" name="username" placeholder="Enter username"
             value="<?php echo isset($_POST['username'])?htmlspecialchars($_POST['username']):''; ?>"
             autocomplete="username" required>
    </div>
    <div class="form-group">
      <div class="form-label">Password</div>
      <div class="password-wrap">
        <input class="fi" type="password" name="password" id="passInput"
               placeholder="Enter password" autocomplete="current-password" required>
        <button type="button" class="eye-btn" onclick="togglePass()">&#128065;</button>
      </div>
    </div>
    <button type="submit" class="btn-login">Sign in</button>
  </form>

  <a href="index.php" class="back-link">&#8592; Back to Contacts</a>
</div>

<script>
function togglePass(){
  var el=document.getElementById('passInput');
  el.type=el.type==='password'?'text':'password';
}
</script>
</body>
</html>
