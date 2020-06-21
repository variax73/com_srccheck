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

$document = JFactory::getDocument();

if (!JFactory::getUser()->authorise('core.manage', 'com_srccheck'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}
JLoader::register('SrcCheckHelper', JPATH_COMPONENT . '/helpers/srccheck.php');
$controller = JControllerLegacy::getInstance('SrcCheck');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();