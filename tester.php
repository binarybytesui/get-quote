<?php
/**
 * Browser-Based Project Tester
 * URL: http://localhost/get-quote/tester.php
 */

$base = "http://localhost/get-quote/public";

$tests = [
    "Admin Index"         => "$base/admin/views/index.php",
    "Admin Quote"         => "$base/admin/views/quote.php",
    "Admin API Auth"      => "$base/admin/api/auth.php",
    "Admin API Products"  => "$base/admin/api/get-products.php",

    "User Index"          => "$base/user/views/index.html",
    "User Quote"          => "$base/user/views/quote.html",
    "User API Products"   => "$base/user/api/get-products.php",

    "Admin CSS"           => "$base/admin/assets/css/style.css",
    "User CSS"            => "$base/user/assets/css/style.css",
];

function testUrl($url) {
    $headers = @get_headers($url);
    if (!$headers) return ["fail", "No response"];
    
    $status = $headers[0];
    if (strpos($status, "200") !== false) {
        return ["pass", $status];
    } else {
        return ["fail", $status];
    }
}

function testDB() {
    try {
        require_once __DIR__ . "/src/database/connection.php";
        $pdo = db();
        $pdo->query("SELECT 1");
        return ["pass", "Database OK"];
    } catch (Exception $e) {
        return ["fail", $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Get-Quote Project Tester</title>
    <style>
        body { background:#111; color:#fff; font-family:Arial; padding:20px; }
        .box { background:#222; padding:15px; margin-bottom:20px; border-radius:8px; }
        h2 { color:#0ff; }
        .pass { color:#0f0; font-weight:bold; }
        .fail { color:#f00; font-weight:bold; }
        table { width:100%; margin-top:10px; border-collapse:collapse; }
        td { padding:8px; border-bottom:1px solid #444; }
        .url { color:#0af; }
    </style>
</head>
<body>

<h1>🚀 Get-Quote Project Tester</h1>

<div class="box">
    <h2>🌐 URL Tests</h2>
    <table>
        <?php foreach ($tests as $name => $url): 
            [$status, $msg] = testUrl($url);
        ?>
        <tr>
            <td><?= $name ?></td>
            <td class="url"><?= $url ?></td>
            <td class="<?= $status ?>"><?= strtoupper($status) ?></td>
            <td><?= $msg ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="box">
    <h2>🗄 Database Test</h2>
    <?php [$dbStatus, $dbMsg] = testDB(); ?>
    <p class="<?= $dbStatus ?>"><?= strtoupper($dbStatus) ?> — <?= $dbMsg ?></p>
</div>

<div class="box">
    <h2>📁 Essential File Check</h2>
    <table>
        <?php
        $files = [
            "src/database/connection.php",
            "public/admin/api/get-products.php",
            "public/user/api/get-products.php",
            "public/admin/views/index.php",
            "public/user/views/index.html",
            "src/security/credentials.json",
        ];

        foreach ($files as $file):
            $exists = file_exists(__DIR__ . "/$file");
        ?>
        <tr>
            <td><?= $file ?></td>
            <td class="<?= $exists ? 'pass' : 'fail' ?>">
                <?= $exists ? 'FOUND' : 'MISSING' ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<h2>✔ All tests complete.</h2>

</body>
</html>
