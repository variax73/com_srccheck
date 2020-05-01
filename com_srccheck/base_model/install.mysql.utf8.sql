-- MySQL Script generated by MySQL Workbench
-- Fri May  1 14:58:08 2020
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mydb` ;
-- -----------------------------------------------------
-- Schema joomla
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema joomla
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `joomla` ;
USE `mydb` ;

-- -----------------------------------------------------
-- Table `mydb`.`#__crc_files`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`#__crc_files` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `path` TEXT NOT NULL,
  `filename` VARCHAR(512) NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` TINYINT(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` USING BTREE (`id`) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`#__crc_check_history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`#__crc_check_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `users_id` INT(11) NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_count_files` VARCHAR(45) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) VISIBLE,
  CONSTRAINT `fk_crc_check_history_users`
    FOREIGN KEY ()
    REFERENCES `joomla`.`#__users` ()
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`#__crc_tmp`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`#__crc_tmp` (
  `path` TEXT NOT NULL,
  `filename` VARCHAR(512) NOT NULL,
  `crc` VARCHAR(32) NOT NULL)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`#__crc_check`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`#__crc_check` (
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
    REFERENCES `mydb`.`#__crc_files` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_crc_check_crc_check_history`
    FOREIGN KEY (`crc_check_history_id`)
    REFERENCES `mydb`.`#__crc_check_history` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

USE `joomla` ;
USE `mydb` ;

-- -----------------------------------------------------
-- Placeholder table for view `mydb`.`view1`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`view1` (`id` INT);

-- -----------------------------------------------------
-- View `mydb`.`view1`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`view1`;
USE `mydb`;
CREATE  OR REPLACE VIEW `#__crc_v_summary` AS
select q1.*, q2.*, q3.*
  from
  (select count(cf.filename) AS total_count_files, SUM(if(cf.status=0,1,0)) AS new_files, SUM(if(cf.status=2,1,0)) AS deleted_files from dev_crc_files cf) q1,
  (select SUM(if(cc.veryfied = 1,1,0)) AS count_veryfied_positive, SUM(if(cc.veryfied <> 1,1,0)) AS count_veryfied_negative from dev_crc_check cc WHERE cc.crc_check_history_id = (select max(ccf.id) from dev_crc_check_history ccf)) q2,
  (select max(ccf.timestamp) as last_check_time, ccf.users_id as user_id, u.username as user_login, u.name as user_name from #__crc_check_history ccf, #__users u Where ccf.users_id = u.id) q3;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
