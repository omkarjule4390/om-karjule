<?php
/**
 * Admin Panel - Manage users, activities, feedback
 */
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$pageTitle = 'Admin Panel';
$message = '';

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle_user') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        if ($userId !== currentUserId()) {
            $stmt = db()->prepare('UPDATE users SET is_active = NOT is_active WHERE id = ?');
            $stmt->execute([$userId]);
            logActivity(currentUserId(), 'admin_toggle_user', "User ID: {$userId}");
            $message = 'User status updated.';
        }
    } elseif ($action === 'delete_history') {
        $historyId = (int) ($_POST['history_id'] ?? 0);
        $stmt = db()->prepare('DELETE FROM code_history WHERE id = ?');
        $stmt->execute([$historyId]);
        logActivity(currentUserId(), 'admin_delete_content', "History ID: {$historyId}");
        $message = 'Content deleted.';
    } elseif ($action === 'resolve_feedback') {
        $feedbackId = (int) ($_POST['feedback_id'] ?? 0);
        $stmt = db()->prepare('UPDATE feedback SET status = ? WHERE id = ?');
        $stmt->execute([$_POST['status'] ?? 'resolved', $feedbackId]);
        $message = 'Feedback updated.';
    }
}

// Statistics
$totalUsers = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalSubmissions = (int) db()->query('SELECT COUNT(*) FROM code_history')->fetchColumn();
$totalFeedback = (int) db()->query('SELECT COUNT(*) FROM feedback')->fetchColumn();
$newFeedback = (int) db()->query("SELECT COUNT(*) FROM feedback WHERE status = 'new'")->fetchColumn();

// Users list
$users = db()->query(
    'SELECT u.*, COUNT(ch.id) as submission_count
     FROM users u
     LEFT JOIN code_history ch ON ch.user_id = u.id
     GROUP BY u.id ORDER BY u.created_at DESC LIMIT 50'
)->fetchAll();

// Recent activity
$activities = db()->query(
    'SELECT al.*, u.username FROM activity_log al
     LEFT JOIN users u ON u.id = al.user_id
     ORDER BY al.created_at DESC LIMIT 20'
)->fetchAll();

// Feedback
$feedbackList = db()->query(
    'SELECT f.*, u.username FROM feedback f
     LEFT JOIN users u ON u.id = f.user_id
     ORDER BY f.created_at DESC LIMIT 20'
)->fetchAll();

// All code history for moderation
$allHistory = db()->query(
    'SELECT ch.*, u.username FROM code_history ch
     JOIN users u ON u.id = ch.user_id
     ORDER BY ch.created_at DESC LIMIT 30'
)->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <div class="dashboard-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-shield-fill text-warning"></i> Admin Panel</h2>
            <span class="badge bg-warning text-dark">Administrator</span>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= e($message) ?></div>
        <?php endif; ?>

        <!-- Admin stats -->
        <div class="row g-3 mb-4">
            <div class="col-sm-3">
                <div class="glass-card stat-card text-center">
                    <div class="stat-value"><?= $totalUsers ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="glass-card stat-card text-center">
                    <div class="stat-value"><?= $totalSubmissions ?></div>
                    <div class="stat-label">Code Submissions</div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="glass-card stat-card text-center">
                    <div class="stat-value"><?= $totalFeedback ?></div>
                    <div class="stat-label">Feedback</div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="glass-card stat-card text-center">
                    <div class="stat-value"><?= $newFeedback ?></div>
                    <div class="stat-label">New Reports</div>
                </div>
            </div>
        </div>

        <!-- Users management -->
        <div class="glass-card p-4 mb-4">
            <h5 class="mb-3"><i class="bi bi-people"></i> Manage Users</h5>
            <div class="table-responsive">
                <table class="table admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Submissions</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= (int)$u['id'] ?></td>
                            <td><?= e($u['username']) ?></td>
                            <td><?= e($u['email']) ?></td>
                            <td><span class="badge bg-<?= $u['role'] === 'admin' ? 'warning' : 'primary' ?>"><?= e($u['role']) ?></span></td>
                            <td><?= (int)$u['submission_count'] ?></td>
                            <td>
                                <?php if ($u['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Disabled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int)$u['id'] !== currentUserId()): ?>
                                <form method="POST" class="d-inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="toggle_user">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-<?= $u['is_active'] ? 'danger' : 'success' ?>">
                                        <?= $u['is_active'] ? 'Disable' : 'Enable' ?>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Content moderation -->
        <div class="glass-card p-4 mb-4">
            <h5 class="mb-3"><i class="bi bi-file-earmark-code"></i> Code Content Moderation</h5>
            <div class="table-responsive">
                <table class="table admin-table table-sm">
                    <thead>
                        <tr><th>User</th><th>Title</th><th>Language</th><th>Date</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allHistory as $h): ?>
                        <tr>
                            <td><?= e($h['username']) ?></td>
                            <td><?= e($h['title']) ?></td>
                            <td><span class="lang-badge"><?= e($h['language']) ?></span></td>
                            <td><small><?= formatDate($h['created_at']) ?></small></td>
                            <td>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this content?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete_history">
                                    <input type="hidden" name="history_id" value="<?= (int)$h['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row g-4">
            <!-- Activity log -->
            <div class="col-lg-6">
                <div class="glass-card p-4">
                    <h5 class="mb-3"><i class="bi bi-activity"></i> User Activities</h5>
                    <?php foreach ($activities as $act): ?>
                    <div class="history-item py-2">
                        <strong><?= e($act['username'] ?? 'Guest') ?></strong>
                        <span class="text-muted">— <?= e($act['action']) ?></span>
                        <br><small class="text-muted"><?= formatDate($act['created_at']) ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Feedback -->
            <div class="col-lg-6">
                <div class="glass-card p-4">
                    <h5 class="mb-3"><i class="bi bi-chat-dots"></i> Feedback</h5>
                    <?php foreach ($feedbackList as $fb): ?>
                    <div class="history-item py-2">
                        <div class="d-flex justify-content-between">
                            <strong><?= e($fb['name']) ?></strong>
                            <span class="badge bg-<?= $fb['status'] === 'new' ? 'danger' : 'secondary' ?>"><?= e($fb['status']) ?></span>
                        </div>
                        <p class="mb-1 small"><?= e($fb['subject']) ?></p>
                        <p class="text-muted small mb-1"><?= e(substr($fb['message'], 0, 100)) ?>...</p>
                        <form method="POST" class="d-inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="resolve_feedback">
                            <input type="hidden" name="feedback_id" value="<?= (int)$fb['id'] ?>">
                            <input type="hidden" name="status" value="resolved">
                            <button class="btn btn-sm btn-outline-success">Mark Resolved</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
