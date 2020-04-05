<?php

/* 
 * Copyright (C) 2020 Your Name <your.name at your.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @package     Joomla.Administrator
 * @subpackage  com_srccheck
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * HelloWorldList Model
 *
 * @since  0.0.1
 */
class SrcCheckModelManage extends JModelList
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     */
    public function __construct($config = array())
    {
    	if (empty($config['filter_fields']))
    	{
            $config['filter_fields'] = array(
            	'path',
            	'filename',
            	'status',
                'veryfied'
            );
    	}
        
   	parent::__construct($config);
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return      string  An SQL query
     */
    protected function getListQuery()
    {
    	// Initialize variables.
    	$db    = JFactory::getDbo();
    	$query = $db->getQuery(true);

        // Create the base select statement.
    	$query->select('cf.path AS path, cf.filename AS filename, cf.status AS status')
              ->from($db->quoteName('#__crc_files', 'cf'));
        
        $query->select($db->quoteName('cc.veryfied', 'veryfied'))
              ->select($db->quoteName('cc.id', 'check_id'))
              ->join('LEFT', $db->quoteName('#__crc_check', 'cc') . ' ON cc.crc_files_id = cf.id AND (cc.crc_files_id, cc.crc_check_history_id) IN (select cct.crc_files_id, MAX(cct.crc_check_history_id) FROM #__crc_check AS cct group by cct.crc_files_id)' );

        $query->select( 'cch.id AS last_check_id')
              ->join('LEFT', $db->quoteName('#__crc_check_history', 'cch') . ' ON cch.id = cc.crc_check_history_id');

//        $query->group($db->quoteName(array('cf.path', 'cf.filename', 'cf.status')));

        // Filter: like / search
        $search = $this->getState('filter.search');

        if (!empty($search))
        {
            $like = $db->quote('%' . $search . '%');
            $query->where( '((cf.path LIKE ' . $like . ') OR (cf.filename LIKE ' . $like . '))');
        }

        // Filtered by file status
        
        $file_status = $this->getState('filter.file_status');

        if( is_numeric( $file_status ) ){
            $query->where( "cf.status = ".(int)$file_status );
        }

        $file_veryfied = $this->getState('filter.file_veryfied');

        if( is_numeric( $file_veryfied ) ){
            $query->where( "cc.veryfied = ".(int)$file_veryfied );
        }

        // Add the list ordering clause.
	$orderCol	= $this->state->get('list.ordering', 'path');
	$orderDirn 	= $this->state->get('list.direction', 'asc');

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

//echo $query;
        return $query;
    }
}