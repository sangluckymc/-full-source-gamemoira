<?php
// admin/header.php
require_once __DIR__ . '/_init.php';

// Đặt mặc định nếu trang con chưa gán
$pageTitle = $pageTitle ?? 'Admin Panel';
$adminPage = $adminPage ?? '';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($pageTitle) ?> - Gamemoira Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style_admin.css?v=<?= time() ?>">
</head>
<body class="admin-body">

<div class="admin-root">

    <!-- TOPBAR -->
    <header class="admin-topbar">
        <div class="admin-topbar-left">
            <a href="<?= BASE_URL ?>admin/index.php" class="admin-logo">
                <span class="admin-logo-mark">GM</span>
                <span class="admin-logo-text">Gamemoira Admin</span>
            </a>
        </div>
        <div class="admin-topbar-right">
            <span class="admin-user-name">
                Xin chào,
                <strong><?= htmlspecialchars($currentUser['full_name'] ?? 'Admin') ?></strong>
            </span>
            <a href="<?= BASE_URL ?>templates/logout.php" class="admin-btn-logout">
                Đăng xuất
            </a>
        </div>
    </header>

    <!-- LAYOUT -->
    <div class="admin-layout">

        <!-- SIDEBAR -->
        <aside class="admin-sidebar">
            <nav class="admin-nav">

                <a href="index.php"
                   class="admin-nav-link <?= $adminPage === 'dashboard' ? 'active' : '' ?>">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>

                <div class="admin-nav-group-title">Nội dung</div>

                <a href="posts.php"
                   class="admin-nav-link <?= $adminPage === 'posts' ? 'active' : '' ?>">
                    <span class="icon">📝</span>
                    <span>Tin đăng</span>
                </a>

                <a href="categories.php"
                   class="admin-nav-link <?= $adminPage === 'categories' ? 'active' : '' ?>">
                    <span class="icon">📂</span>
                    <span>Danh mục</span>
                </a>

                <a href="tags.php"
                   class="admin-nav-link <?= $adminPage === 'tags' ? 'active' : '' ?>">
                    <span class="icon">🏷</span>
                    <span>Tag bài viết</span>
                </a>

                <a href="pages.php"
                   class="admin-nav-link <?= $adminPage === 'pages' ? 'active' : '' ?>">
                    <span class="icon">📄</span>
                    <span>Trang tĩnh</span>
                </a>

                <div class="admin-nav-group-title">Giao diện</div>

                <a href="menus.php"
                   class="admin-nav-link <?= $adminPage === 'menus' ? 'active' : '' ?>">
                    <span class="icon">📑</span>
                    <span>Menu</span>
                </a>

                <a href="banners.php"
                   class="admin-nav-link <?= $adminPage === 'banners' ? 'active' : '' ?>">
                    <span class="icon">🖼</span>
                    <span>Banner</span>
                </a>

                <a href="banner_categories.php"
                   class="admin-nav-link <?= $adminPage === 'banner_categories' ? 'active' : '' ?>">
                    <span class="icon">🗂</span>
                    <span>Danh mục banner</span>
                </a>

                <div class="admin-nav-group-title">Thành viên</div>

                <a href="users.php"
                   class="admin-nav-link <?= $adminPage === 'users' ? 'active' : '' ?>">
                    <span class="icon">👤</span>
                    <span>Tài khoản</span>
                </a>

                <a href="transactions.php"
                   class="admin-nav-link <?= $adminPage === 'transactions' ? 'active' : '' ?>">
                    <span class="icon">💰</span>
                    <span>Giao dịch</span>
                </a>

                <a href="gold_topups.php"
                   class="admin-nav-link <?= $adminPage === 'gold_topups' ? 'active' : '' ?>">
                    <span class="icon">💳</span>
                    <span>Yêu cầu nạp GOLD</span>
                </a>

                </a>

                <div class="admin-nav-group-title">Khác</div>

                <a href="contacts.php"
                   class="admin-nav-link <?= $adminPage === 'contacts' ? 'active' : '' ?>">
                    <span class="icon">📨</span>
                    <span>Liên hệ</span>
                </a>

                <a href="documents.php"
                   class="admin-nav-link <?= $adminPage === 'documents' ? 'active' : '' ?>">
                    <span class="icon">📚</span>
                    <span>Tài liệu / hướng dẫn</span>
                </a>

                <a href="settings.php"
                   class="admin-nav-link <?= $adminPage === 'settings' ? 'active' : '' ?>">
                    <span class="icon">⚙️</span>
                    <span>Cài đặt hệ thống</span>
                </a>

                <a href="game_submissions.php"
                   class="admin-nav-link <?= $adminPage === 'game_submissions' ? 'active' : '' ?>">
                    <span class="icon">🎮</span>
                    <span>Game submissions</span>
                </a>

            </nav>
        </aside>

        <!-- NỘI DUNG CHÍNH -->
        <main class="admin-content">
