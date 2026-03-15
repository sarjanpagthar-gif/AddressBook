<?php
// flags.php — Read and toggle feature flags via AJAX
require_once 'config.php';
require_once 'auth.php';
requireLogin();

header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : 'list';

// All flags with metadata
$FLAG_META = [
  // Feature flags
  'FEATURE_WA_OTP'        => ['label'=>'WhatsApp OTP',          'group'=>'Features',          'desc'=>'Require OTP before approve/reject'],
  'FEATURE_OTP_FALLBACK'  => ['label'=>'OTP Fallback',          'group'=>'Features',          'desc'=>'Show OTP on screen if WhatsApp fails'],
  'FEATURE_EXPORT'        => ['label'=>'Excel Export',          'group'=>'Features',          'desc'=>'Allow Excel export on contacts page'],
  'FEATURE_APPROVAL'      => ['label'=>'Approval Workflow',     'group'=>'Features',          'desc'=>'Require approval before changes go live'],
  'FEATURE_GOOGLE_PLACES' => ['label'=>'Google Places',         'group'=>'Features',          'desc'=>'Address autocomplete from Google'],
  'FEATURE_COPY_ADDR'     => ['label'=>'Copy Address Button',   'group'=>'Features',          'desc'=>'Copy current address to Vatan address'],
  // Validation flags
  'VALIDATE_FIRST_NAME'   => ['label'=>'First name',            'group'=>'Basic info',        'desc'=>'Required field'],
  'VALIDATE_LAST_NAME'    => ['label'=>'Last name',             'group'=>'Basic info',        'desc'=>'Required field'],
  'VALIDATE_DOB'          => ['label'=>'Date of birth',         'group'=>'Basic info',        'desc'=>'Required field'],
  'VALIDATE_GENDER'       => ['label'=>'Gender',                'group'=>'Basic info',        'desc'=>'Required field'],
  'VALIDATE_FATHER_NAME'  => ['label'=>'Father name',           'group'=>'Basic info',        'desc'=>'Required field'],
  'VALIDATE_MOTHER_NAME'  => ['label'=>'Mother name',           'group'=>'Basic info',        'desc'=>'Required field'],
  'VALIDATE_MOBILE'       => ['label'=>'Mobile no',             'group'=>'Basic info',        'desc'=>'Required field'],
  'VALIDATE_WHATSAPP'     => ['label'=>'WhatsApp no',           'group'=>'Basic info',        'desc'=>'Required field'],
  'VALIDATE_HOME_TOWN'    => ['label'=>'Home town',             'group'=>'Basic info',        'desc'=>'Required field'],
  'VALIDATE_BLOCK_NO'     => ['label'=>'Block no',              'group'=>'Current address',   'desc'=>'Required field'],
  'VALIDATE_ADDRESS_LINE1'=> ['label'=>'Address line 1',        'group'=>'Current address',   'desc'=>'Required field'],
  'VALIDATE_STREET_ADDRESS'=> ['label'=>'Street address',       'group'=>'Current address',   'desc'=>'Required field'],
  'VALIDATE_CITY'         => ['label'=>'City',                  'group'=>'Current address',   'desc'=>'Required field'],
  'VALIDATE_STATE'        => ['label'=>'State',                 'group'=>'Current address',   'desc'=>'Required field'],
  'VALIDATE_ZIP'          => ['label'=>'Zip',                   'group'=>'Current address',   'desc'=>'Required field'],
  'VALIDATE_COUNTRY'      => ['label'=>'Country',               'group'=>'Current address',   'desc'=>'Required field'],
  'VALIDATE_VATAN_VILLAGE'=> ['label'=>'Vatan village',         'group'=>'Vatan address',     'desc'=>'Required field'],
  'VALIDATE_VATAN_BLOCK_NO'=> ['label'=>'Vatan block no',       'group'=>'Vatan address',     'desc'=>'Required field'],
  'VALIDATE_VATAN_STREET' => ['label'=>'Vatan street address',  'group'=>'Vatan address',     'desc'=>'Required field'],
  'VALIDATE_VATAN_ADDR1'  => ['label'=>'Vatan address line 1',  'group'=>'Vatan address',     'desc'=>'Required field'],
  'VALIDATE_VATAN_CITY'   => ['label'=>'Vatan city',            'group'=>'Vatan address',     'desc'=>'Required field'],
  'VALIDATE_VATAN_STATE'  => ['label'=>'Vatan state',           'group'=>'Vatan address',     'desc'=>'Required field'],
  'VALIDATE_VATAN_ZIP'    => ['label'=>'Vatan zip',             'group'=>'Vatan address',     'desc'=>'Required field'],
  'VALIDATE_VATAN_COUNTRY'=> ['label'=>'Vatan country',         'group'=>'Vatan address',     'desc'=>'Required field'],
];

if ($action === 'list') {
    // Return current state of all flags
    $result = [];
    foreach ($FLAG_META as $key => $meta) {
        $result[] = [
            'key'     => $key,
            'label'   => $meta['label'],
            'group'   => $meta['group'],
            'desc'    => $meta['desc'],
            'value'   => defined($key) ? constant($key) : false,
        ];
    }
    echo json_encode(['success' => true, 'flags' => $result]);

} elseif ($action === 'toggle') {
    $key      = isset($_POST['key'])   ? trim($_POST['key'])   : '';
    $newval   = isset($_POST['value']) ? ($_POST['value'] === 'true' || $_POST['value'] === '1') : false;

    if (!array_key_exists($key, $FLAG_META)) {
        echo json_encode(['success' => false, 'message' => 'Unknown flag: ' . $key]);
        exit;
    }

    // Read config.php content
    $configFile = __DIR__ . '/config.php';
    $content    = file_get_contents($configFile);

    if ($content === false) {
        echo json_encode(['success' => false, 'message' => 'Cannot read config.php']);
        exit;
    }

    // Replace define line for this key
    $trueStr  = 'true';
    $falseStr = 'false';
    $newValStr = $newval ? $trueStr : $falseStr;

    // Match: define('KEY', true/false);  or define('KEY',true/false);
    $pattern     = "/define\('" . preg_quote($key, '/') . "',\s*(true|false)\)/";
    $replacement = "define('" . $key . "', " . $newValStr . ")";

    if (!preg_match($pattern, $content)) {
        echo json_encode(['success' => false, 'message' => 'Flag not found in config.php: ' . $key]);
        exit;
    }

    $newContent = preg_replace($pattern, $replacement, $content);

    if (file_put_contents($configFile, $newContent) === false) {
        echo json_encode(['success' => false, 'message' => 'Cannot write config.php — check file permissions (chmod 666)']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'key'     => $key,
        'value'   => $newval,
        'message' => $key . ' set to ' . $newValStr,
    ]);

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
