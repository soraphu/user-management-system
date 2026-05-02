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
    $sql = "SELECT * FROM accounts WHERE email = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$email]);

    try {
        if ($stmt->fetch()) {
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

function decodeAccessToken($access_token)
{
    // Check if the cookie even exists
    if (empty($access_token)) {
        http_response_code(401);
        echo json_encode(["message" => "Unauthorized: No token provided"]);
        exit();
    }

    $secretKey = $_ENV['JWT_SECRET']; // Your secret from .env

    try {
        // Decode and Verify
        // This checks the signature AND the expiration (exp) automatically
        $decoded = JWT::decode($access_token, new Key($secretKey, 'HS256'));

        // Return the user data to be used in your logic
        return (array) $decoded->data;

    } catch (Exception $e) {
        // Handle errors (Expired, Tampered, Invalid)
        http_response_code(401);
        echo json_encode(["message" => "Unauthorized: " . $e->getMessage()]);
        exit();
    }
}