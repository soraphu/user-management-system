<?php
function handleFetchUser()
{
    $rawData = ensureAndGetDecodedAccessToken();

    http_response_code(200);
    echo json_encode(
        [
            "success" => true,
            "message" => "Fetch user successful.",
            "user" => $rawData
        ]
    );
}
function handleChangeUsername($db)
{
    $input = ensureAndGetRequestBody();
    if (empty($input['username'])) {
        respond400FieldsMissing();
    }

    $decodedData = ensureAndGetDecodedAccessToken();
    ensureNotSpecificAdmin(id: $decodedData['id']);

    $newUsername = $input['new_username'];

    if (empty($newUsername)) {
        respond400FieldsMissing();
    }

    if (strlen($newUsername) < 3) {
        respondFailed(400, "Username must at least 3 characters long.");
    }

    if ($decodedData['username'] === $newUsername) {
        respondFailed(409, "Current username was same as new.");
    }

    try {
        $sqlChangeUsername = "UPDATE accounts SET username = ? WHERE id = ?";
        dbSqlExecute($db, sql: $sqlChangeUsername, execute: [$input['new_username'], $decodedData['id']]);

        respondSuccess(200, "Username updated successfully.");
    } catch (\Throwable $th) {
        respondFailed(500, $th->getMessage());
    }
} //handleChangeUsername.