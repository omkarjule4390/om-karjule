<?php
/**
 * AI Code Error Solver - Main analysis page
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle = 'Error Solver';
$languages = getLanguages();

// Sample code snippets for quick start
$samples = [
    'python' => "def greet(name)\n    print('Hello ' + name)\n\ngreet('World')",
    'javascript' => "function add(a, b) {\n  return a + b\n}\n\nconsole.log add(5, 3)",
    'php' => "<?php\necho 'Hello World'\n$name = 'User'\nif ($name = 'Admin') {\n    echo 'Welcome Admin';\n}\n?>",
    'java' => "public class Main {\n    public static void main(String[] args) {\n        System.out.println(\"Hello\")\n    }\n}",
];

$extraScripts = '<script src="' . baseUrl('js/solver.js') . '"></script>';
require_once __DIR__ . '/includes/header.php';
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <div class="dashboard-content">
        <h2 class="mb-1"><i class="bi bi-bug text-danger"></i> AI Error Solver</h2>
        <p class="text-muted mb-4">Paste your code below and let AI detect and fix errors</p>

        <form id="solverForm" class="glass-card p-4 mb-4">
            <?= csrfField() ?>
            <meta name="csrf-token" content="<?= e(generateCsrfToken()) ?>">

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Programming Language</label>
                    <select name="language" id="language" class="form-select" style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border-glass)">
                        <?php foreach ($languages as $key => $name): ?>
                            <option value="<?= e($key) ?>"><?= e($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Snippet Title</label>
                    <input type="text" id="snippetTitle" class="form-control" placeholder="My code snippet"
                           style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border-glass)">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="beginnerMode">
                        <label class="form-check-label" for="beginnerMode">
                            <i class="bi bi-mortarboard"></i> Beginner Learning Mode
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Your Code</label>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="loadSampleBtn">
                        <i class="bi bi-file-code"></i> Load Sample
                    </button>
                </div>
                <textarea id="codeInput" class="code-editor" name="code" placeholder="Paste your code here..."
                          spellcheck="false"></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-glow btn-lg">
                <i class="bi bi-search"></i> Analyze & Fix Code
            </button>
        </form>

        <!-- Results section -->
        <div id="resultsSection" class="d-none fade-in">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="bi bi-clipboard-data"></i> Analysis Results</h4>
                <span id="providerBadge" class="badge bg-secondary d-none"></span>
            </div>

            <!-- Errors -->
            <div class="glass-card p-4 mb-3">
                <h5><i class="bi bi-exclamation-triangle text-warning"></i> Detected Errors</h5>
                <div id="errorsList" class="mt-2"></div>
            </div>

            <!-- Code comparison -->
            <div class="glass-card p-4 mb-3">
                <h5 class="mb-3"><i class="bi bi-arrow-left-right"></i> Original vs Corrected</h5>
                <div class="code-compare">
                    <div>
                        <h6 class="text-danger"><i class="bi bi-x-circle"></i> Original</h6>
                        <div id="originalCodeDisplay" class="code-output font-monospace small"></div>
                    </div>
                    <div>
                        <h6 class="text-success"><i class="bi bi-check-circle"></i> Corrected</h6>
                        <div class="code-output position-relative">
                            <pre><code id="correctedCode" class="language-python"></code></pre>
                            <div class="position-absolute top-0 end-0 p-2 d-flex gap-1">
                                <button type="button" id="copyCorrectedBtn" class="btn btn-sm btn-outline-light" title="Copy">
                                    <i class="bi bi-clipboard"></i> Copy
                                </button>
                                <button type="button" id="downloadCorrectedBtn" class="btn btn-sm btn-outline-light" title="Download">
                                    <i class="bi bi-download"></i> Download
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Explanation -->
            <div class="glass-card p-4 mb-3">
                <h5><i class="bi bi-chat-quote"></i> Explanation</h5>
                <p id="explanationText" class="mb-0 mt-2"></p>
            </div>

            <!-- Tips -->
            <div class="glass-card p-4 beginner-panel">
                <h5><i class="bi bi-lightbulb"></i> Tips & Best Practices</h5>
                <div id="tipsList" class="mt-2"></div>
            </div>
        </div>
    </div>
</div>

<script>
const samples = <?= json_encode($samples) ?>;
document.getElementById('loadSampleBtn')?.addEventListener('click', function() {
    const lang = document.getElementById('language').value;
    document.getElementById('codeInput').value = samples[lang] || samples['python'];
});
document.getElementById('language')?.addEventListener('change', function() {
    // Update prism class hint
});
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
