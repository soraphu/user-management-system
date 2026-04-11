CREATE TABLE IF NOT EXISTS accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(10) NOT NULL DEFAULT 'user',
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add some test data so you can test your login immediately
INSERT INTO accounts (username, email, password, role) VALUES 
('admin', 'admin@example.com', '$2y$10$YourHashedPasswordHere', 'admin'),
('testuser', 'testuser@example.com', '$2y$10$AnotherHashedPassword', 'user');