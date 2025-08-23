CREATE TABLE IF NOT EXISTS rbms_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission VARCHAR(255) NOT NULL,
    FOREIGN KEY (role_id) REFERENCES rbms_roles(id) ON DELETE CASCADE
);
