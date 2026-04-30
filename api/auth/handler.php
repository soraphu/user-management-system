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
    $input = handleFetchJsonBody();

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
    $input = handleFetchJsonBody();

    ensureValidRegisterData($input);

    $username = $input['username'];
    $email = strtolower($input['email']);
    $password = $input['password'];

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

        handleCreateInbox($db, $user, "//password/reset?token=$token");

        responseSuccess(200, "Password reset link was send to $email.");
        //wait for implement email sending logic here.

    } catch (\Throwable $th) {
        responseError(500, $th->getMessage());
    }
} //Handle forget password.

function handleResetPassword($db)
{
    $input = handleFetchJsonBody();
    $token = $input['token'];
    $newPassword = $input['new_password'];

    if (empty($token) || empty($newPassword)) {
        responseError(400, "Token and new password are required.");
    }

    $hashNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $hashedToken = hash('sha256', $token);

    try {
        $sqlFindToken = "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()";
        $stmt = $db->prepare($sqlFindToken);
        $stmt->execute([$hashedToken]);

        $resetToken = $stmt->fetch();

        if (empty($resetToken)) {
            responseError(404, "Invalid or expired reset token.");
        }

        //Security check successful, execute reset password.
        $sqlResetPassword = "UPDATE accounts SET password = ? WHERE email = ?";
        $stmt = $db->prepare($sqlResetPassword);
        $stmt->execute([$hashNewPassword, $resetToken['email']]);

        //Delete reset password token.
        $sqlDeleteToken = "DELETE FROM password_resets WHERE token = ?";
        $stmt = $db->prepare($sqlDeleteToken);
        $stmt->execute([$hashedToken]);

        responseSuccess(200, "Password reset successfully.");
    } catch (\Throwable $th) {
        responseError(500, $th->getMessage());
    }
} //Reset password.

function handleVerifyEmailRequest($db)
{
    $input = handleFetchJsonBody();
    $email = $input['email'];

    if (empty($email)) {
        responseError(400, "Email is required.");
    }//Valdation email was send with request body.

    try {
        //Find email.
        $sqlFindEmail = "SELECT * FROM accounts WHERE email = ?";
        $stmt = $db->prepare($sqlFindEmail);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        //Is email was exist in accounts table?
        if (empty($user)) {
            responseError(404, "Account with this email not found.");
        }

        //Turn 0 | 1 to bool
        $user['verified'] = (bool) $user['verified'];

        //Is this email already verified?
        if ($user['verified'] == true) {
            responseError(409, "This email already verified.");
        }

        $token = createVerifyEmailToken($db, $user['email']);

        //Send verify token to mock mail.
        handleCreateInbox($db, $user, "/verify-email?token=$token");

        responseSuccess(201, "Verify email request was send to {$user['email']}.");
    } catch (\Throwable $th) {
        responseSuccess(500, $th->getMessage());
    }
}//Handle request email verification.

function handleVerifiedEmail($db)
{
    $input = handleFetchJsonBody();
    $token = $input['token'];

    if (empty($token)) {
        responseError(400, "Token is required.");
    }

    $hashedToken = hash('sha256', $token);

    try {
        $sqlFindToken = "SELECT * FROM email_verifications WHERE token = ? AND expires_at > NOW()";
        $stmt = $db->prepare($sqlFindToken);
        $stmt->execute([$hashedToken]);

        $verifyToken = $stmt->fetch();

        if (empty($verifyToken)) {
            responseError(404, "Invalid or expired verification token.");
        }

        //Update email to verified.
        $sqlEmailVerifed = "UPDATE accounts SET verified = 1 WHERE email = ?";
        $stmt = $db->prepare($sqlEmailVerifed);
        $stmt->execute([$verifyToken['email']]);

        //Delete token.
        $sqlDeleteToken = "DELETE FROM email_verifications WHERE token = ?";
        $stmt = $db->prepare($sqlDeleteToken);
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