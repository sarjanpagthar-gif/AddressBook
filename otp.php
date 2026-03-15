<?php
// otp.php — AJAX handler for OTP send/verify/status
require_once 'config.php';
require_once 'auth.php';
requireLogin();

header('Content-Type: application/json');

// If OTP feature is disabled, auto-verify and return
if (!FEATURE_WA_OTP) {
    $_SESSION['otp_verified'] = true;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    echo json_encode([
        'success'    => true,
        'verified'   => true,
        'disabled'   => true,
        'message'    => 'OTP verification is disabled.',
        'expires_in' => 86400
    ]);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {

    case 'send':
        // Rate limit
        $cooldownEnd = !empty($_SESSION['otp_expires'])
            ? $_SESSION['otp_expires'] - OTP_EXPIRY + OTP_RESEND_COOLDOWN
            : 0;
        if ($cooldownEnd > time()) {
            $wait = $cooldownEnd - time();
            echo json_encode([
                'success' => false,
                'message' => "Please wait {$wait} second(s) before requesting a new OTP."
            ]);
            exit;
        }

        $result = sendWhatsAppOTP();

        if ($result['success']) {
            echo json_encode([
                'success'    => true,
                'message'    => 'OTP sent to your WhatsApp.',
                'expires_in' => OTP_EXPIRY
            ]);
        } else {
            if (FEATURE_OTP_FALLBACK) {
                echo json_encode([
                    'success'    => true,
                    'fallback'   => true,
                    'otp'        => $result['otp'],
                    'message'    => 'WhatsApp delivery failed. Fallback OTP: ' . $result['otp'],
                    'expires_in' => OTP_EXPIRY
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to send WhatsApp OTP. Please try again.'
                ]);
            }
        }
        break;

    case 'verify':
        $input  = isset($_POST['otp']) ? trim($_POST['otp']) : '';
        $result = verifyOTP($input);
        echo json_encode([
            'success' => $result['valid'],
            'message' => $result['message']
        ]);
        break;

    case 'status':
        echo json_encode([
            'enabled'    => true,
            'verified'   => !empty($_SESSION['otp_verified']),
            'has_otp'    => !empty($_SESSION['otp_code']),
            'expires_in' => !empty($_SESSION['otp_expires'])
                            ? max(0, $_SESSION['otp_expires'] - time()) : 0
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
