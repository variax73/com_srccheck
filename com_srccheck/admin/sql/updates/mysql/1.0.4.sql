/**
 **************************************************************************
 Source Files Check - component that verifies the integrity of Joomla files
 **************************************************************************
 * @author    Maciej Bednarski (Green Line) <maciek.bednarski@gmail.com>
 * @copyright Copyright (C) 2020 Green Line. All Rights Reserved.
 * @license   GNU General Public License version 3, or later
 * @version   HEAD
 **************************************************************************
 */

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Alter table #__crc_check_history 
-- -----------------------------------------------------
ALTER TABLE `#__crc_check_history` DROP FOREIGN KEY `fk_crc_check_history_users`;

-- -----------------------------------------------------
-- View `#__crc_v_summary`
-- -----------------------------------------------------
CREATE  OR REPLACE VIEW `#__crc_v_summary` AS
SELECT q1.*, q2.*, q3.*
  FROM
  (SELECT count(cf.filename) AS total_count_files, SUM(if(cf.status=0,1,0)) AS new_files, SUM(if(cf.status=2,1,0)) AS deleted_files FROM #__crc_files cf) q1,
  (SELECT SUM(if(cc.veryfied = 1,1,0)) AS count_veryfied_positive, SUM(if(cc.veryfied <> 1,1,0)) AS count_veryfied_negative FROM #__crc_check cc WHERE cc.crc_check_history_id = (SELECT max(ccf.id) FROM #__crc_check_history ccf)) q2,
  (SELECT ccf.timestamp AS last_check_time, ccf.users_id AS user_id, IFNULL( u.username, 'Cron') AS user_login, IFNULL(u.name,'Cron') AS user_name FROM #__crc_check_history ccf LEFT JOIN #__users u ON ccf.users_id = u.id WHERE ccf.id = (SELECT MAX(tccf.id) FROM #__crc_check_history tccf)) q3;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;