<?php
session_start();

// Define the path to your credentials file outside the public web root
define('CREDENTIALS_FILE', '/Applications/XAMPP/xamppfiles/htdocs/get-quote/passwords/credentials.json'); // <--- IMPORTANT: Update this path!

header('Content-Type: application/json'); // Set header for JSON response

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
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Input validation and sanitization
        $username = trim($username);
        $password = trim($password);

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