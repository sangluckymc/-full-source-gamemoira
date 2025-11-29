<?php
// admin/pages.php
require_once __DIR__ . '/_init.php';

$adminPage = 'pages';
$pageTitle = 'Quản lý trang tĩnh';

$errors = [];
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = null;

if ($editId > 0) {
    $stm = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
    $stm->execute([$editId]);
    $editing = $stm->fetch(PDO::FETCH_ASSOC);
    if (!$editing) {
        $editId = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug             = trim($_POST['slug'] ?? '');
    $title            = trim($_POST['title'] ?? '');
    $content          = $_POST['content'] ?? '';
    $meta_title       = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');

    if ($slug === '') {
        $errors[] = 'Vui lòng nhập slug trang.';
    }
    if ($title === '') {
        $errors[] = 'Vui lòng nhập tiêu đề trang.';
    }

    if (!$errors) {
        if ($editId > 0) {
            $stm = $pdo->prepare("UPDATE pages
                                  SET slug=:slug,title=:title,content=:content,
                                      meta_title=:meta_title,meta_description=:meta_description
                                  WHERE id=:id");
            $stm->execute([
                ':slug'             => $slug,
                ':title'            => $title,
                ':content'          => $content,
                ':meta_title'       => $meta_title,
                ':meta_description' => $meta_description,
                ':id'               => $editId,
            ]);
        } else {
            $stm = $pdo->prepare("INSERT INTO pages
                                  (slug,title,content,meta_title,meta_description)
                                  VALUES(:slug,:title,:content,:meta_title,:meta_description)");
            $stm->execute([
                ':slug'             => $slug,
                ':title'            => $title,
                ':content'          => $content,
                ':meta_title'       => $meta_title,
                ':meta_description' => $meta_description,
            ]);
        }
        header('Location: pages.php');
        exit;
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $pdo->prepare("DELETE FROM pages WHERE id = ?")->execute([$id]);
    }
    header('Location: pages.php');
    exit;
}

$rows = $pdo->query("SELECT * FROM pages ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/header.php';
?>

<h1 class="admin-page-title">Quản lý trang tĩnh</h1>

<div class="admin-grid-2">
    <div class="admin-card">
        <h2 class="admin-card-title"><?= $editId ? 'Sửa trang' : 'Thêm trang mới' ?></h2>

        <?php if ($errors): ?>
            <div class="admin-alert admin-alert-danger">
                <?php foreach ($errors as $err): ?>
                    <div>- <?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="admin-form-row">
                <label>Slug:</label>
                <input type="text" name="slug" class="input"
                       value="<?= htmlspecialchars($editing['slug'] ?? '') ?>" required>
            </div>
            <div class="admin-form-row">
                <label>Tiêu đề:</label>
                <input type="text" name="title" class="input"
                       value="<?= htmlspecialchars($editing['title'] ?? '') ?>" required>
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
            <div class="admin-form-row">
                <label>Nội dung:</label>
                <textarea name="content" class="input" rows="6"><?= htmlspecialchars($editing['content'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Lưu trang</button>
        </form>
    </div>

    <div class="admin-card">
        <h2 class="admin-card-title">Danh sách trang</h2>

        <table class="admin-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Slug</th>
                <th>Tiêu đề</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="4" class="empty">Chưa có trang nào.</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['slug']) ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td>
                            <a class="admin-link" href="pages.php?edit=<?= $row['id'] ?>">Sửa</a> |
                            <a class="admin-link text-red"
                               href="pages.php?delete=<?= $row['id'] ?>"
                               onclick="return confirm('Xóa trang này?');">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>
