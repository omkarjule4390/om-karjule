<?php
/**
 * Code History Management
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle = 'History';
$userId = currentUserId();
$search = sanitizeInput($_GET['search'] ?? '');
$viewId = (int) ($_GET['id'] ?? 0);

// View single history item
$detail = null;
if ($viewId > 0) {
    $stmt = db()->prepare(
        'SELECT ch.*, ar.errors_json, ar.corrected_code, ar.explanation, ar.tips, ar.beginner_mode
         FROM code_history ch
         LEFT JOIN ai_responses ar ON ar.history_id = ch.id
         WHERE ch.id = ? AND ch.user_id = ?
         ORDER BY ar.id DESC LIMIT 1'
    );
    $stmt->execute([$viewId, $userId]);
    $detail = $stmt->fetch();
}

// List history with search
$sql = 'SELECT ch.*,
        (SELECT errors_json FROM ai_responses WHERE history_id = ch.id ORDER BY id DESC LIMIT 1) AS errors_json,
        (SELECT corrected_code FROM ai_responses WHERE history_id = ch.id ORDER BY id DESC LIMIT 1) AS corrected_code
        FROM code_history ch
        WHERE ch.user_id = ?';
$params = [$userId];

if ($search) {
    $sql .= ' AND (ch.title LIKE ? OR ch.original_code LIKE ? OR ch.language LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$sql .= ' ORDER BY ch.created_at DESC LIMIT 50';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$historyList = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <div class="dashboard-content">
        <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h2 class="mb-0"><i class="bi bi-clock-history"></i> Code History</h2>
            <form method="GET" class="d-flex gap-2" style="max-width:400px;width:100%">
                <input type="text" name="search" class="form-control" placeholder="Search history..."
                       value="<?= e($search) ?>"
                       style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border-glass)">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
            </form>
        </div>

        <?php if ($detail): ?>
        <div class="glass-card p-4 mb-4 fade-in">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4><?= e($detail['title']) ?></h4>
                    <span class="lang-badge"><?= e($detail['language']) ?></span>
                    <small class="text-muted ms-2"><?= formatDate($detail['created_at']) ?></small>
                </div>
                <a href="<?= e(baseUrl('history.php')) ?>" class="btn btn-sm btn-outline-secondary">Back to list</a>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <h6>Original Code</h6>
                    <div class="code-output">
                        <pre><code class="<?= e(getHighlightClass($detail['language'])) ?>"><?= e($detail['original_code']) ?></code></pre>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6>Corrected Code</h6>
                    <div class="code-output">
                        <pre><code class="<?= e(getHighlightClass($detail['language'])) ?>"><?= e($detail['corrected_code'] ?? 'N/A') ?></code></pre>
                    </div>
                    <?php if ($detail['corrected_code']): ?>
                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="copyToClipboard(<?= json_encode($detail['corrected_code']) ?>, this)">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($detail['explanation']): ?>
            <div class="mt-3">
                <h6>Explanation</h6>
                <p class="text-muted"><?= e($detail['explanation']) ?></p>
            </div>
            <?php endif; ?>

            <?php
            $errors = json_decode($detail['errors_json'] ?? '[]', true);
            if (!empty($errors)):
            ?>
            <div class="mt-3">
                <h6>Errors Found</h6>
                <?php foreach ($errors as $err): ?>
                    <span class="error-badge <?= e($err['type'] ?? 'syntax') ?>">
                        Line <?= (int)($err['line'] ?? 0) ?>: <?= e($err['message'] ?? '') ?>
                    </span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="glass-card p-0">
            <?php if (empty($historyList)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                    <?= $search ? 'No results found.' : 'No history yet. Start analyzing code!' ?>
                </div>
            <?php else: ?>
                <?php foreach ($historyList as $item):
                    $errors = json_decode($item['errors_json'] ?? '[]', true);
                    $errorCount = is_array($errors) ? count($errors) : 0;
                ?>
                <div class="history-item d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <strong><?= e($item['title']) ?></strong>
                        <span class="lang-badge ms-2"><?= e($item['language']) ?></span>
                        <br>
                        <small class="text-muted"><?= formatDate($item['created_at']) ?></small>
                        <?php if ($errorCount > 0): ?>
                            <span class="error-badge syntax"><?= $errorCount ?> error(s)</span>
                        <?php else: ?>
                            <span class="badge bg-success">No errors</span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= e(baseUrl('history.php?id=' . $item['id'])) ?>" class="btn btn-sm btn-outline-primary">View</a>
                        <button class="btn btn-sm btn-outline-danger delete-history"
                                data-id="<?= (int)$item['id'] ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.delete-history').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('Delete this history item?')) return;
        const res = await fetch('api/history.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                history_id: this.dataset.id,
                csrf_token: document.querySelector('input[name="csrf_token"]')?.value
            })
        });
        const data = await res.json();
        if (data.success) location.reload();
        else alert(data.message || 'Delete failed');
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
