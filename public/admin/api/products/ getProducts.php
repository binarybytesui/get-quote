<?php
session_start();

// ADMIN PROTECTION
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403);
    echo json_encode(["error" => "Admin authentication required"]);
    exit;
}

// SECURITY HEADERS
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("X-XSS-Protection: 1; mode=block");

// CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header("Content-Type: application/json");

require_once __DIR__ . "/../../../../src/database/connection.php";
$pdo = db();

$sql = "SELECT * FROM products ORDER BY category, name";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert to camelCase
$converted = [];
foreach ($data as $row) {
    $converted[] = [
        "id" => $row["id"],
        "category" => $row["category"],
        "name" => $row["name"],
        "partNo" => $row["part_no"],
        "mainPrice" => $row["main_price"],
        "discountPercent" => $row["discount_percent"],
        "price" => $row["price"],
        "labourCharges" => $row["labour_charges"],
        "wireCost" => $row["wire_cost"]
    ];
}

echo json_encode($converted);
?>
