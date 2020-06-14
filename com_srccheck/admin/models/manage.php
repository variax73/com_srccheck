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

class SrcCheckModelManage extends JModelList
{
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
    protected function getListQuery()
    {
    	$db    = JFactory::getDbo();
    	$query = $db->getQuery(true);
    	$query->select('cf.id AS file_id, cf.path AS path, cf.filename AS filename, cf.status AS status')
              ->from($db->quoteName('#__crc_files', 'cf'));
        $query->select($db->quoteName('cc.veryfied', 'veryfied'))
              ->select($db->quoteName('cc.id', 'check_id'))
              ->join('LEFT', $db->quoteName('#__crc_check', 'cc') . ' ON cc.crc_files_id = cf.id AND (cc.crc_files_id, cc.crc_check_history_id) IN (select cct.crc_files_id, MAX(cct.crc_check_history_id) FROM #__crc_check AS cct group by cct.crc_files_id)' );
        $query->select( 'cch.id AS last_check_id')
              ->join('LEFT', $db->quoteName('#__crc_check_history', 'cch') . ' ON cch.id = cc.crc_check_history_id');

        $search = $this->getState('filter.search');
        if (!empty($search))
        {
            $like = $db->quote('%' . $search . '%');
            $query->where( '((cf.path LIKE ' . $like . ') OR (cf.filename LIKE ' . $like . '))');
        }
        $file_status = $this->getState('filter.file_status');
        if( is_numeric( $file_status ) ){
            $query->where( "cf.status = ".(int)$file_status );
        }
        $file_veryfied = $this->getState('filter.file_veryfied');
        if( is_numeric( $file_veryfied ) ){
            $query->where( "cc.veryfied = ".(int)$file_veryfied );
        }
	$orderCol	= $this->state->get('list.ordering', 'path');
	$orderDirn 	= $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
        return $query;
    }
}