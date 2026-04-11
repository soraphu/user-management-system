<?php
require_once 'db.php';
// Get the path (e.g., /api/users )
$requestUrl = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Clean it up (remove query strings like ?id=1)
$path = parse_url($requestUrl, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

header('Content-Type: application/json');

if ($segments[0] === 'api' && $segments[1] === 'users') { //path: /api/users
    $id = $segments[2] ?? null;

    switch ($request_method) {
        case 'GET': //Login and respond user detail.
            if (idValidate($id)) {
                handleLogin($id, $pdo);
            }
            break;

        case 'POST': //Sign up user.
            handleRegister($pdo);
            break;

        case 'PUT': //Reset user password by user ID.
            if (idValidate($id)) {
                handleResetPassword($pdo);
            }
            break;

        case 'DELETE': //Delete user by user ID.
            if (idValidate($id)) {
                handleDeleteUser($id, $pdo);
            }
            break;

        default:
            handleFaultRequestMethod();
            break;
    }
} else {
    handlePageNotFound();
}

function idValidate($id)
{
    if ($id === null) {
        http_response_code(400);
        echo json_encode([
            "error" => "User ID is required",
            "message" => "Exmaple: /api/users/:id"
        ]);
        return false;
    } else if (!is_numeric($id) || intval($id) <= 0) {
        http_response_code(400);
        echo json_encode([
            "error" => "User ID must be a positive integer",
            "message" => "Exmaple: /api/users/:id"
        ]);
        return false;
    } else {
        return true;
    }
} //Validate user ID.

function handleFaultRequestMethod()
{
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
} //Alert fault request.

function handlePageNotFound()
{
    http_response_code(404);
    echo json_encode(["error" => "Page not found."]);
} //Handle page not found.

function handleLogin($id, $db)
{
    try {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);

        $user = $stmt->fetch();

        echo json_encode(["status" => "success", "user" => $user]);
    } catch (Throwable $th) {
        echo json_encode(["status" => "error", "message" => $th->getMessage()]);
    }
} //Handle user login.

function handleDeleteUser($id, $db) {} //Delete user.

function handleRegister($db) {} //Register.

function handleResetPassword($db) {} //Reset password.

function getUserById($id, $db) {}//Get user from database by id.
