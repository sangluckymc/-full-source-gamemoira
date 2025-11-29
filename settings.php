<?php
// admin/settings.php
require_once __DIR__ . '/_init.php';

$adminPage = 'settings';
$pageTitle = 'Cài đặt hệ thống';

// Nếu chưa có bảng settings thì tạo nhanh
$pdo->exec("CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(191) NOT NULL UNIQUE,
    setting_value TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Tự định nghĩa hàm nếu helpers.php chưa có
if (!function_exists('gm_get_setting')) {
    function gm_get_setting(string $key, $default = null) {
        global $pdo;
        $stm = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
        $stm->execute([$key]);
        $val = $stm->fetchColumn();
        return $val !== false ? $val : $default;
    }
}
if (!function_exists('gm_set_setting')) {
    function gm_set_setting(string $key, $value): void {
        global $pdo;
        $stm = $pdo->prepare("INSERT INTO settings (setting_key, setting_value)
                              VALUES (:k,:v)
                              ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stm->execute([':k' => $key, ':v' => $value]);
    }
}

$errors  = [];
$success = '';

// Lấy giá trị hiện tại
$siteName       = gm_get_setting('site_name', 'Gamemoira Pro');
$siteTagline    = gm_get_setting('site_tagline', 'Website tổng hợp MU Online');
$siteLogo       = gm_get_setting('site_logo', '');
$contactEmail   = gm_get_setting('contact_email', '');
$contactHotline = gm_get_setting('contact_hotline', '');
$contactZalo    = gm_get_setting('contact_zalo', '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteName       = trim($_POST['site_name'] ?? '');
    $siteTagline    = trim($_POST['site_tagline'] ?? '');
    $siteLogo       = trim($_POST['site_logo'] ?? '');
    $contactEmail   = trim($_POST['contact_email'] ?? '');
    $contactHotline = trim($_POST['contact_hotline'] ?? '');
    $contactZalo    = trim($_POST['contact_zalo'] ?? '');

    if ($siteName === '') {
        $errors[] = 'Vui lòng nhập tên website.';
    }

    if (!$errors) {
        gm_set_setting('site_name', $siteName);
        gm_set_setting('site_tagline', $siteTagline);
        gm_set_setting('site_logo', $siteLogo);
        gm_set_setting('contact_email', $contactEmail);
        gm_set_setting('contact_hotline', $contactHotline);
        gm_set_setting('contact_zalo', $contactZalo);

        $success = 'Đã lưu cài đặt hệ thống.';
    }
}

require __DIR__ . '/header.php';
?>

<h1 class="admin-page-title">Cài đặt hệ thống</h1>

<div class="admin-card">
    <h2 class="admin-card-title">Thông tin chung</h2>

    <?php if ($errors): ?>
        <div class="admin-alert admin-alert-danger">
            <?php foreach ($errors as $err): ?>
                <div>- <?= htmlspecialchars($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php elseif ($success): ?>
        <div class="admin-alert admin-alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="admin-form-row">
            <label>Tên website:</label>
            <input type="text" name="site_name" class="input"
                   value="<?= htmlspecialchars($siteName) ?>" required>
        </div>
        <div class="admin-form-row">
            <label>Slogan / Tagline:</label>
            <input type="text" name="site_tagline" class="input"
                   value="<?= htmlspecialchars($siteTagline) ?>">
        </div>
        <div class="admin-form-row">
            <label>Logo (URL ảnh):</label>
            <input type="text" name="site_logo" class="input"
                   value="<?= htmlspecialchars($siteLogo) ?>">
        </div>
        <div class="admin-form-row">
            <label>Email liên hệ:</label>
            <input type="email" name="contact_email" class="input"
                   value="<?= htmlspecialchars($contactEmail) ?>">
        </div>
        <div class="admin-form-row">
            <label>Hotline:</label>
            <input type="text" name="contact_hotline" class="input"
                   value="<?= htmlspecialchars($contactHotline) ?>">
        </div>
        <div class="admin-form-row">
            <label>Zalo:</label>
            <input type="text" name="contact_zalo" class="input"
                   value="<?= htmlspecialchars($contactZalo) ?>">
        </div>

        <button type="submit" class="btn btn-primary">Lưu cài đặt</button>
    </form>
</div>

<?php require __DIR__ . '/footer.php'; ?>
