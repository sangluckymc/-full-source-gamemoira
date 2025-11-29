<?php
// admin/gold_topups.php
require_once __DIR__ . '/_init.php';

$adminPage = 'gold_topups';
$pageTitle = 'Yêu cầu nạp GOLD';

// Lọc theo trạng thái
$status = $_GET['status'] ?? 'pending';
$allowedStatus = ['pending', 'approved', 'rejected', 'all'];
if (!in_array($status, $allowedStatus, true)) {
    $status = 'pending';
}

$errors = [];
$success = '';

// Xử lý duyệt / từ chối
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $id      = (int)$_POST['id'];
    $action  = $_POST['action'];
    $adminNote = trim($_POST['admin_note'] ?? '');

    try {
        // Lấy bản ghi
        $stmt = $pdo->prepare("SELECT * FROM gold_topups WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $topup = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$topup) {
            $errors[] = 'Không tìm thấy yêu cầu nạp GOLD.';
        } elseif ($topup['status'] !== 'pending') {
            $errors[] = 'Yêu cầu này đã được xử lý trước đó.';
        } else {
            if ($action === 'approve') {
                $gold = (int)$topup['expected_gold'];

                if ($gold <= 0) {
                    throw new Exception('Số GOLD dự kiến không hợp lệ.');
                }

                // Cộng GOLD cho user bằng helper chung
                $newGold = changeUserGold(
                    $pdo,
                    (int)$topup['user_id'],
                    $gold,
                    'nap_gold',
                    'Nạp GOLD banking #' . $topup['id'] . ' - ' . ($topup['transfer_content'] ?? '')
                );

                // Cập nhật trạng thái
                $stmtU = $pdo->prepare("
                    UPDATE gold_topups
                    SET status = 'approved',
                        admin_note = :note,
                        processed_at = NOW()
                    WHERE id = :id
                ");
                $stmtU->execute([
                    ':note' => $adminNote,
                    ':id'   => $id,
                ]);

                $success = 'Đã duyệt và cộng GOLD cho tài khoản. Số dư mới: ' . number_format($newGold, 0, ',', '.') . ' GOLD';
            } elseif ($action === 'reject') {
                $stmtU = $pdo->prepare("
                    UPDATE gold_topups
                    SET status = 'rejected',
                        admin_note = :note,
                        processed_at = NOW()
                    WHERE id = :id
                ");
                $stmtU->execute([
                    ':note' => $adminNote,
                    ':id'   => $id,
                ]);

                $success = 'Đã từ chối yêu cầu nạp GOLD.';
            }
        }
    } catch (Exception $e) {
        $errors[] = 'Lỗi xử lý: ' . $e->getMessage();
    }
}

// Lấy danh sách yêu cầu
$whereSql = '';
$params   = [];

if ($status !== 'all') {
    $whereSql = 'WHERE t.status = :status';
    $params[':status'] = $status;
}

$sql = "
    SELECT t.*, u.email, u.full_name
    FROM gold_topups t
    JOIN users u ON u.id = t.user_id
    $whereSql
    ORDER BY t.id DESC
    LIMIT 200
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/header.php';
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Yêu cầu nạp GOLD</h1>
        <p class="admin-page-desc">
            Danh sách các yêu cầu người dùng bấm &quot;Tôi đã chuyển khoản&quot; trên trang Nạp GOLD.
            Admin kiểm tra sao kê và duyệt / từ chối tại đây.
        </p>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="admin-alert admin-alert-danger">
        <?php foreach ($errors as $err): ?>
            <div>- <?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="admin-alert admin-alert-success">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<form method="get" class="gm-filter-row" style="margin-bottom: 12px;">
    <div class="gm-filter-field">
        <label class="gm-filter-label">Trạng thái</label>
        <select name="status" class="gm-select" onchange="this.form.submit()">
            <option value="pending"  <?= $status === 'pending'  ? 'selected' : '' ?>>Chờ duyệt</option>
            <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
            <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Đã từ chối</option>
            <option value="all"      <?= $status === 'all'      ? 'selected' : '' ?>>Tất cả</option>
        </select>
    </div>
</form>

<div class="gm-table-wrapper">
    <table class="gm-table gm-table-transactions">
        <thead>
        <tr>
            <th>ID</th>
            <th>Tài khoản</th>
            <th>Email</th>
            <th>Số tiền (VND)</th>
            <th>GOLD dự kiến</th>
            <th>Nội dung CK</th>
            <th>Trạng thái</th>
            <th>Thời gian</th>
            <th>Thao tác</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr>
                <td colspan="9" style="text-align:center;padding:14px 0;">
                    Không có yêu cầu nào với bộ lọc hiện tại.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($rows as $r): ?>
                <?php
                $badgeClass = 'gm-badge-other';
                if ($r['status'] === 'pending') {
                    $badgeClass = 'gm-badge-out';
                } elseif ($r['status'] === 'approved') {
                    $badgeClass = 'gm-badge-in';
                }
                ?>
                <tr>
                    <td class="gm-text-mono">#<?= (int)$r['id'] ?></td>
                    <td><?= htmlspecialchars($r['full_name']) ?></td>
                    <td class="gm-text-mono"><?= htmlspecialchars($r['email']) ?></td>
                    <td><?= number_format((int)$r['amount_vnd'], 0, ',', '.') ?></td>
                    <td><?= number_format((int)$r['expected_gold'], 0, ',', '.') ?> GOLD</td>
                    <td><?= htmlspecialchars($r['transfer_content']) ?></td>
                    <td>
                        <span class="gm-badge <?= $badgeClass ?>">
                            <?= htmlspecialchars($r['status']) ?>
                        </span>
                    </td>
                    <td class="gm-text-mono">
                        <?= htmlspecialchars($r['created_at']) ?>
                        <?php if (!empty($r['processed_at'])): ?>
                            <br><small>XL: <?= htmlspecialchars($r['processed_at']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($r['status'] === 'pending'): ?>
                            <form method="post" style="display:flex;flex-direction:column;gap:4px;min-width:160px;">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <textarea name="admin_note"
                                          rows="2"
                                          class="gm-input"
                                          placeholder="Ghi chú (nếu có)"></textarea>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                    <button type="submit"
                                            name="action"
                                            value="approve"
                                            class="gm-btn gm-btn-primary"
                                            onclick="return confirm('Xác nhận duyệt và cộng GOLD cho tài khoản này?');">
                                        Duyệt
                                    </button>
                                    <button type="submit"
                                            name="action"
                                            value="reject"
                                            class="gm-btn gm-btn-outline"
                                            onclick="return confirm('Từ chối yêu cầu nạp GOLD này?');">
                                        Từ chối
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <?= htmlspecialchars($r['admin_note'] ?? '') ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/footer.php'; ?>
