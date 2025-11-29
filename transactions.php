<?php
// admin/transactions.php
require_once __DIR__ . '/_init.php';

$adminPage = 'transactions';
$pageTitle = 'Lịch sử giao dịch GOLD';

$stm = $pdo->query("SELECT t.*, u.full_name, u.email
                    FROM transactions t
                    JOIN users u ON u.id = t.user_id
                    ORDER BY t.id DESC
                    LIMIT 300");
$transactions = $stm->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/header.php';
?>

<h1 class="admin-page-title">Lịch sử giao dịch GOLD</h1>

<div class="admin-card">
    <h2 class="admin-card-title">Danh sách giao dịch gần nhất</h2>

    <table class="admin-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Thành viên</th>
            <th>Email</th>
            <th>Loại</th>
            <th>GOLD</th>
            <th>Ghi chú</th>
            <th>Thời gian</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$transactions): ?>
            <tr><td colspan="7" class="empty">Chưa có giao dịch nào.</td></tr>
        <?php else: ?>
            <?php foreach ($transactions as $t): ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td><?= htmlspecialchars($t['full_name']) ?></td>
                    <td><?= htmlspecialchars($t['email']) ?></td>
                    <td><?= htmlspecialchars($t['type']) ?></td>
                    <td><?= (int)$t['gold'] ?></td>
                    <td><?= htmlspecialchars($t['note'] ?? '') ?></td>
                    <td><?= htmlspecialchars($t['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/footer.php'; ?>
