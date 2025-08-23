CREATE TABLE IF NOT EXISTS rbms_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO rbms_roles (role_name) VALUES ('super_admin'), ('planner'), ('attendee');
