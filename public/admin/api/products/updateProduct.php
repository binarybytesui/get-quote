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
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

header("Content-Type: application/json");

require_once __DIR__ . "/../../../../src/database/connection.php";
require_once __DIR__ . "/../../../../src/helpers/sanitize.php";
require_once __DIR__ . "/../../../../src/helpers/price.php";
require_once __DIR__ . "/../../../../src/helpers/csrf.php";

$allowedFields = require __DIR__ . "/../../../../src/config/allowedProductFields.php";

$pdo = db();

// Read body JSON or POST
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
    echo json_encode(["success" => false, "error" => "Invalid product ID"]);
    exit;
}

// Inline update: field + value
if (isset($input["field"])) {

    $field = $input["field"];
    $value = $input["value"] ?? "";

    // Map camelCase → snake_case
    $camelMap = [
        "partNo" => "part_no",
        "mainPrice" => "main_price",
        "discountPercent" => "discount_percent",
        "labourCharges" => "labour_charges",
        "wireCost" => "wire_cost",
        "extras" => "extras",
    ];
    if (isset($camelMap[$field])) $field = $camelMap[$field];

    // Validate field
    if (!in_array($field, $allowedFields)) {
        echo json_encode(["success" => false, "error" => "Invalid field"]);
        exit;
    }

    // Fetch current values
    $stmt = $pdo->prepare("SELECT main_price, discount_percent, labour_charges, wire_cost 
                           FROM products WHERE id=? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) {
        echo json_encode(["success" => false, "error" => "Product not found"]);
        exit;
    }

    // Correct values for recalculation
    $mainPrice = ($field === "main_price") ? cleanFloat($value) : $row["main_price"];
    $discount  = ($field === "discount_percent") ? cleanFloat($value) : $row["discount_percent"];
    $labour    = ($field === "labour_charges") ? cleanFloat($value) : $row["labour_charges"];
    $wire      = ($field === "wire_cost") ? cleanFloat($value) : $row["wire_cost"];

    // Recalculate price using NEW FORMULA
    $newPrice = calculatePrice($mainPrice, $discount, $labour, $wire);

    // Update
    $stmt = $pdo->prepare("UPDATE products SET $field=?, price=? WHERE id=?");
    $stmt->execute([cleanString($value), $newPrice, $id]);

    echo json_encode(["success" => true]);
    exit;
}

// ------------------------------------------------------
// FULL UPDATE (From Product Edit Form)
// ------------------------------------------------------

$updates = [];
$params = [];

$mainPrice = null;
$discountPercent = null;
$labourCharges = null;
$wireCost = null;

// Loop allowed fields
foreach ($allowedFields as $f) {
    if (isset($input[$f])) {

        $val = $input[$f];
        $clean = is_numeric($val) ? cleanFloat($val) : cleanString($val);

        $updates[] = "$f=?";
        $params[] = $clean;

        // track fields for recalculation
        if ($f === "main_price") $mainPrice = $clean;
        if ($f === "discount_percent") $discountPercent = $clean;
        if ($f === "labour_charges") $labourCharges = $clean;
        if ($f === "wire_cost") $wireCost = $clean;
    }
}

// If none provided
if (!$updates) {
    echo json_encode(["success" => false, "error" => "No valid fields provided"]);
    exit;
}

// Fetch current values to fill missing ones
$stmt = $pdo->prepare("SELECT main_price, discount_percent, labour_charges, wire_cost FROM products WHERE id=?");
$stmt->execute([$id]);
$existing = $stmt->fetch();

if (!$existing) {
    echo json_encode(["success" => false, "error" => "Product not found"]);
    exit;
}

$mainPrice = $mainPrice ?? $existing["main_price"];
$discountPercent = $discountPercent ?? $existing["discount_percent"];
$labourCharges = $labourCharges ?? $existing["labour_charges"];
$wireCost = $wireCost ?? $existing["wire_cost"];

// Final NEW PRICE CALCULATION
$newPrice = calculatePrice($mainPrice, $discountPercent, $labourCharges, $wireCost);

$updates[] = "price=?";
$params[] = $newPrice;

$params[] = $id;

// Final query
$sql = "UPDATE products SET " . implode(", ", $updates) . " WHERE id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode(["success" => true]);
?>
