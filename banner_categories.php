<?php
// admin/banner_categories.php
// Quản lý danh mục / chuyên mục cho Banner

require_once __DIR__ . '/_init.php';

$adminPage  = 'banner_categories';
$pageTitle  = 'Quản lý danh mục Banner';

$errors  = [];
$editId  = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = null;

// Thử load bản ghi đang sửa (nếu có)
if ($editId > 0) {
    try {
        $stm = $pdo->prepare("SELECT * FROM banner_categories WHERE id = ?");
        $stm->execute([$editId]);
        $editing = $stm->fetch(PDO::FETCH_ASSOC);
        if (!$editing) {
            $errors[] = 'Không tìm thấy danh mục cần sửa.';
        }
    } catch (Exception $e) {
        $errors[] = 'Lỗi DB (có thể chưa tạo bảng banner_categories).';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $slug        = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order  = (int)($_POST['sort_order'] ?? 0);
    $is_active   = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '') {
        $errors[] = 'Vui lòng nhập Tên danh mục.';
    }
    if ($slug === '') {
        // sinh slug đơn giản từ name
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/iu', '-', $name), '-'));
    }

    if (!$errors) {
        try {
            if (!empty($_POST['id'])) {
                // Cập nhật
                $id = (int)$_POST['id'];
                $stm = $pdo->prepare("
                    UPDATE banner_categories
                    SET name = :name,
                        slug = :slug,
                        description = :description,
                        sort_order = :sort_order,
                        is_active = :is_active
                    WHERE id = :id
                ");
                $stm->execute([
                    ':name'        => $name,
                    ':slug'        => $slug,
                    ':description' => $description,
                    ':sort_order'  => $sort_order,
                    ':is_active'   => $is_active,
                    ':id'          => $id,
                ]);
            } else {
                // Thêm mới
                $stm = $pdo->prepare("
                    INSERT INTO banner_categories (name, slug, description, sort_order, is_active)
                    VALUES (:name, :slug, :description, :sort_order, :is_active)
                ");
                $stm->execute([
                    ':name'        => $name,
                    ':slug'        => $slug,
                    ':description' => $description,
                    ':sort_order'  => $sort_order,
                    ':is_active'   => $is_active,
                ]);
            }
            header('Location: banner_categories.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Lỗi DB: ' . $e->getMessage();
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        try {
            $stm = $pdo->prepare("DELETE FROM banner_categories WHERE id = ?");
            $stm->execute([$id]);
        } catch (Exception $e) {
            $errors[] = 'Không thể xóa danh mục: ' . $e->getMessage();
        }
        header('Location: banner_categories.php');
        exit;
    }
}

try {
    $stm = $pdo->query("SELECT * FROM banner_categories ORDER BY sort_order ASC, id ASC");
    $items = $stm->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $items  = [];
    $errors[] = "Chưa có bảng banner_categories trong CSDL.\n\nBạn tạo nhanh bằng SQL mẫu:\n
CREATE TABLE `banner_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
}

include __DIR__ . '/header.php';
?>
<div class="gm-admin-page">
    <h1 class="gm-admin-title">Quản lý danh mục Banner</h1>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $e): ?>
                <div>- <?= e($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="gm-admin-two-col">
        <div class="gm-admin-col-left">
            <h2 class="gm-admin-subtitle"><?= $editing ? 'Sửa danh mục' : 'Thêm danh mục mới' ?></h2>

            <form method="post" action="">
                <?php if ($editing): ?>
                    <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Tên danh mục</label>
                    <input type="text" name="name" class="form-control"
                           value="<?= e($editing['name'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Slug (URL thân thiện)</label>
                    <input type="text" name="slug" class="form-control"
                           value="<?= e($editing['slug'] ?? '') ?>">
                    <div class="form-text">
                        Nếu bỏ trống hệ thống sẽ tự tạo từ tên.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3"><?= e($editing['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Thứ tự</label>
                    <input type="number" name="sort_order" class="form-control"
                           value="<?= e($editing['sort_order'] ?? 0) ?>">
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                           <?= !empty($editing) ? (!empty($editing['is_active']) ? 'checked' : '') : 'checked' ?>>
                    <label class="form-check-label" for="is_active">
                        Kích hoạt / hiển thị
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Lưu danh mục</button>
            </form>
        </div>

        <div class="gm-admin-col-right">
            <h2 class="gm-admin-subtitle">Danh sách danh mục</h2>

            <div class="table-responsive">
                <table class="table table-dark table-striped align-middle">
                    <thead>
                        <tr>
                            <th width="60">ID</th>
                            <th>Tên</th>
                            <th>Slug</th>
                            <th width="80">Thứ tự</th>
                            <th width="100">Trạng thái</th>
                            <th width="100">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($items): ?>
                        <?php foreach ($items as $row): ?>
                            <tr>
                                <td><?= (int)$row['id'] ?></td>
                                <td><?= e($row['name']) ?></td>
                                <td><?= e($row['slug']) ?></td>
                                <td><?= (int)$row['sort_order'] ?></td>
                                <td>
                                    <?php if (!empty($row['is_active'])): ?>
                                        <span class="badge bg-success">Hiển thị</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Ẩn</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="banner_categories.php?edit=<?= (int)$row['id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
                                    <a href="banner_categories.php?delete=<?= (int)$row['id'] ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Xóa danh mục này?');">Xóa</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">Chưa có danh mục.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
