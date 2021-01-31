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

use Joomla\CMS\Factory;
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
srcCheckLog::start();
    	$db                 = JFactory::getDbo();
    	$query              = $db->getQuery(true);
        $trustedarchive_id  = JFactory::getApplication()->input->get( "scat" );

        $query-> select( "cf.id AS file_id, cf.path AS path, cf.filename AS filename, cf.status AS status" )
            -> from( $db->quoteName( "#__crc_files", "cf" ) )
            -> where( $db->quoteName( "cf.status" ) . "<>" . FILE_STATUS_IN_TRASHCAN );

        $query-> select( $db->quoteName( "cc.veryfied",             "veryfied" ) )
            -> select( $db->quoteName( "cc.id",                     "check_id" ) )
            -> select( $db->quoteName( "cc.crc_check_history_id",   "crc_check_history_id" ) )
            -> select( $db->quoteName( "cc.ta_localisation",        "ta_localisation" ) )
            -> select( $db->quoteName( "cc.crc_trustedarchive_id",  "trustedarchive_id" ) )
            -> join( "inner", $db->quoteName( "#__crc_check", "cc" )    . " ON " . $db->quoteName( "cc.crc_files_id" ) . " = " . $db->quoteName( "cf.id" )
                                                                        . " AND (" . $db->quoteName( "cc.crc_files_id" ) . "," . $db->quoteName( "cc.crc_check_history_id" )
                                                                                                      . ") IN (" . $db->getQuery( true )
                                                                                                                        ->select( $db->quoteName( "crc_files_id" ) )
                                                                                                                        ->select( "MAX(" . $db->quoteName( "crc_check_history_id" ) . ")" )
                                                                                                                        ->from( $db->quoteName( "#__crc_check" ) )
                                                                                                                        ->group( $db->quoteName( "crc_files_id" ) ) . ")"
                   );
        $query->select( 'cch.id AS last_check_id')
              ->join('LEFT', $db->quoteName('#__crc_check_history', 'cch') . ' ON cch.id = cc.crc_check_history_id')
              ->join( '', $db->quoteName( '#__crc_files_has_trustedarchive', 'ctfa' ) . ' ON ctfa.crc_files_id = cf.id AND ctfa.crc_trustedarchive_id = ' . $trustedarchive_id );
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
srcCheckLog::debug( "query = >>" . $query . "<<" );

srcCheckLog::stop();
        return $query;
    }
}