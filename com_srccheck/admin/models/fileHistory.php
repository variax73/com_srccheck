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
class SrcCheckModelFileHistory extends JModelList
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     */
//    public function __construct($config = array())
//    {
////    	if (empty($config['filter_fields']))
////    	{
////            $config['filter_fields'] = array(
////            	'path',
////            	'filename',
////            	'status',
////                'veryfied'
////            );
////    	}
//        
//   	parent::__construct($config);
//    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return      string  An SQL query
     */
    protected function getListQuery()
    {
srcCheckLog::start();
        // Initialize variables.
        $trustedarchive_id  = JFactory::getApplication()->input->get( "scat" );
        $file_id            = JFactory::getApplication()->input->get( "file_id" );

srcCheckLog::debug( "trustedarchive_id = >" . $trustedarchive_id . "<"
                    . "\nfile_id = >" . $file_id . "<" );



        // Create the base select statement.
        $db                 = JFactory::getDbo();

        $query = $db->getQuery(true)
            -> select ( $db->quoteName( array(  "cta.name",
                                                "cta.path",
                                                "cta.filename",
                                                "cf.timestamp",
                                                "cf.status",
                                                "cf.path",
                                                "cf.filename",
                                                "cc.id",
                                                "cc.veryfied",
                                                "cc.timestamp",
                                                "cc.crc_check_history_id",
                                                "cc.ta_localisation" 
                                            ),
                                       array(   "ta_name",
                                                "ta_path",
                                                "ta_filename",
                                                "f_timestamp",
                                                "f_status",
                                                "f_path",
                                                "f_filename",
                                                "c_id",
                                                "c_veryfied",
                                                "c_timestamp",
                                                "c_crc_check_history_id",
                                                "c_ta_localisation"
                                            )
                                     )
                     )
            -> from ( $db->quoteName( "#__crc_files", "cf" ) )
            -> join ( "inner", $db->quoteName( "#__crc_check", "cc" )
                    . " ON " . $db->quoteName( "cc.crc_files_id" ) . " = " . $db->quoteName( "cf.id" )
                                                                        . " AND " . $db->quoteName( "cc.crc_trustedarchive_id" ) . " = " . $trustedarchive_id )
            -> join ( "inner", $db->quoteName( "#__crc_trustedarchive", "cta" ) . " ON " . $db->quoteName( "cta.id" ) . " = " . $db->quoteName( "cc.crc_trustedarchive_id" ) )
            -> where ( $db->quoteName( "cf.id") . " = " . $file_id )
            -> order ( $db->quoteName( "cc.crc_check_history_id" ) . " DESC" );
srcCheckLog::debug( "query = >>" . $query . "<<" );

srcCheckLog::stop();
        return $query;
    }
}