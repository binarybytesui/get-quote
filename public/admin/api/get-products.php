<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403);
    echo json_encode(["error" => "Forbidden: Admin login required"]);
    exit;
}

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("X-XSS-Protection: 1; mode=block");

// ----- CORS HEADERS -----
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../../logs/error.log');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header("Content-Type: application/json");
require_once __DIR__ . "/../../../src/database/connection.php";
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
        "partNo" => $row["part_no"],
        "price" => $row["price"],
        "mainPrice" => $row["main_price"],
        "discountPercent" => $row["discount_percent"],
        "labourCharges" => $row["labour_charges"],
        "wireCost" => $row["wire_cost"]
    ];
}

echo json_encode($converted);
