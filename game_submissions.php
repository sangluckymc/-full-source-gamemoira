<?php
// admin/game_submissions.php
require_once __DIR__ . '/_init.php';

$adminPage = 'game_submissions';
$pageTitle = 'Duyệt Game submissions';

// Duyệt bài -> tạo post
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    if ($id > 0) {
        $stm = $pdo->prepare("SELECT * FROM game_submissions WHERE id = ?");
        $stm->execute([$id]);
        $sub = $stm->fetch(PDO::FETCH_ASSOC);

        if ($sub && $sub['status'] === 'pending') {
            // Tạo post mới
            $title   = $sub['game_name'] ?? 'Game mới';
            $slug    = strtolower(preg_replace('~[^a-z0-9]+~i', '-', $title));
            $excerpt = $sub['short_desc'] ?? '';
            $content = $sub['content'] ?? '';

            $postStmt = $pdo->prepare("INSERT INTO posts
                (title, slug, excerpt, content, type, status, thumbnail, user_id, category_id, meta_title, meta_description, created_at, published_at)
                VALUES (:title,:slug,:excerpt,:content,'thuong','published',:thumbnail,NULL,NULL,:meta_title,:meta_description,NOW(),NOW())");
            $postStmt->execute([
                ':title'            => $title,
                ':slug'             => $slug,
                ':excerpt'          => $excerpt,
                ':content'          => $content,
                ':thumbnail'        => $sub['image_url'] ?? '',
                ':meta_title'       => $title,
                ':meta_description' => mb_substr($excerpt, 0, 150),
            ]);
            $postId = (int)$pdo->lastInsertId();

            // Cập nhật lại submission
            $upd = $pdo->prepare("UPDATE game_submissions
                                  SET post_id = :post_id, status = 'approved'
                                  WHERE id = :id");
            $upd->execute([
                ':post_id' => $postId,
                ':id'      => $id,
            ]);
        }
    }
    header('Location: game_submissions.php');
    exit;
}

if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    if ($id > 0) {
        $pdo->prepare("UPDATE game_submissions SET status='rejected' WHERE id=?")->execute([$id]);
    }
    header('Location: game_submissions.php');
    exit;
}

$subs = $pdo->query("SELECT * FROM game_submissions ORDER BY id DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/header.php';
?>

<h1 class="admin-page-title">Duyệt Game submissions</h1>

<div class="admin-card">
    <h2 class="admin-card-title">Danh sách submissions</h2>

    <table class="admin-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Tên Game</th>
            <th>Trang chủ</th>
            <th>Fanpage</th>
            <th>Trạng thái</th>
            <th>Đã tạo Post?</th>
            <th>Thời gian</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$subs): ?>
            <tr><td colspan="8" class="empty">Chưa có submissions nào.</td></tr>
        <?php else: ?>
            <?php foreach ($subs as $g): ?>
                <tr>
                    <td><?= $g['id'] ?></td>
                    <td><?= htmlspecialchars($g['game_name']) ?></td>
                    <td><?= htmlspecialchars($g['homepage']) ?></td>
                    <td><?= htmlspecialchars($g['fanpage']) ?></td>
                    <td><?= htmlspecialchars($g['status']) ?></td>
                    <td>
                        <?php if (!empty($g['post_id'])): ?>
                            <span class="admin-badge bg-green">Đã tạo (ID <?= (int)$g['post_id'] ?>)</span>
                        <?php else: ?>
                            <span class="admin-badge bg-red">Chưa tạo</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($g['created_at']) ?></td>
                    <td>
                        <?php if ($g['status'] === 'pending'): ?>
                            <a class="admin-link"
                               href="game_submissions.php?approve=<?= $g['id'] ?>"
                               onclick="return confirm('Duyệt và tạo bài đăng cho Game này?');">Duyệt</a> |
                            <a class="admin-link text-red"
                               href="game_submissions.php?reject=<?= $g['id'] ?>"
                               onclick="return confirm('Từ chối submissions này?');">Từ chối</a>
                        <?php else: ?>
                            <em>Đã xử lý</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/footer.php'; ?>
