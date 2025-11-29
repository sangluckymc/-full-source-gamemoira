<?php
// admin/documents.php
require_once __DIR__ . '/_init.php';

$adminPage = 'documents';
$pageTitle = 'Tài liệu / hướng dẫn';

$errors = [];
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = null;

if ($editId > 0) {
    $stm = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
    $stm->execute([$editId]);
    $editing = $stm->fetch(PDO::FETCH_ASSOC);
    if (!$editing) {
        $editId = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $file_url    = trim($_POST['file_url'] ?? '');
    $video_url   = trim($_POST['video_url'] ?? '');
    $doc_type    = $_POST['doc_type'] ?? 'beginner';

    if ($title === '') {
        $errors[] = 'Vui lòng nhập tiêu đề tài liệu.';
    }

    if (!$errors) {
        if ($editId > 0) {
            $stm = $pdo->prepare("UPDATE documents
                                  SET title=:title,description=:description,file_url=:file_url,
                                      video_url=:video_url,doc_type=:doc_type
                                  WHERE id=:id");
            $stm->execute([
                ':title'       => $title,
                ':description' => $description,
                ':file_url'    => $file_url,
                ':video_url'   => $video_url,
                ':doc_type'    => $doc_type,
                ':id'          => $editId,
            ]);
        } else {
            $stm = $pdo->prepare("INSERT INTO documents
                (title,description,file_url,video_url,doc_type)
                VALUES (:title,:description,:file_url,:video_url,:doc_type)");
            $stm->execute([
                ':title'       => $title,
                ':description' => $description,
                ':file_url'    => $file_url,
                ':video_url'   => $video_url,
                ':doc_type'    => $doc_type,
            ]);
        }
        header('Location: documents.php');
        exit;
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $pdo->prepare("DELETE FROM documents WHERE id=?")->execute([$id]);
    }
    header('Location: documents.php');
    exit;
}

$rows = $pdo->query("SELECT * FROM documents ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/header.php';
?>

<h1 class="admin-page-title">Tài liệu / hướng dẫn</h1>

<div class="admin-grid-2">
    <div class="admin-card">
        <h2 class="admin-card-title"><?= $editId ? 'Sửa tài liệu' : 'Thêm tài liệu mới' ?></h2>

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
                       value="<?= htmlspecialchars($editing['title'] ?? '') ?>" required>
            </div>
            <div class="admin-form-row">
                <label>Mô tả:</label>
                <textarea name="description" class="input" rows="3"><?= htmlspecialchars($editing['description'] ?? '') ?></textarea>
            </div>
            <div class="admin-form-row">
                <label>File URL (PDF, DOC...):</label>
                <input type="text" name="file_url" class="input"
                       value="<?= htmlspecialchars($editing['file_url'] ?? '') ?>">
            </div>
            <div class="admin-form-row">
                <label>Video URL (YouTube...):</label>
                <input type="text" name="video_url" class="input"
                       value="<?= htmlspecialchars($editing['video_url'] ?? '') ?>">
            </div>
            <div class="admin-form-row">
                <label>Loại tài liệu:</label>
                <select name="doc_type" class="input">
                    <?php
                    $types = [
                        'beginner' => 'Cơ bản',
                        'faq'      => 'FAQ / Hỏi đáp',
                        'video'    => 'Video hướng dẫn',
                    ];
                    $cur = $editing['doc_type'] ?? 'beginner';
                    foreach ($types as $k => $v):
                    ?>
                        <option value="<?= $k ?>" <?= $k === $cur ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Lưu tài liệu</button>
        </form>
    </div>

    <div class="admin-card">
        <h2 class="admin-card-title">Danh sách tài liệu</h2>

        <table class="admin-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Tiêu đề</th>
                <th>Loại</th>
                <th>File</th>
                <th>Video</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="6" class="empty">Chưa có tài liệu nào.</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $d): ?>
                    <tr>
                        <td><?= $d['id'] ?></td>
                        <td><?= htmlspecialchars($d['title']) ?></td>
                        <td><?= htmlspecialchars($d['doc_type']) ?></td>
                        <td><?= htmlspecialchars($d['file_url']) ?></td>
                        <td><?= htmlspecialchars($d['video_url']) ?></td>
                        <td>
                            <a class="admin-link" href="documents.php?edit=<?= $d['id'] ?>">Sửa</a> |
                            <a class="admin-link text-red"
                               href="documents.php?delete=<?= $d['id'] ?>"
                               onclick="return confirm('Xóa tài liệu này?');">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>
