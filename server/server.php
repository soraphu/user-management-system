<?php
require_once 'db.php';
// Get the path (e.g., /api/users )
$requestUrl = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Clean it up (remove query strings like ?id=1)
$path = parse_url($requestUrl, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

header('Content-Type: application/json');

if ($segments[0] === 'api' && $segments[1] === 'users') { //url: /api/users
    $id = $segments[2] ?? null;

    switch ($request_method) {
        case 'GET': //Login and respond user detail.
            handleLogin($pdo);
            break;

        case 'POST': //Register new user.
            handleRegister($pdo);
            break;

        case 'PUT': //Reset user password by user ID.
            if (idValidate($id)) {
                handleResetPassword($id, $pdo);
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
} //Main router for API endpoints.

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


function handleRegister($pdo)
{
    $input = json_decode(file_get_contents('php://input'), true);

    $username = $input['username'];
    $email = $input['email'];
    $password = $input['password'];

    if (empty($username) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Required fields are missing."]);
        return;
    } //Validate input.

    $hashPassword = password_hash($password, PASSWORD_DEFAULT);

    try {

        $sql = "INSERT INTO accounts (username, email, password) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email, $hashPassword]);

        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "User registered successfully."]);
    } catch (Throwable $th) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $th->getMessage()]);
    }
} //Register.

function handleLogin($db)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'];
    $password = $input['password'];

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Email and password is required."]);
        return;
    } //Validate input.

    try {
        $sql = "SELECT * FROM accounts WHERE email = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Login successful.", "data" => $user]);
        } else {
            throw new Exception("Invalid Email or Password.");
        }
    } catch (\Throwable $th) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => $th->getMessage()]);
    }
} //Handle user login.

function handleResetPassword($id, $db) {} //Reset password.

function handleDeleteUser($id, $db) {} //Delete user.

function getUserById($id, $db) {}//Get user from database by id.
