<?php
function respondFailed($status, $message)
{
    http_response_code($status);
    echo json_encode(["success" => false, "message" => $message]);
    exit;
}

function respond400FieldsMissing()
{
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Required fields are missing."]);
    exit;
}

function respondSuccess($status, $message)
{
    http_response_code($status);
    echo json_encode(["success" => true, "message" => $message]);
    exit;
}

function respondPageNotFound()
{
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Page not found."
    ]);
    exit;
} //Handle page not found.

