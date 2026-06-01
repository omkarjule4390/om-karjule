<?php
/**
 * User Dashboard
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle = 'Dashboard';
$userId = currentUserId();

// Fetch statistics
$stmt = db()->prepare('SELECT COUNT(*) as total FROM code_history WHERE user_id = ?');
$stmt->execute([$userId]);
$totalSubmissions = (int) $stmt->fetch()['total'];

$stmt = db()->prepare(
    'SELECT COUNT(*) as total FROM ai_responses ar
     JOIN code_history ch ON ar.history_id = ch.id WHERE ch.user_id = ?'
);
$stmt->execute([$userId]);
$totalAnalyses = (int) $stmt->fetch()['total'];

$stmt = db()->prepare(
    'SELECT language, COUNT(*) as cnt FROM code_history WHERE user_id = ?
     GROUP BY language ORDER BY cnt DESC LIMIT 1'
);
$stmt->execute([$userId]);
$topLang = $stmt->fetch();
$favoriteLang = $topLang ? strtoupper($topLang['language']) : 'N/A';

// Recent activity
$stmt = db()->prepare(
    'SELECT ch.*, ar.explanation, ar.errors_json
     FROM code_history ch
     LEFT JOIN ai_responses ar ON ar.history_id = ch.id
     WHERE ch.user_id = ?
     ORDER BY ch.created_at DESC LIMIT 5'
);
$stmt->execute([$userId]);
$recentActivity = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <div class="dashboard-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Dashboard</h2>
                <p class="text-muted mb-0">Welcome back, <?= e(currentUser()['full_name']) ?>!</p>
            </div>
            <a href="<?= e(baseUrl('error_solver.php')) ?>" class="btn btn-primary btn-glow">
                <i class="bi bi-plus-lg"></i> New Analysis
            </a>
        </div>

        <!-- Statistics cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="glass-card stat-card">
                    <div class="stat-value"><?= $totalSubmissions ?></div>
                    <div class="stat-label"><i class="bi bi-code-slash"></i> Code Submissions</div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="glass-card stat-card">
                    <div class="stat-value"><?= $totalAnalyses ?></div>
                    <div class="stat-label"><i class="bi bi-robot"></i> AI Analyses</div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="glass-card stat-card">
                    <div class="stat-value" style="font-size:1.5rem"><?= e($favoriteLang) ?></div>
                    <div class="stat-label"><i class="bi bi-star"></i> Top Language</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Quick actions -->
            <div class="col-lg-4">
                <div class="glass-card p-4 h-100">
                    <h5 class="mb-3"><i class="bi bi-lightning-charge text-warning"></i> Quick Actions</h5>
                    <div class="d-grid gap-2">
                        <a href="<?= e(baseUrl('error_solver.php')) ?>" class="btn btn-outline-primary">
                            <i class="bi bi-bug"></i> Analyze Code
                        </a>
                        <a href="<?= e(baseUrl('history.php')) ?>" class="btn btn-outline-primary">
                            <i class="bi bi-clock-history"></i> View History
                        </a>
                        <a href="<?= e(baseUrl('profile.php')) ?>" class="btn btn-outline-primary">
                            <i class="bi bi-person"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent activity -->
            <div class="col-lg-8">
                <div class="glass-card p-0 h-100">
                    <div class="p-3 border-bottom" style="border-color: var(--border-glass) !important">
                        <h5 class="mb-0"><i class="bi bi-activity"></i> Recent Activity</h5>
                    </div>
                    <?php if (empty($recentActivity)): ?>
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                            No activity yet. <a href="<?= e(baseUrl('error_solver.php')) ?>">Analyze your first code!</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentActivity as $item):
                            $errors = json_decode($item['errors_json'] ?? '[]', true);
                            $errorCount = is_array($errors) ? count($errors) : 0;
                        ?>
                        <div class="history-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= e($item['title']) ?></strong>
                                <span class="lang-badge ms-2"><?= e($item['language']) ?></span>
                                <br>
                                <small class="text-muted"><?= formatDate($item['created_at']) ?></small>
                                <?php if ($errorCount > 0): ?>
                                    <span class="error-badge syntax ms-2"><?= $errorCount ?> error(s)</span>
                                <?php else: ?>
                                    <span class="badge bg-success ms-2">Clean</span>
                                <?php endif; ?>
                            </div>
                            <a href="<?= e(baseUrl('history.php?id=' . $item['id'])) ?>" class="btn btn-sm btn-outline-primary">View</a>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Beginner tips -->
        <div class="glass-card p-4 mt-4 beginner-panel">
            <h5><i class="bi bi-mortarboard text-secondary"></i> Beginner Tip of the Day</h5>
            <p class="mb-0 text-muted">
                Always read error messages from top to bottom. The first error is often the root cause —
                fixing it may resolve others automatically!
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
