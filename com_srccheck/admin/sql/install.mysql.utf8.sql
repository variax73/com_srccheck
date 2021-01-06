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
  `path` VARCHAR(4096) NOT NULL,
  `filename` VARCHAR(512) NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` USING BTREE (`id`) VISIBLE,
  INDEX `idx_filename` USING BTREE (`filename`) VISIBLE)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `#__crc_check_history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__crc_check_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `users_id` INT(11) NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__crc_tmp`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__crc_tmp` (
  `path` VARCHAR(4096) NOT NULL,
  `filename` VARCHAR(512) NOT NULL,
  `crc` VARCHAR(32) NOT NULL,
  `uuid` VARCHAR(36) NOT NULL,
  INDEX `idx_filename` (`filename` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__crc_trustedarchive`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__crc_trustedarchive` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `path` VARCHAR(4096) NOT NULL,
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
  INDEX `fk_trustedarchive_last_check_histor_id_idx` (`last_check_history_id` ASC) VISIBLE)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `#__crc_check`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__crc_check` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `crc` VARCHAR(32) NOT NULL,
  `veryfied` TINYINT NOT NULL DEFAULT 0,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `crc_files_id` BIGINT UNSIGNED NOT NULL,
  `crc_check_history_id` BIGINT UNSIGNED NOT NULL,
  `ta_localisation` VARCHAR(36) NOT NULL,
  `crc_trustedarchive_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) INVISIBLE,
  INDEX `fk_check_files_id_idx` USING BTREE (`crc_files_id`) VISIBLE,
  INDEX `fk_check_history_id_idx` USING BTREE (`crc_check_history_id`) VISIBLE,
  INDEX `fk_check_trustedarchive_id_idx` USING BTREE (`crc_trustedarchive_id`) VISIBLE,
  CONSTRAINT `fk_crc_check_crc_files`
    FOREIGN KEY (`crc_files_id`)
    REFERENCES `#__crc_files` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_crc_check_crc_check_history`
    FOREIGN KEY (`crc_check_history_id`)
    REFERENCES `#__crc_check_history` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_crc_check_crc_trustedarchive_id`
    FOREIGN KEY (`crc_trustedarchive_id`)
    REFERENCES `#__crc_trustedarchive` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__crc_files_has_trustedarchive`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__crc_files_has_trustedarchive` (
  `crc_files_id` BIGINT UNSIGNED NOT NULL,
  `crc_trustedarchive_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`crc_files_id`, `crc_trustedarchive_id`),
  INDEX `fk_files_has_trustedarchive_trustedarchive_id_idx` (`crc_trustedarchive_id` ASC) VISIBLE,
  INDEX `fk_files_has_trustedarchive_files_id_idx` (`crc_files_id` ASC) INVISIBLE,
  UNIQUE INDEX `fk_files_has_trustedarchive_unique` (`crc_files_id` ASC, `crc_trustedarchive_id` ASC) INVISIBLE,
  CONSTRAINT `fk_files_has_trustedarchive_files_id`
    FOREIGN KEY (`crc_files_id`)
    REFERENCES `#__crc_files` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_files_has_trustedarchive_trustedarchive_id`
    FOREIGN KEY (`crc_trustedarchive_id`)
    REFERENCES `#__crc_trustedarchive` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__crc_files_excluded`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__crc_files_excluded` (
  `path` VARCHAR(4096) NOT NULL,
  `filename` VARCHAR(512),
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `crc_trustedarchive_id` BIGINT UNSIGNED NOT NULL,
  INDEX `idx_filename` USING BTREE (`filename`) INVISIBLE,
  CONSTRAINT `fk_crc_files_excluded_crc_TrustedArchive_id`
    FOREIGN KEY (`crc_trustedarchive_id`)
    REFERENCES `#__crc_trustedarchive` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- View `#__crc_v_summary`
-- -----------------------------------------------------
CREATE OR REPLACE VIEW `#__crc_v_summary` AS
SELECT cta.id, cta.path as ta_path, cta.name as ta_name, cta.filename as ta_filename, cta.root AS root, count( distinct concat( cf.path,cf.filename ) ) AS total_count_files, SUM(if(cf.status=0,1,0)) AS new_files, SUM(if(cf.status=2,1,0)) AS deleted_files,
SUM(if(cc.veryfied = 1,1,0)) AS count_veryfied_positive, SUM(if(cc.veryfied <> 1,1,0)) AS count_veryfied_negative,
max(ccf.timestamp) AS last_check_time, ccf.users_id AS user_id, IFNULL( u.username, 'Cron') AS user_login, IFNULL(u.name,'Cron') AS user_name 
  FROM #__crc_trustedarchive cta
  LEFT JOIN #__crc_files_has_trustedarchive cfta 	ON cfta.crc_trustedarchive_id = cta.id
  LEFT JOIN #__crc_files cf 				ON cf.id = cfta.crc_files_id
  LEFT JOIN #__crc_check cc 				ON cc.crc_files_id = cf.id AND cc.crc_trustedarchive_id = cta.id
  INNER JOIN ( SELECT crc_files_id, MAX( `crc_check_history_id` ) AS `max_check_history_id` FROM `#__crc_check` GROUP BY crc_files_id ) AS cc_max ON cc.crc_files_id = cc_max.crc_files_id AND cc.crc_check_history_id = cc_max.max_check_history_id 
  LEFT JOIN #__crc_check_history ccf			ON ccf.id = cc.crc_check_history_id
  LEFT JOIN #__users u 					ON u.id = ccf.users_id
  GROUP BY cta.id, cta.path, cta.name, cta.filename, cta.root, ccf.users_id, u.username, u.name;

