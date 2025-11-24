<?php
/**
 * ADVANCED TESTER
 * URL: http://localhost/get-quote/tester-advanced.php
 */

$base = "http://localhost/get-quote/public";

header("Content-Type: text/html");

function check_url($url) {
    $start = microtime(true);
    $headers = @get_headers($url);
    $time = round((microtime(true) - $start) * 1000);
    
    if (!$headers) return ["fail", "No response", $time];
    
    $status = $headers[0];
    if (strpos($status, "200") !== false) return ["pass", $status, $time];
    return ["fail", $status, $time];
}

function check_api_json($url) {
    $json = @file_get_contents($url);
    if (!$json) return ["fail", "No response"];

    $decoded = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return ["fail", "Invalid JSON: " . json_last_error_msg()];
    }

    if (!is_array($decoded)) return ["fail", "Not an array"];

    // check required fields
    $required = ["id", "name", "category", "partNo", "price"];

    foreach ($required as $field) {
        if (!isset($decoded[0][$field])) return ["fail", "Missing field: $field"];
    }

    return ["pass", "Valid JSON"];
}

function test_session() {
    session_start();
    $_SESSION['test'] = "OK";
    session_write_close();
    session_start();
    return isset($_SESSION['test']) ? ["pass", "Session working"] : ["fail", "Session failure"];
}

function test_db_schema() {
    require_once __DIR__ . "/src/database/connection.php";
    $pdo = db();

    $columns = $pdo->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);

    $required = ["id","category","name","part_no","main_price","discount_percent","price","labour_charges","wire_cost"];

    foreach ($required as $col) {
        if (!in_array($col, $columns)) {
            return ["fail", "Missing column: $col"];
        }
    }

    return ["pass", "DB schema OK"];
}

function test_security_headers($url) {
    $headers = @get_headers($url, 1);
    if (!$headers) return ["fail", "No headers"];

    $required = ["Content-Type","X-Frame-Options","X-Content-Type-Options"];
    foreach ($required as $h) {
        if (!isset($headers[$h])) return ["fail", "Missing security header: $h"];
    }

    return ["pass", "Headers OK"];
}

function file_perms_ok($path) {
    return is_readable($path) ? ["pass","Readable"] : ["fail","Not readable"];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Advanced Tester</title>
    <style>
        body { background:#111; color:#eee; font-family:Arial; padding:20px; }
        .pass { color:#0f0; font-weight:bold; }
        .fail { color:#f00; font-weight:bold; }
        .box { margin-bottom:30px; padding:20px; background:#222; border-radius:10px; }
        h2 { color:#0ff; }
        table { width:100%; border-collapse:collapse; }
        td { padding:6px; border-bottom:1px solid #333; }
    </style>
</head>
<body>

<h1>🚀 Advanced Tester for Get-Quote</h1>

<div class="box">
    <h2>🌐 Endpoint Performance + Status</h2>
    <table>
        <?php
        $urls = [
            "Admin Index" => "$base/admin/views/index.php",
            "Admin Products API" => "$base/admin/api/get-products.php",
            "User Index" => "$base/user/views/index.html",
            "User Products API" => "$base/user/api/get-products.php",
        ];
        
        foreach ($urls as $label => $url):
            [$status, $msg, $time] = check_url($url);
        ?>
        <tr>
            <td><?= $label ?></td>
            <td class="<?= $status ?>"><?= strtoupper($status) ?></td>
            <td><?= $msg ?></td>
            <td><?= $time ?> ms</td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="box">
    <h2>🧪 JSON Validation (API)</h2>
    <?php
        [$s,$m] = check_api_json("$base/admin/api/get-products.php");
    ?>
    <p class="<?= $s ?>"><?= strtoupper($s) ?> — <?= $m ?></p>
</div>

<div class="box">
    <h2>🗄 Database Schema Check</h2>
    <?php [$s, $m] = test_db_schema(); ?>
    <p class="<?= $s ?>"><?= strtoupper($s) ?> — <?= $m ?></p>
</div>

<div class="box">
    <h2>📦 File Permission Test</h2>
    <?php
    $checkFiles = [
        "src/database/connection.php",
        "public/admin/views/index.php",
        "public/user/views/index.html",
    ];

    foreach ($checkFiles as $f):
        [$s,$m] = file_perms_ok(__DIR__ . "/$f");
    ?>
    <p><?= $f ?> — <span class="<?= $s ?>"><?= strtoupper($s) ?></span> (<?= $m ?>)</p>
    <?php endforeach; ?>
</div>

<div class="box">
    <h2>🔐 Security Header Check</h2>
    <?php
    [$s,$m] = test_security_headers("$base/admin/api/get-products.php");
    ?>
    <p class="<?= $s ?>"><?= strtoupper($s) ?> — <?= $m ?></p>
</div>

<div class="box">
    <h2>🧬 Session Test</h2>
    <?php
    [$s,$m] = test_session();
    ?>
    <p class="<?= $s ?>"><?= strtoupper($s) ?> — <?= $m ?></p>
</div>

<h2>✔ All advanced tests completed</h2>

</body>
</html>
