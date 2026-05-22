START TRANSACTION;

INSERT INTO guards (name, email, google_sub_id, status)
VALUES
  ('Aarav Singh', 'aarav.singh@spmr.edu', 'sample-google-sub-001', 'active'),
  ('Meera Kaul', 'meera.kaul@spmr.edu', 'sample-google-sub-002', 'active'),
  ('Zoya Bhat', 'zoya.bhat@spmr.edu', 'sample-google-sub-003', 'active'),
  ('Rohan Gupta', 'rohan.gupta@spmr.edu', 'sample-google-sub-004', 'inactive')
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  status = VALUES(status),
  updated_at = CURRENT_TIMESTAMP;

DELETE FROM visitor_logs
WHERE visitor_userid LIKE 'SAMPLE-%'
  AND log_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE();

INSERT INTO visitor_logs (visitor_userid, guard_id, log_date, log_time)
SELECT 'SAMPLE-1001', id, DATE_SUB(CURDATE(), INTERVAL 6 DAY), '09:03:00'
FROM guards WHERE email = 'aarav.singh@spmr.edu' LIMIT 1;

INSERT INTO visitor_logs (visitor_userid, guard_id, log_date, log_time)
SELECT 'SAMPLE-1002', id, DATE_SUB(CURDATE(), INTERVAL 6 DAY), '09:45:00'
FROM guards WHERE email = 'meera.kaul@spmr.edu' LIMIT 1;

INSERT INTO visitor_logs (visitor_userid, guard_id, log_date, log_time)
SELECT 'SAMPLE-1003', id, DATE_SUB(CURDATE(), INTERVAL 5 DAY), '08:55:00'
FROM guards WHERE email = 'aarav.singh@spmr.edu' LIMIT 1;

INSERT INTO visitor_logs (visitor_userid, guard_id, log_date, log_time)
SELECT 'SAMPLE-1004', id, DATE_SUB(CURDATE(), INTERVAL 5 DAY), '10:18:00'
FROM guards WHERE email = 'zoya.bhat@spmr.edu' LIMIT 1;

INSERT INTO visitor_logs (visitor_userid, guard_id, log_date, log_time)
SELECT 'SAMPLE-1005', id, DATE_SUB(CURDATE(), INTERVAL 4 DAY), '09:20:00'
FROM guards WHERE email = 'meera.kaul@spmr.edu' LIMIT 1;

INSERT INTO visitor_logs (visitor_userid, guard_id, log_date, log_time)
SELECT 'SAMPLE-1006', id, DATE_SUB(CURDATE(), INTERVAL 4 DAY), '10:24:00'
FROM guards WHERE email = 'zoya.bhat@spmr.edu' LIMIT 1;

INSERT INTO visitor_logs (visitor_userid, guard_id, log_date, log_time)
SELECT 'SAMPLE-1007', id, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '09:08:00'
FROM guards WHERE email = 'aarav.singh@spmr.edu' LIMIT 1;

INSERT INTO visitor_logs (visitor_userid, guard_id, log_date, log_time)
SELECT 'SAMPLE-1008', id, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '09:58:00'
FROM guards WHERE email = 'meera.kaul@spmr.edu' LIMIT 1;

INSERT INTO visitor_logs (visitor_userid, guard_id, log_date, log_time)
SELECT 'SAMPLE-1009', id, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '10:36:00'
FROM guards WHERE email = 'zoya.bhat@spmr.edu' LIMIT 1;

INSERT INTO visitor_logs (visitor_userid, guard_id, log_date, log_time)
SELECT 'SAMPLE-1010', id, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '09:14:00'
FROM guards WHERE email = 'aarav.singh@spmr.edu' LIMIT 1;

INSERT INTO visitor_logs (visitor_userid, guard_id, log_date, log_time)
SELECT 'SAMPLE-1011', id, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '08:49:00'
FROM guards WHERE email = 'meera.kaul@spmr.edu' LIMIT 1;

INSERT INTO visitor_logs (visitor_userid, guard_id, log_date, log_time)
SELECT 'SAMPLE-1012', id, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '10:12:00'
FROM guards WHERE email = 'zoya.bhat@spmr.edu' LIMIT 1;

INSERT INTO visitor_logs (visitor_userid, guard_id, log_date, log_time)
SELECT 'SAMPLE-1013', id, CURDATE(), '09:02:00'
FROM guards WHERE email = 'aarav.singh@spmr.edu' LIMIT 1;

INSERT INTO visitor_logs (visitor_userid, guard_id, log_date, log_time)
SELECT 'SAMPLE-1014', id, CURDATE(), '09:41:00'
FROM guards WHERE email = 'meera.kaul@spmr.edu' LIMIT 1;

INSERT INTO visitor_logs (visitor_userid, guard_id, log_date, log_time)
SELECT 'SAMPLE-1015', id, CURDATE(), '10:28:00'
FROM guards WHERE email = 'zoya.bhat@spmr.edu' LIMIT 1;

COMMIT;