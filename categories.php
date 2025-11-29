<?php
// admin/categories.php
require_once __DIR__ . '/_init.php';

$adminPage = 'categories';
$pageTitle = 'Quản lý danh mục';

$errors = [];
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = null;

if ($editId > 0) {
    $stm = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stm->execute([$editId]);
    $editing = $stm->fetch(PDO::FETCH_ASSOC);
    if (!$editing) {
        $editId = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $slug        = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order  = (int)($_POST['sort_order'] ?? 0);

    if ($name === '') {
        $errors[] = 'Vui lòng nhập tên danh mục.';
    }
    if ($slug === '') {
        $errors[] = 'Vui lòng nhập slug.';
    }

    if (!$errors) {
        if ($editId > 0) {
            $stm = $pdo->prepare("UPDATE categories
                                  SET name = :name, slug = :slug, description = :description,
                                      sort_order = :sort_order
                                  WHERE id = :id");
            $stm->execute([
                ':name'        => $name,
                ':slug'        => $slug,
                ':description' => $description,
                ':sort_order'  => $sort_order,
                ':id'          => $editId,
            ]);
        } else {
            $stm = $pdo->prepare("INSERT INTO categories
                                  (name, slug, description, sort_order)
                                  VALUES (:name,:slug,:description,:sort_order)");
            $stm->execute([
                ':name'        => $name,
                ':slug'        => $slug,
                ':description' => $description,
                ':sort_order'  => $sort_order,
            ]);
        }
        header('Location: categories.php');
        exit;
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    }
    header('Location: categories.php');
    exit;
}

$rows = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/header.php';
?>

<h1 class="admin-page-title">Quản lý danh mục</h1>

<div class="admin-grid-2">

    <div class="admin-card">
        <h2 class="admin-card-title"><?= $editId ? 'Sửa danh mục' : 'Thêm danh mục' ?></h2>

        <?php if ($errors): ?>
            <div class="admin-alert admin-alert-danger">
                <?php foreach ($errors as $err): ?>
                    <div>- <?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="admin-form-row">
                <label>Tên danh mục:</label>
                <input type="text" name="name" class="input"
                       value="<?= htmlspecialchars($editing['name'] ?? '') ?>" required>
            </div>

            <div class="admin-form-row">
                <label>Slug:</label>
                <input type="text" name="slug" class="input"
                       value="<?= htmlspecialchars($editing['slug'] ?? '') ?>" required>
            </div>

            <div class="admin-form-row">
                <label>Mô tả:</label>
                <textarea name="description" class="input" rows="3"><?= htmlspecialchars($editing['description'] ?? '') ?></textarea>
            </div>

            <div class="admin-form-row">
                <label>Thứ tự:</label>
                <input type="number" name="sort_order" class="input"
                       value="<?= htmlspecialchars($editing['sort_order'] ?? 0) ?>">
            </div>

            <button type="submit" class="btn btn-primary">Lưu danh mục</button>
        </form>
    </div>

    <div class="admin-card">
        <h2 class="admin-card-title">Danh sách danh mục</h2>

        <table class="admin-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Slug</th>
                <th>Thứ tự</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="5" class="empty">Chưa có danh mục nào.</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['slug']) ?></td>
                        <td><?= (int)$row['sort_order'] ?></td>
                        <td>
                            <a class="admin-link" href="categories.php?edit=<?= $row['id'] ?>">Sửa</a> |
                            <a class="admin-link text-red"
                               href="categories.php?delete=<?= $row['id'] ?>"
                               onclick="return confirm('Xóa danh mục này?');">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require __DIR__ . '/footer.php'; ?>
