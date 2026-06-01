<?php
/**
 * Contact & Feedback Page
 */
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Contact';
$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $subject = sanitizeInput($_POST['subject'] ?? '');
        $message = sanitizeInput($_POST['message'] ?? '');
        $rating = max(1, min(5, (int) ($_POST['rating'] ?? 5)));

        if (strlen($name) < 2) $errors[] = 'Name is required.';
        if (!isValidEmail($email)) $errors[] = 'Valid email is required.';
        if (strlen($subject) < 3) $errors[] = 'Subject is required.';
        if (strlen($message) < 10) $errors[] = 'Message must be at least 10 characters.';

        if (empty($errors)) {
            $stmt = db()->prepare(
                'INSERT INTO feedback (user_id, name, email, subject, message, rating) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                isLoggedIn() ? currentUserId() : null,
                $name, $email, $subject, $message, $rating
            ]);
            logActivity(currentUserId(), 'feedback_submitted', "Subject: {$subject}");
            $success = 'Thank you! Your message has been submitted successfully.';
        }
    }
}

$user = currentUser();
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <h2><i class="bi bi-envelope text-primary"></i> Contact Us</h2>
                <p class="text-muted">Have questions or feedback? We'd love to hear from you!</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success glass-alert"><?= e($success) ?></div>
            <?php endif; ?>
            <?php foreach ($errors as $err): ?>
                <div class="alert alert-danger"><?= e($err) ?></div>
            <?php endforeach; ?>

            <div class="glass-card p-4 p-md-5">
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Your Name</label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= e($_POST['name'] ?? $user['full_name'] ?? '') ?>"
                                   style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border-glass)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?= e($_POST['email'] ?? $user['email'] ?? '') ?>"
                                   style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border-glass)">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" required
                                   value="<?= e($_POST['subject'] ?? '') ?>"
                                   style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border-glass)">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Rating</label>
                            <select name="rating" class="form-select"
                                    style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border-glass)">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <option value="<?= $i ?>" <?= ($_POST['rating'] ?? 5) == $i ? 'selected' : '' ?>>
                                        <?= str_repeat('★', $i) . str_repeat('☆', 5 - $i) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="5" required
                                      style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border-glass)"
                                      placeholder="Tell us about your experience..."><?= e($_POST['message'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-glow w-100">
                                <i class="bi bi-send"></i> Send Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
