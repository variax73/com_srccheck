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

class SrcCheckViewManage extends JViewLegacy
{
	function display($tpl = null)
	{
            $app = JFactory::getApplication();
            $context = "srccheck.list.admin.manage";

            $this->items		= $this->get('Items');
            $this->pagination           = $this->get('Pagination');
            $this->state		= $this->get('State');
            $this->filter_order 	= $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'path', 'cmd');
            $this->filter_order_Dir     = $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', 'asc', 'cmd');
            $this->filterForm    	= $this->get('FilterForm');
            $this->activeFilters 	= $this->get('ActiveFilters');

            $this->canDo = JHelperContent::getActions('com_srccheck');

            if (count($errors = $this->get('Errors')))
            {
                JFactory::getApplication()->enqueueMessage(500, implode('<br />', $errors));
                return false;
            }

            SrcCheckHelper::addSubmenu('manage');

            $this->addToolBar();
            
            $this->setDocument();

            $this->sidebar = JHtmlSidebar::render();                        

            parent::display($tpl);
	}
	protected function addToolBar()
	{
            JToolbarHelper::title(JText::_('COM_SRCCHECK_ADMINISTRATION_MANAGE'));

            if ($this->canDo->get('srccheck.verify')) 
            {
                JToolbarHelper::custom('srcchecks.verify', 'refresh.png', null, 'COM_SRCCHECK_BTN_VERIFY',false);
            }
            if ($this->canDo->get('srccheck.valid')) 
            {
                JToolbarHelper::custom('srcchecks.valid', 'checkin.png', null, 'COM_SRCCHECK_BTN_VALID',true);
            }
            if ($this->canDo->get('srccheck.erase')) 
            {
                JToolbarHelper::custom('srcchecks.erase', 'cancel.png', null, 'COM_SRCCHECK_BTN_ERASE',true);
            }

            if (JFactory::getUser()->authorise('core.admin', 'com_srccheck')) 
            {
                JToolBarHelper::preferences('com_srccheck');
            }
        }
	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_SRCCHECK_ADMINISTRATION_MANAGE'));
	}
}
