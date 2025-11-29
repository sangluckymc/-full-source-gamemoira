<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

// Logout user (public)
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    redirect(BASE_URL);
}

$route = $_GET['route'] ?? '';

$route = trim($route, '/');

// Map các route cố định
switch ($route) {
    case '':
    case 'trang-chu':
        $template = 'home.php';
        break;
    case 'blog':
        $template = 'blog_list.php';
        break;
    case 'tai-lieu':
        $template = 'docs.php';
        break;
    case 'lien-he':
        $template = 'contact.php';
        break;
    case 'tim-kiem':
        $template = 'search.php';
        break;
    case 'dang-nhap':
        $template = 'auth_login.php';
        break;
    case 'dang-ky':
        $template = 'auth_register.php';
        break;
    case 'quen-mat-khau':
        $template = 'auth_forgot.php';
        break;
    case 'doi-mat-khau':
        $template = 'auth_change.php';
        break;
    case 'dang-bai':
        $template = 'post_form_public.php';
        break;
    default:
        // Bài viết: /bai-viet/slug-id
        if (preg_match('~^bai-viet/(.+)-(\d+)$~', $route, $m)) {
            $_GET['post_id'] = (int)$m[2];
            $template = 'post_detail.php';
        } else {
            // Trang tĩnh: slug trong bảng pages
            $slug = $route;
            $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ?");
            $stmt->execute([$slug]);
            $page = $stmt->fetch();
            if ($page) {
                $template = 'page.php';
                $GLOBALS['current_page_data'] = $page;
            } else {
                // 404 đơn giản
                http_response_code(404);
                $pageTitle = 'Trang không tồn tại';
                $metaDescription = 'Không tìm thấy nội dung yêu cầu.';
                include __DIR__ . '/templates/header.php';
                echo '<div class="container my-5"><h1 class="h4">404 - Không tìm thấy trang</h1><p class="text-muted">Liên kết bạn truy cập không tồn tại hoặc đã bị xóa.</p></div>';
                include __DIR__ . '/templates/footer.php';
                exit;
            }
        }
        break;
}

include __DIR__ . '/templates/header.php';
include __DIR__ . '/templates/' . $template;
include __DIR__ . '/templates/footer.php';
