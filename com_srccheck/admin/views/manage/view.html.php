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

class SrcCheckViewManage extends JViewLegacy
{
    function display($tpl = null)
    {
srcCheckLog::start();
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
srcCheckLog::debug( $this->getLayout() );
        if ($this->getLayout() !== 'fileHistory')
        {
srcCheckLog::debug( "I'm in modal: fileHistory" );
SrcCheckHelper::addSubmenu('manage');
            $this->addToolBar();
            $this->sidebar = JHtmlSidebar::render();
        }
        $this->setDocument();
        parent::display($tpl);
srcCheckLog::stop();
    }
    protected function addToolBar()
    {
srcCheckLog::start();
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
srcCheckLog::stop();
    }
    protected function setDocument() 
    {
	$document = JFactory::getDocument();
	$document->setTitle(JText::_( 'COM_SRCCHECK_ADMINISTRATION_MANAGE' ) );
    }
    protected function addHistory( $p )
    {
srcCheckLog::start();
        $title = JText::_( "COM_SRCCHECK_MANAGE_FILE_HISTORY" ) . " " . $p[ "filename" ];
srcCheckLog::debug( "title = >" . $title . "<"
                    . "\nselector = >" . $p[ "selector" ] . "<"
                    . "\nfile_id = >" . $p[ "file_id" ] . "<"
                    . "\ntrustedarchive_id = >" . $p[ "trustedarchive_id" ] . "<"
                  );
        $data = array(
            "selector" => $p[ "selector" ],
            "params"   => array( "title"    => $title,
                                 "url"      => "index.php?option=com_srccheck&view=filehistory&tmpl=component&scat=" . $p[ "trustedarchive_id" ] . "&file_id=".$p[ "file_id" ],
                                 "height"   => "800px",
                                 "footer"   => '<a role="button" class="btn" data-dismiss="modal" aria-hidden="true">' . JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>'
                               )
                     );
        $layout = new JLayoutFile( "joomla.modal.main" );
srcCheckLog::stop();
        return $layout->render( $data );
    }
}
