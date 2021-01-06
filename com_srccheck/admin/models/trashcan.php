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

use Joomla\CMS\Factory;

/**
 * HelloWorldList Model
 *
 * @since  0.0.1
 */
class SrcCheckModelTrashcan extends JModelList
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
            	'filename' //,
//            	'status',
//              'veryfied'
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
srcCheckLog::start();
        // Initialize variables.
    	$db                 = JFactory::getDbo();
    	$query              = $db->getQuery(true);
        $trustedarchive_id  = JFactory::getApplication()->input->get( "scat" );

        // Create the base select statement.
    	$query->select( "cf.id AS file_id, cf.path AS path, cf.filename AS filename, cf.status AS status" )
              ->from( $db->quoteName("#__crc_files", "cf") )
              ->where( $db->quoteName( "cf.status" ) . "=" . FILE_STATUS_IN_TRASHCAN );
        
        $query->select($db->quoteName('cc.veryfied', 'veryfied'))
              ->select($db->quoteName('cc.id', 'check_id'))
              ->join('LEFT', $db->quoteName('#__crc_check', 'cc') . ' ON cc.crc_files_id = cf.id AND (cc.crc_files_id, cc.crc_check_history_id) IN (select cct.crc_files_id, MAX(cct.crc_check_history_id) FROM #__crc_check AS cct group by cct.crc_files_id)' );

        $query->select( 'cch.id AS last_check_id')
              ->join('LEFT', $db->quoteName('#__crc_check_history', 'cch') . ' ON cch.id = cc.crc_check_history_id')
              ->join( '', $db->quoteName( '#__crc_files_has_trustedarchive', 'ctfa' ) . ' ON ctfa.crc_files_id = cf.id AND ctfa.crc_trustedarchive_id = ' . $trustedarchive_id );

        // Filter: like / search
        $search = $this->getState('filter.search');

        if (!empty($search))
        {
            $like = $db->quote('%' . $search . '%');
            $query->where( '((cf.path LIKE ' . $like . ') OR (cf.filename LIKE ' . $like . '))');
        }

        // Add the list ordering clause.
	$orderCol	= $this->state->get('list.ordering', 'path');
	$orderDirn 	= $this->state->get('list.direction', 'asc');

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
srcCheckLog::debug( "query = >>" . $query . "<<" );

srcCheckLog::stop();
        return $query;
    }
}