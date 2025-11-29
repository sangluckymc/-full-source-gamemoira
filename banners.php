<?php
// admin/banners.php
require_once __DIR__ . '/_init.php';

$adminPage = 'banners';
$pageTitle = 'Quản lý banner';

$errors = [];
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = null;

if ($editId > 0) {
    $stm = $pdo->prepare("SELECT * FROM banners WHERE id = ?");
    $stm->execute([$editId]);
    $editing = $stm->fetch(PDO::FETCH_ASSOC);
    if (!$editing) {
        $editId = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title'] ?? '');
    $image_path = trim($_POST['image_path'] ?? '');
    $link_url   = trim($_POST['link_url'] ?? '');
    $position   = $_POST['position'] ?? 'center';
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $start_date = $_POST['start_date'] ?? null;
    $end_date   = $_POST['end_date'] ?? null;
    $is_active  = !empty($_POST['is_active']) ? 1 : 0;

    if ($image_path === '') {
        $errors[] = 'Vui lòng nhập đường dẫn ảnh banner.';
    }

    if (!$errors) {
        if ($editId > 0) {
            $stm = $pdo->prepare("UPDATE banners
                                  SET title=:title,image_path=:image_path,link_url=:link_url,
                                      position=:position,sort_order=:sort_order,
                                      start_date=:start_date,end_date=:end_date,
                                      is_active=:is_active
                                  WHERE id=:id");
            $stm->execute([
                ':title'      => $title,
                ':image_path' => $image_path,
                ':link_url'   => $link_url,
                ':position'   => $position,
                ':sort_order' => $sort_order,
                ':start_date' => $start_date ?: null,
                ':end_date'   => $end_date ?: null,
                ':is_active'  => $is_active,
                ':id'         => $editId,
            ]);
        } else {
            $stm = $pdo->prepare("INSERT INTO banners
                (title,image_path,link_url,position,sort_order,start_date,end_date,is_active)
                VALUES (:title,:image_path,:link_url,:position,:sort_order,:start_date,:end_date,:is_active)");
            $stm->execute([
                ':title'      => $title,
                ':image_path' => $image_path,
                ':link_url'   => $link_url,
                ':position'   => $position,
                ':sort_order' => $sort_order,
                ':start_date' => $start_date ?: null,
                ':end_date'   => $end_date ?: null,
                ':is_active'  => $is_active,
            ]);
        }
        header('Location: banners.php');
        exit;
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $pdo->prepare("DELETE FROM banners WHERE id = ?")->execute([$id]);
    }
    header('Location: banners.php');
    exit;
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    if ($id > 0) {
        $pdo->prepare("UPDATE banners SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
    }
    header('Location: banners.php');
    exit;
}

$banners = $pdo->query("SELECT * FROM banners ORDER BY position ASC, sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/header.php';
?>

<h1 class="admin-page-title">Quản lý Banner</h1>

<div class="admin-grid-2">
    <div class="admin-card">
        <h2 class="admin-card-title"><?= $editId ? 'Sửa banner' : 'Thêm banner mới' ?></h2>

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
                       value="<?= htmlspecialchars($editing['title'] ?? '') ?>">
            </div>
            <div class="admin-form-row">
                <label>Ảnh banner (URL):</label>
                <input type="text" name="image_path" class="input"
                       value="<?= htmlspecialchars($editing['image_path'] ?? '') ?>" required>
            </div>
            <div class="admin-form-row">
                <label>Link khi click:</label>
                <input type="text" name="link_url" class="input"
                       value="<?= htmlspecialchars($editing['link_url'] ?? '') ?>">
            </div>
            <div class="admin-form-row">
                <label>Vị trí:</label>
                <select name="position" class="input">
                    <?php
                    $pos = ['left' => 'Trái', 'center' => 'Giữa', 'right' => 'Phải'];
                    $cur = $editing['position'] ?? 'center';
                    foreach ($pos as $k => $v):
                    ?>
                        <option value="<?= $k ?>" <?= $k === $cur ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-row">
                <label>Thứ tự:</label>
                <input type="number" name="sort_order" class="input"
                       value="<?= htmlspecialchars($editing['sort_order'] ?? 0) ?>">
            </div>
            <div class="admin-form-row">
                <label>Thời gian chạy:</label>
                <div style="display:flex;gap:8px;flex:1;">
                    <input type="date" name="start_date" class="input"
                           value="<?= htmlspecialchars($editing['start_date'] ?? '') ?>">
                    <input type="date" name="end_date" class="input"
                           value="<?= htmlspecialchars($editing['end_date'] ?? '') ?>">
                </div>
            </div>
            <div class="admin-form-row">
                <label>Trạng thái:</label>
                <label>
                    <input type="checkbox" name="is_active"
                           <?= !empty($editing['is_active']) ? 'checked' : '' ?>>
                    Hiển thị
                </label>
            </div>

            <button type="submit" class="btn btn-primary">Lưu banner</button>
        </form>
    </div>

    <div class="admin-card">
        <h2 class="admin-card-title">Danh sách banner</h2>

        <table class="admin-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Tiêu đề</th>
                <th>Ảnh</th>
                <th>Vị trí</th>
                <th>Thứ tự</th>
                <th>Chạy từ / đến</th>
                <th>Trạng thái</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$banners): ?>
                <tr><td colspan="8" class="empty">Chưa có banner nào.</td></tr>
            <?php else: ?>
                <?php foreach ($banners as $b): ?>
                    <tr>
                        <td><?= $b['id'] ?></td>
                        <td><?= htmlspecialchars($b['title']) ?></td>
                        <td><?= htmlspecialchars($b['image_path']) ?></td>
                        <td><?= htmlspecialchars($b['position']) ?></td>
                        <td><?= (int)$b['sort_order'] ?></td>
                        <td>
                            <?= htmlspecialchars($b['start_date'] ?? '') ?> -
                            <?= htmlspecialchars($b['end_date'] ?? '') ?>
                        </td>
                        <td>
                            <?php if ($b['is_active']): ?>
                                <span class="admin-badge bg-green">Hiển thị</span>
                            <?php else: ?>
                                <span class="admin-badge bg-red">Tạm tắt</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="admin-link" href="banners.php?edit=<?= $b['id'] ?>">Sửa</a> |
                            <a class="admin-link" href="banners.php?toggle=<?= $b['id'] ?>">Ẩn/Hiện</a> |
                            <a class="admin-link text-red"
                               href="banners.php?delete=<?= $b['id'] ?>"
                               onclick="return confirm('Xóa banner này?');">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>
