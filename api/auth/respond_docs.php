<?php
function respondRootDocs()
{
    echo json_encode(
        [
            "info" => [
                "title" => "Welcome to my User Mangement System API.",
                "version" => "1.0.0",
                "note" => "To see API tutorial go to https://domain.com/api"
            ],
        ]
    );
    exit;
} //root doc

function respondApiDocs()
{
    echo json_encode([
        "info" => [
            "title" => "User Management System API",
            "base_url" => "https://domain.com/api/v1",
            "version" => "1.0.0"
        ],
        "endpoints" => [
            "/auth" => [
                "/register" => [
                    "description" => "Register a new user.",
                    "method" => "POST",
                    "requestBody" => [
                        "content_type" => "application/json",
                        "schema" => [
                            "username" => "string (min: 3)",
                            "email" => "string (valid email)",
                            "password" => "string (min: 8)"
                        ]
                    ],
                    "response" => [
                        "201" => ["success" => true, "message" => "User registered."],
                        "400" => [
                            "success" => false,
                            "message" => [
                                "Data can't be empty.",
                                "Password must be at least 8 characters long.",
                                "Required fields are missing.",
                            ]
                        ],
                        "403" => [
                            "success" => false,
                            "message" => [
                                "Invalid email format.",
                                "Please use a fake email (e.g. @test.com) for PDPA safety.",
                            ]
                        ],
                        "409" => ["success" => false, "message" => "This email already exists."],
                        "500" => ["success" => false, "message" => "Internal server error."]
                    ]
                ],

                "/login" => [
                    "description" => "Authenticate user and issue tokens.",
                    "method" => "POST",
                    "requestBody" => [
                        "content_type" => "application/json",
                        "schema" => [
                            "email" => "string (required)",
                            "password" => "string (required)"
                        ]
                    ],
                    "response" => [
                        "200" => [
                            "success" => true,
                            "access_token" => "string (JWT - Store in JS Memory)",
                            "note" => "Refresh token is automatically set via HttpOnly Cookie."
                        ],
                        "401" => ["success" => false, "message" => "Invalid credentials."],
                        "500" => ["success" => false, "message" => "Database connection error."]
                    ]
                ],

                "/refresh-token" => [
                    "description" => "Exchange a Refresh Token (from cookie) for a new Access Token.",
                    "method" => "POST",
                    "note" => "Does not require a request body. Reads 'refresh_token' cookie automatically.",
                    "response" => [
                        "200" => [
                            "success" => true,
                            "access_token" => "string (New JWT)"
                        ],
                        "401" => ["success" => false, "message" => "Session expired. Please login again."],
                        "500" => ["success" => false, "message" => "Internal server error."]
                    ]
                ],

                "/password/forget" => [
                    "description" => "Initiate password reset flow by sending an email.",
                    "method" => "POST",
                    "requestBody" => [
                        "content_type" => "application/json",
                        "schema" => ["email" => "string (valid email)"]
                    ],
                    "response" => [
                        "200" => ["success" => true, "message" => "Reset link sent to your email."],
                        "404" => ["success" => false, "message" => "User with this email not found."],
                        "500" => ["success" => false, "message" => "Internal server error."]
                    ]
                ],

                "/password/reset" => [
                    "description" => "Update password using a valid reset token.",
                    "method" => "POST",
                    "requestBody" => [
                        "content_type" => "application/json",
                        "schema" => [
                            "token" => "string (required)",
                            "new_password" => "string (min: 8)"
                        ]
                    ],
                    "response" => [
                        "200" => ["success" => true, "message" => "Password updated successfully."],
                        "400" => ["success" => false, "message" => "Invalid or expired token."],
                        "500" => ["success" => false, "message" => "Internal server error."]
                    ]
                ],

                "/logout" => [
                    "description" => "Revoke refresh token and clear cookies.",
                    "method" => "POST",
                    "response" => [
                        "200" => ["success" => true, "message" => "Logged out. Database record deleted."],
                        "500" => ["success" => false, "message" => "Could not complete logout on server."]
                    ]
                ]
            ]
        ]
    ]);
    exit;
} //api doc