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

abstract class SrcCheckHelper extends JHelperContent
{
    public static function addSubmenu($submenu) 
    {
srcCheckLog::start();
        JHtmlSidebar::addEntry(
            JText::_('COM_SRCCHECK_SUBMENU_SUMMARY'),
            'index.php?option=com_srccheck&scat=',
            $submenu == 'srcchecks'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_SRCCHECK_SUBMENU_MANAGE'),
            'index.php?option=com_srccheck&view=manage&scat=',
            $submenu == 'manage'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_SRCCHECK_SUBMENU_TRASHCAN'),
            'index.php?option=com_srccheck&view=trashcan&scat=',
            $submenu == 'trashcan'
        );

        $document = JFactory::getDocument();

        if ($submenu == 'manage') 
        {
            $document->setTitle(JText::_('COM_SRCCHECK_ADMINISTRATION_MANAGE'));
        }
        if ($submenu == 'trashcan') 
        {
            $document->setTitle(JText::_('COM_SRCCHECK_ADMINISTRATION_TRASHCAN'));
        }
srcCheckLog::stop();
    }
}