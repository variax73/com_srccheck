<?php
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
defined('_JEXEC') or die('Restricted access');

class SrcCheckViewFileHistory extends JViewLegacy
{
    function display($tpl = null)
    {
srcCheckLog::start();
        $app = JFactory::getApplication();
        $this->items		= $this->get('Items');
        $this->canDo = JHelperContent::getActions('com_srccheck');
        if (count($errors = $this->get('Errors')))
        {
            JFactory::getApplication()->enqueueMessage(500, implode('<br />', $errors));
            return false;
        }
        parent::display($tpl);
srcCheckLog::stop();
    }
}
