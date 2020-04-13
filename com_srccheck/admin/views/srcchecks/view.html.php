<?php

/* 
* Copyright (C) 2020 Your Name <your.name at your.org>
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @package     Joomla.Administrator
* @subpackage  com_srccheck
*
* @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class SrcCheckViewSrcChecks extends JViewLegacy
{
	/**
	 * Display the Src Check view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	function display($tpl = null)
	{
		// Get data from the model
		$this->items		= $this->get('Items');

                // What Access Permissions does this user have? What can (s)he do?
		$this->canDo = JHelperContent::getActions('com_srccheck');
                
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
                    throw new Exception(implode("\n", $errors), 500);
//		JError::raiseError(500, implode('<br />', $errors));
//			return false;
		}

                // Set the submenu
		SrcCheckHelper::addSubmenu('srcchecks');

                // Set the toolbar
		$this->addToolBar();

                // Set the document
		$this->setDocument();
                
                $this->sidebar = JHtmlSidebar::render();

                // Display the template
		parent::display($tpl);
	}
        /**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolBar()
	{
            JToolbarHelper::title(JText::_('COM_SRCCHECH_MANAGER_TITLE'));

            if ($this->canDo->get('srcchecks.verify')) 
            {
                JToolbarHelper::custom('srcchecks.verify', 'refresh.png', null, 'COM_SRCCHECK_BTN_VERIFY',false);
            }

            // Options button.
            if (JFactory::getUser()->authorise('core.admin', 'com_srccheck')) 
            {
                JToolBarHelper::preferences('com_srccheck');
            }
	}
        	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_SRCCHECK_ADMINISTRATION'));
	}
}
