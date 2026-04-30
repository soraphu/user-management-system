<?php
include_once "validation.php";
include_once "response.php";
include_once "function_generate.php";
// use Firebase\JWT\JWT;
// use Firebase\JWT\Key;

function handleFetchJsonBody()
{
    $data = json_decode(file_get_contents('php://input'), true) ?? null;
    ensureDataNotEmpty($data);
    return $data;
}

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
    $user = handleFetchJsonBody();

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
    $user = handleFetchJsonBody();

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

function handleForgetPassword($db)
{
    $input = handleFetchJsonBody();

    $email = $input['email'];

    if (empty($email)) {
        responseError(400, "Email is required.");
    }

    try {
        $sql = "SELECT * FROM accounts WHERE email = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if (!$user) {
            responseError(404, "Email not found.");
        }

        // Generate reset token and save to database (for simplicity, we use a random string here)
        $token = createResetPasswordToken($db, $email);

        handleCreateInbox($db, $user, "/verified?token=$token");

        responseSuccess(200, "Password reset link was send to $email.");
        //wait for implement email sending logic here.

    } catch (\Throwable $th) {
        responseError(500, $th->getMessage());
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

function handleVerifyEmailRequest($db)
{
    $input = handleFetchJsonBody();
    $email = $input['email'];

    //Is body empty?
    if (empty($email)) {
        responseError(400, "Email is required.");
    }

    try {
        //Find email.
        $sqlFindEmail = "SELECT * FROM accounts WHERE email = ?";
        $stmt = $db->prepare($sqlFindEmail);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        //Is email was exist in accounts table?
        if (!$user) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Account with this email not found."]);
            return;
        }

        //Is this email already verified?
        $user['verified'] = (bool) $user['verified'];

        if ($user['verified'] == true) {
            http_response_code(409);
            echo json_encode(["status" => "error", "message" => "This email already verified."]);
            return;
        }

        //Generate verify token.
        $token = createVerifyEmailToken($db, $user['email']);

        //Send verify token to mock mail.
        handleCreateInbox($db, $user, "/verify-email?token=$token");

        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Verify email request was send to {$user['email']}."]);

    } catch (\Throwable $th) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $th->getMessage()]);
    }
}//Handle request email verification.

function handleVerifiedEmail($db)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $token = $input['token'];

    if (empty($token)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Token is required."]);
        return;
    }

    $hashedToken = hash('sha256', $token);

    try {
        $sql = "SELECT * FROM email_verifications WHERE token = ? AND expires_at > NOW()";
        $stmt = $db->prepare($sql);
        $stmt->execute([$hashedToken]);

        $verificationRequest = $stmt->fetch();

        if (!$verificationRequest) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid or expired verification token."]);
            return;
        }

        $sql = "UPDATE accounts SET verified = 1 WHERE email = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$verificationRequest['email']]);

        $sql = "DELETE FROM email_verifications WHERE token = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$hashedToken]);

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Email verified successfully."]);
    } catch (\Throwable $th) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $th->getMessage()]);
    }
}

function handleGetInbox($db)
{
    $email = $_GET['email'];

    ensureDataNotEmpty($email);

    try {
        $sqlFindEmail = "SELECT * FROM accounts WHERE email = ?";
        $stmt = $db->prepare($sqlFindEmail);
        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if (!$user) {
            responseError(404, "Email not found.");
        }//Exist email check.

        $sql = "SELECT id, sender, subject, preview, url, buttonLabel, time, isRead 
                FROM inbox 
                WHERE owner_email = ? 
                ORDER BY time DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$email]);

        // Fetch all rows as an associative array.
        $inbox = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // MySQL stores BOOLEAN as 0 or 1; this ensures React sees true/false
        foreach ($inbox as &$row) {
            $row['isRead'] = (bool) $row['isRead'];
            $row['id'] = (int) $row['id']; // Ensure ID is a number
        }

        // Response
        http_response_code(200);
        echo json_encode(
            ["success" => true, "inbox" => $inbox]
        );

    } catch (PDOException $e) {
        responseError(500, $e->getMessage());
    }
}//Inbox