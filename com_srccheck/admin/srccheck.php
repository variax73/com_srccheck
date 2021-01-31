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
include_once (JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'srcchecklog.php');
include_once (JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'trustedarchive.php');
$srcCheckLogger = new srcCheckLog();

// Set some global property
$document = JFactory::getDocument();
//$document->addStyleDeclaration('.icon-helloworld {background-image: url(../media/com_helloworld/images/tux-16x16.png);}');


// Access check: is this user allowed to access the backend of this component?
if ( !JFactory::getUser()->authorise('core.manage', 'com_srccheck') )
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Require helper file
JLoader::register('SrcCheckHelper', JPATH_COMPONENT . '/helpers/srccheck.php');

// Get an instance of the controller prefixed by HelloWorld
$controller = JControllerLegacy::getInstance('SrcCheck');

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();