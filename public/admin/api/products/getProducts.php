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

// Keep OLD UI-compatible format
$sql = "SELECT id, category, name, part_no, main_price, discount_percent, price, labour_charges, wire_cost
        FROM products
        WHERE deleted_at IS NULL
        ORDER BY category, name";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert rows to camelCase fields (UI expects this)
$converted = [];
foreach ($data as $row) {
    $converted[] = [
        "id" => (int)$row["id"],
        "category" => $row["category"],
        "name" => $row["name"],
        "partNo" => $row["part_no"],
        "mainPrice" => (float)$row["main_price"],
        "discountPercent" => (float)$row["discount_percent"],
        "price" => (float)$row["price"],
        "labourCharges" => (float)$row["labour_charges"],
        "wireCost" => (float)$row["wire_cost"]
    ];
}

// IMPORTANT: Return EXACT old format (NO jsonSuccess wrapper)
echo json_encode($converted);
exit;
?>
