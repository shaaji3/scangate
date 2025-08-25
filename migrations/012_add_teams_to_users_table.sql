ALTER TABLE users
ADD COLUMN parent_planner_id INT NULL DEFAULT NULL,
ADD CONSTRAINT fk_parent_planner
FOREIGN KEY (parent_planner_id)
REFERENCES users(id)
ON DELETE SET NULL;
