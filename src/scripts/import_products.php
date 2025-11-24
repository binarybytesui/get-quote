<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../database/connection.php";
$pdo = db();

$jsonPath = "/../json/products.json";

if (!file_exists($jsonPath)) {
    die("products.json not found");
}

$data = json_decode(file_get_contents($jsonPath), true);
if (!$data) {
    die("Invalid JSON format");
}

$sql = "INSERT INTO products 
        (category, name, part_no, main_price, discount_percent, price, labour_charges, wire_cost, extras)
        VALUES (:category, :name, :part_no, :main_price, :discount_percent, :price, :labour_charges, :wire_cost, :extras)";

$stmt = $pdo->prepare($sql);

$count = 0;

foreach ($data as $category => $items) {
    foreach ($items as $p) {
        $stmt->execute([
            ":category" => $category,
            ":name" => $p["name"] ?? "",
            ":part_no" => $p["partNo"] ?? "",
            ":main_price" => $p["Main_Price"] ?? null,
            ":discount_percent" => $p["Discount_in_%"] ?? null,
            ":price" => $p["price"] ?? null,
            ":labour_charges" => $p["Labour_Charges"] ?? null,
            ":wire_cost" => $p["Wire_Cost"] ?? null,
            ":extras" => json_encode($p)
        ]);
        $count++;
    }
}

echo "Imported $count products successfully!";
