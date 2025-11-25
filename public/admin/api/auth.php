<?php
session_start();

// ----- SECURITY HEADERS -----
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("X-XSS-Protection: 1; mode=block");
header("Content-Type: application/json; charset=UTF-8");



// ----- CORS HEADERS -----
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../src/database/connection.php';


// Define the path to your credentials file outside the public web root
//define('CREDENTIALS_FILE', '/get-quote/src/security/credentials.json'); // <--- IMPORTANT: Update this path!
define('CREDENTIALS_FILE', __DIR__ . '/../../../src/security/credentials.json');
//$credentialsFile = __DIR__ . '/../../../src/security/credentials.json';



// Function to safely read JSON from a file
function readJsonFile($filePath)
{
    if (!file_exists($filePath)) {
        error_log("File not found: " . $filePath);
        return false;
    }
    $content = file_get_contents($filePath);
    if ($content === false) {
        error_log("Failed to read file: " . $filePath);
        return false;
    }
    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        return false;
    }
    return $data;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $username = htmlspecialchars(trim($_POST['username'] ?? ''));
        $password = htmlspecialchars(trim($_POST['password'] ?? ''));       
        //$username = trim($username);
        //$password = trim($password);

        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
            exit;
        }

        $credentials = readJsonFile(CREDENTIALS_FILE);

        if ($credentials && $username === $credentials['username']) {
            // Security Comment: password_verify() securely compares the provided password with the hashed password.
            // It automatically handles the salting and hashing, preventing timing attacks.
            if (password_verify($password, $credentials['password'])) {
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $username;
                session_regenerate_id(true); // Security: Regenerate session ID to prevent session fixation
                echo json_encode(['success' => true, 'message' => 'Login successful!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        }
        exit;

    } elseif ($action === 'logout') {
        // Unset all session variables
        $_SESSION = array();

        // Destroy the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Destroy the session
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
        exit;
    }
}

// If accessed directly or with an invalid method/action, deny access
echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
exit;
?>