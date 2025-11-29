<?php
// admin/contacts.php
require_once __DIR__ . '/_init.php';

$adminPage = 'contacts';
$pageTitle = 'Quản lý liên hệ';

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    if ($id > 0) {
        $pdo->prepare("UPDATE contacts SET is_processed = 1 - is_processed WHERE id=?")->execute([$id]);
    }
    header('Location: contacts.php');
    exit;
}

$rows = $pdo->query("SELECT * FROM contacts ORDER BY id DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/header.php';
?>

<h1 class="admin-page-title">Quản lý liên hệ</h1>

<div class="admin-card">
    <h2 class="admin-card-title">Danh sách liên hệ</h2>

    <table class="admin-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Họ tên</th>
            <th>Điện thoại</th>
            <th>Email</th>
            <th>Nội dung</th>
            <th>Trạng thái</th>
            <th>Thời gian</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
            <tr><td colspan="8" class="empty">Chưa có liên hệ nào.</td></tr>
        <?php else: ?>
            <?php foreach ($rows as $c): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['full_name']) ?></td>
                    <td><?= htmlspecialchars($c['phone']) ?></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <td><?= nl2br(htmlspecialchars($c['message'])) ?></td>
                    <td>
                        <?php if (!empty($c['is_processed'])): ?>
                            <span class="admin-badge bg-green">Đã xử lý</span>
                        <?php else: ?>
                            <span class="admin-badge bg-red">Chưa xử lý</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($c['created_at']) ?></td>
                    <td>
                        <a class="admin-link"
                           href="contacts.php?toggle=<?= $c['id'] ?>">Đánh dấu</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/footer.php'; ?>
