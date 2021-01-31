/**
 **************************************************************************
 Source Files Check - component that verifies the integrity of Joomla files
 **************************************************************************
 * @author    Maciej Bednarski (Green Line) <maciek.bednarski@gmail.com>
 * @copyright Copyright (C) 2020 Green Line. All Rights Reserved.
 * @license   GNU General Public License version 3, or later
 * @version   2.0.0
 **************************************************************************
 */

-- -----------------------------------------------------
-- View `__crc_v_summary`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `#__crc_v_summary`;

-- -----------------------------------------------------
-- Table `#__crc_files_excluded`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__crc_files_excluded`;

-- -----------------------------------------------------
-- Table `#__crc_files_has_trustedarchive`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__crc_files_has_trustedarchive`;

-- -----------------------------------------------------
-- Table `#__crc_check`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__crc_check`;

-- -----------------------------------------------------
-- Table `#__crc_trustedarchive`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__crc_trustedarchive`;

-- -----------------------------------------------------
-- Table `#__crc_tmp`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__crc_tmp`;

-- -----------------------------------------------------
-- Table `#__crc_check_history`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__crc_check_history`;

-- -----------------------------------------------------
-- Table `#__crc_files`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__crc_files`;
