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

class SrcCheckViewFileHistory extends JViewLegacy
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
//            $context = "srccheck.list.admin.manage";
            // Get data from the model
            $this->items		= $this->get('Items');
//            $this->pagination           = $this->get('Pagination');
//            $this->state		= $this->get('State');
//            $this->filter_order 	= $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'path', 'cmd');
//            $this->filter_order_Dir     = $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', 'asc', 'cmd');
//            $this->filterForm    	= $this->get('FilterForm');
//            $this->activeFilters 	= $this->get('ActiveFilters');

            // What Access Permissions does this user have? What can (s)he do?
            $this->canDo = JHelperContent::getActions('com_srccheck');
                
            // Check for errors.
            if (count($errors = $this->get('Errors')))
            {
                JFactory::getApplication()->enqueueMessage(500, implode('<br />', $errors));
                return false;
            }
//srcCheckLog::debug( $this->getLayout() );
//            if ($this->getLayout() !== 'fileHistory')
//            {
//srcCheckLog::debug( "I'm in modal: fileHistory" );
//SrcCheckHelper::addSubmenu('manage');
//                // Set the toolbar
//            $this->addToolBar();
//                $this->sidebar = JHtmlSidebar::render();
//            }
//            // Set the document
//            $this->setDocument();

            // Display the view
            parent::display($tpl);
srcCheckLog::stop();
	}
        /**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 */
//	protected function addToolBar()
//	{
//srcCheckLog::start();
////            $input = JFactory::getApplication()->input;
//
//            JToolbarHelper::title(JText::_('COM_SRCCHECK_ADMINISTRATION_MANAGE'));
//
//            JToolbarHelper::custom('srcchecks.valid', 'checkin.png', null, 'COM_SRCCHECK_BTN_VALID',true);
//srcCheckLog::stop();
//        }
//        /**
//	 * Method to set up the document properties
//	 *
//	 * @return void
//	 */
//	protected function setDocument() 
//	{
//		$document = JFactory::getDocument();
//		$document->setTitle(JText::_( 'COM_SRCCHECK_ADMINISTRATION_MANAGE' ) );
//	}
//
//        protected function addHistory( $p )
//        {
//srcCheckLog::start();
//            $title = JText::_( "COM_SRCCHECK_MANAGE_FILE_HISTORY" ) . " " . $p[ "filename" ];
//srcCheckLog::debug( "title = >" . $title . "<"
//                    . "\nfile_id = >" . $p[ "file_id" ] . "<"
//                    . "\ntrustedarchive_id = >" . $p[ "trustedarchive_id" ] . "<"
//                  );
//            $body = "TT EE SS TT";
//            $data = array(
//                "selector" => "fileHistory".$p[ "file_id" ],
//                "params"   => array( "title"    => $title,
//                                     "url"      => "index.php?option=com_srccheck&view=manage&layout=filehistory&tmpl=component&scat=" . $p[ "trustedarchive_id" ] . "&file_id=".$p[ "file_id" ],
//                                     "width"    => '800px',
//                                     "footer"   => '<a role="button" class="btn" data-dismiss="modal" aria-hidden="true">' . JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>'
//                                   ),
//                "body"     => $body
//            );
//            $layout = new JLayoutFile( "joomla.modal.main" );
//srcCheckLog::stop();
//            return $layout->render( $data );
//        }
}
