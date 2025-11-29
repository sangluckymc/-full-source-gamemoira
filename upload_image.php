<?php
require_once __DIR__ . '/../config.php';

if (empty($_FILES['file']) || $_FILES['file']['error'] != UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Không nhận được file upload.']);
    exit;
}

$maxSize = 5 * 1024 * 1024;
if ($_FILES['file']['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => 'File quá lớn, tối đa 5MB.']);
    exit;
}

$allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$filename   = $_FILES['file']['name'];
$ext        = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt)) {
    http_response_code(400);
    echo json_encode(['error' => 'Định dạng không hỗ trợ.']);
    exit;
}

$uploadDir = __DIR__ . '/../assets/uploads/editor/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

$newName = 'editor_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
$target  = $uploadDir . $newName;

if (!move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
    http_response_code(500);
    echo json_encode(['error' => 'Không lưu được file trên server.']);
    exit;
}

$publicPath = 'assets/uploads/editor/' . $newName;

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['location' => BASE_URL . $publicPath]);
