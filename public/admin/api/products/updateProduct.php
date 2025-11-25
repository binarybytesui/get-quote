<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403);
    echo json_encode(["error" => "Admin authentication required"]);
    exit;
}

header("Content-Type: application/json");

require_once __DIR__ . "/../../../../src/database/connection.php";
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid method"]);
    exit;
}

$id = intval($_POST["id"] ?? 0);
$field = $_POST["field"] ?? "";
$value = $_POST["value"] ?? "";

// Allowed fields
$allowed = [
    "category", "name", "part_no", "main_price",
    "discount_percent", "labour_charges", "wire_cost"
];

if (!in_array($field, $allowed)) {
    echo json_encode(["error" => "Invalid field"]);
    exit;
}

// Auto-calc price if needed
if ($field === "main_price" || $field === "discount_percent") {
    $stmt = $pdo->prepare("SELECT main_price, discount_percent FROM products WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    $mainPrice = ($field === "main_price") ? $value : $row["main_price"];
    $discount = ($field === "discount_percent") ? $value : $row["discount_percent"];

    $price = $mainPrice - ($mainPrice * ($discount / 100));

    $stmt = $pdo->prepare("UPDATE products SET $field=?, price=? WHERE id=?");
    $ok = $stmt->execute([$value, $price, $id]);

} else {
    $stmt = $pdo->prepare("UPDATE products SET $field=? WHERE id=?");
    $ok = $stmt->execute([$value, $id]);
}

echo json_encode(["success" => $ok]);
?>
