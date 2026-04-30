<?php
include_once 'response.php';
function ensureDataNotEmpty($data)
{
    if (empty($data)) {
        responseError(400, "Request data can't be empty.");
    }
}

function ensureEmailNotDuplicate($db, $email)
{
    $sql = "SELECT * FROM accounts WHERE email = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$email]);

    try {
        if ($stmt->fetch()) {
            responseError(409, "This email already exists.");
        }
    } catch (\Throwable $th) {
        responseError(500, "Internal");
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
        responseError(400, "Required fields are missing.");
    } //Validate required field.

    // Check if it "looks" like an email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        responseError(403, "Invalid email format.");
    }//Email format validation.

    if (in_array($domain, $blocked_domains)) {
        responseError(403, "Please use a fake email (e.g. @test.com) for PDPA safety.");
    }//Fake email validation.

    if (strlen($password) < 8) {
        responseError(400, "Password must be at least 8 characters long.");
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