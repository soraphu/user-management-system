<?php
function ensureIsAdmin($role)
{
    if ($role !== 'admin') {
        respondFailed(401, "Must be admin only for this process.");
    }
}

function handleFetchAllUsers($db)
{
    $decodedData = ensureAndGetDecodedAccessToken();

    $id = $decodedData['id'];

    try {
        $row = dbFetch(
            $db,
            cols: 'role',
            table: 'accounts',
            condition: 'WHERE id = ?',
            execute: [$id]
        );

        ensureIsAdmin($row['role']);

        $rows = dbFetchAll(
            $db,
            cols: "id, username, email, role, verified",
            table: "accounts",
            condition: "WHERE id != ?",
            execute: [$id]
        );

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
