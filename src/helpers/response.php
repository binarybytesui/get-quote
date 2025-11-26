<?php
// /src/helpers/response.php
function jsonSuccess($data = [], $message = "") {
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode([
        "success" => true,
        "message" => $message,
        "data" => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonError($message, $code = 400) {
    http_response_code($code);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode([
        "success" => false,
        "error" => $message,
        "code" => $code
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
