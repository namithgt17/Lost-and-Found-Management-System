<?php
require __DIR__ . '/config.php';

function back(string $type, string $msg, bool $ok): void {
    $_SESSION['flash'] = ['msg' => $msg, 'ok' => $ok];
    if (!$ok) $_SESSION['old'] = $_POST;
    header('Location: index.php' . ($ok ? '' : '#' . $type . '-form'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }

$type = $_POST['type'] ?? '';
$tables = ['lost' => 'lost_items', 'found' => 'found_items']; // whitelist
if (!isset($tables[$type])) back('lost', 'Invalid submission.', false);

if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) back($type, 'Session expired. Please try again.', false);

$usn      = $_SESSION['usn'] ?? '';
if ($usn === '') { header('Location: index.php'); exit; }
$name     = trim($_POST['object_name'] ?? '');
$locFound = trim($_POST['location_found'] ?? '');
$locColl  = trim($_POST['collect_location'] ?? '');
$phone    = preg_replace('/[\s\-]/', '', trim($_POST['phone'] ?? ''));

if (!preg_match('/^[0-9][A-Z]{2}[0-9]{2}[A-Z]{2}[0-9]{3}$/', $usn))            back($type, 'Invalid USN.', false);
if ($name === '' || mb_strlen($name) > 150)             back($type, 'Object name is required (max 150 characters).', false);
if ($locFound === '' || mb_strlen($locFound) > 200)     back($type, 'Location is required (max 200 characters).', false);
if ($type === 'found' && ($locColl === '' || mb_strlen($locColl) > 200)) back($type, 'Collect location is required (max 200 characters).', false);
if (!preg_match('/^\+?[0-9]{10,13}$/', $phone))         back($type, 'Enter a valid phone number.', false);

// ---- Image upload (optional) ----
$photoPath = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $f = $_FILES['photo'];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        $msg = in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE])
            ? 'Image exceeds the server upload limit.' : 'Image upload failed. Try again.';
        back($type, $msg, false);
    }
    if (MAX_UPLOAD_BYTES > 0 && $f['size'] > MAX_UPLOAD_BYTES) back($type, 'Image is too large.', false);
    if (!is_uploaded_file($f['tmp_name']))                      back($type, 'Invalid upload.', false);

    // Verify real content type (never trust the client-supplied name/type)
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime]) || @getimagesize($f['tmp_name']) === false) {
        back($type, 'Only JPG, PNG, GIF or WEBP images are allowed.', false);
    }

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $fileName = bin2hex(random_bytes(16)) . '.' . $allowed[$mime]; // random name, extension from detected type
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $fileName)) back($type, 'Could not save the image.', false);
    $photoPath = UPLOAD_URL . $fileName;
}

try {
    $sql = "INSERT INTO {$tables[$type]} (usn, object_name, location_found, collect_location, phone, photo)
            VALUES (?, ?, ?, ?, ?, ?)";
    db()->prepare($sql)->execute([$usn, $name, $locFound, $locColl, $phone, $photoPath]);
} catch (Throwable $ex) {
    if ($photoPath) @unlink(__DIR__ . '/' . $photoPath);
    back($type, 'Database error. Please try again later.', false);
}

unset($_SESSION['old']);
back($type, ucfirst($type) . ' item added.', true);
