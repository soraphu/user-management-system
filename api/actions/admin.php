<?php
function ensureIsAdmin($role)
{
    if ($role !== 'admin') {
        respondFailed(401, "Must be admin only for this process.");
    }
}

function handleFetchAllUsers($db)
{
    $decodedData = handleEnsureAndDecodeAccessToken();

    $id = $decodedData['id'];

    try {
        $sqlFetchRole = "SELECT role FROM accounts WHERE id = ?";
        $stmt = $db->prepare($sqlFetchRole);
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        ensureIsAdmin($row['role']);

        $sqlFetchAllUsers = 'SELECT id, username, email, role, verified FROM accounts WHERE id != ? ';
        $stmt = $db->prepare($sqlFetchAllUsers);
        $stmt->execute([$id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => "Fetch all users successfully.",
            'users' => $rows
        ]);

    } catch (\Throwable $th) {
        respondFailed(500, $th->getMessage());
    }
} //handleFetchAllUsers();
