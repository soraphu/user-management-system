<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function verifyAccessTokens()
{
    // 1. Check if the cookie even exists
    if (!isset($_COOKIE['auth_token'])) {
        http_response_code(401);
        echo json_encode(["message" => "Unauthorized: No token provided"]);
        exit();
    }

    $jwt = $_COOKIE['auth_token'];
    $secretKey = $_ENV['JWT_SECRET']; // Your secret from .env

    try {
        // 2. Decode and Verify
        // This checks the signature AND the expiration (exp) automatically
        $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

        // 3. Return the user data to be used in your logic
        return (array) $decoded->data;

    } catch (Exception $e) {
        // 4. Handle errors (Expired, Tampered, Invalid)
        http_response_code(401);
        echo json_encode(["message" => "Unauthorized: " . $e->getMessage()]);
        exit();
    }
}

function generateIdentityToken($userId, $secretKey)
{
    // 1. Create the Header
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

    // 2. Create the Payload
    $payload = json_encode([
        'user_id' => $userId,
        'iat' => time(),        // Issued at
        'exp' => time() + 3600  // Expires in 1 hour
    ]);
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

    // 3. Create the Signature
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secretKey, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    // 4. Combine them
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

function respondWithIdentityTokens()
{
    // Your JWT secret and payload logic here...
    $jwt = "your.generated.jwt.token";

    setcookie(
        "auth_token",        // Name
        $jwt,               // Value
        [
            "expires" => time() + 3600, // 1 hour
            "path" => "/",
            "domain" => "localhost",    // Match your dev domain
            "secure" => false,          // Set to true in Production (HTTPS)
            "httponly" => true,         // CRITICAL: Prevents JS access
            "samesite" => "Lax"         // Prevents CSRF attacks
        ]
    );

    echo json_encode(["message" => "Login successful"]);
}

function isValidRegisterData($user)
{
    $email = $user['email'];
    $password = $user['password'];

    // 1. Check if it "looks" like an email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(403);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid email format."
        ]);
        return false;
    }//Email format validation.

    // 2. Check if it's a "Real" provider you want to block
    $blocked_domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
    $domain = strtolower(substr(strrchr($email, "@"), 1));

    if (in_array($domain, $blocked_domains)) {
        http_response_code(403);
        echo json_encode([
            "status" => "error",
            "message" => "Please use a fake email (e.g. @test.com) for PDPA safety."
        ]);
        return false;
    }//Fake email validation.

    if (strlen($password) < 8) {
        http_response_code(403);
        echo json_encode([
            "status" => "error",
            "message" => "Password must be at least 8 characters long."
        ]);
        return false;
    }//Password validation.

    return true;
}//Validation the register data.

function isEmailDuplicate($db, $email)
{
    $sql = "SELECT * FROM accounts WHERE email = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        return true;
    } else {
        return false;
    }
} //Check if email already exists in database.

function isRequestBodyEmpty($input)
{
    if (empty($input)) {
        return true;
    }
    return false;
} //Handle empty request.

function handleLogin($db)
{
    $input = json_decode(file_get_contents('php://input'), true) ?? null;

    if (isRequestBodyEmpty($input)) {
        exit;
    }//Empty req body validation.

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

        if (empty($user) || !password_verify($password, $user['password'])) {
            http_response_code(409);
            echo json_encode(["status" => "error", "message" => "Invalid Email or Password."]);
            return;
        }

        if (password_verify($password, $user['password']) && !$user['verified']) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Verify email required."]);
            return;
        }

        if (password_verify($password, $user['password']) && $user['verified']) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Login successful."]);
            return;
        }

    } catch (\Throwable $th) {
        echo json_encode(["status" => "error", "message" => $th->getMessage()]);
    }
} //Handle user login.

function handleRegister($db)
{
    $user = json_decode(file_get_contents('php://input'), true) ?? null;

    if (isRequestBodyEmpty($user)) {
        exit;
    }//Empty req body validation.

    if (!isValidRegisterData($user)) {
        exit;
    }//Register data validation.

    $username = $user['username'];
    $email = strtolower($user['email']);
    $password = $user['password'];

    if (empty($username) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Required fields are missing."]);
        return;
    } //Validate input.

    $hashPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        if (isEmailDuplicate($db, $email)) {
            http_response_code(409);
            echo json_encode(["status" => "error", "message" => "Email already exists."]);
            return;
        }

        $sql = "INSERT INTO accounts (username, email, password) VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$username, $email, $hashPassword]);

        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "User registered successfully."]);
    } catch (Throwable $th) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $th->getMessage()]);
    }
} //Register.