<?php
require_once 'db/db_connect.php';
require_once 'auth/handler.php';
require_once 'auth/respond_docs.php';

// Define which frontends are allowed to talk to this API
$allowed_origins = [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Check if the requester is in our "Trusted List"
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $origin);
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Credentials: true");
}

// Handle the "Preflight" OPTIONS request immediately
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

// Get the path (e.g., /api/users )
$requestUrl = $_SERVER['REQUEST_URI'];
// Clean it up (remove query strings like ?id=1)
$path = parse_url($requestUrl, PHP_URL_PATH);
// Remove the trailing slash if it exists (but keep it if it's just "/")
$path = ($path !== '/') ? rtrim($path, '/') : $path;

if ($path === "/") {
    respondRootDocs();
}//root docs

if ($path === "/api") { //url: /api - show API info.
    respondAPIDocs();
}//api docs

// Cut each path to array.
$pathSegments = explode('/', trim($path, '/'));
$first2PathSegments = "/{$pathSegments[0]}/{$pathSegments[1]}/{$pathSegments[2]}";

if ($first2PathSegments === "/api/v1/auth") {
    $service = $pathSegments[3] ?? null; //Get service from URL if exists.

    switch ($service) {

        //Handle register.
        case 'register':
            ensureReqMethod("POST");
            handleRegister($pdo);
            exit;

        //Handle login.
        case 'login':
            ensureReqMethod("POST");
            handleLogin($pdo);
            exit;

        //Handle password reset.
        case 'password':
            ensureReqMethod("POST");
            if ($pathSegments[4] === 'forget') {
                handleForgetPassword($pdo);
                exit;
            }
            if ($pathSegments[4] === 'reset') {
                handleResetPassword($pdo);
                exit;
            }

        //Handle email verification.
        case 'email':
            ensureReqMethod("POST");
            if ($pathSegments[4] === 'verify-request') {
                handleVerifyEmailRequest($pdo);
                exit;
            }

            if ($pathSegments[4] === 'verified') {
                handleVerifiedEmail($pdo);
                exit;
            }

        //Handle get inbox data.
        case 'inbox':
            ensureReqMethod("GET");
            handleGetInbox($pdo);
            exit;

        default:
            respondPageNotFound();
            exit;

    }//switch-case
} //Main router for API endpoints.

respondPageNotFound();