<?php
// debug.php — Temporary diagnostics — DELETE after fixing!
// Visit: https://addressbook.free.nf/Test/debug.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>\n";
echo "=== PHP VERSION ===\n";
echo phpversion() . "\n\n";

echo "=== config.php LOAD ===\n";
try {
    require_once __DIR__ . '/config.php';
    echo "OK\n\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
}

echo "=== CONSTANTS DEFINED ===\n";
$needed = [
    'ADMIN_USER','ADMIN_PASS','SESSION_TIMEOUT',
    'WA_PHONE','WA_API_KEY','OTP_EXPIRY','OTP_RESEND_COOLDOWN',
    'FEATURE_WA_OTP','FEATURE_OTP_FALLBACK','FEATURE_EXPORT',
    'FEATURE_APPROVAL','FEATURE_GOOGLE_PLACES','FEATURE_COPY_ADDR',
    'VALIDATE_FIRST_NAME','VALIDATE_MOBILE','VALIDATE_CITY',
    'VALIDATE_VATAN_CITY','DB_HOST','DB_USER','DB_NAME',
];
foreach ($needed as $c) {
    echo $c . ': ' . (defined($c) ? var_export(constant($c), true) : 'NOT DEFINED') . "\n";
}

echo "\n=== DB CONNECTION ===\n";
try {
    $db = getDB();
    echo "Connected OK\n";
    $r = $db->query("SHOW TABLES");
    echo "Tables:\n";
    while ($row = $r->fetch_row()) echo "  - " . $row[0] . "\n";
    $db->close();
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== SESSION TEST ===\n";
if (session_status() === PHP_SESSION_NONE) session_start();
echo "Session ID: " . session_id() . "\n";
echo "Session data: "; print_r($_SESSION);

echo "\n=== auth.php LOAD ===\n";
try {
    require_once __DIR__ . '/auth.php';
    echo "OK\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== flags.php EXISTS ===\n";
echo file_exists(__DIR__ . '/flags.php') ? "YES\n" : "NO — upload flags.php!\n";

echo "\n=== config.php WRITABLE ===\n";
echo is_writable(__DIR__ . '/config.php') ? "YES — toggles will work\n" : "NO — run: chmod 666 config.php\n";

echo "</pre>";
