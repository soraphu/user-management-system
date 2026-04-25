<?php

function duplicateEmailValidtion($pdo, $email)
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

function emptyRequestBodyValidation()
{
    http_response_code(400);
    echo json_encode(["error" => "Request body cannot be empty."]);
} //Handle empty request.

function handleLogin($db)
{
    $input = json_decode(file_get_contents('php://input'), true) ?? null;

    if (empty($input)) {
        emptyRequestBodyValidation();
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

function handleRegister($pdo)
{
    $input = json_decode(file_get_contents('php://input'), true) ?? null;

    if (empty($input)) {
        emptyRequestBodyValidation();
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
        if (duplicateEmailValidtion($pdo, $email)) {
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