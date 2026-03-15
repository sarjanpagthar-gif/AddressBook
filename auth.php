<?php
// auth.php — Session auth, OTP functions — reads flags from config.php
// config.php is included by the calling script before auth.php
// session_start only if not already started
if(session_status() === PHP_SESSION_NONE) { session_start(); }

// ── Auth guard ────────────────────────────────────────────────
function requireLogin() {
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: login.php');
        exit;
    }
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > SESSION_TIMEOUT) {
        session_destroy();
        header('Location: login.php?timeout=1');
        exit;
    }
    $_SESSION['login_time'] = time();
}

// ── Require OTP (honours FEATURE_WA_OTP flag) ─────────────────
function requireOTP() {
    if (!FEATURE_WA_OTP) return; // OTP disabled — skip check
    if (empty($_SESSION['otp_verified'])) {
        http_response_code(403);
        echo json_encode([
            'success'      => false,
            'message'      => 'OTP verification required.',
            'otp_required' => true
        ]);
        exit;
    }
}

// ── Send WhatsApp OTP ─────────────────────────────────────────
function sendWhatsAppOTP() {
    $otp     = strval(rand(100000, 999999));
    $expires = time() + OTP_EXPIRY;

    $_SESSION['otp_code']    = $otp;
    $_SESSION['otp_expires'] = $expires;
    $_SESSION['otp_verified']= false;

    $msg  = urlencode('Your Contacts App OTP is: *' . $otp . '*. Valid for 5 minutes. Do not share.');
    $url  = 'https://api.callmebot.com/whatsapp.php?phone=' . WA_PHONE . '&text=' . $msg . '&apikey=' . WA_API_KEY;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'success'  => ($httpCode >= 200 && $httpCode < 300),
        'otp'      => $otp,
        'response' => $response,
        'expires'  => $expires
    ];
}

// ── Verify OTP ────────────────────────────────────────────────
function verifyOTP($input) {
    if (empty($_SESSION['otp_code']) || empty($_SESSION['otp_expires'])) {
        return ['valid' => false, 'message' => 'No OTP found. Please request a new one.'];
    }
    if (time() > $_SESSION['otp_expires']) {
        unset($_SESSION['otp_code'], $_SESSION['otp_expires']);
        return ['valid' => false, 'message' => 'OTP expired. Please request a new one.'];
    }
    if (trim($input) !== $_SESSION['otp_code']) {
        return ['valid' => false, 'message' => 'Incorrect OTP. Please try again.'];
    }
    $_SESSION['otp_verified'] = true;
    unset($_SESSION['otp_code'], $_SESSION['otp_expires']);
    return ['valid' => true, 'message' => 'OTP verified successfully.'];
}
