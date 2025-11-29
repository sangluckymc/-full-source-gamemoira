<?php
// Cấu hình chung cho Gamemoira.vn

define('BASE_URL', 'https://gamemoira.vn/');

// ===== Kết nối MySQL (theo thông tin bạn cung cấp) =====
define('DB_HOST', 'localhost');
define('DB_NAME', 'btqsgr3aeb6m_dangquangcao');
define('DB_USER', 'btqsgr3aeb6m_dangquangcao');
define('DB_PASS', 'aMDDg!fVvf2G');

// Các cấu hình khác
define('SITE_NAME', 'Gamemoira.vn');
define('SITE_DESCRIPTION', 'Giới thiệu & triển khai giải pháp Gamemoira Pro, tối ưu chiến dịch thông minh.');
define('ITEMS_PER_PAGE', 10);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
