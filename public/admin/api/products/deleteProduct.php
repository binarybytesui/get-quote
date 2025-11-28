<?php
// /public/admin/api/products/deleteProduct.php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Admin authentication required"]);
    exit;
}

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("X-XSS-Protection: 1; mode=block");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . "/../../../../src/database/connection.php";
require_once __DIR__ . "/../../../../src/helpers/response.php";
require_once __DIR__ . "/../../../../src/helpers/csrf.php";
require_once __DIR__ . "/../../../../src/helpers/sanitize.php";

$pdo = db();

// read body
$input = $_POST;
$raw = file_get_contents('php://input');
if (empty($input) && $raw) {
    $json = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $input = $json;
    }
}

// CSRF
$csrf = $input['csrf'] ?? ($_POST['csrf'] ?? '');
if (!validateCsrfToken($csrf)) {
    jsonError("Invalid CSRF token", 403);
}

$id = isset($input['id']) ? cleanInt($input['id']) : 0;
if ($id <= 0) jsonError("Invalid product id", 400);

// soft delete: set deleted_at
$stmt = $pdo->prepare("UPDATE products SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL");
$ok = $stmt->execute([$id]);

if (!$ok) jsonError("Failed to delete product", 500);
if ($stmt->rowCount() === 0) {
    jsonError("Product not found or already deleted", 404);
}

jsonSuccess([], "Product deleted (soft)");
