<?php
include_once "validation.php";
include_once "respond.php";
include_once "function_generate.php";
include_once __DIR__ . '/../db/db_execute.php';

function handleLogin($db)
{
    $input = ensureAndGetRequestBody();

    $email = $input['email'];
    $password = $input['password'];

    if (empty($email) || empty($password)) {
        respond400FieldsMissing();
    } //Validate input.

    try {
        $row = dbFetch(
            $db,
            cols: '*',
            table: 'accounts',
            condition: 'WHERE email = ?',
            execute: [$email]
        );

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

        dbInsertInto(
            $db,
            table: 'accounts',
            cols: '(username, email, password)',
            execute: [$username, $email, $hashPassword]
        );

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
        $row = dbFetch(
            $db,
            cols: '*',
            table: 'accounts',
            condition: 'WHERE email = ?',
            execute: [$email]
        );

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
        $row = dbFetch(
            $db,
            cols: '*',
            table: 'password_resets',
            condition: 'WHERE token = ? AND expires_at > NOW()',
            execute: [$hashedToken]
        );

        if (empty($row)) {
            respondFailed(404, "Invalid or expired reset token.");
        }

        //Security check successful, execute reset password.
        $sqlResetPassword = "UPDATE accounts SET password = ? WHERE email = ?";
        dbSqlExecute($db, $sqlResetPassword, [$hashNewPassword, $row['email']]);

        //Delete reset password token.
        dbDelete($db, table: 'password_resets', where: 'token = ?', execute: [$hashedToken]);

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
        $row = dbFetch(
            $db,
            cols: '*',
            table: 'accounts',
            condition: 'WHERE email = ?',
            execute: [$email]
        );

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
        $row = dbFetch(
            $db,
            cols: '*',
            table: 'email_verifications',
            condition: 'WHERE token = ? AND expires_at > NOW()',
            execute: [$hashedToken]
        );

        if (empty($row)) {
            respondFailed(404, "Invalid or expired verification token.");
        }

        //Update email to verified.
        $sqlEmailVerifed = "UPDATE accounts SET verified = 1 WHERE email = ?";
        dbSqlExecute($db, $sqlEmailVerifed, [$row['email']]);

        //Delete token.
        dbDelete($db, table: 'email_verifications', where: 'token = ?', execute: [[$hashedToken]]);

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
        $row = dbFetch(
            $db,
            cols: '*',
            table: 'accounts',
            condition: 'WHERE email = ?',
            execute: [$email]
        );

        if (empty($row)) {
            respondFailed(404, "User with this email not found.");
        }//Exist email check.

        $rows = dbFetchAll(
            $db,
            cols: 'id, sender, subject, preview, url, buttonLabel, time, isRead',
            table: 'inbox',
            condition: 'WHERE owner_email = ? ORDER BY time DESC',
            execute: [$email]
        );

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
        $row = dbFetch(
            $db,
            cols: '*',
            table: 'inbox',
            condition: 'WHERE id = ?',
            execute: [$id]
        );

        if (empty($row)) {
            respondFailed(404, "Mail not found.");
        }//Mail exist check.

        $sql = "UPDATE inbox SET isRead = 1 WHERE id = ?";
        dbSqlExecute($db, $sql, [$id]);

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
            dbDelete($db, table: 'refresh_tokens', where: 'token = ?', execute: [$hashedRefreshToken]);
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