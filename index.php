<?php
/**
 * Landing Page - AI Code Error Solver
 */
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero-section">
    <div class="container">
        <h1 class="hero-title fade-in">
            Fix Code Errors with <span>AI Power</span>
        </h1>
        <p class="hero-subtitle fade-in">
            Automatically detect programming errors, get corrected code suggestions,
            and learn coding the beginner-friendly way.
        </p>
        <div class="d-flex flex-wrap gap-3 justify-content-center fade-in">
            <?php if (isLoggedIn()): ?>
                <a href="<?= e(baseUrl('error_solver.php')) ?>" class="btn btn-primary btn-lg btn-glow">
                    <i class="bi bi-bug"></i> Start Solving
                </a>
                <a href="<?= e(baseUrl('dashboard.php')) ?>" class="btn btn-outline-light btn-lg">Dashboard</a>
            <?php else: ?>
                <a href="<?= e(baseUrl('register.php')) ?>" class="btn btn-primary btn-lg btn-glow">
                    <i class="bi bi-rocket-takeoff"></i> Get Started Free
                </a>
                <a href="<?= e(baseUrl('login.php')) ?>" class="btn btn-outline-light btn-lg">Login</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="glass-card feature-card">
                    <div class="feature-icon"><i class="bi bi-search"></i></div>
                    <h5>Error Detection</h5>
                    <p class="text-muted">AI scans your code for syntax and logical errors with line-by-line highlighting.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card feature-card">
                    <div class="feature-icon"><i class="bi bi-magic"></i></div>
                    <h5>Auto Correction</h5>
                    <p class="text-muted">Get corrected code with side-by-side comparison and clear explanations.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card feature-card">
                    <div class="feature-icon"><i class="bi bi-mortarboard"></i></div>
                    <h5>Beginner Mode</h5>
                    <p class="text-muted">Simple explanations, coding tips, and best practices for new programmers.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container text-center">
        <h3 class="mb-4">Supported Languages</h3>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <?php foreach (getLanguages() as $key => $name): ?>
                <span class="badge bg-primary bg-opacity-25 text-primary px-3 py-2"><?= e($name) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="glass-card p-4 p-md-5 text-center">
            <h3>Ready to debug smarter?</h3>
            <p class="text-muted mb-4">Join thousands of learners using AI to improve their coding skills.</p>
            <a href="<?= e(baseUrl(isLoggedIn() ? 'error_solver.php' : 'register.php')) ?>" class="btn btn-primary btn-glow btn-lg">
                <?= isLoggedIn() ? 'Open Error Solver' : 'Create Free Account' ?>
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
