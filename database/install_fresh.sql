-- PERINGATAN: file ini MENGHAPUS tabel dan seluruh data. Hanya untuk instalasi awal / reset lokal. JANGAN dijalankan di produksi.

CREATE DATABASE IF NOT EXISTS monitoring_cr
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE monitoring_cr;

DROP TABLE IF EXISTS cr_events;
DROP TABLE IF EXISTS cr_items;
DROP TABLE IF EXISTS user_clients;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS clients;

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

CREATE TABLE cr_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id INT UNSIGNED NOT NULL,
  display_order INT UNSIGNED NOT NULL,
  form_name VARCHAR(160) NOT NULL,
  sub_area VARCHAR(150) NULL,
  cr_title VARCHAR(255) NOT NULL,
  stage ENUM('Klarifikasi','Scope Fix','Development','QC/UAT','Ready','Released') NOT NULL DEFAULT 'Klarifikasi',
  ticket_no VARCHAR(80) NULL,
  ticket_url TEXT NULL,
  figma_url TEXT NULL,
  pic VARCHAR(80) NOT NULL,
  blocker VARCHAR(80) NOT NULL DEFAULT '-',
  blocker_type ENUM('none','internal','external') NOT NULL DEFAULT 'none',
  target_date DATE NULL,
  target_label VARCHAR(40) NULL,
  today_update TEXT NOT NULL,
  last_update_at DATE NULL,
  is_hold_contract TINYINT(1) NOT NULL DEFAULT 0,
  is_rework TINYINT(1) NOT NULL DEFAULT 0,
  need_clarification TINYINT(1) NOT NULL DEFAULT 0,
  need_decision TINYINT(1) NOT NULL DEFAULT 0,
  is_priority TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_cr_items_client
    FOREIGN KEY (client_id) REFERENCES clients(id),
  INDEX idx_cr_items_client_active (client_id, is_active),
  INDEX idx_stage (stage),
  INDEX idx_target_date (target_date),
  INDEX idx_blocker (blocker),
  INDEX idx_last_update (last_update_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_item_id INT UNSIGNED NOT NULL,
  event_type ENUM('created','stage','field_change','comment') NOT NULL DEFAULT 'field_change',
  field_name VARCHAR(80) NULL,
  old_value TEXT NULL,
  new_value TEXT NULL,
  note TEXT NULL,
  created_by VARCHAR(80) NOT NULL,
  user_id INT UNSIGNED NULL,
  user_name VARCHAR(160) NULL,
  action VARCHAR(80) NULL,
  entity_type VARCHAR(40) NULL,
  entity_id INT UNSIGNED NULL,
  before_value TEXT NULL,
  after_value TEXT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cr_events_item
    FOREIGN KEY (cr_item_id) REFERENCES cr_items(id)
    ON DELETE CASCADE,
  INDEX idx_event_date (created_at),
  INDEX idx_event_item (cr_item_id),
  INDEX idx_event_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO clients (code, name, is_active)
VALUES
  ('RSPAD', 'RSPAD Gatot Soebroto', 1),
  ('PRIMAYA', 'Primaya', 1);

INSERT INTO users (name, username, password_hash, role, is_active, force_password_change)
VALUES
  ('Andreas', 'andreas', '$2y$10$nnE.2kT3sry0qb0EahHBk.GyS8FcZy4yuLRxtLQBprSp/.zhVVVue', 'super_admin', 1, 1),
  ('Pak Handoyo', 'pak_handoyo', '$2y$10$nnE.2kT3sry0qb0EahHBk.GyS8FcZy4yuLRxtLQBprSp/.zhVVVue', 'management_viewer', 1, 1),
  ('Pak Direktur', 'pak_direktur', '$2y$10$nnE.2kT3sry0qb0EahHBk.GyS8FcZy4yuLRxtLQBprSp/.zhVVVue', 'management_viewer', 1, 1),
  ('Pak Heri', 'pak_heri', '$2y$10$nnE.2kT3sry0qb0EahHBk.GyS8FcZy4yuLRxtLQBprSp/.zhVVVue', 'developer', 1, 1),
  ('Pak Febio', 'pak_febio', '$2y$10$nnE.2kT3sry0qb0EahHBk.GyS8FcZy4yuLRxtLQBprSp/.zhVVVue', 'developer', 1, 1),
  ('Pak Morris', 'pak_morris', '$2y$10$nnE.2kT3sry0qb0EahHBk.GyS8FcZy4yuLRxtLQBprSp/.zhVVVue', 'developer', 1, 1);

INSERT INTO user_clients (user_id, client_id)
SELECT u.id, c.id
FROM users u
JOIN clients c
WHERE u.username IN ('andreas', 'pak_handoyo');

INSERT INTO user_clients (user_id, client_id)
SELECT u.id, c.id
FROM users u
JOIN clients c ON c.code = 'RSPAD'
WHERE u.username IN ('pak_direktur', 'pak_heri', 'pak_febio', 'pak_morris');
