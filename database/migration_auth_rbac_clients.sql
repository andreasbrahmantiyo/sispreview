CREATE TABLE clients (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL,
  name VARCHAR(160) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_clients_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  username VARCHAR(80) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('super_admin','management_viewer','developer','client_viewer') NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  force_password_change TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_username (username),
  INDEX idx_users_role (role),
  INDEX idx_users_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_clients (
  user_id INT UNSIGNED NOT NULL,
  client_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (user_id, client_id),
  UNIQUE KEY uq_user_clients (user_id, client_id),
  CONSTRAINT fk_user_clients_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_user_clients_client
    FOREIGN KEY (client_id) REFERENCES clients(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO clients (code, name, is_active)
VALUES
  ('RSPAD', 'RSPAD Gatot Soebroto', 1),
  ('PRIMAYA', 'Primaya', 1)
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  is_active = VALUES(is_active),
  updated_at = NOW();

ALTER TABLE cr_items ADD COLUMN client_id INT UNSIGNED NULL AFTER id;

UPDATE cr_items
SET client_id = (SELECT id FROM clients WHERE code = 'RSPAD' LIMIT 1)
WHERE client_id IS NULL;

ALTER TABLE cr_items MODIFY client_id INT UNSIGNED NOT NULL;
ALTER TABLE cr_items ADD INDEX idx_cr_items_client_active (client_id, is_active);
ALTER TABLE cr_items ADD CONSTRAINT fk_cr_items_client
  FOREIGN KEY (client_id) REFERENCES clients(id);

ALTER TABLE cr_events ADD COLUMN user_id INT UNSIGNED NULL AFTER created_by;
ALTER TABLE cr_events ADD COLUMN user_name VARCHAR(160) NULL AFTER user_id;
ALTER TABLE cr_events ADD COLUMN action VARCHAR(80) NULL AFTER user_name;
ALTER TABLE cr_events ADD COLUMN entity_type VARCHAR(40) NULL AFTER action;
ALTER TABLE cr_events ADD COLUMN entity_id INT UNSIGNED NULL AFTER entity_type;
ALTER TABLE cr_events ADD COLUMN before_value TEXT NULL AFTER entity_id;
ALTER TABLE cr_events ADD COLUMN after_value TEXT NULL AFTER before_value;
ALTER TABLE cr_events ADD COLUMN ip_address VARCHAR(45) NULL AFTER after_value;
ALTER TABLE cr_events ADD COLUMN user_agent VARCHAR(255) NULL AFTER ip_address;
ALTER TABLE cr_events ADD INDEX idx_event_user (user_id);
ALTER TABLE cr_events ADD CONSTRAINT fk_cr_events_user
  FOREIGN KEY (user_id) REFERENCES users(id)
  ON DELETE SET NULL;

INSERT INTO users (name, username, password_hash, role, is_active, force_password_change)
VALUES
  ('Andreas', 'andreas', '$2y$10$nnE.2kT3sry0qb0EahHBk.GyS8FcZy4yuLRxtLQBprSp/.zhVVVue', 'super_admin', 1, 1),
  ('Pak Handoyo', 'pak_handoyo', '$2y$10$nnE.2kT3sry0qb0EahHBk.GyS8FcZy4yuLRxtLQBprSp/.zhVVVue', 'management_viewer', 1, 1),
  ('Pak Direktur', 'pak_direktur', '$2y$10$nnE.2kT3sry0qb0EahHBk.GyS8FcZy4yuLRxtLQBprSp/.zhVVVue', 'management_viewer', 1, 1),
  ('Pak Heri', 'pak_heri', '$2y$10$nnE.2kT3sry0qb0EahHBk.GyS8FcZy4yuLRxtLQBprSp/.zhVVVue', 'developer', 1, 1),
  ('Pak Febio', 'pak_febio', '$2y$10$nnE.2kT3sry0qb0EahHBk.GyS8FcZy4yuLRxtLQBprSp/.zhVVVue', 'developer', 1, 1),
  ('Pak Morris', 'pak_morris', '$2y$10$nnE.2kT3sry0qb0EahHBk.GyS8FcZy4yuLRxtLQBprSp/.zhVVVue', 'developer', 1, 1)
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  role = VALUES(role),
  is_active = VALUES(is_active),
  updated_at = NOW();

INSERT IGNORE INTO user_clients (user_id, client_id)
SELECT u.id, c.id
FROM users u
JOIN clients c
WHERE u.username IN ('andreas', 'pak_handoyo');

INSERT IGNORE INTO user_clients (user_id, client_id)
SELECT u.id, c.id
FROM users u
JOIN clients c ON c.code = 'RSPAD'
WHERE u.username IN ('pak_direktur', 'pak_heri', 'pak_febio', 'pak_morris');
