<?php
session_start();

// ADMIN PROTECTION
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

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

header("Content-Type: application/json");

require_once __DIR__ . "/../../../../src/database/connection.php";
require_once __DIR__ . "/../../../../src/helpers/sanitize.php";
require_once __DIR__ . "/../../../../src/helpers/price.php";
require_once __DIR__ . "/../../../../src/helpers/csrf.php";
require_once __DIR__ . "/../../../../src/validators/productValidator.php";

$pdo = db();

// Read input (POST or JSON)
$input = $_POST;
$raw = file_get_contents("php://input");
if (empty($input) && $raw) {
    $json = json_decode($raw, true);
    if ($json !== null) $input = $json;
}

// CSRF check
$csrf = $input["csrf"] ?? "";
if (!validateCsrfToken($csrf)) {
    echo json_encode(["success" => false, "error" => "Invalid CSRF token"]);
    exit;
}

// Sanitize
$category = cleanString($input["category"] ?? "");
$name = cleanString($input["name"] ?? "");
$partNo = cleanString($input["partNo"] ?? "");
$mainPrice = cleanFloat($input["mainPrice"] ?? 0);
$discountPercent = cleanFloat($input["discountPercent"] ?? 0);
$labourCharges = cleanFloat($input["labourCharges"] ?? 0);
$wireCost = cleanFloat($input["wireCost"] ?? 0);
$extras = $input["extras"] ?? null;

// Validate
$validation = validateProduct([
    "category" => $category,
    "name" => $name,
    "mainPrice" => $mainPrice,
    "discountPercent" => $discountPercent
]);

if ($validation !== true) {
    echo json_encode(["success" => false, "error" => $validation]);
    exit;
}

$price = calculatePrice($mainPrice, $discountPercent);

// Insert
$sql = "INSERT INTO products 
(category, name, part_no, main_price, discount_percent, price, labour_charges, wire_cost, extras, created_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $pdo->prepare($sql);
$ok = $stmt->execute([
    $category, $name, $partNo, $mainPrice,
    $discountPercent, $price, $labourCharges, $wireCost, $extras
]);

if (!$ok) {
    echo json_encode(["success" => false, "error" => "Database insert failed"]);
    exit;
}

echo json_encode([
    "success" => true,
    "id" => $pdo->lastInsertId()
]);
?>
