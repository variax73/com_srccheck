<?php
/**
 ************************************************************************
 Source Files Check - component that verifies the integrity of Joomla files
 ************************************************************************
 * @author    Maciej Bednarski (Green Line) <maciek.bednarski@gmail.com>
 * @copyright Copyright (C) 2020 Green Line. All Rights Reserved.
 * @license   GNU General Public License version 3, or later
 * @version   1.0.2
 ************************************************************************
 */

defined('_JEXEC') or die('Restricted access');

class SrcCheckViewSrcChecks extends JViewLegacy
{
	function display($tpl = null)
	{
		$this->items = $this->get('Items');
		$this->canDo = JHelperContent::getActions('com_srccheck');
		if (count($errors = $this->get('Errors')))
		{
                    throw new Exception(implode("\n", $errors), 500);
		}
                SrcCheckHelper::addSubmenu('srcchecks');
		$this->addToolBar();
		$this->setDocument();
                $this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}
	protected function addToolBar()
	{
            JToolbarHelper::title(JText::_('COM_SRCCHECH_MANAGER_TITLE'));

            if ($this->canDo->get('srccheck.verify')) 
            {
                JToolbarHelper::custom('srcchecks.verify', 'refresh.png', null, 'COM_SRCCHECK_BTN_VERIFY',false);
            }

            if (JFactory::getUser()->authorise('core.admin', 'com_srccheck')) 
            {
                JToolBarHelper::preferences('com_srccheck');
            }
	}
	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_SRCCHECK_ADMINISTRATION'));
	}
}
