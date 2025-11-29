<?php
// admin/_init.php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    $redirect = BASE_URL . 'admin/index.php';
    header('Location: ' . BASE_URL . 'templates/login.php?redirect=' . urlencode($redirect));
    exit;
}

$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, full_name, email, role FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $userId]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser || ($currentUser['role'] ?? '') !== 'admin') {
    die('Bạn không có quyền truy cập khu vực quản trị.');
}
