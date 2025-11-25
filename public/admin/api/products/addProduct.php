<?php
session_start();

// ADMIN PROTECTION
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403);
    echo json_encode(["error" => "Admin authentication required"]);
    exit;
}

header("Content-Type: application/json");
require_once __DIR__ . "/../../../../src/database/connection.php";
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request"]);
    exit;
}

// Sanitize input
$category = trim($_POST["category"] ?? "");
$name = trim($_POST["name"] ?? "");
$partNo = trim($_POST["partNo"] ?? "");
$mainPrice = $_POST["mainPrice"] ?? 0;
$discountPercent = $_POST["discountPercent"] ?? 0;
$labourCharges = $_POST["labourCharges"] ?? 0;
$wireCost = $_POST["wireCost"] ?? 0;

$price = $mainPrice - ($mainPrice * ($discountPercent / 100));

// Insert
$sql = "INSERT INTO products 
(category, name, part_no, main_price, discount_percent, price, labour_charges, wire_cost)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);

$ok = $stmt->execute([$category, $name, $partNo, $mainPrice, $discountPercent, $price, $labourCharges, $wireCost]);

echo json_encode(["success" => $ok]);
?>
