-- MySQL Workbench Forward Engineering

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
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) VISIBLE)
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
    REFERENCES `#__users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `mydb`.`#__crc_check`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`#__crc_check` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `crc` VARCHAR(32) NOT NULL,
  `veryfied` TINYINT NOT NULL DEFAULT 0,
  `checked_out` INT(10) NOT NULL DEFAULT 0,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `crc_files_id` BIGINT UNSIGNED NOT NULL,
  `checked_out_time` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`, `checked_out_time`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) VISIBLE,
  INDEX `fk_crc_check_crc_files_idx` (`crc_files_id` ASC) VISIBLE,
  INDEX `fk_crc_check_crc_check_history_idx` (`checked_out_time` ASC) VISIBLE,
  CONSTRAINT `fk_crc_check_crc_files`
    FOREIGN KEY (`crc_files_id`)
    REFERENCES `mydb`.`#__crc_files` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_crc_check_crc_check_history`
    FOREIGN KEY (`checked_out_time`)
    REFERENCES `mydb`.`#__crc_check_history` (`id`)
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

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
