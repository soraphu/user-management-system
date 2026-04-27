CREATE TABLE IF NOT EXISTS accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(10) NOT NULL DEFAULT 'user',
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    verified BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS password_resets (
    email VARCHAR(100) NOT NULL,
    token CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,

    PRIMARY KEY (email),
    UNIQUE (token) 
);

CREATE TABLE IF NOT EXISTS email_verifications (
    email VARCHAR(100) NOT NULL,
    token CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,

    PRIMARY KEY (email),
    UNIQUE (token)
);

CREATE TABLE IF NOT EXISTS inbox (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_email VARCHAR(255) NOT NULL, -- The foreign key column
    sender VARCHAR(255) DEFAULT "server@user.management.system.com",
    subject VARCHAR(255) NOT NULL,
    preview TEXT NOT NULL,
    url VARCHAR(500) DEFAULT NULL,    -- Optional (?)
    buttonLabel VARCHAR(100) DEFAULT NULL, -- Optional (?)
    time TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    isRead BOOLEAN DEFAULT FALSE,
    
    -- Relationship: Links this mail to a specific user in the accounts table
    CONSTRAINT fk_owner 
        FOREIGN KEY (owner_email) 
        REFERENCES accounts(email) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);