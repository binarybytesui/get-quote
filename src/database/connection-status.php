<?php
function db() {
    $host = "127.0.0.1";   // XAMPP / Localhost
    $dbname = "quote_db";
    $user = "root";
    $pass = "";  // Default XAMPP password is empty

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        // --- SUCCESS MESSAGE ---
        echo "✅ Database Connected Successfully!";
        return $pdo;

    } catch (Exception $e) {
        // --- FAILURE MESSAGE ---
        // We change the JSON output to plain text so you can read the error easily
        http_response_code(500);
        echo "❌ Database Connection Failed: " . $e->getMessage();
        exit;
    }
}

// Call the function to test it immediately
db();
?>