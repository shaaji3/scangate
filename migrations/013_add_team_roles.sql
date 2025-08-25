-- First, add the new roles to the roles table for reference
INSERT INTO rbms_roles (role_name) VALUES ('event_manager'), ('gate_agent');

-- Second, update the users table to allow these new roles
ALTER TABLE users
MODIFY COLUMN role ENUM(
    'super_admin',
    'planner',
    'attendee',
    'event_manager',
    'gate_agent'
) NOT NULL;
