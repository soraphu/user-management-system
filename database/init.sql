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
    token CHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,

    PRIMARY KEY (email),
    UNIQUE (token) 
);

CREATE TABLE IF NOT EXISTS email_verifications (
    email VARCHAR(100) NOT NULL,
    token CHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,

    PRIMARY KEY (email),
    UNIQUE (token)
);

CREATE TABLE IF NOT EXISTS inbox (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_email VARCHAR(255) NOT NULL, -- The foreign key column
    sender VARCHAR(255) NOT NULL,
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

CREATE TABLE IF NOT EXISTS refresh_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    
    UNIQUE (token),
    UNIQUE (user_id),
    FOREIGN KEY (user_id) 
        REFERENCES accounts(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ;

-- Insert initial admin user and 15 test accounts in one statement
INSERT IGNORE INTO accounts (role, username, email, password, verified) VALUES 
    ('admin', 'STATIC_ADMIN', 'admin@test.com', '$2y$10$D7Ed4ifbDaKHWqtXXc5.IuvBve09Mv.qipzossZTJTW.BpbFuiA7a', 1),
    ('user', 'john_doe', 'john@example.com', '$2y$10$qZqtj.6aQhvPmMdNYMvLouUc7yJ3vYMfF8XDqF0Z5pVjZV8F3Q1WC', 1),
    ('user', 'jane_smith', 'jane@example.com', '$2y$10$pQcR0rr7B8vNsQzLgNx0Qu3RBnGO0.6cM7T2Yd9KqPXvLx4K8QHPO', 0),
    ('user', 'mike_johnson', 'mike@example.com', '$2y$10$lU6sK8V3N2X5pJwQhFmZeOm9vCzR4dT1aStY7bE6fGhIjKlMnOpQq', 0),
    ('user', 'sarah_williams', 'sarah@example.com', '$2y$10$fW5gN7pL2qRtUvXyZaB3Cu9dEfHjKmNoPqRsT4uVwXyZ8aBcDeFgI', 1),
    ('user', 'alex_brown', 'alex@example.com', '$2y$10$iY1qMn7oJkLpQrSvWxYzAe2fGhIjKlMnOpQrSuTvUwXyZaBcDeFg', 0),
    ('user', 'emma_davis', 'emma@example.com', '$2y$10$bZ3sMt5uVwXyZaBcDeFgHiJ1kLmNoPqRsT4uVwXyZaBcDeFgHiJk', 1),
    ('user', 'chris_miller', 'chris@example.com', '$2y$10$nO2pQrSuTvUwXyZaBcDeFgHiJ1kLmNoPqRsT4uVwXyZaBcDeFgHiJ', 1),
    ('user', 'olivia_taylor', 'olivia@example.com', '$2y$10$eFgHiJ1kLmNoPqRsT4uVwXyZaBcDeFgHiJ1kLmNoPqRsT4uVwXyZaB', 0),
    ('user', 'david_anderson', 'david@example.com', '$2y$10$rSuTvUwXyZaBcDeFgHiJ1kLmNoPqRsT4uVwXyZaBcDeFgHiJ1kLmN', 1),
    ('user', 'sophia_thomas', 'sophia@example.com', '$2y$10$DeFgHiJ1kLmNoPqRsT4uVwXyZaBcDeFgHiJ1kLmNoPqRsT4uVwXy', 1),
    ('user', 'daniel_jackson', 'daniel@example.com', '$2y$10$wXyZaBcDeFgHiJ1kLmNoPqRsT4uVwXyZaBcDeFgHiJ1kLmNoPqRs', 1),
    ('user', 'isabella_white', 'isabella@example.com', '$2y$10$T4uVwXyZaBcDeFgHiJ1kLmNoPqRsT4uVwXyZaBcDeFgHiJ1kLmNo', 1),
    ('user', 'james_harris', 'james@example.com', '$2y$10$qRsT4uVwXyZaBcDeFgHiJ1kLmNoPqRsT4uVwXyZaBcDeFgHiJ1kL', 1),
    ('user', 'mia_martin', 'mia@example.com', '$2y$10$mNoPqRsT4uVwXyZaBcDeFgHiJ1kLmNoPqRsT4uVwXyZaBcDeFgHiJ', 1),
    ('user', 'lucas_garcia', 'lucas@example.com', '$2y$10$pQrSuTvUwXyZaBcDeFgHiJ1kLmNoPqRsT4uVwXyZaBcDeFgHiJ1kL', 1);