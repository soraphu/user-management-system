<?php
require_once 'db_connect.php';
require_once 'handlers/account_access.php';
require_once 'handlers/account_management.php';

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

// Get the path (e.g., /api/users )
$requestUrl = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Clean it up (remove query strings like ?id=1)
$path = parse_url($requestUrl, PHP_URL_PATH);
// Remove the trailing slash if it exists (but keep it if it's just "/")
$path = ($path !== '/') ? rtrim($path, '/') : $path;

$pathSegments = explode('/', trim($path, '/'));

header('Content-Type: application/json');

if ($path === "/") {
    echo json_encode(
        [
            "info" => [
                "title" => "Welcome to my User Mangement System API.",
                "version" => "1.0.0",
                "note" => "To see API tutorial go to https://domain.com/api"
            ],
        ]
    );
    exit;
}//root path

if ($path === "/api") { //url: /api - show API info.
    echo json_encode([
        "info" => [
            "title" => "User Management System API",
            "base_url" => "https://domain.com/api/v1",
            "version" => "1.0.0"
        ],
        "endpoints" => [
            "/auth" => [
                "/register" => [
                    "description" => "Register a new user.",
                    "method" => "POST",
                    "requestBody" => [
                        "content_type" => "application/json",
                        "schema" => [
                            "username" => "string (min: 3)",
                            "email" => "string (valid email)",
                            "password" => "string (min: 8)"
                        ]
                    ],
                    "response" => [
                        "201" => ["success" => true, "message" => "User registered."],
                        "400" => ["success" => false, "message" => "Password must be at least 8 characters."],
                        "409" => ["success" => false, "message" => "This email already signup."],
                        "500" => ["success" => false, "message" => "Internal server error."]
                    ]
                ],

                "/login" => [
                    "description" => "Authenticate user and issue tokens.",
                    "method" => "POST",
                    "requestBody" => [
                        "content_type" => "application/json",
                        "schema" => [
                            "email" => "string (required)",
                            "password" => "string (required)"
                        ]
                    ],
                    "response" => [
                        "200" => [
                            "success" => true,
                            "access_token" => "string (JWT - Store in JS Memory)",
                            "note" => "Refresh token is automatically set via HttpOnly Cookie."
                        ],
                        "401" => ["success" => false, "message" => "Invalid credentials."],
                        "500" => ["success" => false, "message" => "Database connection error."]
                    ]
                ],

                "/refresh-token" => [
                    "description" => "Exchange a Refresh Token (from cookie) for a new Access Token.",
                    "method" => "POST",
                    "note" => "Does not require a request body. Reads 'refresh_token' cookie automatically.",
                    "response" => [
                        "200" => [
                            "success" => true,
                            "access_token" => "string (New JWT)"
                        ],
                        "401" => ["success" => false, "message" => "Session expired. Please login again."],
                        "500" => ["success" => false, "message" => "Internal server error."]
                    ]
                ],

                "/password/forget" => [
                    "description" => "Initiate password reset flow by sending an email.",
                    "method" => "POST",
                    "requestBody" => [
                        "content_type" => "application/json",
                        "schema" => ["email" => "string (valid email)"]
                    ],
                    "response" => [
                        "200" => ["success" => true, "message" => "Reset link sent to your email."],
                        "404" => ["success" => false, "message" => "User with this email not found."],
                        "500" => ["success" => false, "message" => "Internal server error."]
                    ]
                ],

                "/password/reset" => [
                    "description" => "Update password using a valid reset token.",
                    "method" => "POST",
                    "requestBody" => [
                        "content_type" => "application/json",
                        "schema" => [
                            "token" => "string (required)",
                            "new_password" => "string (min: 8)"
                        ]
                    ],
                    "response" => [
                        "200" => ["success" => true, "message" => "Password updated successfully."],
                        "400" => ["success" => false, "message" => "Invalid or expired token."],
                        "500" => ["success" => false, "message" => "Internal server error."]
                    ]
                ],

                "/logout" => [
                    "description" => "Revoke refresh token and clear cookies.",
                    "method" => "POST",
                    "response" => [
                        "200" => ["success" => true, "message" => "Logged out. Database record deleted."],
                        "500" => ["success" => false, "message" => "Could not complete logout on server."]
                    ]
                ]
            ]
        ]
    ]);
    exit;
}//api

$first2PathSegments = "/{$pathSegments[0]}/{$pathSegments[1]}/{$pathSegments[2]}";

if ($first2PathSegments === "/api/auth/v1") {
    $id = $pathSegments[3] ?? null; //Get user ID from URL if exists.
    $service = $pathSegments[3] ?? null; //Get service from URL if exists.

    switch ($service) {

        //Handle register.
        case 'register':
            ensureReqMethod("POST");
            handleRegister($pdo);
            exit;

        case 'login': //Handle login.
            ensureReqMethod("POST");
            handleLogin($pdo);
            exit;

        case 'password': //Handle password reset.
            ensureReqMethod("POST");
            if ($pathSegments[4] === 'forget') {
                handleForgetPassword($pdo);
                exit;
            }
            if ($pathSegments[4] === 'reset') {
                handleResetPassword($pdo);
                exit;
            }

        case 'email': //Handle email verification.
            ensureReqMethod("POST");
            if ($pathSegments[4] === 'verify-request') {
                handleVerifyEmailRequest($pdo);
                exit;
            }

            if ($pathSegments[4] === 'verified') {
                handleVerifiedEmail($pdo);
                exit;
            }

        case 'inbox':
            ensureReqMethod("POST");
            handleGetInbox($pdo);
            exit;

        default:
            pageNotFoundRespond();
            exit;

    }//switch-case
} //Main router for API endpoints.

pageNotFoundRespond();

function ensureReqMethod($expectMethod)
{
    $request_method = $_SERVER['REQUEST_METHOD'];

    if ($request_method !== $expectMethod) {
        http_response_code(405);
        echo json_encode([
            "success" => false,
            "message" => "Method not allowed."
        ]);
        exit;
    }//Validation.
} //Handle method not allowed.

function pageNotFoundRespond()
{
    http_response_code(404);
    echo json_encode(["error" => "Page not found."]);
} //Handle page not found.