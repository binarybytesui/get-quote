<?php
/**
 * tester-advanced.php
 * Comprehensive single-file advanced diagnostic for the Get-Quote project.
 *
 * Place this file in your project root (e.g. /Applications/XAMPP/xamppfiles/htdocs/get-quote/)
 * and open in browser: http://localhost/get-quote/tester-advanced.php
 *
 * NOTE: This script performs many filesystem and network checks. It will try to:
 *  - test endpoints
 *  - validate API JSON and schema
 *  - test DB connection and schema
 *  - scan PHP files for include/require and check referenced files exist
 *  - run php -l syntax check on PHP files
 *  - scan assets (css/js/img)
 *  - test security headers
 *  - test sessions
 *  - display auto-fix suggestions
 *
 * Some checks (like php -l) require CLI php available to the webserver user.
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

// ---------------------- CONFIG ----------------------
$base = "http://localhost/get-quote/public";
$projectRoot = __DIR__; // project root where this file sits
$uploaded_file = '/mnt/data/Screenshot 2025-11-22 at 3.47.30 PM.png'; // local uploaded path from session history

// Expected JSON schema for products API (field => type)
$expectedProductSchema = [
    "id" => "int",
    "name" => "string",
    "category" => "string",
    "partNo" => "string",
    "Main_Price" => "number",
    "Discount_in_%" => "number",
    "price" => "number",
    "Labour_Charges" => "number",
    "Wire_Cost" => "number"
];

// endpoints to test
$endpoints = [
    "Admin Index" => "$base/admin/views/index.php",
    "Admin Quote" => "$base/admin/views/quote.php",
    "Admin API - get-products" => "$base/admin/api/get-products.php",
    "Admin API - auth" => "$base/admin/api/auth.php",
    "User Index" => "$base/user/views/index.html",
    "User Quote" => "$base/user/views/quote.html",
    "User API - get-products" => "$base/user/api/get-products.php",
];

// assets to test (relative to public)
$assets = [
    "$base/admin/assets/css/style.css" => 'css',
    "$base/user/assets/css/style.css" => 'css',
];

// ---------------------- HELPERS ----------------------
function get_headers_and_time($url) {
    $start = microtime(true);
    $headers = @get_headers($url, 1);
    $timeMs = round((microtime(true) - $start) * 1000);
    return [$headers, $timeMs];
}

function fetch_body_and_time($url) {
    $start = microtime(true);
    $opts = ["http" => ["timeout" => 10]];
    $context = stream_context_create($opts);
    $body = @file_get_contents($url, false, $context);
    $timeMs = round((microtime(true) - $start) * 1000);
    return [$body, $timeMs];
}

function safe_json_decode($str) {
    $decoded = json_decode($str, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [null, json_last_error_msg()];
    }
    return [$decoded, null];
}

// Check if CLI php is available for php -l
function php_cli_available() {
    $which = trim(@shell_exec('which php'));
    return !empty($which);
}

// Recursively find files of type
function find_files($dir, $exts = ['php']) {
    $results = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if ($file->isFile()) {
            $path = $file->getPathname();
            $e = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($e, $exts)) $results[] = $path;
        }
    }
    return $results;
}

// Parse PHP file and find require/include statements with simple regex
function find_includes_in_php($filePath) {
    $code = @file_get_contents($filePath);
    if ($code === false) return [];
    $pattern = '/\b(require_once|require|include_once|include)\s*\(?\s*[\'"]([^\'"]+)[\'"]\s*\)?\s*;/i';
    preg_match_all($pattern, $code, $matches, PREG_SET_ORDER);
    $found = [];
    foreach ($matches as $m) {
        $found[] = ['full' => $m[0], 'type' => $m[1], 'path' => $m[2]];
    }
    return $found;
}

// Resolve relative include path to absolute
function resolve_include_path($baseFile, $includedPath) {
    if (substr($includedPath, 0, 1) === DIRECTORY_SEPARATOR) {
        return $includedPath; // absolute path
    }
    $baseDir = dirname($baseFile);
    $resolved = realpath($baseDir . DIRECTORY_SEPARATOR . $includedPath);
    return $resolved ?: null;
}

// Run php -l syntax check
function php_syntax_check($file) {
    $cmd = 'php -l ' . escapeshellarg($file) . ' 2>&1';
    $out = @shell_exec($cmd);
    if ($out === null) return ["unknown", "php CLI not available or disabled"];
    if (strpos($out, 'No syntax errors detected') !== false) return ["pass", trim($out)];
    return ["fail", trim($out)];
}

// Check file permissions
function check_file_perms($file) {
    if (!file_exists($file)) return ["missing", "File missing"];
    $readable = is_readable($file);
    $writable = is_writable($file);
    return ["ok", ($readable ? 'R' : '-') . ($writable ? 'W' : '-')];
}

// ---------------------- RUN TESTS ----------------------

// 1) Endpoint tests + JSON API validation + perf
$endpointResults = [];
foreach ($endpoints as $label => $url) {
    list($headers, $timeMs) = get_headers_and_time($url);
    $status = $headers ? $headers[0] : "No Response";
    $bodyResult = null;
    if ($headers && strpos($status, '200') !== false) {
        list($body, $fetchTime) = fetch_body_and_time($url);
        $bodyResult = ['body' => $body, 'time' => $fetchTime];
    }
    $endpointResults[$label] = ['url' => $url, 'status' => $status, 'headers' => $headers, 'body' => $bodyResult, 'time' => $timeMs];
}

// 2) API JSON deep validation for product endpoints
$apiJsonResults = [];
foreach (['Admin API - get-products' => "$base/admin/api/get-products.php", 'User API - get-products' => "$base/user/api/get-products.php"] as $label => $url) {
    list($body, $fetchTime) = fetch_body_and_time($url);
    if (!$body) {
        $apiJsonResults[$label] = ['ok' => false, 'reason' => 'No response', 'time' => $fetchTime];
        continue;
    }
    list($decoded, $err) = safe_json_decode($body);
    if ($err) {
        $apiJsonResults[$label] = ['ok' => false, 'reason' => "Invalid JSON: $err", 'time' => $fetchTime];
        continue;
    }
    if (!is_array($decoded) || count($decoded) === 0) {
        $apiJsonResults[$label] = ['ok' => true, 'warning' => 'Empty array or non-array result', 'time' => $fetchTime, 'sample' => $decoded[0] ?? null];
        continue;
    }
    // Check schema: ensure required keys exist on first item (for speed)
    $first = $decoded[0];
    $missing = [];
    foreach ($expectedProductSchema as $k => $t) {
        if (!array_key_exists($k, $first)) $missing[] = $k;
    }
    $apiJsonResults[$label] = ['ok' => count($missing) === 0, 'missing_fields' => $missing, 'time' => $fetchTime, 'sample' => $first];
}

// 3) DB schema & connection
$dbResult = ['ok' => false, 'message' => ''];
try {
    require_once $projectRoot . '/src/database/connection.php';
    $pdo = db();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $check = $pdo->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
    $required = ["id","category","name","part_no","main_price","discount_percent","price","labour_charges","wire_cost"];
    $missing = array_diff($required, $check);
    if (count($missing) === 0) {
        $dbResult = ['ok' => true, 'message' => 'products table OK', 'columns' => $check];
    } else {
        $dbResult = ['ok' => false, 'message' => 'Missing columns: ' . implode(', ', $missing), 'columns' => $check];
    }
} catch (Exception $e) {
    $dbResult = ['ok' => false, 'message' => 'DB error: ' . $e->getMessage()];
}

// 4) PHP files syntax + include scanner
$phpFiles = find_files($projectRoot, ['php']);
$phpLintResults = [];
$includeIssues = [];
$missingIncludes = [];
foreach ($phpFiles as $phpFile) {
    // Skip vendor or irrelevant (if any)
    // php -l if available
    if (php_cli_available()) {
        list($lintStatus, $lintMsg) = php_syntax_check($phpFile);
    } else {
        $lintStatus = "unknown";
        $lintMsg = "php CLI not available";
    }
    $phpLintResults[$phpFile] = ['status' => $lintStatus, 'msg' => $lintMsg];

    // find includes
    $incs = find_includes_in_php($phpFile);
    if ($incs) {
        foreach ($incs as $inc) {
            $resolved = resolve_include_path($phpFile, $inc['path']);
            if (!$resolved || !file_exists($resolved)) {
                $missingIncludes[] = ['file' => $phpFile, 'include' => $inc['path'], 'resolved' => $resolved];
            }
        }
    }
}

// 5) Assets scanner (exists, size)
$assetResults = [];
foreach ($assets as $url => $type) {
    list($headers, $timeMs) = get_headers_and_time($url);
    $exists = $headers && strpos($headers[0], '200') !== false;
    $size = null;
    if ($exists && isset($headers['Content-Length'])) $size = $headers['Content-Length'];
    $assetResults[$url] = ['exists' => $exists, 'status' => $headers[0] ?? 'No response', 'size' => $size, 'time' => $timeMs];
}

// 6) Security headers check for APIs
$securityResults = [];
foreach (["$base/admin/api/get-products.php", "$base/user/api/get-products.php"] as $url) {
    list($headers, $t) = get_headers_and_time($url);
    $ok = true;
    $missing = [];
    if (!$headers) { $ok = false; $missing[] = "No response"; }
    else {
        $hdrs = array_change_key_case($headers, CASE_LOWER);
        if (!isset($hdrs['content-type'])) $missing[] = 'Content-Type';
        if (!isset($hdrs['x-frame-options'])) $missing[] = 'X-Frame-Options';
        if (!isset($hdrs['x-content-type-options'])) $missing[] = 'X-Content-Type-Options';
    }
    $securityResults[$url] = ['ok' => $ok && empty($missing), 'missing' => $missing, 'raw' => $headers];
}

// 7) Session test
$sessionResult = ['ok' => false, 'msg' => ''];
try {
    session_start();
    $_SESSION['__tester__'] = time();
    session_write_close();
    session_start();
    $sessionResult['ok'] = isset($_SESSION['__tester__']);
    $sessionResult['msg'] = $sessionResult['ok'] ? 'Session write/read OK' : 'Session persistent failure';
    session_unset();
    session_destroy();
} catch (Exception $e) {
    $sessionResult = ['ok' => false, 'msg' => 'Session error: ' . $e->getMessage()];
}

// 8) File permission checks for key files
$filesToCheck = [
    $projectRoot . '/src/database/connection.php',
    $projectRoot . '/public/admin/views/index.php',
    $projectRoot . '/public/user/views/index.html',
    $projectRoot . '/src/security/credentials.json',
];
$filePermResults = [];
foreach ($filesToCheck as $f) {
    $filePermResults[$f] = check_file_perms($f);
}

// 9) Uploaded local file presence check (from session history)
$uploadedFileExists = file_exists($uploaded_file);

// 10) Auto-fix suggestions (basic)
$fixSuggestions = [];
if (!$dbResult['ok']) {
    $fixSuggestions[] = "DB schema: " . $dbResult['message'] . ". Run migration SQL to add missing columns or import JSON.";
}
if (!empty($missingIncludes)) {
    foreach ($missingIncludes as $mi) {
        $fixSuggestions[] = "Missing include: In {$mi['file']} the include '{$mi['include']}' resolves to '{$mi['resolved']}' which is missing. Update require path or move file.";
    }
}
foreach ($apiJsonResults as $k => $res) {
    if (isset($res['missing_fields']) && count($res['missing_fields'])>0) {
        $fixSuggestions[] = "API {$k} missing fields: " . implode(', ', $res['missing_fields']) . ". Map DB fields to expected JSON keys (camelCase).";
    }
}
foreach ($securityResults as $url => $r) {
    if (!$r['ok']) $fixSuggestions[] = "Security headers missing on {$url}: " . implode(', ', $r['missing']);
}
foreach ($phpLintResults as $f => $r) {
    if ($r['status'] === 'fail') $fixSuggestions[] = "PHP syntax error in $f: " . $r['msg'];
}

// ---------------------- RENDER HTML REPORT ----------------------
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Get-Quote Advanced Tester</title>
    <style>
        body { background:#0e1012; color:#e6eef2; font-family:Inter, Arial, sans-serif; padding:18px; }
        h1 { color:#6be2a5; }
        .panel { background:#111; border-radius:8px; padding:12px; margin-bottom:14px; box-shadow:0 2px 6px rgba(0,0,0,0.6); }
        table { width:100%; border-collapse:collapse; }
        td, th { padding:8px; border-bottom:1px solid rgba(255,255,255,0.05); text-align:left; }
        .pass { color:#7ef29d; font-weight:600; }
        .fail { color:#ff866c; font-weight:700; }
        .warn { color:#ffd166; font-weight:700; }
        .small { font-size:12px; color:#9fb1bd; }
        .muted { color:#7b8b94; }
        pre { background:#062028; padding:12px; overflow:auto; border-radius:6px; color:#cfecec; }
        .suggest { background:#08221b; padding:10px; border-left:4px solid #ffd166; margin-bottom:8px; }
        .okbox { display:inline-block; padding:6px 10px; border-radius:6px; background:#06241e; color:#6be2a5; font-weight:700; }
    </style>
</head>
<body>
    <h1>Get-Quote — Advanced Tester</h1>
    <p class="small muted">Project root: <?= htmlspecialchars($projectRoot) ?></p>

    <div class="panel">
        <h2>Endpoints & Performance</h2>
        <table>
            <thead><tr><th>Label</th><th>URL</th><th>Status</th><th>Time (ms)</th><th>Notes</th></tr></thead>
            <tbody>
            <?php foreach ($endpointResults as $label => $r): ?>
                <tr>
                    <td><?= htmlspecialchars($label) ?></td>
                    <td class="small"><a href="<?= htmlspecialchars($r['url']) ?>" target="_blank"><?= htmlspecialchars($r['url']) ?></a></td>
                    <td><?= strpos($r['status'],'200') !== false ? '<span class="pass">PASS</span>' : '<span class="fail">FAIL</span>' ?></td>
                    <td><?= htmlspecialchars($r['time']) ?></td>
                    <td class="small muted"><?= htmlspecialchars($r['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="panel">
        <h2>API JSON Validation</h2>
        <?php foreach ($apiJsonResults as $k => $v): ?>
            <div style="margin-bottom:8px;">
                <strong><?= htmlspecialchars($k) ?>:</strong>
                <?php if (!$v['ok']): ?>
                    <span class="fail">FAIL</span>
                    <div class="small muted">Reason: <?= htmlspecialchars($v['reason'] ?? ($v['missing_fields'] ? 'Missing fields' : '')) ?></div>
                <?php else: ?>
                    <span class="pass">OK</span>
                    <?php if (!empty($v['missing_fields'])): ?>
                        <div class="warn small">Missing fields: <?= implode(', ', $v['missing_fields']) ?></div>
                    <?php endif; ?>
                <?php endif; ?>
                <div class="small muted">Response time: <?= $v['time'] ?? 'n/a' ?> ms</div>
                <?php if (!empty($v['sample'])): ?>
                    <pre><?= htmlspecialchars(json_encode($v['sample'], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)) ?></pre>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="panel">
        <h2>Database Check</h2>
        <?php if ($dbResult['ok']): ?>
            <div class="okbox">DB OK</div>
            <div class="small muted">Columns: <?= htmlspecialchars(implode(', ', $dbResult['columns'])) ?></div>
        <?php else: ?>
            <div class="fail">DB ISSUE</div>
            <div class="small muted"><?= htmlspecialchars($dbResult['message']) ?></div>
        <?php endif; ?>
    </div>

    <div class="panel">
        <h2>PHP Lint & Include Scanner</h2>
        <div class="small muted">PHP files scanned: <?= count($phpFiles) ?></div>
        <?php if (!empty($missingIncludes)): ?>
            <div class="fail">Missing includes detected: <?= count($missingIncludes) ?></div>
            <?php foreach ($missingIncludes as $mi): ?>
                <div class="suggest">
                    <strong>File:</strong> <?= htmlspecialchars($mi['file']) ?><br>
                    <strong>Include:</strong> <?= htmlspecialchars($mi['include']) ?><br>
                    <strong>Resolved to:</strong> <?= htmlspecialchars($mi['resolved'] ?? 'N/A') ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="pass">No missing includes found (quick scan)</div>
        <?php endif; ?>

        <?php
            $errorsFound = false;
            foreach ($phpLintResults as $f => $res) {
                if ($res['status'] === 'fail') { $errorsFound = true; break; }
            }
        ?>
        <?php if ($errorsFound): ?>
            <div class="fail">PHP syntax errors found (see list)</div>
            <?php foreach ($phpLintResults as $f => $res): if ($res['status'] !== 'pass'): ?>
                <div class="suggest">
                    <strong><?= htmlspecialchars($f) ?></strong><br>
                    <?= htmlspecialchars($res['msg']) ?>
                </div>
            <?php endif; endforeach; ?>
        <?php else: ?>
            <div class="pass">PHP syntax appears OK or php CLI not available for linting.</div>
        <?php endif; ?>
    </div>

    <div class="panel">
        <h2>Asset scanner</h2>
        <?php foreach ($assetResults as $u => $ar): ?>
            <div>
                <strong><?= htmlspecialchars($u) ?></strong> —
                <?= $ar['exists'] ? '<span class="pass">FOUND</span>' : '<span class="fail">MISSING</span>' ?>
                <span class="small muted"> (status: <?= htmlspecialchars($ar['status'] ?? '') ?>, size: <?= htmlspecialchars($ar['size'] ?? 'n/a') ?>, fetch: <?= $ar['time'] ?> ms)</span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="panel">
        <h2>Security Headers</h2>
        <?php foreach ($securityResults as $url => $r): ?>
            <div>
                <strong><?= htmlspecialchars($url) ?></strong> —
                <?= $r['ok'] ? '<span class="pass">OK</span>' : '<span class="warn">Missing: '.implode(', ', $r['missing']).'</span>' ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="panel">
        <h2>Session & Files</h2>
        <div>Session test: <?= $sessionResult['ok'] ? '<span class="pass">OK</span>' : '<span class="fail">FAIL</span>' ?> — <span class="small muted"><?= htmlspecialchars($sessionResult['msg']) ?></span></div>
        <h4>File perms</h4>
        <?php foreach ($filePermResults as $f => $p): ?>
            <div><?= htmlspecialchars($f) ?> — <?= $p[0] === 'missing' ? '<span class="fail">MISSING</span>' : '<span class="pass">OK</span>' ?> (<?= htmlspecialchars($p[1]) ?>)</div>
        <?php endforeach; ?>
        <h4>Uploaded test file</h4>
        <div><?= $uploadedFileExists ? '<span class="pass">Local uploaded file found</span>' : '<span class="warn">Local uploaded file not found at '.htmlspecialchars($uploaded_file).'</span>' ?></div>
    </div>

    <div class="panel">
        <h2>Auto-fix Suggestions</h2>
        <?php if (empty($fixSuggestions)): ?>
            <div class="pass">No automated suggestions — project looks good for Phase-1 restructure.</div>
        <?php else: ?>
            <?php foreach ($fixSuggestions as $s): ?>
                <div class="suggest"><?= htmlspecialchars($s) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="panel">
        <h2>Next steps</h2>
        <ol>
            <li>Fix any missing include paths reported above (update require_once to the new src paths).</li>
            <li>Add missing security headers in your admin/user API entrypoints (see suggestions).</li>
            <li>If php -l failed, open the reported files and fix syntax issues.</li>
            <li>Re-run this tester (refresh) until all PASS.</li>
        </ol>
    </div>

    <footer class="small muted">Tester generated on <?= date('Y-m-d H:i:s') ?> — run in dev only (disable on production).</footer>
</body>
</html>
