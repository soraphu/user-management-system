<?php
// Get the path (e.g., /api/users or /shop/products)
$request = $_SERVER['REQUEST_URI'];

// Clean it up (remove query strings like ?id=1)
$path = parse_url($request, PHP_URL_PATH);

// Now do "whatever you want" based on the path
switch ($path) {
    case '/':
        header('Content-Type: application/json');
        break;

    case '/api/status':
        header('Content-Type: application/json');
        echo json_encode(["status" => "running"]);
        break;

    case '/api/save-sensor':
        // Logic for your ESP32 project
        header('Content-Type: application/json');
        echo "Data saved!";
        break;

    default:
        http_response_code(404);
        echo "404 - Page not found";
        break;
}
