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

-- -----------------------------------------------------
-- Table `#__crc_files`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__crc_files` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `path` TEXT NOT NULL,
  `filename` VARCHAR(512) NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` USING BTREE (`id`))
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `#__crc_check_history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__crc_check_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `users_id` INT NOT NULL DEFAULT 0,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `fk_crc_check_history_users_idx` (`users_id` ASC))
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `#__crc_tmp`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__crc_tmp` (
  `path` TEXT NOT NULL,
  `filename` VARCHAR(512) NOT NULL,
  `crc` VARCHAR(32) NOT NULL,
  `file` LONGBLOB NOT NULL)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `#__crc_check`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__crc_check` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `crc` VARCHAR(32) NOT NULL,
  `veryfied` TINYINT NOT NULL DEFAULT 0,
  `checked_out` INT NOT NULL DEFAULT 0,
  `checked_out_time` DATETIME NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `crc_files_id` BIGINT UNSIGNED NOT NULL,
  `crc_check_history_id` BIGINT UNSIGNED NOT NULL,
  `file` LONGBLOB NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `fk_crc_check_crc_files_idx` USING BTREE (`crc_files_id`),
  INDEX `fk_crc_check_crc_check_history_idx` USING BTREE (`crc_check_history_id`),
  CONSTRAINT `fk_crc_check_crc_files`
    FOREIGN KEY (`crc_files_id`)
    REFERENCES `#__crc_files` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_crc_check_crc_check_history`
    FOREIGN KEY (`crc_check_history_id`)
    REFERENCES `#__crc_check_history` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__crc_TrustedArchive`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__crc_TrustedArchive` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `path` VARCHAR(512) NOT NULL,
  `name` VARCHAR(500) NOT NULL,
  `filename` VARCHAR(512) NOT NULL,
  `root` VARCHAR(512) NOT NULL,
  `users_id` INT(11) NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `commentary` TEXT NULL,
  `last_check_history_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) VISIBLE,
  UNIQUE INDEX `root_UNIQUE` (`root` ASC) VISIBLE,
  INDEX `fk_crc_TrustedArchive_crc_check_history_idx` (`last_check_history_id` ASC) INVISIBLE)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `#__crc_files_has_TrustedArchive`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__crc_files_has_TrustedArchive` (
  `crc_files_id` BIGINT UNSIGNED NOT NULL,
  `crc_trustedarchive_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`crc_files_id`, `crc_trustedarchive_id`),
  INDEX `fk_crc_files_has_crc_TrustedArchive_crc_TrustedArc_idx` (`crc_trustedarchive_id` ASC) VISIBLE,
  INDEX `fk_crc_files_has_crc_TrustedArchive_crc_files2_idx` (`crc_files_id` ASC) VISIBLE,
  CONSTRAINT `fk_crc_files_has_crc_TrustedArchive_crc_files2`
    FOREIGN KEY (`crc_files_id`)
    REFERENCES `#__crc_files` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_crc_files_has_crc_TrustedArchive_crc_TrustedArchi2`
    FOREIGN KEY (`crc_trustedarchive_id`)
    REFERENCES `#__crc_TrustedArchive` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- View `#__crc_v_summary`
-- -----------------------------------------------------
CREATE OR REPLACE VIEW `#__crc_v_summary` AS
SELECT cta.id, cta.path as ta_path, cta.name as ta_name, cta.filename as ta_filename, cta.root AS root, count(cf.filename) AS total_count_files, SUM(if(cf.status=0,1,0)) AS new_files, SUM(if(cf.status=2,1,0)) AS deleted_files,
SUM(if(cc.veryfied = 1,1,0)) AS count_veryfied_positive, SUM(if(cc.veryfied <> 1,1,0)) AS count_veryfied_negative,
ccf.timestamp AS last_check_time, ccf.users_id AS user_id, IFNULL( u.username, 'Cron') AS user_login, IFNULL(u.name,'Cron') AS user_name 
  FROM #__crc_trustedarchive cta
  LEFT JOIN #__crc_files_has_trustedarchive cfta    ON cfta.crc_trustedarchive_id = cta.id
  LEFT JOIN #__crc_files cf                         ON cf.id = cfta.crc_files_id
  LEFT JOIN #__crc_check cc                         ON cc.crc_files_id = cf.id AND cc.crc_check_history_id = (SELECT max(ccf.id) FROM #__crc_check_history ccf)
  LEFT JOIN #__crc_check_history ccf                ON ccf.id = cc.crc_check_history_id
  LEFT JOIN #__users u                              ON u.id = ccf.users_id
  GROUP BY cta.id;
