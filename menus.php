<?php
// admin/menus.php
require_once __DIR__ . '/_init.php';

$adminPage = 'menus';
$pageTitle = 'Quản lý menu';

$errors = [];
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

// Lấy dữ liệu đang sửa (nếu có)
$editing = null;
if ($editId > 0) {
    $stm = $pdo->prepare("SELECT * FROM menus WHERE id = ?");
    $stm->execute([$editId]);
    $editing = $stm->fetch(PDO::FETCH_ASSOC);
    if (!$editing) {
        $editId = 0;
    }
}

// Xử lý submit form thêm / sửa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title'] ?? '');
    $url        = trim($_POST['url'] ?? '');
    $position   = $_POST['position'] ?? 'main';
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active  = !empty($_POST['is_active']) ? 1 : 0;
    $is_highlight = !empty($_POST['is_highlight']) ? 1 : 0;

    if ($title === '') {
        $errors[] = 'Vui lòng nhập tên menu.';
    }
    if ($url === '') {
        $errors[] = 'Vui lòng nhập đường dẫn URL.';
    }

    if (!$errors) {
        if ($editId > 0) {
            $stm = $pdo->prepare("UPDATE menus
                                  SET title = :title, url = :url, position = :position,
                                      sort_order = :sort_order, is_active = :is_active,
                                      is_highlight = :is_highlight
                                  WHERE id = :id");
            $stm->execute([
                ':title'       => $title,
                ':url'         => $url,
                ':position'    => $position,
                ':sort_order'  => $sort_order,
                ':is_active'   => $is_active,
                ':is_highlight'=> $is_highlight,
                ':id'          => $editId,
            ]);
        } else {
            $stm = $pdo->prepare("INSERT INTO menus
                                  (title, url, position, sort_order, is_active, is_highlight)
                                  VALUES (:title,:url,:position,:sort_order,:is_active,:is_highlight)");
            $stm->execute([
                ':title'       => $title,
                ':url'         => $url,
                ':position'    => $position,
                ':sort_order'  => $sort_order,
                ':is_active'   => $is_active,
                ':is_highlight'=> $is_highlight,
            ]);
        }
        header('Location: menus.php');
        exit;
    }
}

// Xóa menu
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $pdo->prepare("DELETE FROM menus WHERE id = ?")->execute([$id]);
    }
    header('Location: menus.php');
    exit;
}

// Bật/tắt menu
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    if ($id > 0) {
        $pdo->prepare("UPDATE menus SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
    }
    header('Location: menus.php');
    exit;
}

$menus = $pdo->query("SELECT * FROM menus ORDER BY position ASC, sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/header.php';
?>

<h1 class="admin-page-title">Quản lý menu</h1>

<div class="admin-grid-2">

    <div class="admin-card">
        <h2 class="admin-card-title"><?= $editId ? 'Sửa menu' : 'Thêm menu mới' ?></h2>

        <?php if ($errors): ?>
            <div class="admin-alert admin-alert-danger">
                <?php foreach ($errors as $err): ?>
                    <div>- <?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="admin-form-row">
                <label>Tên hiển thị:</label>
                <input type="text" name="title" class="input"
                       value="<?= htmlspecialchars($editing['title'] ?? '') ?>" required>
            </div>

            <div class="admin-form-row">
                <label>Đường dẫn URL:</label>
                <input type="text" name="url" class="input"
                       value="<?= htmlspecialchars($editing['url'] ?? '') ?>" required>
            </div>

            <div class="admin-form-row">
                <label>Vị trí:</label>
                <select name="position" class="input">
                    <?php
                    $positions = [
                        'main'   => 'Menu chính (header)',
                        'footer' => 'Menu chân trang (footer)',
                    ];
                    $cur = $editing['position'] ?? 'main';
                    foreach ($positions as $key => $label):
                    ?>
                        <option value="<?= $key ?>" <?= $key === $cur ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-form-row">
                <label>Thứ tự:</label>
                <input type="number" name="sort_order" class="input"
                       value="<?= htmlspecialchars($editing['sort_order'] ?? 0) ?>">
            </div>

            <div class="admin-form-row">
                <label>Hiển thị / Nổi bật:</label>
                <div>
                    <label style="margin-right:12px;">
                        <input type="checkbox" name="is_active"
                               <?= !empty($editing['is_active']) ? 'checked' : '' ?>>
                        Hiển thị
                    </label>
                    <label>
                        <input type="checkbox" name="is_highlight"
                               <?= !empty($editing['is_highlight']) ? 'checked' : '' ?>>
                        Làm nổi bật
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                Lưu menu
            </button>
        </form>
    </div>

    <div class="admin-card">
        <h2 class="admin-card-title">Danh sách menu</h2>

        <table class="admin-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>URL</th>
                <th>Vị trí</th>
                <th>Thứ tự</th>
                <th>Trạng thái</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$menus): ?>
                <tr><td colspan="7" class="empty">Chưa có menu nào.</td></tr>
            <?php else: ?>
                <?php foreach ($menus as $m): ?>
                    <tr>
                        <td><?= $m['id'] ?></td>
                        <td><?= htmlspecialchars($m['title']) ?></td>
                        <td><?= htmlspecialchars($m['url']) ?></td>
                        <td><?= htmlspecialchars($m['position']) ?></td>
                        <td><?= (int)$m['sort_order'] ?></td>
                        <td>
                            <?php if ($m['is_active']): ?>
                                <span class="admin-badge bg-green">Hiển thị</span>
                            <?php else: ?>
                                <span class="admin-badge bg-red">Ẩn</span>
                            <?php endif; ?>
                            <?php if (!empty($m['is_highlight'])): ?>
                                <span class="admin-badge bg-green">HOT</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="admin-link" href="menus.php?edit=<?= $m['id'] ?>">Sửa</a> |
                            <a class="admin-link" href="menus.php?toggle=<?= $m['id'] ?>">Ẩn/Hiện</a> |
                            <a class="admin-link text-red"
                               href="menus.php?delete=<?= $m['id'] ?>"
                               onclick="return confirm('Xóa menu này?');">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require __DIR__ . '/footer.php'; ?>
