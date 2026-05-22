INSERT INTO guards (name, email, google_sub_id, status)
VALUES ('Test Guard', 'guard@example.com', 'google-sub-id-placeholder', 'active')
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  status = VALUES(status),
  updated_at = CURRENT_TIMESTAMP;