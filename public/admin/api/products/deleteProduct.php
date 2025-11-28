<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(["success" => false, "error" => "Admin authentication required"]);
    exit;
}

// SECURITY HEADERS
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("X-XSS-Protection: 1; mode=block");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

header("Content-Type: application/json");

require_once __DIR__ . "/../../../../src/database/connection.php";
require_once __DIR__ . "/../../../../src/helpers/sanitize.php";
require_once __DIR__ . "/../../../../src/helpers/csrf.php";

$pdo = db();

// Read body
$input = $_POST;
$raw = file_get_contents("php://input");
if (empty($input) && $raw) {
    $json = json_decode($raw, true);
    if ($json !== null) $input = $json;
}

// CSRF
$csrf = $input["csrf"] ?? "";
if (!validateCsrfToken($csrf)) {
    echo json_encode(["success" => false, "error" => "Invalid CSRF token"]);
    exit;
}

$id = cleanInt($input["id"] ?? 0);
if ($id <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid product id"]);
    exit;
}

// Soft delete
$stmt = $pdo->prepare("UPDATE products SET deleted_at = NOW() WHERE id=? AND deleted_at IS NULL");
$stmt->execute([$id]);

if ($stmt->rowCount() === 0) {
    echo json_encode(["success" => false, "error" => "Product not found"]);
    exit;
}

echo json_encode(["success" => true]);
?>
