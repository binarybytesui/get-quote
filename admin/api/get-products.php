<?php
header("Content-Type: application/json");
require_once "../../db/connection.php";
$pdo = db();

$category = $_GET["category"] ?? null;

$sql = $category
    ? "SELECT * FROM products WHERE category=? ORDER BY name"
    : "SELECT * FROM products ORDER BY category, name";

$stmt = $pdo->prepare($sql);
$category ? $stmt->execute([$category]) : $stmt->execute();

$data = $stmt->fetchAll();

// Convert snake_case → camelCase for frontend compatibility
$converted = [];
foreach ($data as $row) {
    $converted[] = [
        "id" => $row["id"],
        "category" => $row["category"],
        "name" => $row["name"],
        "partNo" => $row["part_no"],                   // FIX
        "Main_Price" => $row["main_price"],            // FIX
        "Discount_in_%" => $row["discount_percent"],   // FIX
        "price" => $row["price"],                      // FIX
        "Labour_Charges" => $row["labour_charges"],    // FIX
        "Wire_Cost" => $row["wire_cost"],              // FIX
    ];
}

echo json_encode($converted);
