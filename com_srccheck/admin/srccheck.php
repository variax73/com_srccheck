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
include_once (JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'srcchecklog.php');
include_once (JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'trustedarchive.php');
$srcCheckLogger = new srcCheckLog();
$document = JFactory::getDocument();
if ( !JFactory::getUser()->authorise('core.manage', 'com_srccheck') )
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}
JLoader::register('SrcCheckHelper', JPATH_COMPONENT . '/helpers/srccheck.php');
$controller = JControllerLegacy::getInstance('SrcCheck');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();