<?php
function handleSendVerifyEmailToken($db, $user, $token)
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

function handleForgetPassword($db)
{
    $input = json_decode(file_get_contents('php://input'), true);

    $email = $input['email'];
    $sendToMockMail = isset($input['mock_mail']) ? $input['mock_mail'] : false;

    if (empty($email)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Email is required."]);
        return;
    }

    try {
        $sql = "SELECT * FROM accounts WHERE email = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Email not found."]);
            return;
        }

        // Generate reset token and save to database (for simplicity, we use a random string here)
        $token = bin2hex(random_bytes(16));
        $hashedToken = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$email, $hashedToken, $expiresAt]);

        if ($sendToMockMail == true) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Password reset token generated for $email.", "token" => $token]);
        } else {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Password reset token generated for $email."]);
            //wait for implement email sending logic here.
        }
    } catch (\Throwable $th) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $th->getMessage()]);
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
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'];

    if (empty($email)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Email is required."]);
        return;
    }

    try {
        $sqlFindEmail = "SELECT * FROM accounts WHERE email = ?";
        $stmt = $db->prepare($sqlFindEmail);
        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Email not found."]);
            return;
        }

        // Generate verification token and save to database (for simplicity, we use a random string here)
        $token = bin2hex(random_bytes(16));
        $hashedToken = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $sqlCreateToken = "INSERT INTO email_verifications (email, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)";
        $stmt = $db->prepare($sqlCreateToken);
        $stmt->execute([$email, $hashedToken, $expiresAt]);

        handleSendVerifyEmailToken($db, $user, $token);

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