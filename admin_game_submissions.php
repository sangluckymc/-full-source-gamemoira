<?php
require_once __DIR__ . '/header.php';

$action = $_GET['action'] ?? 'list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// =============================
// DUYỆT TIN (VIP / THƯỜNG)
// =============================
if ($action === 'approve' && $id) {
    // loại tin: vip / thuong
    $approveType = ($_GET['type'] ?? 'thuong') === 'vip' ? 'vip' : 'thuong';

    // Lấy bản đăng
    $stmt = $pdo->prepare("SELECT * FROM game_submissions WHERE id = ?");
    $stmt->execute([$id]);
    $g = $stmt->fetch();

    if ($g && $g['status'] !== 'approved') {
        $title   = $g['game_name'];
        $slug    = slugify($title);
        $excerpt = $g['short_desc'] ?: mb_substr(strip_tags($g['content']), 0, 150);
        $content = $g['content'];
        $thumb   = $g['image_url'] ?: null;

        // Tạo bài viết mới
        $stmt = $pdo->prepare("INSERT INTO posts
            (title, slug, excerpt, content, type, status, thumbnail,
             user_id, category_id, meta_title, meta_description, created_at, published_at)
            VALUES (?, ?, ?, ?, ?, 'published', ?, ?, NULL, ?, ?, NOW(), NOW())");

        $metaTitle = $title;
        $metaDesc  = $excerpt;

        $stmt->execute([
            $title,
            $slug,
            $excerpt,
            $content,
            $approveType,          // vip / thuong
            $thumb,
            $currentAdminId,
            $metaTitle,
            $metaDesc
        ]);

        $postId = $pdo->lastInsertId();

        // Cập nhật trạng thái bản đăng
        $up = $pdo->prepare("UPDATE game_submissions
                             SET status = 'approved', post_id = ?
                             WHERE id = ?");
        $up->execute([$postId, $id]);

        header("Location: admin_game_submissions.php?msg=approved&id={$id}");
        exit;
    }

    header("Location: admin_game_submissions.php?msg=not_found");
    exit;
}

// =============================
// TỪ CHỐI / XÓA
// =============================
if ($action === 'reject' && $id) {
    $stmt = $pdo->prepare("UPDATE game_submissions SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin_game_submissions.php?msg=rejected");
    exit;
}

if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM game_submissions WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin_game_submissions.php?msg=deleted");
    exit;
}

// =============================
// XEM CHI TIẾT 1 BẢN ĐĂNG
// =============================
if ($action === 'view' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM game_submissions WHERE id = ?");
    $stmt->execute([$id]);
    $g = $stmt->fetch();

    if (!$g) {
        echo '<div class="alert alert-danger m-3">Không tìm thấy bản đăng này.</div>';
        require __DIR__ . '/footer.php';
        exit;
    }
    ?>
    <h1 class="h4 mb-3">Chi tiết bản đăng #<?= (int)$g['id'] ?></h1>

    <div class="mb-3">
        <a href="admin_game_submissions.php" class="btn btn-sm btn-outline-secondary">← Quay lại danh sách</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    Thông tin Game
                </div>
                <div class="card-body">
                    <p><strong>Tên Game:</strong> <?= e($g['game_name']) ?></p>
                    <p><strong>Trang chủ:</strong> <a href="<?= e($g['homepage']) ?>" target="_blank"><?= e($g['homepage']) ?></a></p>
                    <p><strong>Fanpage / Hội nhóm:</strong> <?= e($g['fanpage']) ?></p>
                    <p><strong>Phiên bản:</strong> <?= e($g['version']) ?> — <strong>Kiểu reset:</strong> <?= e($g['reset_type']) ?></p>
                    <p><strong>Thể loại:</strong> <?= e($g['category']) ?> — <strong>Kiểu point:</strong> <?= e($g['point_type']) ?></p>
                    <p><strong>Máy chủ:</strong> <?= e($g['server_name']) ?></p>
                    <p><strong>Miêu tả ngắn gọn:</strong> <?= e($g['short_desc']) ?></p>
                    <p><strong>Alpha:</strong> <?= e($g['alpha_time']) ?> — <?= e($g['alpha_date']) ?></p>
                    <p><strong>Open:</strong> <?= e($g['open_time']) ?> — <?= e($g['open_date']) ?></p>
                    <p><strong>Exp rate:</strong> <?= e($g['exp_rate']) ?> — <strong>Drop rate:</strong> <?= e($g['drop_rate']) ?></p>
                    <p><strong>Anti Hack:</strong> <?= e($g['anti_hack']) ?></p>
                    <p><strong>Ghi chú ẩn:</strong> <?= nl2br(e($g['note'])) ?></p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    Nội dung mô tả chi tiết
                </div>
                <div class="card-body">
                    <div class="border p-2 bg-light" style="white-space:pre-wrap;"><?= nl2br(e($g['content'])) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <?php if ($g['image_url']): ?>
                <div class="card mb-3">
                    <div class="card-header">
                        Hình ảnh / Video
                    </div>
                    <div class="card-body text-center">
                        <a href="<?= e($g['image_url']) ?>" target="_blank">
                            <img src="<?= e($g['image_url']) ?>" alt="" class="img-fluid mb-2">
                        </a>
                        <div class="small text-muted">Click để mở link gốc.</div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card mb-3">
                <div class="card-header">
                    Hành động
                </div>
                <div class="card-body d-grid gap-2">
                    <?php if ($g['status'] !== 'approved'): ?>
                        <a href="admin_game_submissions.php?action=approve&id=<?= (int)$g['id'] ?>&type=vip"
                           class="btn btn-warning">
                            ✅ Duyệt tin VIP
                        </a>
                        <a href="admin_game_submissions.php?action=approve&id=<?= (int)$g['id'] ?>&type=thuong"
                           class="btn btn-success">
                            ✅ Duyệt tin thường
                        </a>
                    <?php else: ?>
                        <div class="alert alert-success small mb-2">
                            Đã duyệt — bài viết ID: <?= (int)$g['post_id'] ?>
                        </div>
                        <a href="posts.php?action=edit&id=<?= (int)$g['post_id'] ?>"
                           class="btn btn-outline-primary btn-sm">
                            ✏ Sửa bài viết
                        </a>
                    <?php endif; ?>

                    <?php if ($g['status'] !== 'rejected'): ?>
                        <a href="admin_game_submissions.php?action=reject&id=<?= (int)$g['id'] ?>"
                           class="btn btn-outline-danger btn-sm"
                           onclick="return confirm('Từ chối bản đăng này?');">
                            Từ chối
                        </a>
                    <?php endif; ?>

                    <a href="admin_game_submissions.php?action=delete&id=<?= (int)$g['id'] ?>"
                       class="btn btn-outline-secondary btn-sm"
                       onclick="return confirm('Xóa hẳn bản đăng này?');">
                        Xóa bản đăng
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php
    require __DIR__ . '/footer.php';
    exit;
}

// =============================
// DANH SÁCH GAME SUBMISSIONS
// =============================
$statusFilter = $_GET['status'] ?? 'all';
$allowedStatus = ['all', 'pending', 'approved', 'rejected'];
if (!in_array($statusFilter, $allowedStatus, true)) {
    $statusFilter = 'all';
}

$whereSql = '';
if ($statusFilter === 'pending') {
    $whereSql = "WHERE status = 'pending'";
} elseif ($statusFilter === 'approved') {
    $whereSql = "WHERE status = 'approved'";
} elseif ($statusFilter === 'rejected') {
    $whereSql = "WHERE status = 'rejected'";
}

$sql = "SELECT * FROM game_submissions $whereSql ORDER BY id DESC";
$games = $pdo->query($sql)->fetchAll();

$msg = $_GET['msg'] ?? '';
?>

<h1 class="h4 mb-3">Quản lý bài đăng Game (game_submissions)</h1>

<?php if ($msg === 'approved' && isset($_GET['id'])): ?>
    <div class="alert alert-success">
        Đã duyệt và tạo bài viết mới từ bản đăng #<?= (int)$_GET['id'] ?>.
    </div>
<?php elseif ($msg === 'rejected'): ?>
    <div class="alert alert-warning">Đã từ chối bản đăng.</div>
<?php elseif ($msg === 'deleted'): ?>
    <div class="alert alert-secondary">Đã xóa bản đăng.</div>
<?php endif; ?>

<ul class="nav nav-pills mb-3">
    <li class="nav-item">
        <a class="nav-link <?= $statusFilter === 'all' ? 'active' : '' ?>" href="admin_game_submissions.php">Tất cả</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $statusFilter === 'pending' ? 'active' : '' ?>" href="admin_game_submissions.php?status=pending">Pending</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $statusFilter === 'approved' ? 'active' : '' ?>" href="admin_game_submissions.php?status=approved">Đã duyệt</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $statusFilter === 'rejected' ? 'active' : '' ?>" href="admin_game_submissions.php?status=rejected">Đã từ chối</a>
    </li>
</ul>

<table class="table table-sm table-bordered align-middle">
    <thead>
    <tr>
        <th width="50">ID</th>
        <th>Tên Game</th>
        <th>Trang chủ</th>
        <th>Phiên bản</th>
        <th>Máy chủ</th>
        <th>Trạng thái</th>
        <th>Ngày gửi</th>
        <th width="200">Hành động</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($games): ?>
        <?php foreach ($games as $g): ?>
            <tr>
                <td><?= (int)$g['id'] ?></td>
                <td><?= e($g['game_name']) ?></td>
                <td>
                    <?php if ($g['homepage']): ?>
                        <a href="<?= e($g['homepage']) ?>" target="_blank">Link</a>
                    <?php endif; ?>
                </td>
                <td><?= e($g['version']) ?></td>
                <td><?= e($g['server_name']) ?></td>
                <td>
                    <?php if ($g['status'] === 'pending'): ?>
                        <span class="badge bg-warning text-dark">Pending</span>
                    <?php elseif ($g['status'] === 'approved'): ?>
                        <span class="badge bg-success">Approved</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Rejected</span>
                    <?php endif; ?>
                </td>
                <td><?= e($g['created_at']) ?></td>
                <td>
                    <a href="admin_game_submissions.php?action=view&id=<?= (int)$g['id'] ?>"
                       class="btn btn-info btn-sm">Xem</a>
                    <?php if ($g['status'] !== 'approved'): ?>
                        <a href="admin_game_submissions.php?action=approve&id=<?= (int)$g['id'] ?>&type=vip"
                           class="btn btn-warning btn-sm">Duyệt VIP</a>
                        <a href="admin_game_submissions.php?action=approve&id=<?= (int)$g['id'] ?>&type=thuong"
                           class="btn btn-success btn-sm">Duyệt thường</a>
                    <?php endif; ?>
                    <a href="admin_game_submissions.php?action=reject&id=<?= (int)$g['id'] ?>"
                       class="btn btn-outline-danger btn-sm"
                       onclick="return confirm('Từ chối bản đăng này?');">Từ chối</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="8" class="text-center text-muted">Chưa có bản đăng nào.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/footer.php'; ?>
