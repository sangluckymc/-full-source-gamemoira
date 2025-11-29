<?php
// admin/index.php (Dashboard)
require_once __DIR__ . '/_init.php';

$adminPage = 'dashboard';
$pageTitle = 'Dashboard';

$totalUsers        = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPosts        = (int)$pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$totalBanners      = (int)$pdo->query("SELECT COUNT(*) FROM banners")->fetchColumn();
$totalTransactions = (int)$pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();

require __DIR__ . '/header.php';
?>

<h1 class="admin-page-title">Dashboard</h1>

<div class="admin-grid-2">
    <div class="admin-card">
        <h2 class="admin-card-title">Thống kê nhanh</h2>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <tbody>
                <tr>
                    <td>Tài khoản</td>
                    <td><strong><?= $totalUsers ?></strong></td>
                </tr>
                <tr>
                    <td>Tin đăng</td>
                    <td><strong><?= $totalPosts ?></strong></td>
                </tr>
                <tr>
                    <td>Banner</td>
                    <td><strong><?= $totalBanners ?></strong></td>
                </tr>
                <tr>
                    <td>Giao dịch GOLD</td>
                    <td><strong><?= $totalTransactions ?></strong></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card">
        <h2 class="admin-card-title">Ghi chú</h2>
        <p style="font-size:13px;color:var(--admin-text-muted);">
            - Quản lý toàn bộ nội dung website trong khu vực admin này.<br>
            - Không cần chỉnh sửa code PHP, mọi thứ đều thông qua giao diện quản trị.<br>
            - Sử dụng menu bên trái để truy cập các chức năng: Tin đăng, Banner, GOLD, Cài đặt...
        </p>
    </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>
