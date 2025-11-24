<?php
header("Content-Type: application/json");
require_once "../../db/connection.php";
$pdo = db();

$category = $_GET["category"] ?? null;

if ($category) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category=? ORDER BY name");
    $stmt->execute([$category]);
    echo json_encode($stmt->fetchAll());
    exit;
}

$stmt = $pdo->query("SELECT * FROM products ORDER BY category, name");
echo json_encode($stmt->fetchAll());
