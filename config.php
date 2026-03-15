<?php
// ============================================================
//  config.php — Central feature flags & app configuration
//  Edit this file to control app behaviour
// ============================================================

// ── Admin credentials ────────────────────────────────────────
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'Admin@1234');
define('SESSION_TIMEOUT', 3600); // seconds — 3600 = 1 hour

// ── WhatsApp OTP (via CallMeBot) ─────────────────────────────
define('WA_PHONE',   '919999999999'); // Your WhatsApp number with country code
define('WA_API_KEY', 'XXXXXXXX');     // API key from CallMeBot
define('OTP_EXPIRY',  300);           // Seconds OTP remains valid (300 = 5 min)
define('OTP_RESEND_COOLDOWN', 60);    // Seconds before resend is allowed

// ============================================================
//  FEATURE FLAGS
//  Set true to enable, false to disable
// ============================================================
// ── App features ────────────────────────────────────────────
define('FEATURE_WA_OTP',        false);   // WhatsApp OTP required before approve/reject
define('FEATURE_OTP_FALLBACK',  false);   // Show OTP on screen if WhatsApp delivery fails
define('FEATURE_EXPORT',        false);   // Allow Excel export on contacts page
define('FEATURE_APPROVAL',      false);   // Enable approval workflow (pending → approve/reject)
define('FEATURE_GOOGLE_PLACES', false);   // Google Places address autocomplete
define('FEATURE_COPY_ADDR',     true);   // Show "Copy to Vatan address" button in form

// ============================================================
//  FIELD VALIDATION FLAGS
//  true  = field is required (must be filled)
//  false = field is optional (can be left blank)
//  Set to false to make any field optional
// ============================================================

// Basic info
define('VALIDATE_FIRST_NAME',       true);   // required, min 2 chars
define('VALIDATE_LAST_NAME',        true);   // required, min 2 chars
define('VALIDATE_DOB',              false);  // optional, but if filled must be DD-MM-YYYY
define('VALIDATE_GENDER',           true);   // required — must select
define('VALIDATE_FATHER_NAME',      false);   // required, min 2 chars
define('VALIDATE_MOTHER_NAME',      false);   // required, min 2 chars
define('VALIDATE_MOBILE',           true);   // required, valid 10-digit number
define('VALIDATE_WHATSAPP',         false);  // optional, but if filled must be valid number
define('VALIDATE_HOME_TOWN',        true);   // required, min 2 chars

// Current address
define('VALIDATE_BLOCK_NO',         false);  // optional
define('VALIDATE_ADDRESS_LINE1',    true);   // required, min 3 chars
define('VALIDATE_STREET_ADDRESS',   true);   // required, min 3 chars
define('VALIDATE_CITY',             true);   // required, min 2 chars
define('VALIDATE_STATE',            true);   // required, min 2 chars
define('VALIDATE_ZIP',              true);   // required, numeric
define('VALIDATE_COUNTRY',          true);   // required, min 2 chars

// Vatan address
define('VALIDATE_VATAN_VILLAGE',    true);   // required, min 2 chars
define('VALIDATE_VATAN_BLOCK_NO',   false);  // optional
define('VALIDATE_VATAN_STREET',     true);   // required, min 3 chars
define('VALIDATE_VATAN_ADDR1',      true);   // required, min 3 chars
define('VALIDATE_VATAN_CITY',       true);   // required, min 2 chars
define('VALIDATE_VATAN_STATE',      true);   // required, min 2 chars
define('VALIDATE_VATAN_ZIP',        true);   // required, numeric
define('VALIDATE_VATAN_COUNTRY',    true);   // required, min 2 chars

// ── Database connection ──────────────────────────────────────
define('DB_HOST', 'sql301.infinityfree.com');
define('DB_USER', 'if0_41373306');
define('DB_PASS', 'Gautami1993');
define('DB_NAME', 'if0_41373306_contactbook');

function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => 'DB connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
