<?php
function handleCreateInbox($db, $user, $token)
{
    try {
        //Simulate send to email
        $owner_email = $user['email'];
        $sender = "server@user.management.system.com";
        $subject = "Hi {$user['username']},";
        $preview = "Thank for testing my signup feature, this project was make for learning the process of user management system and security.";
        $url = "/verify-email?token=$token";
        $buttonLabel = "Verify your email address";
        $isRead = 0; // 0 for false, 1 for true in MySQL

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
        throw new Exception("Error Processing Request", 1);
    }
}//Handle send verify email token to user.

function createVerifyEmailToken($db, $email)
{
    $token = bin2hex(random_bytes(16));
    $hashedToken = hash('sha256', $token);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

    //Keep hashed token in database.
    $sqlCreateToken = "INSERT INTO email_verifications (email, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)";
    $stmt = $db->prepare($sqlCreateToken);
    $stmt->execute([$email, $hashedToken, $expiresAt]);

    //Send real token.
    return $token;
}