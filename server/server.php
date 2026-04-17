<?php
require_once 'db.php';
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

function handleEmptyRequestBody()
{
    http_response_code(400);
    echo json_encode(["error" => "Request body cannot be empty."]);
} //Handle empty request.

function handleEmailDuplicate($pdo, $email)
{
    $sql = "SELECT * FROM accounts WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        return true;
    } else {
        return false;
    }
} //Check if email already exists in database.

function handleRegister($pdo)
{
    $input = json_decode(file_get_contents('php://input'), true) ?? null;

    if (empty($input)) {
        handleEmptyRequestBody();
        exit;
    }//Validate request body.

    $username = $input['username'];
    $email = strtolower($input['email']);
    $password = $input['password'];

    if (empty($username) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Required fields are missing."]);
        return;
    } //Validate input.

    $hashPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        if (handleEmailDuplicate($pdo, $email)) {
            http_response_code(409);
            echo json_encode(["status" => "error", "message" => "Email already exists."]);
            return;
        }

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
    $input = json_decode(file_get_contents('php://input'), true) ?? null;

    if (empty($input)) {
        handleEmptyRequestBody();
        exit;
    }//Validate request body.

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
            unset($user['password']); //Remove password from response data for security.

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

function handleForgetPassword($db)
{
    $input = json_decode(file_get_contents('php://input'), true);

    $email = $input['email'];
    $sendToMockMail = isset($input['mock_mail']) ? $input['mock_mail'] : false;

    if (empty($email)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Email is required."]);
        return;
    }

    try {
        $sql = "SELECT * FROM accounts WHERE email = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Email not found."]);
            return;
        }

        // Generate reset token and save to database (for simplicity, we use a random string here)
        $token = bin2hex(random_bytes(16));
        $hashedToken = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$email, $hashedToken, $expiresAt]);

        if ($sendToMockMail == true) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Password reset token generated for $email.", "token" => $token]);
        } else {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Password reset token generated for $email."]);
            //wait for implement email sending logic here.
        }
    } catch (\Throwable $th) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $th->getMessage()]);
    }
} //Handle forget password.

function handleResetPassword($db)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $token = $input['token'];
    $newPassword = $input['new_password'];

    if (empty($token) || empty($newPassword)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Token and new password are required."]);
        return;
    }

    $hashNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $hashedToken = hash('sha256', $token);

    try {
        $sql = "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()";
        $stmt = $db->prepare($sql);
        $stmt->execute([$hashedToken]);

        $resetRequest = $stmt->fetch();

        if (!$resetRequest) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid or expired reset token."]);
            return;
        }

        $sql = "UPDATE accounts SET password = ? WHERE email = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$hashNewPassword, $resetRequest['email']]);

        $sql = "DELETE FROM password_resets WHERE token = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$hashedToken]);

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Password reset successfully."]);
    } catch (\Throwable $th) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $th->getMessage()]);
    }
} //Reset password.

function handleDeleteUser($id, $db)
{
} //Delete user.

function getUserById($id, $db)
{
}//Get user from database by id.
