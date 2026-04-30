<?php
function responseError($status, $message)
{
    http_response_code($status);
    echo json_encode(["success" => false, "message" => $message]);
    exit;
}

function responseSuccess($status, $message)
{
    http_response_code($status);
    echo json_encode(["success" => true, "message" => $message]);
    exit;
}

function respondPageNotFound()
{
    http_response_code(404);
    echo json_encode(["error" => "Page not found."]);
    exit;
} //Handle page not found.

