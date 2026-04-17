<?php
require_once 'db_connect.php';
require_once 'handlers/account_access.php';
require_once 'handlers/account_management.php';

// Get the path (e.g., /api/users )
$requestUrl = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Clean it up (remove query strings like ?id=1)
$path = parse_url($requestUrl, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

header('Content-Type: application/json');

if ($segments[0] === 'api' && $segments[1] === 'user') { //url: /api/users
    $id = $segments[3] ?? null; //Get user ID from URL if exists.

    switch ($segments[2]) {

        case 'register': //Handle register.
            if ($request_method !== 'POST') {
                handleMethodNotAllowed();
                exit;
            }
            handleRegister($pdo);
            break;

        case 'login': //Handle login.
            if ($request_method !== 'POST') {
                handleMethodNotAllowed();
                exit;
            }
            handleLogin($pdo);
            break;

        case 'password': //Handle password reset.
            if ($request_method !== 'POST') {
                handleMethodNotAllowed();
                exit;
            }

            if ($segments[3] === 'forget') {
                handleForgetPassword($pdo);
            } else if ($segments[3] === 'reset') {
                handleResetPassword($pdo);
            }
            break;

        case 'email': //Handle email verification.
            if ($request_method !== 'POST') {
                handleMethodNotAllowed();
                exit;
            }
            if ($segments[3] === 'verify') {
                handleVerifyEmailRequest($pdo);
                break;
            } else if ($segments[3] === 'verified') {
                handleVerifiedEmail($pdo);
                break;
            }

        default:
            handlePageNotFound();
            break;
    }
} else {
    handlePageNotFound();
} //Main router for API endpoints.

function handleMethodNotAllowed()
{
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
} //Handle method not allowed.

function handlePageNotFound()
{
    http_response_code(404);
    echo json_encode(["error" => "Page not found."]);
} //Handle page not found.