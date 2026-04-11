<?php
require_once 'db.php';
// Get the path (e.g., /api/users )
$request = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// echo "I am connected to: " . $database . " at " . $host . " with user " . $user . "\n";

// Clean it up (remove query strings like ?id=1)
$path = parse_url($request, PHP_URL_PATH);

header('Content-Type: application/json');

// Now do "whatever you want" based on the path
switch ($path) {
    case '/api':
        if ($request_method == "GET") {
            echo json_encode([
                "message" => "Welcome to the API of User Management System!",
                "endpoints" => [
                    "/api/user-login/:id - Get login user id",
                    "/api/user-register - Sign up user",
                    "/api/user-reset-password - Reset user password"
                ]
            ]);
        } else {
            faultRequestMethod();
        }
        break; //End of case

    case '/api/user-login':
        if ($request_method == "GET") {
            echo json_encode(["status" => "Get login running"]);
        } else {
            faultRequestMethod();
        }
        break; //End of case

    case '/api/user-register':
        if ($request_method == "POST") {
            echo json_encode(["status" => "Post register running"]);
        } else {
            faultRequestMethod();
        }
        break; //End of case

    case '/api/user-reset-password':
        if ($request_method == "POST") {
            echo json_encode(["status" => "Post reset password running"]);
        } else {
            faultRequestMethod();
        }
        break; //End of case

    default:
        http_response_code(404);
        echo "404 - Page not found";
        break;
}

function faultRequestMethod()
{
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
} //End of function

function login() {}

function register() {}

function resetPassword() {}

function getUserById($id) {}
