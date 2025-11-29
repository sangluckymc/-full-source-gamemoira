<?php

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    if (!preg_match('~^https?://~', $url)) {
        $url = BASE_URL . ltrim($url, '/');
    }
    header('Location: ' . $url);
    exit;
}

function slugify($text) {
    $text = trim($text);
    $text = mb_strtolower($text, 'UTF-8');

    $chars = [
        'à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ',
        'è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ',
        'ì','í','ị','ỉ','ĩ',
        'ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ở','ờ','ớ','ợ','ỡ',
        'ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ',
        'ỳ','ý','ỵ','ỷ','ỹ',
        'đ',
        'À','Á','Ạ','Ả','Ã','Â','Ầ','Ấ','Ậ','Ẩ','Ẫ','Ă','Ằ','Ắ','Ặ','Ẳ','Ẵ',
        'È','É','Ẹ','Ẻ','Ẽ','Ê','Ề','Ế','Ệ','Ể','Ễ',
        'Ì','Í','Ị','Ỉ','Ĩ',
        'Ò','Ó','Ọ','Ỏ','Õ','Ô','Ồ','Ố','Ộ','Ổ','Ỗ','Ơ','Ờ','Ớ','Ợ','Ở','Ỡ',
        'Ù','Ú','Ụ','Ủ','Ũ','Ư','Ừ','Ứ','Ự','Ử','Ữ',
        'Ỳ','Ý','Ỵ','Ỷ','Ỹ',
        'Đ'
    ];
    $latin = [
        'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
        'e','e','e','e','e','e','e','e','e','e','e',
        'i','i','i','i','i',
        'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
        'u','u','u','u','u','u','u','u','u','u','u',
        'y','y','y','y','y',
        'd',
        'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
        'e','e','e','e','e','e','e','e','e','e','e',
        'i','i','i','i','i',
        'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
        'u','u','u','u','u','u','u','u','u','u','u',
        'y','y','y','y','y',
        'd'
    ];
    $text = str_replace($chars, $latin, $text);
    $text = preg_replace('~[^a-z0-9]+~', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'slug';
}

function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function currentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function isAdmin() {
    return in_array(currentUserRole(), ['editor','admin','superadmin'], true);
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect(BASE_URL . 'admin/login.php');
    }
}

function isBannerActive($banner) {
    if (empty($banner['is_active'])) {
        return false;
    }
    $today = date('Y-m-d');
    if (!empty($banner['start_date']) && $banner['start_date'] > $today) {
        return false;
    }
    if (!empty($banner['end_date']) && $banner['end_date'] < $today) {
        return false;
    }
    return true;
}



function get_main_menu(PDO $pdo): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $stmt = $pdo->prepare("
        SELECT id, title, url, is_highlight
        FROM menus
        WHERE position = 'main' AND is_active = 1
        ORDER BY sort_order ASC, id ASC
    ");
    $stmt->execute();
    $cache = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $cache;
}
