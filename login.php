<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

session_start();

// URL sẽ quay lại sau khi login xong
$redirect = $_GET['redirect'] ?? BASE_URL;

// Xử lý submit form
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errors[] = 'Vui lòng nhập đầy đủ Email và Mật khẩu.';
    } else {
        // TODO: sửa lại tên bảng user cho đúng
        $stmt = $pdo->prepare("SELECT id, name, password_hash FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Email hoặc mật khẩu không đúng.';
        } else {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            header('Location: ' . $redirect);
            exit;
        }
    }
}

// render form
$pageTitle       = 'Đăng nhập';
$metaDescription = 'Đăng nhập tài khoản Gamemoira Pro để đăng MU mới.';

require __DIR__ . '/templates/header.php';
require __DIR__ . '/templates/auth_login.php';
require __DIR__ . '/templates/footer.php';
