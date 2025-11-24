<?php
require_once __DIR__ . "/../database/connection.php";
$password = 'password'; // <--- The new password you want to use
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
echo "Hashed Password: " . $hashedPassword . "\n";
?>