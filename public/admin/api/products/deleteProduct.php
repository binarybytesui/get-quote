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

$id = intval($_POST["id"] ?? 0);

$stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
$ok = $stmt->execute([$id]);

echo json_encode(["success" => $ok]);
?>
