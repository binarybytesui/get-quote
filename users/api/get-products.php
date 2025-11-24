<?php
header("Content-Type: application/json");
require_once "../../db/connection.php";
$pdo = db();

$category = $_GET["category"] ?? null;

if ($category) {
    $stmt = $pdo->prepare(
        "SELECT id, category, name, part_no 
         FROM products 
         WHERE category=? 
         ORDER BY name"
    );
    $stmt->execute([$category]);
    echo json_encode($stmt->fetchAll());
    exit;
}

$stmt = $pdo->query(
    "SELECT id, category, name, part_no 
     FROM products 
     ORDER BY category, name"
);
echo json_encode($stmt->fetchAll());
