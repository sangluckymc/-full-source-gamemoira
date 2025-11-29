<?php
// admin/users.php
require_once __DIR__ . '/_init.php';

$adminPage = 'users';
$pageTitle = 'Quản lý tài khoản';

$errors = [];
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = null;

if ($editId > 0) {
    $stm = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stm->execute([$editId]);
    $editing = $stm->fetch(PDO::FETCH_ASSOC);
    if (!$editing) {
        $editId = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $gold      = (int)($_POST['gold'] ?? 0);
    $role      = $_POST['role'] ?? 'user';
    $is_active = !empty($_POST['is_active']) ? 1 : 0;
    $password  = $_POST['password'] ?? '';

    if ($full_name === '') {
        $errors[] = 'Vui lòng nhập họ tên.';
    }
    if ($email === '') {
        $errors[] = 'Vui lòng nhập email.';
    }

    if (!$errors) {
        if ($editId > 0) {
            // cập nhật
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stm = $pdo->prepare("UPDATE users
                                      SET full_name=:full_name,email=:email,gold=:gold,
                                          role=:role,is_active=:is_active,password_hash=:password
                                      WHERE id=:id");
                $stm->execute([
                    ':full_name' => $full_name,
                    ':email'     => $email,
                    ':gold'      => $gold,
                    ':role'      => $role,
                    ':is_active' => $is_active,
                    ':password'  => $hash,
                    ':id'        => $editId,
                ]);
            } else {
                $stm = $pdo->prepare("UPDATE users
                                      SET full_name=:full_name,email=:email,gold=:gold,
                                          role=:role,is_active=:is_active
                                      WHERE id=:id");
                $stm->execute([
                    ':full_name' => $full_name,
                    ':email'     => $email,
                    ':gold'      => $gold,
                    ':role'      => $role,
                    ':is_active' => $is_active,
                    ':id'        => $editId,
                ]);
            }
        } else {
            // thêm mới
            if ($password === '') {
                $errors[] = 'Vui lòng nhập mật khẩu cho tài khoản mới.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stm = $pdo->prepare("INSERT INTO users
                    (full_name,email,gold,password_hash,role,is_active,created_at)
                    VALUES (:full_name,:email,:gold,:password,:role,:is_active,NOW())");
                $stm->execute([
                    ':full_name' => $full_name,
                    ':email'     => $email,
                    ':gold'      => $gold,
                    ':password'  => $hash,
                    ':role'      => $role,
                    ':is_active' => $is_active,
                ]);
            }
        }

        if (!$errors) {
            header('Location: users.php');
            exit;
        }
    }
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    if ($id > 0) {
        $pdo->prepare("UPDATE users SET is_active = 1 - is_active WHERE id=?")->execute([$id]);
    }
    header('Location: users.php');
    exit;
}

$users = $pdo->query("SELECT * FROM users ORDER BY id DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/header.php';
?>

<h1 class="admin-page-title">Quản lý tài khoản</h1>

<div class="admin-grid-2">
    <div class="admin-card">
        <h2 class="admin-card-title"><?= $editId ? 'Sửa tài khoản' : 'Thêm tài khoản mới' ?></h2>

        <?php if ($errors): ?>
            <div class="admin-alert admin-alert-danger">
                <?php foreach ($errors as $err): ?>
                    <div>- <?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="admin-form-row">
                <label>Họ tên:</label>
                <input type="text" name="full_name" class="input"
                       value="<?= htmlspecialchars($editing['full_name'] ?? '') ?>" required>
            </div>
            <div class="admin-form-row">
                <label>Email:</label>
                <input type="email" name="email" class="input"
                       value="<?= htmlspecialchars($editing['email'] ?? '') ?>" required>
            </div>
            <div class="admin-form-row">
                <label>GOLD:</label>
                <input type="number" name="gold" class="input"
                       value="<?= htmlspecialchars($editing['gold'] ?? 0) ?>">
            </div>
            <div class="admin-form-row">
                <label>Vai trò:</label>
                <select name="role" class="input">
                    <?php
                    $roles = [
                        'user'       => 'User',
                        'editor'     => 'Editor',
                        'admin'      => 'Admin',
                        'superadmin' => 'Super Admin',
                    ];
                    $curRole = $editing['role'] ?? 'user';
                    foreach ($roles as $key => $label):
                    ?>
                        <option value="<?= $key ?>" <?= $key === $curRole ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-row">
                <label>Trạng thái:</label>
                <label>
                    <input type="checkbox" name="is_active"
                           <?= !empty($editing['is_active']) ? 'checked' : '' ?>>
                    Hoạt động
                </label>
            </div>
            <div class="admin-form-row">
                <label>Mật khẩu <?= $editId ? '(để trống nếu không đổi)' : '' ?>:</label>
                <input type="password" name="password" class="input">
            </div>

            <button type="submit" class="btn btn-primary">Lưu tài khoản</button>
        </form>
    </div>

    <div class="admin-card">
        <h2 class="admin-card-title">Danh sách tài khoản</h2>

        <table class="admin-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>GOLD</th>
                <th>Vai trò</th>
                <th>Trạng thái</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$users): ?>
                <tr><td colspan="7" class="empty">Chưa có tài khoản nào.</td></tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['full_name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= (int)$u['gold'] ?></td>
                        <td><?= htmlspecialchars($u['role']) ?></td>
                        <td>
                            <?php if (!empty($u['is_active'])): ?>
                                <span class="admin-badge bg-green">Hoạt động</span>
                            <?php else: ?>
                                <span class="admin-badge bg-red">Khóa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="admin-link" href="users.php?edit=<?= $u['id'] ?>">Sửa</a> |
                            <a class="admin-link"
                               href="users.php?toggle=<?= $u['id'] ?>">Bật/Tắt</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>
