/**
 ************************************************************************
 Source Check - module that verifies the integrity of Joomla files
 ************************************************************************
 * @author    Maciej Bednarski (Green Line) <maciek.bednarski@gmail.com>
 * @copyright Copyright (C) 2020 Green Line. All Rights Reserved.
 * @license   GNU General Public License version 3, or later
 * @version   1.0.0
 ************************************************************************
 */

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Table `#__crc_files`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__crc_files` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `path` TEXT NOT NULL,
  `filename` VARCHAR(512) NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` TINYINT(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` USING BTREE (`id`) VISIBLE)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `#__crc_check_history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__crc_check_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `users_id` INT(11) NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) VISIBLE,
  INDEX `fk_crc_check_history_users_idx` (`users_id` ASC) VISIBLE,
  CONSTRAINT `fk_crc_check_history_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `joomla`.`#__users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `#__crc_tmp`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__crc_tmp` (
  `path` TEXT NOT NULL,
  `filename` VARCHAR(512) NOT NULL,
  `crc` VARCHAR(32) NOT NULL)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `#__crc_check`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__crc_check` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `crc` VARCHAR(32) NOT NULL,
  `veryfied` TINYINT NOT NULL DEFAULT 0,
  `checked_out` INT(10) NOT NULL DEFAULT 0,
  `checked_out_time` DATETIME NOT NULL DEFAULT 0000-00-00 00:00:00,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `crc_files_id` BIGINT UNSIGNED NOT NULL,
  `crc_check_history_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) INVISIBLE,
  INDEX `fk_crc_check_crc_files_idx` USING BTREE (`crc_files_id`) VISIBLE,
  INDEX `fk_crc_check_crc_check_history_idx` USING BTREE (`crc_check_history_id`) VISIBLE,
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

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;