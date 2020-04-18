<?php
/**
 ************************************************************************
 Source Check - module that verifies the integrity of Joomla files
 ************************************************************************
 * @author    Maciej Bednarski (Green Line) <maciek.bednarski@gmail.com>
 * @copyright Copyright (C) 2020 Green Line. All Rights Reserved.
 * @license   GNU General Public License version 3, or later
 * @version   HEAD
 ************************************************************************
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
