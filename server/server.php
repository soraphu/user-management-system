<?php
// Get the path (e.g., /api/users or /shop/products)
$request = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Clean it up (remove query strings like ?id=1)
$path = parse_url($request, PHP_URL_PATH);

header('Content-Type: application/json');

// Now do "whatever you want" based on the path
switch ($path) {
    case '/api':
        if ($request_method == "GET") {
            echo json_encode([
                "message" => "Welcome to the PHP server!",
                "endpoints" => [
                    "/api/user-login/:id - Get login user id",
                    "/api/user-register - Sign up user",
                    "/api/user-reset-password - Reset user password"
                ]
            ]);
        } else {
            faultMethod();
        }
        break; //End of case

    case '/api/user-login':
        if ($request_method == "GET") {
            echo json_encode(["status" => "Get login running"]);
        } else {
            faultMethod();
        }
        break; //End of case

    case '/api/user-register':
        if ($request_method == "POST") {
            echo json_encode(["status" => "Post register running"]);
        } else {
            faultMethod();
        }
        break; //End of case

    case '/api/user-reset-password':
        if ($request_method == "POST") {
            echo json_encode(["status" => "Post reset password running"]);
        } else {
            faultMethod();
        }
        break; //End of case

    default:
        http_response_code(404);
        echo "404 - Page not found";
        break;
}

function faultMethod()
{
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}//End of function