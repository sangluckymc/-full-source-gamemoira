<?php
// admin/posts.php
require_once __DIR__ . '/_init.php';

$adminPage = 'posts';
$pageTitle = 'Quản lý tin đăng';

$errors = [];
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = null;

if ($editId > 0) {
    $stm = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stm->execute([$editId]);
    $editing = $stm->fetch(PDO::FETCH_ASSOC);
    if (!$editing) {
        $editId = 0;
    }
}

// Lấy danh mục cho select
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title            = trim($_POST['title'] ?? '');
    $slug             = trim($_POST['slug'] ?? '');
    $excerpt          = trim($_POST['excerpt'] ?? '');
    $content          = $_POST['content'] ?? '';
    $type             = $_POST['type'] ?? 'thuong';
    $status           = $_POST['status'] ?? 'published';
    $thumbnail        = trim($_POST['thumbnail'] ?? '');
    $category_id      = (int)($_POST['category_id'] ?? 0);
    $meta_title       = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');

    if ($title === '') {
        $errors[] = 'Vui lòng nhập tiêu đề tin.';
    }
    if ($slug === '') {
        $errors[] = 'Vui lòng nhập slug.';
    }

    if (!$errors) {
        if ($editId > 0) {
            $stm = $pdo->prepare("UPDATE posts
                                  SET title=:title, slug=:slug, excerpt=:excerpt, content=:content,
                                      type=:type, status=:status, thumbnail=:thumbnail,
                                      category_id=:category_id,
                                      meta_title=:meta_title, meta_description=:meta_description
                                  WHERE id=:id");
            $stm->execute([
                ':title'            => $title,
                ':slug'             => $slug,
                ':excerpt'          => $excerpt,
                ':content'          => $content,
                ':type'             => $type,
                ':status'           => $status,
                ':thumbnail'        => $thumbnail,
                ':category_id'      => $category_id ?: null,
                ':meta_title'       => $meta_title,
                ':meta_description' => $meta_description,
                ':id'               => $editId,
            ]);
        } else {
            $stm = $pdo->prepare("INSERT INTO posts
                (title, slug, excerpt, content, type, status, thumbnail, user_id, category_id, meta_title, meta_description, created_at)
                VALUES (:title,:slug,:excerpt,:content,:type,:status,:thumbnail,:user_id,:category_id,:meta_title,:meta_description, NOW())");
            $stm->execute([
                ':title'            => $title,
                ':slug'             => $slug,
                ':excerpt'          => $excerpt,
                ':content'          => $content,
                ':type'             => $type,
                ':status'           => $status,
                ':thumbnail'        => $thumbnail,
                ':user_id'          => $currentUser['id'] ?? null,
                ':category_id'      => $category_id ?: null,
                ':meta_title'       => $meta_title,
                ':meta_description' => $meta_description,
            ]);
        }
        header('Location: posts.php');
        exit;
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
    }
    header('Location: posts.php');
    exit;
}

$posts = $pdo->query("SELECT p.*, c.name AS category_name
                      FROM posts p
                      LEFT JOIN categories c ON c.id = p.category_id
                      ORDER BY p.id DESC
                      LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/header.php';
?>

<h1 class="admin-page-title">Quản lý tin đăng</h1>

<div class="admin-grid-2">
    <div class="admin-card">
        <h2 class="admin-card-title"><?= $editId ? 'Sửa tin' : 'Thêm tin mới' ?></h2>

        <?php if ($errors): ?>
            <div class="admin-alert admin-alert-danger">
                <?php foreach ($errors as $err): ?>
                    <div>- <?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="admin-form-row">
                <label>Tiêu đề:</label>
                <input type="text" name="title" class="input"
                       value="<?= htmlspecialchars($editing['title'] ?? '') ?>" required>
            </div>
            <div class="admin-form-row">
                <label>Slug:</label>
                <input type="text" name="slug" class="input"
                       value="<?= htmlspecialchars($editing['slug'] ?? '') ?>" required>
            </div>
            <div class="admin-form-row">
                <label>Loại tin:</label>
                <select name="type" class="input">
                    <?php
                    $types = ['thuong' => 'Thường', 'vip' => 'VIP'];
                    $curType = $editing['type'] ?? 'thuong';
                    foreach ($types as $k => $v):
                    ?>
                        <option value="<?= $k ?>" <?= $k === $curType ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-row">
                <label>Trạng thái:</label>
                <select name="status" class="input">
                    <?php
                    $st = ['draft' => 'Nháp', 'published' => 'Đã đăng'];
                    $curSt = $editing['status'] ?? 'published';
                    foreach ($st as $k => $v):
                    ?>
                        <option value="<?= $k ?>" <?= $k === $curSt ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-row">
                <label>Danh mục:</label>
                <select name="category_id" class="input">
                    <option value="0">-- Không chọn --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= !empty($editing['category_id']) && (int)$editing['category_id'] === (int)$cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-row">
                <label>Thumbnail (URL):</label>
                <input type="text" name="thumbnail" class="input"
                       value="<?= htmlspecialchars($editing['thumbnail'] ?? '') ?>">
            </div>
            <div class="admin-form-row">
                <label>Mô tả ngắn:</label>
                <textarea name="excerpt" class="input" rows="3"><?= htmlspecialchars($editing['excerpt'] ?? '') ?></textarea>
            </div>
            <div class="admin-form-row">
                <label>Nội dung:</label>
                <textarea name="content" class="input" rows="6"><?= htmlspecialchars($editing['content'] ?? '') ?></textarea>
            </div>
            <div class="admin-form-row">
                <label>Meta title:</label>
                <input type="text" name="meta_title" class="input"
                       value="<?= htmlspecialchars($editing['meta_title'] ?? '') ?>">
            </div>
            <div class="admin-form-row">
                <label>Meta description:</label>
                <input type="text" name="meta_description" class="input"
                       value="<?= htmlspecialchars($editing['meta_description'] ?? '') ?>">
            </div>

            <button type="submit" class="btn btn-primary">Lưu tin</button>
        </form>
    </div>

    <div class="admin-card">
        <h2 class="admin-card-title">Danh sách tin mới nhất</h2>

        <table class="admin-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Tiêu đề</th>
                <th>Loại</th>
                <th>Trạng thái</th>
                <th>Danh mục</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$posts): ?>
                <tr><td colspan="6" class="empty">Chưa có tin nào.</td></tr>
            <?php else: ?>
                <?php foreach ($posts as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><?= htmlspecialchars($p['title']) ?></td>
                        <td><?= htmlspecialchars($p['type']) ?></td>
                        <td><?= htmlspecialchars($p['status']) ?></td>
                        <td><?= htmlspecialchars($p['category_name'] ?? '') ?></td>
                        <td>
                            <a class="admin-link" href="posts.php?edit=<?= $p['id'] ?>">Sửa</a> |
                            <a class="admin-link text-red"
                               href="posts.php?delete=<?= $p['id'] ?>"
                               onclick="return confirm('Xóa tin này?');">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>
