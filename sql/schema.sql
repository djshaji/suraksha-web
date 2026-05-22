CREATE TABLE IF NOT EXISTS guards (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(191) NOT NULL UNIQUE,
  google_sub_id VARCHAR(64) NOT NULL UNIQUE,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_guards_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS refresh_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  guard_id BIGINT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_refresh_guard FOREIGN KEY (guard_id) REFERENCES guards(id) ON DELETE CASCADE,
  INDEX idx_refresh_guard (guard_id),
  INDEX idx_refresh_expires (expires_at),
  UNIQUE KEY uq_refresh_hash (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS visitor_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  visitor_userid VARCHAR(120) NOT NULL,
  guard_id BIGINT UNSIGNED NOT NULL,
  log_date DATE NOT NULL,
  log_time TIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_visitor_guard FOREIGN KEY (guard_id) REFERENCES guards(id) ON DELETE RESTRICT,
  INDEX idx_visitor_guard (guard_id),
  INDEX idx_visitor_date (log_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;