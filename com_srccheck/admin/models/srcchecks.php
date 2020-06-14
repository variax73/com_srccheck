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

class SrcCheckModelSrcChecks extends JModelList
{
	protected function getListQuery()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
                ->from($db->quoteName('#__crc_v_summary'));
		return $query;
	}
}