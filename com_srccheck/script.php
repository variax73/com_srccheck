<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Script file of Source Check component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_srccheck
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class com_SrcCheckInstallerScript
{
    /**
     * This method is called after a component is installed.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function install($parent) 
    {
//        $parent->getParent()->setRedirectURL('index.php?option=com_srccheck');
    }

    /**
     * This method is called after a component is uninstalled.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function uninstall($parent) 
    {
        echo '<p>' . JText::_('COM_SRCCHECK_UNINSTALL_TEXT') . '</p>';
    }

    /**
     * This method is called after a component is updated.
     *
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function update($parent) 
    {
        echo '<p>' . JText::sprintf('COM_SRCCHECK_UPDATE_TEXT', $parent->get('manifest')->version) . '</p>';

        $parent->getParent()->setRedirectURL('index.php?option=com_srccheck');
    }

    /**
     * Runs just before any installation action is performed on the component.
     * Verifications and pre-requisites should run in this function.
     *
     * @param  string    $type   - Type of PreFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function preflight($type, $parent) 
    {
        echo '<p>' . JText::_('COM_SRCCHECK_PREFLIGHT_' . $type . '_TEXT') . '</p>';
    }

    /**
     * Runs right after any installation action is performed on the component.
     *
     * @param  string    $type   - Type of PostFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    function postflight($type, $parent) 
    {
        include_once (JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'crc_lib.php');

//        echo "type=".$type."<br>";
//        echo "parent=".var_dump($parent)."<br>";

        if($type == "install"){
            generate_crc_tmp( JPATH_ROOT );
            update_crc_from_tmp(TRUE);
        }

        echo '<p>' . JText::_('COM_SRCCHECK_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
    }
}
