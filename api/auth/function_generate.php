<?php
function createMailtoInbox($db, $user, $newUrl)
{
    //Simulate send to email
    $owner_email = $user['email'];
    $sender = "server@user.management.system.com";
    $subject = "Hi {$user['username']},";
    $preview = "Thank for testing my signup feature, this project was make for learning the process of user management system and security.";
    $url = $newUrl;
    $buttonLabel = "Verify your email address";
    $isRead = 0; // 0 for false, 1 for true in MySQL

    try {
        $sqlCreateMail = "INSERT INTO inbox (
                    owner_email, 
                    sender, 
                    subject, 
                    preview, 
                    url, 
                    buttonLabel, 
                    isRead
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sqlCreateMail);
        $stmt->execute([
            $owner_email,
            $sender,
            $subject,
            $preview,
            $url,
            $buttonLabel,
            $isRead
        ]);

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
        $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$email, $hashedToken, $expiresAt]);

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
        $sqlCreateToken = "INSERT INTO email_verifications (email, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)";
        $stmt = $db->prepare($sqlCreateToken);
        $stmt->execute([$email, $hashedToken, $expiresAt]);

        //Real token.
        return $token;
    } catch (\Throwable $th) {
        respondFailed(500, $th->getMessage());
    }
}//Generate verify email token.