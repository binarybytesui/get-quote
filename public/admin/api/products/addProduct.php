<?php
// /public/admin/api/products/addProduct.php
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
require_once __DIR__ . "/../../../../src/helpers/sanitize.php";
require_once __DIR__ . "/../../../../src/helpers/price.php";
require_once __DIR__ . "/../../../../src/helpers/csrf.php";
require_once __DIR__ . "/../../../../src/validators/productValidator.php";

$pdo = db();

// read body (support JSON or form-data)
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

// sanitize
$category = cleanString($input['category'] ?? '');
$name = cleanString($input['name'] ?? '');
$partNo = cleanString($input['partNo'] ?? $input['part_no'] ?? '');
$mainPrice = cleanFloat($input['mainPrice'] ?? $input['main_price'] ?? 0);
$discountPercent = cleanFloat($input['discountPercent'] ?? $input['discount_percent'] ?? 0);
$labourCharges = cleanFloat($input['labourCharges'] ?? $input['labour_charges'] ?? 0);
$wireCost = cleanFloat($input['wireCost'] ?? $input['wire_cost'] ?? 0);
$extras = isset($input['extras']) ? cleanString($input['extras']) : null;

// validate
$validation = validateProduct([
    "category" => $category,
    "name" => $name,
    "mainPrice" => $mainPrice,
    "discountPercent" => $discountPercent
]);
if ($validation !== true) {
    jsonError($validation, 400);
}

$price = calculatePrice($mainPrice, $discountPercent);

// insert
$sql = "INSERT INTO products
        (category, name, part_no, main_price, discount_percent, price, labour_charges, wire_cost, extras, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $pdo->prepare($sql);
$ok = $stmt->execute([
    $category,
    $name,
    $partNo,
    $mainPrice,
    $discountPercent,
    $price,
    $labourCharges,
    $wireCost,
    $extras
]);

if (!$ok) {
    jsonError("Failed to insert product", 500);
}

$insertId = (int)$pdo->lastInsertId();

jsonSuccess(["id" => $insertId], "Product added successfully");
