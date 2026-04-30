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
                                "Request data can't be empty.",
                                "Required fields are missing.",
                                "Password must be at least 8 characters long.",
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

                "/email/verify-request" => [
                    "description" => "Request email verify link (send to inbox).",
                    "method" => "POST",
                    "requestBody" => [
                        "content_type" => "application/json",
                        "schema" => [
                            "email" => "string (required)",
                        ]
                    ],
                    "response" => [
                        "201" => ["success" => true, "message" => "Verify email request was send to {your_email}."],
                        "400" => [
                            "success" => false,
                            "message" => [
                                "Request data can't be empty.",
                                "Required fields are missing.",
                            ]
                        ],
                        "404" => ["success" => false, "message" => "Account with this email not found."],
                        "409" => ["success" => false, "message" => "This email already verified."],
                        "500" => ["success" => false, "message" => "{Catch message}"],
                    ]
                ],

                "/email/verified" => [
                    "description" => "Request to verified email.",
                    "method" => "POST",
                    "requestBody" => [
                        "content_type" => "application/json",
                        "schema" => [
                            "token" => "string (required)",
                        ]
                    ],
                    "response" => [
                        "200" => ["success" => true, "message" => "Email {your_email} verified successfully."],
                        "400" => [
                            "success" => false,
                            "message" => [
                                "Request data can't be empty.",
                                "Required fields are missing.",
                            ]
                        ],
                        "404" => ["success" => false, "message" => "Invalid or expired verification token."],
                        "500" => ["success" => false, "message" => "{Catch message}"],
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
                            "message" => "User login successfully.",
                            "access_token" => "string (JWT - Store in JS Memory)",
                            "refresh_token" => "Automatically set via HttpOnly Cookie."
                        ],
                        "400" => ["success" => false, "message" => "Required fields are missing."],
                        "409" => ["success" => false, "message" => "Invalid Email or Password."],
                        "401" => ["success" => false, "message" => "Verify email required."],
                    ]
                ],

                "login/refresh-token" => [
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

                "/password/forget" => [
                    "description" => "Initiate password reset flow by sending an email.",
                    "method" => "POST",
                    "requestBody" => [
                        "content_type" => "application/json",
                        "schema" => ["email" => "string (valid email)"]
                    ],
                    "response" => [
                        "200" => ["success" => true, "message" => "Reset link was sent to {your_email}."],
                        "400" => ["success" => false, "message" => "Request data can't be empty."],
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
                        "400" => [
                            "success" => false,
                            "message" => [
                                "Required fields are missing.",
                                "Password must be at least 8 characters long.",
                                "Invalid or expired token.",
                            ]
                        ],
                        "404" => ["success" => false, "message" => "Invalid or exipred reset token."],
                        "500" => ["success" => false, "message" => "Internal server error."]
                    ]
                ],

            ]
        ]
    ]);
    exit;
} //api doc