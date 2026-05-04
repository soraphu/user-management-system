<?php
include_once "validation.php";
include_once "respond.php";
include_once "function_generate.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function handleFetchJsonBody()
{
    $data = json_decode(file_get_contents('php://input'), true) ?? null;
    ensureDataNotEmpty($data);
    return $data;
}

function handleLogin($db)
{
    $input = handleFetchJsonBody();

    $email = $input['email'];
    $password = $input['password'];

    if (empty($email) || empty($password)) {
        respond400FieldsMissing();
    } //Validate input.

    try {
        $sqlFindUser = "SELECT * FROM accounts WHERE email = ?";
        $stmt = $db->prepare($sqlFindUser);
        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if (empty($user) || !password_verify($password, $user['password'])) {
            respondFailed(409, "Invalid Email or Password.");
        }

        if (password_verify($password, $user['password']) && !$user['verified']) {
            respondFailed(401, "Verify email required.");
        }

        if (password_verify($password, $user['password']) && $user['verified']) {

            $accessToken = createIdentityTokens($user, $db);

            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "User {$user['email']} login successfully.",
                "access_token" => $accessToken,
                "refresh_token" => "Automatically set via HttpOnly Cookie."
            ]);
        }
    } catch (\Throwable $th) {
        respondFailed(500, $th->getMessage());
    }
} //Handle user login.

function handleRefreshAccessToken($db)
{
    $user = ensureValidRefreshToken($db);

    if (isset($user)) {
        $accessToken = generateAccessToken($user);

        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Access token refreshed successfully.",
            "access_token" => $accessToken
        ]);
    }
}

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

        respondSuccess(201, "User registered.");
    } catch (Throwable $th) {
        respondFailed(500, "Internal server error.");
    }
} //Register.

function handleForgetPassword($db)
{
    $input = handleFetchJsonBody();

    $email = $input['email'];

    if (empty($email)) {
        respond400FieldsMissing();
    }

    if ($email === 'admin@example.com') {
        respondFailed(403, "Hehe, this account not allow to reset password.");
    }

    try {
        $sqlFindEmail = "SELECT * FROM accounts WHERE email = ?";
        $stmt = $db->prepare($sqlFindEmail);
        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if (!$user) {
            respondFailed(404, "User with this email not found.");
        }

        $token = createResetPasswordToken($db, $email);

        createMailtoInbox($db, $user, "reset", "/password/reset?token=$token");

        respondSuccess(200, "Password reset link was send to $email.");
    } catch (\Throwable $th) {
        respondFailed(500, $th->getMessage());
    }
} //Handle forget password.

function handleResetPassword($db)
{
    $input = handleFetchJsonBody();
    $token = $input['token'];
    $newPassword = $input['new_password'];

    if (empty($token) || empty($newPassword)) {
        respond400FieldsMissing();
    }

    if (strlen($newPassword) < 8) {
        respondFailed(400, "Password must be at least 8 characters long.");
    }

    $hashNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $hashedToken = hash('sha256', $token);

    try {
        $sqlFindToken = "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()";
        $stmt = $db->prepare($sqlFindToken);
        $stmt->execute([$hashedToken]);

        $rows = $stmt->fetch();

        if (empty($rows)) {
            respondFailed(404, "Invalid or expired reset token.");
        }

        //Security check successful, execute reset password.
        $sqlResetPassword = "UPDATE accounts SET password = ? WHERE email = ?";
        $stmt = $db->prepare($sqlResetPassword);
        $stmt->execute([$hashNewPassword, $rows['email']]);

        //Delete reset password token.
        $sqlDeleteToken = "DELETE FROM password_resets WHERE token = ?";
        $stmt = $db->prepare($sqlDeleteToken);
        $stmt->execute([$hashedToken]);

        respondSuccess(200, "Password updated successfully.");
    } catch (\Throwable $th) {
        respondFailed(500, $th->getMessage());
    }
} //Reset password.

function handleVerifyEmailRequest($db)
{
    $input = handleFetchJsonBody();
    $email = $input['email'];

    if (empty($email)) {
        respond400FieldsMissing();
    }//Valdation email was send with request body.

    try {
        //Find email.
        $sqlFindEmail = "SELECT * FROM accounts WHERE email = ?";
        $stmt = $db->prepare($sqlFindEmail);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        //Is email was exist in accounts table?
        if (empty($user)) {
            respondFailed(404, "Account with this email not found.");
        }

        //Turn 0 | 1 to bool
        $user['verified'] = (bool) $user['verified'];

        //Is this email already verified?
        if ($user['verified'] == true) {
            respondFailed(409, "This email already verified.");
        }

        $token = createVerifyEmailToken($db, $user['email']);

        //Send verify token to mock mail.
        createMailtoInbox($db, $user, "verify", "/verify-email?token=$token");

        respondSuccess(201, "Verify email request was send to {$user['email']}.");
    } catch (\Throwable $th) {
        respondSuccess(500, $th->getMessage());
    }
}//Handle request email verification.

function handleVerifiedEmail($db)
{
    $input = handleFetchJsonBody();
    $token = $input['token'];

    if (empty($token)) {
        respond400FieldsMissing();
    }

    $hashedToken = hash('sha256', $token);

    try {
        $sqlFindToken = "SELECT * FROM email_verifications WHERE token = ? AND expires_at > NOW()";
        $stmt = $db->prepare($sqlFindToken);
        $stmt->execute([$hashedToken]);

        $fetchRows = $stmt->fetch();

        if (empty($fetchRows)) {
            respondFailed(404, "Invalid or expired verification token.");
        }

        //Update email to verified.
        $sqlEmailVerifed = "UPDATE accounts SET verified = 1 WHERE email = ?";
        $stmt = $db->prepare($sqlEmailVerifed);
        $stmt->execute([$fetchRows['email']]);

        //Delete token.
        $sqlDeleteToken = "DELETE FROM email_verifications WHERE token = ?";
        $stmt = $db->prepare($sqlDeleteToken);
        $stmt->execute([$hashedToken]);

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Email {$fetchRows['email']} verified successfully."]);
    } catch (\Throwable $th) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $th->getMessage()]);
    }
}
function handleGetInbox($db)
{
    $email = $_GET['email'] ?? null;

    ensureDataNotEmpty($email);

    try {
        $sqlFindEmail = "SELECT * FROM accounts WHERE email = ?";
        $stmt = $db->prepare($sqlFindEmail);
        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if (empty($user)) {
            respondFailed(404, "User with this email not found.");
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
        respondFailed(500, $e->getMessage());
    }
}//Inbox

function handleMarkMailAsRead($db, $id)
{
    if (empty($id)) {
        respond400FieldsMissing();
    }

    try {
        $sqlFindMail = "SELECT * FROM inbox WHERE id = ?";
        $stmt = $db->prepare($sqlFindMail);
        $stmt->execute([$id]);
        $mail = $stmt->fetch();

        if (empty($mail)) {
            respondFailed(404, "Mail not found.");
        }//Mail exist check.

        $sql = "UPDATE inbox SET isRead = 1 WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);

        respondSuccess(200, "Mail marked as read.");
    } catch (PDOException $e) {
        respondFailed(500, $e->getMessage());
    }
}//Mark mail as read.

function handleFetchUser()
{
    $token = handleEnsureGetAccessToken();

    $rawData = handleDecodeAccessToken($token);

    http_response_code(200);
    echo json_encode(
        [
            "success" => true,
            "message" => "Fetch user successful.",
            "user" => $rawData
        ]
    );
}

function handleEnsureGetAccessToken()
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
}//handleEnsureGetAccessToken.

function handleDecodeAccessToken($accessToken)
{
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
}

function handleLogout($db)
{
    $refreshToken = $_COOKIE['refresh_token'] ?? null;
    $isDevMode = (bool) $_ENV['DEV'];

    if (isset($refreshToken)) {
        $hashedRefreshToken = hash('sha256', $refreshToken);

        try {
            $sqlDeleteToken = "DELETE FROM refresh_tokens WHERE token = ?";
            $stmt = $db->prepare($sqlDeleteToken);
            $stmt->execute([$hashedRefreshToken]);
        } catch (\Throwable $th) {
            respondFailed(500, $th->getMessage());
        }
    }

    setcookie("refresh_token", "", [
        'expires' => time() - 3600,
        'httponly' => true,
        'secure' => !$isDevMode,
        'samesite' => 'Lax'
    ]);

    respondSuccess(200, "Logged out successfully.");
} //Handle log out

function handleChangeUsername($db) {
    
}