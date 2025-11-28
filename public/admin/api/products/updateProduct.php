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
require_once __DIR__ . "/../../../../src/helpers/price.php";
require_once __DIR__ . "/../../../../src/helpers/csrf.php";

$allowed = require __DIR__ . "/../../../../src/config/allowedProductFields.php";

$pdo = db();

// Read input
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

// Determine if inline update (field + value)
if (isset($input["field"])) {
    $field = $input["field"];
    $value = $input["value"] ?? "";

    // Convert camelCase → snake_case
    $camelMap = [
        "partNo" => "part_no",
        "mainPrice" => "main_price",
        "discountPercent" => "discount_percent",
        "labourCharges" => "labour_charges",
        "wireCost" => "wire_cost"
    ];
    if (isset($camelMap[$field])) $field = $camelMap[$field];

    if (!in_array($field, $allowed)) {
        echo json_encode(["success" => false, "error" => "Invalid field"]);
        exit;
    }

    // Price recalculation needed?
    if ($field === "main_price" || $field === "discount_percent") {
        $stmt = $pdo->prepare("SELECT main_price, discount_percent FROM products WHERE id=? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            echo json_encode(["success" => false, "error" => "Product not found"]);
            exit;
        }

        $mainPrice = ($field === "main_price") ? cleanFloat($value) : $row["main_price"];
        $discount  = ($field === "discount_percent") ? cleanFloat($value) : $row["discount_percent"];
        $newPrice = calculatePrice($mainPrice, $discount);

        $stmt = $pdo->prepare("UPDATE products SET $field=?, price=? WHERE id=?");
        $stmt->execute([cleanFloat($value), $newPrice, $id]);

        echo json_encode(["success" => true]);
        exit;
    }

    // Normal inline update
    $stmt = $pdo->prepare("UPDATE products SET $field=? WHERE id=?");
    $stmt->execute([cleanString($value), $id]);

    echo json_encode(["success" => true]);
    exit;
}

// MULTI-FIELD UPDATE (from edit form)
$updates = [];
$params = [];

foreach ($allowed as $f) {
    if (isset($input[$f])) {
        $raw = $input[$f];
        $value = is_numeric($raw) ? cleanFloat($raw) : cleanString($raw);
        $updates[] = "$f=?";
        $params[] = $value;
    }
}

if (!$updates) {
    echo json_encode(["success" => false, "error" => "No valid fields provided"]);
    exit;
}

// Recalc price if necessary
if (isset($input["main_price"]) || isset($input["discount_percent"])) {
    $stmt = $pdo->prepare("SELECT main_price, discount_percent FROM products WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    $mainPrice = isset($input["main_price"]) ? cleanFloat($input["main_price"]) : $row["main_price"];
    $discount  = isset($input["discount_percent"]) ? cleanFloat($input["discount_percent"]) : $row["discount_percent"];
    $newPrice  = calculatePrice($mainPrice, $discount);

    $updates[] = "price=?";
    $params[] = $newPrice;
}

$params[] = $id;

$sql = "UPDATE products SET " . implode(", ", $updates) . " WHERE id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode(["success" => true]);
?>
