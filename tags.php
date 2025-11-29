<?php
// admin/tags.php
require_once __DIR__ . '/_init.php';

$adminPage = 'tags';
$pageTitle = 'Quản lý tag bài viết';

$errors = [];
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = null;

if ($editId > 0) {
    $stm = $pdo->prepare("SELECT * FROM tags WHERE id = ?");
    $stm->execute([$editId]);
    $editing = $stm->fetch(PDO::FETCH_ASSOC);
    if (!$editing) {
        $editId = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');

    if ($name === '') {
        $errors[] = 'Vui lòng nhập tên tag.';
    }
    if ($slug === '') {
        $errors[] = 'Vui lòng nhập slug.';
    }

    if (!$errors) {
        if ($editId > 0) {
            $stm = $pdo->prepare("UPDATE tags SET name = :name, slug = :slug WHERE id = :id");
            $stm->execute([
                ':name' => $name,
                ':slug' => $slug,
                ':id'   => $editId,
            ]);
        } else {
            $stm = $pdo->prepare("INSERT INTO tags (name, slug) VALUES (:name,:slug)");
            $stm->execute([
                ':name' => $name,
                ':slug' => $slug,
            ]);
        }
        header('Location: tags.php');
        exit;
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $pdo->prepare("DELETE FROM tags WHERE id = ?")->execute([$id]);
    }
    header('Location: tags.php');
    exit;
}

$rows = $pdo->query("SELECT * FROM tags ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/header.php';
?>

<h1 class="admin-page-title">Quản lý Tag</h1>

<div class="admin-grid-2">
    <div class="admin-card">
        <h2 class="admin-card-title"><?= $editId ? 'Sửa tag' : 'Thêm tag mới' ?></h2>

        <?php if ($errors): ?>
            <div class="admin-alert admin-alert-danger">
                <?php foreach ($errors as $err): ?>
                    <div>- <?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="admin-form-row">
                <label>Tên tag:</label>
                <input type="text" name="name" class="input"
                       value="<?= htmlspecialchars($editing['name'] ?? '') ?>" required>
            </div>
            <div class="admin-form-row">
                <label>Slug:</label>
                <input type="text" name="slug" class="input"
                       value="<?= htmlspecialchars($editing['slug'] ?? '') ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Lưu tag</button>
        </form>
    </div>

    <div class="admin-card">
        <h2 class="admin-card-title">Danh sách tag</h2>

        <table class="admin-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Slug</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="4" class="empty">Chưa có tag nào.</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['slug']) ?></td>
                        <td>
                            <a class="admin-link" href="tags.php?edit=<?= $row['id'] ?>">Sửa</a> |
                            <a class="admin-link text-red"
                               href="tags.php?delete=<?= $row['id'] ?>"
                               onclick="return confirm('Xóa tag này?');">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>
