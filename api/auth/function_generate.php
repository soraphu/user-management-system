<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;

function createMailtoInbox($db, $user, $type, $navigateUrl)
{
    $newPreview = "";
    $newButtonLabel = "";

    if ($type === "verify") {
        $newPreview = "Thank for testing my signup feature, this project was make for learning about user management system and security, so click the button below to verify your email address.";
        $newButtonLabel = "Verify your email address";
    }
    if ($type === "reset") {
        $newPreview = "Thank for testing my reset password feature, this project was make for learning about user management system and security, so click the button below to reset your password.";
        $newButtonLabel = "Reset your password";
    }

    //Simulate send to email
    $owner_email = $user['email'];
    $sender = "server@user.management.system.com";
    $subject = "Hi {$user['username']},";
    $preview = $newPreview;
    $url = $navigateUrl;
    $buttonLabel = $newButtonLabel;
    $isRead = 0; // 0 for false, 1 for true in MySQL

    try {
        dbInsertInto(
            $db,
            table: 'inbox',
            cols: '(owner_email, sender, subject, preview, url, buttonLabel, isRead )',
            execute: [$owner_email, $sender, $subject, $preview, $url, $buttonLabel, $isRead]
        );

    } catch (\Throwable $th) {
        respondFailed(500, $th->getMessage());
    }
}//Sent email to inbox.

function createResetPasswordToken($db, $email)
{
    $token = bin2hex(random_bytes(16));
    $hashedToken = hash('sha256', $token);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    try {
        //Keep hash token in password_resets table.
        $sql = "INSERT INTO password_resets (email, token, expires_at) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)";
        dbSqlExecute($db, $sql, [$email, $hashedToken, $expiresAt]);

        //Real token.
        return $token;
    } catch (\Throwable $th) {
        respondFailed(500, $th->getMessage());
    }
}//Generate reset password token.

function createVerifyEmailToken($db, $email)
{
    $token = bin2hex(random_bytes(16));
    $hashedToken = hash('sha256', $token);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

    try {
        //Keep hashed token in email_verifications table.
        $sqlCreateToken = "INSERT INTO email_verifications (email, token, expires_at) 
                            VALUES (?, ?, ?) 
                            ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)";
        dbSqlExecute($db, $sqlCreateToken, [$email, $hashedToken, $expiresAt]);

        //Real token.
        return $token;
    } catch (\Throwable $th) {
        respondFailed(500, $th->getMessage());
    }
}//Generate verify email token.

function createIdentityTokens($user, $db)
{
    // Create access token.
    $accessToken = generateAccessToken($user);

    // Create the Refresh Token
    handleSetupRefreshToken($user, $db);

    return $accessToken;
}//Generate access token and refresh token.

function generateAccessToken($user)
{
    $accessToken = JWT::encode([
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role'],
        'iat' => time(),
        'exp' => time() + (15 * 60)
    ], $_ENV['JWT_SECRET'], 'HS256');

    return $accessToken;
} //Generate access token.

function handleSetupRefreshToken($user, $db)
{
    $isDevMode = (bool) $_ENV['DEV'];

    $refreshToken = bin2hex(random_bytes(32));
    $hashedRefreshToken = hash('sha256', $refreshToken);

    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));

    // Save to MySQL (Your refresh_tokens table)
    try {
        $sqlCreateToken = "INSERT INTO refresh_tokens (user_id, token, expires_at) 
                            VALUES (?, ?, ?) 
                            ON DUPLICATE KEY 
                            UPDATE token = VALUES(token), expires_at = VALUES(expires_at)";
        dbSqlExecute($db, $sqlCreateToken, execute: [$user['id'], $hashedRefreshToken, $expires]);

        setcookie("refresh_token", $refreshToken, [
            'expires' => strtotime($expires),
            'httponly' => true,
            'secure' => !$isDevMode, //Set to true in production (requires HTTPS)
            'samesite' => 'Lax'
        ]);
    } catch (\Throwable $th) {
        respondFailed(500, $th->getMessage());
    }
}