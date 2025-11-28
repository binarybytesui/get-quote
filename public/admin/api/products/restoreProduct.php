<?php
session_start();
header("Content-Type: application/json");
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(["success"=>false, "error"=>"Admin authentication required"]);
    exit;
}
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("X-XSS-Protection: 1; mode=block");

require_once __DIR__ . "/../../../../src/helpers/csrf.php";
require_once __DIR__ . "/../../../../src/helpers/sanitize.php";
require_once __DIR__ . "/../../../../src/database/connection.php";

$pdo = db();

// read JSON / POST
$input = $_POST;
$raw = file_get_contents('php://input');
if (empty($input) && $raw) {
    $json = json_decode($raw, true);
    if (json_last_error()===JSON_ERROR_NONE) $input = $json;
}

$csrf = $input['csrf'] ?? '';
if (!validateCsrfToken($csrf)) {
    echo json_encode(["success"=>false,"error"=>"Invalid CSRF token"]);
    exit;
}

$id = cleanInt($input['id'] ?? 0);
if ($id <= 0) { echo json_encode(["success"=>false,"error"=>"Invalid product id"]); exit; }

$stmt = $pdo->prepare("UPDATE products SET deleted_at = NULL WHERE id = ?");
$ok = $stmt->execute([$id]);
if (!$ok) { echo json_encode(["success"=>false,"error"=>"Restore failed"]); exit; }

echo json_encode(["success"=>true]);
exit;
