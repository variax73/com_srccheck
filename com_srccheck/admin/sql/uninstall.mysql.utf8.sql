/**
 ************************************************************************
 Source Files Check - module that verifies the integrity of Joomla files
 ************************************************************************
 * @author    Maciej Bednarski (Green Line) <maciek.bednarski@gmail.com>
 * @copyright Copyright (C) 2020 Green Line. All Rights Reserved.
 * @license   GNU General Public License version 3, or later
 * @version   HEAD
 ************************************************************************
 */

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- View `mydb`.`view1`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `#__crc_v_summary`;

-- -----------------------------------------------------
-- Table `#__crc_check_history`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__crc_check_history`;


-- -----------------------------------------------------
-- Table `#__crc_check`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__crc_check`;

-- -----------------------------------------------------
-- Table `#__crc_files`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__crc_files`;

-- -----------------------------------------------------
-- Table `#__crc_tmp`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__crc_tmp`;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;