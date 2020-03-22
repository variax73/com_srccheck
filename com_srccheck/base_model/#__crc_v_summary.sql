CREATE VIEW `#__crc_v_summary` AS
select q1.*, q2.*, q3.*
  from
  (select count(cf.filename) AS total_count_files from #__crc_files cf) q1,
  (select SUM(if(cc.veryfied = 1,1,0)) AS count_veryfied_psitive, SUM(if(cc.veryfied <> 1,1,0)) AS count_veryfied_negative from #__crc_check cc) q2,
  (select max(ccf.timestamp) as last_check_time, ccf.users_id as user_id, u.username as user_login, u.name as user_name from #__crc_check_history ccf, #__users u Where ccf.users_id = u.id) q3;