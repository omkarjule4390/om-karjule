    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5><i class="bi bi-cpu-fill"></i> AI Code Solver</h5>
                    <p class="text-muted">Detect errors, get fixes, and learn programming with AI-powered assistance.</p>
                </div>
                <div class="col-md-4">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="<?= e(baseUrl('index.php')) ?>">Home</a></li>
                        <li><a href="<?= e(baseUrl('error_solver.php')) ?>">Error Solver</a></li>
                        <li><a href="<?= e(baseUrl('contact.php')) ?>">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6>Languages</h6>
                    <p class="text-muted small">Python, C, C++, Java, PHP, JavaScript, HTML, CSS</p>
                </div>
            </div>
            <hr class="footer-divider">
            <p class="text-center text-muted mb-0 small">
                &copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. Built for learning. v<?= e(APP_VERSION) ?>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-c.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-cpp.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-java.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-css.min.js"></script>
    <script src="<?= e(baseUrl('js/main.js')) ?>"></script>
    <?php if (isset($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html>
