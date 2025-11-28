<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403);
    echo json_encode(["error"=>"Admin authentication required"]);
    exit;
}
header("Content-Type: application/json");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("X-XSS-Protection: 1; mode=block");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

require_once __DIR__ . "/../../../../src/database/connection.php";
$pdo = db();

$sql = "SELECT id, category, name, part_no, main_price, discount_percent, price, labour_charges, wire_cost, extras, deleted_at
        FROM products
        WHERE deleted_at IS NOT NULL
        ORDER BY deleted_at DESC, id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$converted = [];
foreach ($data as $row){
    $converted[] = [
        "id" => (int)$row["id"],
        "category" => $row["category"],
        "name" => $row["name"],
        "partNo" => $row["part_no"],
        "mainPrice" => (float)$row["main_price"],
        "discountPercent" => (float)$row["discount_percent"],
        "price" => (float)$row["price"],
        "labourCharges" => (float)$row["labour_charges"],
        "wireCost" => (float)$row["wire_cost"],
        "extras" => $row["extras"],
        "deletedAt" => $row["deleted_at"]
    ];
}

echo json_encode($converted);
exit;
