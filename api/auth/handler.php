<?php
include_once "validation.php";
include_once "respond.php";
include_once "function_generate.php";
include_once '../db/db_execute.php';

function handleLogin($db)
{
    $input = ensureAndGetRequestBody();

    $email = $input['email'];
    $password = $input['password'];

    if (empty($email) || empty($password)) {
        respond400FieldsMissing();
    } //Validate input.

    try {
        $sqlFindUser = "SELECT * FROM accounts WHERE email = ?";
        $stmt = $db->prepare($sqlFindUser);
        $stmt->execute([$email]);

        $row = $stmt->fetch();

        if (empty($row) || !password_verify($password, $row['password'])) {
            respondFailed(409, "Invalid Email or Password.");
        }

        if (password_verify($password, $row['password']) && !$row['verified']) {
            respondFailed(401, "Verify email required.");
        }

        if (password_verify($password, $row['password']) && $row['verified']) {

            $accessToken = createIdentityTokens($row, $db);

            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "User {$row['email']} login successfully.",
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
    $input = ensureAndGetRequestBody();

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
    $input = ensureAndGetRequestBody();

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

        $row = $stmt->fetch();

        if (!$row) {
            respondFailed(404, "User with this email not found.");
        }

        $token = createResetPasswordToken($db, $email);

        createMailtoInbox($db, $row, "reset", "/password/reset?token=$token");

        respondSuccess(200, "Password reset link was send to $email.");
    } catch (\Throwable $th) {
        respondFailed(500, $th->getMessage());
    }
} //Handle forget password.

function handleResetPassword($db)
{
    $input = ensureAndGetRequestBody();
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

        $row = $stmt->fetch();

        if (empty($row)) {
            respondFailed(404, "Invalid or expired reset token.");
        }

        //Security check successful, execute reset password.
        $sqlResetPassword = "UPDATE accounts SET password = ? WHERE email = ?";
        $stmt = $db->prepare($sqlResetPassword);
        $stmt->execute([$hashNewPassword, $row['email']]);

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
    $input = ensureAndGetRequestBody();
    $email = $input['email'];

    if (empty($email)) {
        respond400FieldsMissing();
    }//Valdation email was send with request body.

    try {
        //Find email.
        $sqlFindEmail = "SELECT * FROM accounts WHERE email = ?";
        $stmt = $db->prepare($sqlFindEmail);
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        //Is email was exist in accounts table?
        if (empty($row)) {
            respondFailed(404, "Account with this email not found.");
        }

        //Turn 0 | 1 to bool
        $row['verified'] = (bool) $row['verified'];

        //Is this email already verified?
        if ($row['verified'] == true) {
            respondFailed(409, "This email already verified.");
        }

        $token = createVerifyEmailToken($db, $row['email']);

        //Send verify token to mock mail.
        createMailtoInbox($db, $row, "verify", "/verify-email?token=$token");

        respondSuccess(201, "Verify email request was send to {$row['email']}.");
    } catch (\Throwable $th) {
        respondSuccess(500, $th->getMessage());
    }
}//Handle request email verification.

function handleVerifiedEmail($db)
{
    $input = ensureAndGetRequestBody();
    $token = $input['token'];

    if (empty($token)) {
        respond400FieldsMissing();
    }

    $hashedToken = hash('sha256', $token);

    try {
        $sqlFindToken = "SELECT * FROM email_verifications WHERE token = ? AND expires_at > NOW()";
        $stmt = $db->prepare($sqlFindToken);
        $stmt->execute([$hashedToken]);

        $row = $stmt->fetch();

        if (empty($row)) {
            respondFailed(404, "Invalid or expired verification token.");
        }

        //Update email to verified.
        $sqlEmailVerifed = "UPDATE accounts SET verified = 1 WHERE email = ?";
        $stmt = $db->prepare($sqlEmailVerifed);
        $stmt->execute([$row['email']]);

        //Delete token.
        $sqlDeleteToken = "DELETE FROM email_verifications WHERE token = ?";
        $stmt = $db->prepare($sqlDeleteToken);
        $stmt->execute([$hashedToken]);

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Email {$row['email']} verified successfully."]);
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

        $row = $stmt->fetch();

        if (empty($row)) {
            respondFailed(404, "User with this email not found.");
        }//Exist email check.

        $sql = "SELECT id, sender, subject, preview, url, buttonLabel, time, isRead 
                FROM inbox 
                WHERE owner_email = ? 
                ORDER BY time DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$email]);

        // Fetch all rows as an associative array.
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // MySQL stores BOOLEAN as 0 or 1; this ensures React sees true/false
        foreach ($rows as &$row) {
            $row['isRead'] = (bool) $row['isRead'];
            $row['id'] = (int) $row['id']; // Ensure ID is a number
        }

        // Response
        http_response_code(200);
        echo json_encode(
            ["success" => true, "inbox" => $rows]
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
        $row = $stmt->fetch();

        if (empty($row)) {
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