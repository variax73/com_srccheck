<?php
/**
 **************************************************************************
 Source Files Check - component that verifies the integrity of Joomla files
 **************************************************************************
 * @author    Maciej Bednarski (Green Line) <maciek.bednarski@gmail.com>
 * @copyright Copyright (C) 2020 Green Line. All Rights Reserved.
 * @license   GNU General Public License version 3, or later
 * @version   HEAD
 **************************************************************************
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class SrcCheckViewTrashcan extends JViewLegacy
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
srcCheckLog::start();
            $app = JFactory::getApplication();
srcCheckLog::debug( "Test 1" );
            $context = "srccheck.list.admin.trashcan";
srcCheckLog::debug( "Test 2" );

            // Get data from the model
            $this->items		= $this->get('Items');
            $this->pagination           = $this->get('Pagination');
            $this->state		= $this->get('State');
            $this->filter_order 	= $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'path', 'cmd');
            $this->filter_order_Dir     = $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', 'asc', 'cmd');
            $this->filterForm    	= $this->get('FilterForm');
            $this->activeFilters 	= $this->get('ActiveFilters');
srcCheckLog::debug( "Test 3" );


            // What Access Permissions does this user have? What can (s)he do?
            $this->canDo = JHelperContent::getActions( "com_srccheck" );

srcCheckLog::debug( "Test 4" );
            // Check for errors.
            if ( count( $errors = $this->get( "Errors" ) ) )
            {
                JFactory::getApplication()->enqueueMessage(500, implode('<br />', $errors));
                return false;
            }

srcCheckLog::debug( "Test 5" );
            // Set the submenu
            SrcCheckHelper::addSubmenu( "trashcan" );

srcCheckLog::debug( "Test 6" );
            // Set the toolbar
            $this->addToolBar();
            
srcCheckLog::debug( "Test 7" );
            // Set the document
            $this->setDocument();
srcCheckLog::debug( "Sidebar render - pre" );
            $this->sidebar = JHtmlSidebar::render();                        
srcCheckLog::debug( "Sidebar render - after" );

            // Display the view
            parent::display($tpl);
srcCheckLog::stop();
	}
        /**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 */
	protected function addToolBar()
	{
srcCheckLog::start();
            $input = JFactory::getApplication()->input;

            JToolbarHelper::title(JText::_('COM_SRCCHECK_ADMINISTRATION_TRASHCAN'));

            if ($this->canDo->get('srccheck.EraseFromTraschcan')) 
            {
                JToolbarHelper::custom('srcchecks.EraseCheckedFromTrashcan', 'checkin.png', null, 'COM_SRCCHECK_BTN_ERASE_SELECTED_FILES_FROM_TRASHCAN',true);
                JToolbarHelper::custom('srcchecks.EraseAllFromTrashcan', 'trash.png', null, 'COM_SRCCHECK_BTN_ERASE_ALL_FILES_FROM_TRASHCAN',false);
//                JToolbarHelper::trash('srcchecks.EraseCheckedFromTrashcan', 'checkin.png', null, 'COM_SRCCHECK_BTN_ERASE_SELECTED_FILES_FROM_TRASHCAN',true);
//                JToolbarHelper::trash('srcchecks.EraseAllFromTrashcan', 'cancel.png', null, 'COM_SRCCHECK_BTN_ERASE_ALL_FILES_FROM_TRASHCAN',false);
            }

            // Options button.
            if (JFactory::getUser()->authorise('core.admin', 'com_srccheck')) 
            {
                JToolBarHelper::preferences('com_srccheck');
            }
srcCheckLog::stop();
	}
        /**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
srcCheckLog::start();
		$document = JFactory::getDocument();
		$document->setTitle(JText::_( "COM_SRCCHECK_ADMINISTRATION_TRASHCAN" ) );
srcCheckLog::stop();
	}
}
