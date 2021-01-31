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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

abstract class SrcCheckHelper extends JHelperContent
{
    /**
     * Configure the Linkbar.
     *
     * @return Bool
     */

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

        // Set some global property
        $document = JFactory::getDocument();
//        $document->addStyleDeclaration('.icon-48-helloworld ' .
//            '{background-image: url(../media/com_helloworld/images/tux-48x48.png);}');

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