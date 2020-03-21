SELECT *
  FROM tst_crc_check cc,
       tst_crc_check_history ch
 where cc.crc_check_history_id=ch.id
order by cc.crc_files_id desc;

select max(cid.id) c_id, max(pid.id) p_id
  from tst_crc_check_history cid,
       tst_crc_check_history pid
 where pid.id < cid.id;

SELECT * -- count(*) 
  FROM tst_crc_check ccc,
       tst_crc_check ccp,
       (SELECT max(cid.id) c_id, max(pid.id) p_id
          FROM tst_crc_check_history cid,
               tst_crc_check_history pid
         WHERE pid.id < cid.id) ids
 WHERE ccc.crc_check_history_id = ids.c_id
   AND ccp.crc_check_history_id = ids.p_id
   AND ccc.crc_files_id = ccp.crc_files_id
   AND ccp.veryfied = 1;

