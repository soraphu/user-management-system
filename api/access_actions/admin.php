<?php
function ensureNotSpecificAdmin($uid)
{
    $forbiddenId = (int) $_ENV['STATIC_ADMIN_ID'];

    if ($uid === $forbiddenId) {
        respondFailed(403, "Hehe, this account not allow to change any infomation.");
        exit;
    }
}

function ensureIsAdmin($db, $id)
{
    try {
        $user = dbFetch(
            $db,
            cols: 'role',
            table: 'accounts',
            condition: 'WHERE id = ?',
            execute: [$id]
        );

        if (empty($user)) {
            respondFailed(404, "User not found.");
        }

        if ($user['role'] !== 'admin') {
            respondFailed(401, "Must be admin only for this process.");
        }
    } catch (\Throwable $th) {
        respondFailed(500, $th->getMessage());
    }
}

function handleFetchAllUsers($db)
{
    $decodedData = ensureAndGetDecodedAccessToken();
    $operatorId = $decodedData['id'];

    ensureIsAdmin($db, $operatorId);

    try {

        $rows = dbFetchAll(
            $db,
            cols: "id, username, email, role, verified",
            table: "accounts",
            condition: "WHERE id != ?",
            execute: [$operatorId]
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

function handleEditUserInfo($db)
{
    $input = ensureAndGetRequestBody();

    $uid = $input['uid'];
    $username = $input['username'];
    $role = $input['role'];

    $decodedData = ensureAndGetDecodedAccessToken();

    ensureNotSpecificAdmin($uid);

    $operatorId = $decodedData['id'];

    ensureIsAdmin($db, $operatorId);

    try {
        $sqlEditUserInfo = 'UPDATE accounts SET username = ?, role = ?  WHERE id = ?';
        dbSqlExecute(
            $db,
            sql: $sqlEditUserInfo,
            execute: [$username, $role, $uid]
        );

        respondSuccess(200, "Edit UID: 1 info successfully");
    } catch (\Throwable $th) {
        respondFailed(500, $th->getMessage());
    }
}//handleEditUserInfo