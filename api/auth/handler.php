<?php
include_once "validation.php";
include_once "response.php";
// use Firebase\JWT\JWT;
// use Firebase\JWT\Key;

// function verifyAccessTokens()
// {
//     // 1. Check if the cookie even exists
//     if (!isset($_COOKIE['auth_token'])) {
//         http_response_code(401);
//         echo json_encode(["message" => "Unauthorized: No token provided"]);
//         exit();
//     }

//     $jwt = $_COOKIE['auth_token'];
//     $secretKey = $_ENV['JWT_SECRET']; // Your secret from .env

//     try {
//         // 2. Decode and Verify
//         // This checks the signature AND the expiration (exp) automatically
//         $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

//         // 3. Return the user data to be used in your logic
//         return (array) $decoded->data;

//     } catch (Exception $e) {
//         // 4. Handle errors (Expired, Tampered, Invalid)
//         http_response_code(401);
//         echo json_encode(["message" => "Unauthorized: " . $e->getMessage()]);
//         exit();
//     }
// }

// function issueIdentityTokens($userId) {
//     // 1. Create the JWT Access Token
//     $accessToken = JWT::encode([
//         'sub' => $userId,
//         'iat' => time(),
//         'exp' => time() + (15 * 60) // 15 mins
//     ], $_ENV['JWT_SECRET'], 'HS256');

//     // 2. Create the Refresh Token
//     $rawRefreshToken = bin2hex(random_bytes(32));
//     $hashedToken = password_hash($rawRefreshToken, PASSWORD_BCRYPT);

//     // 3. Save to MySQL (Your refresh_tokens table)
//     $db->query("INSERT INTO refresh_tokens (user_id, token, expires_at) 
//                 VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))", 
//                 [$userId, $hashedToken]);

//     // 4. Send Refresh Token via Secure Cookie
//     setcookie("refresh_token", $rawRefreshToken, [
//         'expires' => time() + 604800,
//         'httponly' => true,
//         'secure' => true,
//         'samesite' => 'Strict'
//     ]);

//     // 5. Return Access Token for React
//     return ["access_token" => $accessToken];
// }

function handleLogin($db)
{
    $user = json_decode(file_get_contents('php://input'), true) ?? null;

    ensureDataNotEmpty($user);

    $email = $user['email'];
    $password = $user['password'];

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

    ensureDataNotEmpty($user);
    ensureValidRegisterData($user);

    $username = $user['username'];
    $email = strtolower($user['email']);
    $password = $user['password'];

    $hashPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        ensureEmailNotDuplicate($db, $email);

        $sql = "INSERT INTO accounts (username, email, password) VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$username, $email, $hashPassword]);

        responseSuccess(201, "Internal server error.");
    } catch (Throwable $th) {
        responseError(500, "Internal server error.");
    }
} //Register.