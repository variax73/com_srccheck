<?php
/**
 ************************************************************************
 Source Files Check - module that verifies the integrity of Joomla files
 ************************************************************************
 * @author    Maciej Bednarski (Green Line) <maciek.bednarski@gmail.com>
 * @copyright Copyright (C) 2020 Green Line. All Rights Reserved.
 * @license   GNU General Public License version 3, or later
 * @version   1.0.2
 ************************************************************************
 */

defined('_JEXEC') or die('Restricted access');

class com_SrcCheckInstallerScript
{
    public function install($parent) 
    {
        $parent->getParent()->setRedirectURL('index.php?option=com_srccheck');
    }

    public function uninstall($parent) 
    {
        echo '<p>' . JText::_('COM_SRCCHECK_UNINSTALL_TEXT') . '</p>';
    }

    public function update($parent) 
    {
        echo '<p>' . JText::sprintf('COM_SRCCHECK_UPDATE_TEXT', $parent->get('manifest')->version) . '</p>';

        $parent->getParent()->setRedirectURL('index.php?option=com_srccheck');
    }

    public function preflight($type, $parent) 
    {
        echo '<p>' . JText::_('COM_SRCCHECK_PREFLIGHT_' . $type . '_TEXT') . '</p>';
    }

    function postflight($type, $parent) 
    {
        include_once (JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'crc_lib.php');

        if($type == "install"){
            generate_crc_tmp( JPATH_ROOT );
            update_crc_from_tmp(TRUE);
        }

        echo '<p>' . JText::_('COM_SRCCHECK_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
    }
}
