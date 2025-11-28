<?php
// /public/admin/api/products/updateProduct.php
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

// allowed fields config
$allowedFields = require __DIR__ . "/../../../../src/config/allowedProductFields.php";

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

// Expect at minimum: id and a field to update.
// Support two styles:
// 1) Inline: id, field, value
// 2) Full update: id with multiple fields

$id = isset($input['id']) ? cleanInt($input['id']) : 0;
if ($id <= 0) {
    jsonError("Invalid product id", 400);
}

// mapping camelCase -> snake_case for convenience
$fieldMap = [
    "partNo" => "part_no",
    "mainPrice" => "main_price",
    "discountPercent" => "discount_percent",
    "labourCharges" => "labour_charges",
    "wireCost" => "wire_cost",
    "extras" => "extras",
    "category" => "category",
    "name" => "name"
];

// If input has 'field' and 'value' do simple inline update
if (isset($input['field']) && array_key_exists('value', $input)) {
    $inField = $input['field'];
    $inValue = $input['value'];

    // Map to snake_case if needed
    $field = isset($fieldMap[$inField]) ? $fieldMap[$inField] : $inField;

    if (!in_array($field, $allowedFields)) {
        jsonError("Invalid field: $field", 400);
    }

    // sanitize value based on field
    if (in_array($field, ["main_price", "discount_percent", "labour_charges", "wire_cost"])) {
        $value = cleanFloat($inValue);
    } else {
        $value = cleanString($inValue);
    }

    // If we update main_price or discount_percent we need to recalc price
    if ($field === "main_price" || $field === "discount_percent") {
        // fetch current other value
        $stmt = $pdo->prepare("SELECT main_price, discount_percent FROM products WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            jsonError("Product not found", 404);
        }

        $mainPrice = ($field === "main_price") ? $value : (float)$row["main_price"];
        $discount = ($field === "discount_percent") ? $value : (float)$row["discount_percent"];
        $newPrice = calculatePrice($mainPrice, $discount);

        $sql = "UPDATE products SET {$field} = ?, price = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([$value, $newPrice, $id]);

        if (!$ok) jsonError("Update failed", 500);
        jsonSuccess([], "Product updated");
    } else {
        // normal single-field update
        $sql = "UPDATE products SET {$field} = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([$value, $id]);
        if (!$ok) jsonError("Update failed", 500);
        jsonSuccess([], "Product updated");
    }
}

// Otherwise handle full update: sanitize provided allowed fields and update set
$updates = [];
$params = [];
foreach ($fieldMap as $k => $mapped) {
    // allow both camelCase and snake_case keys
    if (isset($input[$k]) || isset($input[$mapped])) {
        $rawVal = $input[$k] ?? $input[$mapped];
        if (in_array($mapped, ["main_price", "discount_percent", "labour_charges", "wire_cost"])) {
            $val = cleanFloat($rawVal);
        } else {
            $val = cleanString($rawVal);
        }
        if (!in_array($mapped, $allowedFields)) continue;
        $updates[] = "{$mapped} = ?";
        $params[] = $val;
    }
}

if (empty($updates)) {
    jsonError("No updatable fields provided", 400);
}

// If main_price or discount_percent included, recalc price
$hasMain = false; $hasDiscount = false;
foreach ($updates as $u) {
    if (strpos($u, "main_price") !== false) $hasMain = true;
    if (strpos($u, "discount_percent") !== false) $hasDiscount = true;
}

if ($hasMain || $hasDiscount) {
    // fetch current ones (if not provided in $params we will use current)
    $stmt = $pdo->prepare("SELECT main_price, discount_percent FROM products WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) jsonError("Product not found", 404);

    // determine mainPrice and discount
    // build a key=>value map from updates array to find values easily
    // simpler: check input arrays
    $mainPrice = isset($input['mainPrice']) ? cleanFloat($input['mainPrice']) : (isset($input['main_price']) ? cleanFloat($input['main_price']) : (float)$row['main_price']);
    $discount = isset($input['discountPercent']) ? cleanFloat($input['discountPercent']) : (isset($input['discount_percent']) ? cleanFloat($input['discount_percent']) : (float)$row['discount_percent']);

    $newPrice = calculatePrice($mainPrice, $discount);
    $updates[] = "price = ?";
    $params[] = $newPrice;
}

// finalize update statement
$params[] = $id;
$sql = "UPDATE products SET " . implode(", ", $updates) . " WHERE id = ?";
$stmt = $pdo->prepare($sql);
$ok = $stmt->execute($params);
if (!$ok) jsonError("Failed to update product", 500);

jsonSuccess([], "Product updated");
