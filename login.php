<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Vui lòng nhập email và mật khẩu.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1 AND role IN ('editor','admin','superadmin')");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_role'] = $user['role'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Thông tin đăng nhập không đúng hoặc tài khoản không có quyền admin.';
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Đăng nhập Admin - Gamemoira</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card shadow-sm" style="max-width: 360px; width: 100%;">
        <div class="card-body">
            <h1 class="h5 mb-3 text-center">Admin Gamemoira</h1>
            <?php if ($error): ?>
                <div class="alert alert-danger small"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label small">Email</label>
                    <input type="email" name="email" class="form-control form-control-sm" required
                           value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Mật khẩu</label>
                    <input type="password" name="password" class="form-control form-control-sm" required>
                </div>
                <button class="btn btn-primary w-100 btn-sm">Đăng nhập</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
