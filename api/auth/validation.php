<?php
include_once 'respond.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
function ensureDataNotEmpty($data)
{
    if (empty($data)) {
        respondFailed(400, "Request data can't be empty.");
    }
}

function ensureEmailNotDuplicate($db, $email)
{
    try {
        $existingUser = dbFetch(
            $db,
            cols: '*',
            table: 'accounts',
            condition: 'WHERE email = ?',
            execute: [$email]
        );

        if ($existingUser) {
            respondFailed(409, "This email already exists.");
        }
    } catch (\Throwable $th) {
        respondFailed(500, "Internal server error.");
        exit;
    }
} //Check if email already exists in database.

function ensureValidRegisterData($user)
{
    $username = $user['username'];
    $email = $user['email'];
    $password = $user['password'];

    // Check if it's a "Real" provider you want to block
    $blocked_domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
    $domain = strtolower(substr(strrchr($email, "@"), 1));

    if (empty($username) || empty($email) || empty($password)) {
        respond400FieldsMissing();
    } //Validate required field.

    // Check if it "looks" like an email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respondFailed(403, "Invalid email format.");
    }//Email format validation.

    if (in_array($domain, $blocked_domains)) {
        respondFailed(403, "Please use a fake email (e.g. @test.com) for PDPA safety.");
    }//Fake email validation.

    if (strlen($password) < 8) {
        respondFailed(400, "Password must be at least 8 characters long.");
    }//Password validation.
}//Validation the register data.

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

function ensureValidRefreshToken($db)
{
    $refreshToken = $_COOKIE['refresh_token'] ?? null;

    if (empty($refreshToken)) {
        respondFailed(400, "Unauthorized: No refresh token provided.");
        exit();
    }

    $hashedRefreshToken = hash('sha256', $refreshToken);

    try {
        $refreshTokenRows = dbFetch(
            $db,
            cols: '*',
            table: 'refresh_tokens',
            condition: 'WHERE token = ? AND expires_at > NOW()',
            execute: [$hashedRefreshToken]
        );

        $id = $refreshTokenRows['user_id'];
        if (empty($refreshTokenRows)) {
            respondFailed(404, "Invalid or expired refresh token.");
        }

        $userRows = dbFetch(
            $db,
            cols: '*',
            table: 'accounts',
            condition: 'WHERE id = ?',
            execute: [$id]
        );

        return $userRows;
    } catch (\Throwable $th) {
        respondFailed(500, $th->getMessage());
        exit();
    }
}//ensure valid refresh token.

function ensureAndGetRequestBody()
{
    $data = json_decode(file_get_contents('php://input'), true) ?? null;
    ensureDataNotEmpty($data);
    return $data;
}

function ensureAndGetAccessToken()
{
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (empty($authHeader)) {
        respondFailed(400, "Access token is missing.");
    }

    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {

        $accessToken = $matches[1];

        return $accessToken;
    } else {
        respondFailed(401, "Unauthorized: No Bearer token found");
    }
}//ensureAndGetAccessToken.

function ensureAndGetDecodedAccessToken()
{
    $accessToken = ensureAndGetAccessToken();
    $secretKey = $_ENV['JWT_SECRET'];

    try {
        // Decode and Verify
        // This checks the signature AND the expiration (exp) automatically

        $decoded = JWT::decode($accessToken, new Key($secretKey, 'HS256'));

        // Return the user data to be used in your logic
        return (array) $decoded;

    } catch (Exception $e) {
        // Handle errors (Expired, Tampered, Invalid)
        respondFailed(401, $e->getMessage());
    }
}//ensureAndGetDecodedAccessToken()