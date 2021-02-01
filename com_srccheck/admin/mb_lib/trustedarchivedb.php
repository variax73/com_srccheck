<?php
srcCheckLog::debug( __FILE__ );
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
/*
     * 0 - new file;
     * 1 - checked file;
     * 2 - changed file;
     * 3 - deleted file;
*/
define( 'FILE_STATUS_NEW'               ,'0' );
define( 'FILE_STATUS_VERIFIED'          ,'1' );
define( 'FILE_STATUS_DELETED'           ,'2' );
define( 'FILE_STATUS_IN_TRASHCAN'       ,'3' );

define( 'FILE_CHECKED_STATUS_INVALID'   ,'0' );
define( 'FILE_CHECKED_STATUS_VALID'     ,'1' );

define( 'MYSQL_LONG_BLOB_SIZE'  ,4294967296 );

class TrustedArchiveDB
{
    /**
     *
     * Atributes in table #__crc_trustedarchive
     */
    public      $id;
    public      $path;
    public      $name;
    public      $filename;
    public      $root;
    public      $users_id;
    private     $chosen_files_id;
    private     $last_check_history_id=0;
    private     $max_allowed_packet=0;
    /**
     * Connection to joomla base.
     */
    public      $db;

    public function __construct( $p )
    {
srcCheckLog::start();
srcCheckLog::debug(
        "id                     = " . $p[ "id" ] . "\n" .
        "path                   = " . $p[ "path" ] . "\n" .
        "name                   = " . $p[ "name" ] . "\n" .
        "filename               = " . $p[ "filename" ] . "\n" .
        "root                   = " . $p[ "root" ] . "\n" .
        "users_id               = " . $p[ "users_id" ] . "\n" .
        "last_check_history_id  = " . $p[ "last_check_history_id" ] );

        $this->db = JFactory::getDbo();
        $this->db->getQuery( true );
        $query = "SHOW VARIABLES WHERE Variable_name='max_allowed_packet'";
        $this->db->setQuery($query);
        $this->db->execute();
        $result = $this->db->loadObjectList();
        $this->max_allowed_packet = min( $result[0]->Value, $this->return_bytes( ini_get('memory_limit') ) );
srcCheckLog::debug( "mysql max_allowed_packet = >>" . $result[0]->Value . "<<\n" .
                    "php memory_limit         = >>" . $this->return_bytes( ini_get('memory_limit') ) . "<<\n" .
                    "max_allowed_packet       = >>" . $this->max_allowed_packet . "<<\n" .
                    "max_text                 = >>" . pow( 2, 32 ) . "<<" );

        if( $p[ "id" ] == null )
        {
srcCheckLog::debug( "New archive" );
            $this->path                     = $p[ "path" ];
            $this->name                     = $p[ "name" ];
            $this->filename                 = $p[ "filename" ];
            $this->root                     = $p[ "root" ];
            $this->users_id                 = $p[ "users_id" ];
            $this->last_check_history_id    = $this->getLastCheckHistoryId();

            $this->registerTrustedArchiveInBase();
        }
        else
        {
srcCheckLog::debug( "Existing archive" );
            $this->id = $p[ "id" ];

            $this->getTrustedArchivefromBase();
        }

srcCheckLog::debug(
        "id       = >" . $this->id . "\n".
        "path     = >" . $this->path . "\n".
        "name     = >" . $this->name . "\n".
        "filename = >" . $this->filename . "\n".
        "root     = >" . $this->root . "\n".
        "users_id = >" . $this->users_id );

srcCheckLog::stop();
    }

    private function return_bytes( $val )
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    private function getLastCheckHistoryId()
    {
srcCheckLog::start();
        $query  = $this->db->getQuery(true)
            -> select( "MAX(" . $this->db->quoteName( "ch.id" ) . ")" )
            -> from( $this->db->quoteName( "#__crc_check_history", "ch" ) );
srcCheckLog::debug( "query = >>" . $query . "<<" );
        $this->db->setQuery( $query );
        $this->db->execute();

        $lchi = $this->db->loadResult();
srcCheckLog::debug( "last_check_history_id = >>" . $lchi . "<<" );
        if( $lchi == null ) $lchi = 0;
        return $this->last_check_history_id = $lchi;
srcCheckLog::stop();
    }

    public function lastCheckId()
    {
srcCheckLog::start();
srcCheckLog::stop();
        return $this->last_check_history_id;
    }

    public function clearCrcTmp()
    {
srcCheckLog::start();
        $query = $this->db->getQuery(true)
            ->delete($this->db->quoteName( "#__crc_tmp" ) );
srcCheckLog::debug( "query = >>" . $query . "<<" );
        $this->db->setQuery( $query );
        $this->db->execute();
srcCheckLog::stop();
    }

    public function  selectCrcFilesToAddToTa( /*$dest_table,*/ $mode = TA_MODE_NORMAL )
    {
srcCheckLog::start();
        $result[] = null;

        $column_list = $this->db->quoteName( array( "cf.path", "cf.filename", "c_cc.crc", "c_cc.ta_localisation" ) );
srcCheckLog::debug( "column_list = >" . $column_list . "<");
        $column_list[0] = "DISTINCT " . $column_list[0];
srcCheckLog::debug( "column_list = >" . $column_list . "<");
        if( $mode == TA_MODE_INIT )
        {
srcCheckLog::debug( "Init Mode entry" );
            $query = $this->db->getQuery(true)
                    ->select( $column_list )
                                        ->from( $this->db->quoteName( "#__crc_files", "cf" ) )
                                        ->join( "inner", "(" . $this->db->getQuery(true)
                                                                        ->select( $this->db->quoteName( "cur_cc.crc_files_id", "crc_files_id" ) )
                                                                        ->select( "MAX(" . $this->db->quoteName( "cur_cc.crc_check_history_id" ) . ") AS " . $this->db->quoteName( "cur_crc_check_history" ) )
                                                                        ->select( "MAX( " . $this->db->quoteName( "prv_cc.crc_check_history_id" ) . " ) AS " . $this->db->quoteName( "prv_crc_check_history" ) )
                                                                        ->from( $this->db->quoteName( "#__crc_check", "cur_cc" ) )
                                                                        ->join( "left", $this->db->quoteName( "#__crc_check", "prv_cc" )    . " ON " . $this->db->quoteName( "cur_cc.crc_files_id" ) . " = " . $this->db->quoteName( "prv_cc.crc_files_id" )
                                                                                                                                            . " AND " . $this->db->quoteName( "prv_cc.crc_check_history_id" ) . " < " . $this->db->quoteName( "cur_cc.crc_check_history_id" )
                                                                                                                                            . " AND " . $this->db->quoteName( "cur_cc.crc_trustedarchive_id" ) . " = " . $this->id
                                                                              )
                                                                        ->group( $this->db->quoteName( "cur_cc.crc_files_id" ) ) . ") AS " . $this->db->quoteName( "max_cc" ) . " ON " . $this->db->quoteName( "cf.id" ) . " = " . $this->db->quoteName( "max_cc.crc_files_id" )
                                              )
                                        ->join( "left", $this->db->quoteName( "#__crc_files_has_trustedarchive", "cfht" )   . " ON " . $this->db->quoteName( "cfht.crc_files_id" ) . " = " . $this->db->quoteName( "cf.id" )
                                                                                                                            . " AND " . $this->db->quoteName( "cfht.crc_trustedarchive_id" ) . " = " . $this->id
                                              )
                                        ->join( "inner", $this->db->quoteName( "#__crc_check", "c_cc" ) . " ON " . $this->db->quoteName( "cf.id" ) . " = " . $this->db->quoteName( "c_cc.crc_files_id" )
                                                                                                        . " AND " . $this->db->quoteName( "c_cc.crc_check_history_id" ) . " = " . $this->db->quoteName( "max_cc.cur_crc_check_history" )
                                              )
                                        ->where( $this->db->quoteName( "cfht.crc_files_id" ) . " IS NULL" )
                    ;
srcCheckLog::debug( "query = >>" . $query . "<<" );
            $this->db->setQuery($query);
            $this->db->execute();
srcCheckLog::debug( "selected records  = >>" . $this->db->getNumRows() . "<<" );
            $result = $this->db->loadObjectList();
srcCheckLog::debug(
        "strlen(values_next)  =>>" . strlen( $result ) . "<<\n" .
        "max_allowed_packet   =>>" . $this->max_allowed_packet . "<<\n" .
        "memory_get_usage     =>>" . memory_get_usage() . "<<\n" );
srcCheckLog::currentDuration();
        }
        $query = $this->db->getQuery(true)->select( $column_list )
            ->from( $this->db->quoteName( "#__crc_files", "cf" ) )
            ->join( "inner", "(" . $this->db->getQuery(true)
                                        ->select( $this->db->quoteName( "cur_cc.crc_files_id", "crc_files_id" ) )
                                        ->select( "MAX(" . $this->db->quoteName( "cur_cc.crc_check_history_id" ) . ") AS " . $this->db->quoteName( "cur_crc_check_history" ) )
                                        ->select( "MAX( " . $this->db->quoteName( "prv_cc.crc_check_history_id" ) . " ) AS " . $this->db->quoteName( "prv_crc_check_history" ) )
                                        ->from( $this->db->quoteName( "#__crc_check", "cur_cc" ) )
                                        ->join( "left", $this->db->quoteName( "#__crc_check", "prv_cc" )    . " ON " . $this->db->quoteName( "cur_cc.crc_files_id" ) . " = " . $this->db->quoteName( "prv_cc.crc_files_id" )
                                                                                                            . " AND " . $this->db->quoteName( "prv_cc.crc_check_history_id" ) . " < " . $this->db->quoteName( "cur_cc.crc_check_history_id" )
                                                                                                            . " AND " . $this->db->quoteName( "cur_cc.crc_trustedarchive_id" ) . " = " . $this->id
                                                                                                            . " AND " . $this->db->quoteName( "cur_cc.crc_check_history_id" ) . " = " . $this->last_check_history_id
                                              )
                                        ->group( $this->db->quoteName( "cur_cc.crc_files_id" ) ) . ") AS " . $this->db->quoteName( "max_cc" )   . " ON " . $this->db->quoteName( "cf.id" ) . " = " . $this->db->quoteName( "max_cc.crc_files_id" )
                  )
            ->join( "inner", $this->db->quoteName( "#__crc_check", "c_cc" ) . " ON " . $this->db->quoteName( "cf.id" ) . " = " . $this->db->quoteName( "c_cc.crc_files_id" )
                                                                            . " AND " . $this->db->quoteName( "c_cc.crc_check_history_id" ) . " = " . $this->db->quoteName( "max_cc.cur_crc_check_history" )
                  )
            ->join( "left", $this->db->quoteName( "#__crc_check", "p_cc" )  . " ON " . $this->db->quoteName( "cf.id" ) . " = " . $this->db->quoteName( "p_cc.crc_files_id" )
                                                                            . " AND " . $this->db->quoteName( "p_cc.crc_check_history_id" ) . " = " . $this->db->quoteName( "max_cc.prv_crc_check_history" )
                  )
            ->where(  array( // $this->db->quoteName( "cf.status" ) . " < " . FILE_STATUS_DELETED,
                            $this->db->quoteName( "c_cc.crc" ) . " <> " . "IF( " . $this->db->quoteName( "cf.status" ) . " = " . FILE_STATUS_NEW . ",-1, " . $this->db->quoteName( "p_cc.crc" ) . " )",
                          )
                   );
srcCheckLog::debug( "query = >>" . $query . "<<" );
        $this->db->setQuery($query);
        $this->db->execute();
srcCheckLog::debug( "selected records  = >>" . $this->db->getNumRows() . "<<" );
        $result = array_merge( $result, $this->db->loadObjectList() );
srcCheckLog::debug(
        "strlen(values_next)  =>>" . strlen( $result ) . "<<\n" .
        "max_allowed_packet   =>>" . $this->max_allowed_packet . "<<\n" .
        "memory_get_usage     =>>" . memory_get_usage() . "<<\n" );

srcCheckLog::debug( "selected records (result) = >>" . sizeof( $result ) . "<<" );

srcCheckLog::stop();
        return $result;
    }

    public function insertCrcTmp( $records )
    {
srcCheckLog::start();
        $query = $this->db->getQuery(true)
            ->insert( $this->db->quoteName( '#__crc_tmp' ) )
            ->columns( $this->db->quoteName( array( 'path', 'filename', 'crc', 'uuid' ) ) );

        foreach( $records as $i => $record )
        {
            $query->values( "'".implode( "','", array(  addslashes($record["path"]),
                                                        addslashes($record["filename"]),
                                                        addslashes($record["crc"])
                                                     )
                                        )."',UUID()"
                          );
            $values_next = "('".implode( "','", array(  addslashes($records[$i+1]["path"]),
                                                        addslashes($records[$i+1]["filename"]),
                                                        addslashes($records[$i+1]["crc"])
                                                    )
                                        )."',UUID()),";
            if( ( strlen( $query ) + strlen( $values_next ) ) >= $this->max_allowed_packet )
            {
srcCheckLog::debug( 
        "strlen(values_next)  =>>" . strlen( $values_next ) . "<<\n" .
        "strlen(query)        =>>" . strlen( $query ) . "<<\n" .
        "max_allowed_packet   =>>" . $this->max_allowed_packet . "<<\n" .
        "memory_get_usage     =>>" . memory_get_usage() . "<<\n" .
        "i                    =>>" . $i . "<<\n" );

srcCheckLog::debug( "query = >>" . $query . "<<" );
                $this->db->setQuery($query);
                $result = $this->db->execute();

                $query = $this->db->getQuery(true)
                    ->insert( $this->db->quoteName( '#__crc_tmp' ) )
                    ->columns( $this->db->quoteName( array( 'path', 'filename', 'crc', 'uuid' ) ) );
srcCheckLog::currentDuration();
            }
        }
srcCheckLog::debug( 
        "strlen(values_next)  =>>" . strlen( $values_next ) . "<<\n" .
        "strlen(query)        =>>" . strlen( $query ) . "<<\n" .
        "max_allowed_packet   =>>" . $this->max_allowed_packet . "<<\n" .
        "memory_get_usage     =>>" . memory_get_usage() . "<<\n" .
        "i                    =>>" . $i . "<<\n" );
srcCheckLog::debug( "query = >>" . $query . "<<" );
        $this->db->setQuery($query);
        $this->db->execute();
srcCheckLog::stop();
    }

    public function insertCrcFiles( $veryfied )
    {
srcCheckLog::start();

        $query = $this->db->getQuery(true)
            -> insert( $this->db->quoteName( "#__crc_files" ) )
            -> columns( $this->db->quoteName( array( "path", "filename", "status" ) ) )
            -> values( $this->db->getQuery(true)
                            -> select( $this->db->quoteName( array( "ct.path", "ct.filename" ) ) )
                            -> select( $veryfied )
                            -> from( $this->db->quoteName( "#__crc_tmp", "ct" ) )
                            -> join( "LEFT", $this->db->quoteName( "#__crc_files", "cf" )   . " ON " . $this->db->quoteName( "cf.path" ) . " = " . $this->db->quoteName( "ct.path" )
                                                                                            . " AND " . $this->db->quoteName( "cf.filename" ) . " = " . $this->db->quoteName( "ct.filename" )
                                                                                            . " AND " . $this->db->quoteName( "cf.status" ) . " < " . FILE_STATUS_DELETED )
                            -> join( "LEFT", $this->db->quoteName( "#__crc_files_excluded", "cfex" )    . " ON " . $this->db->quoteName( "ct.path" ) . " = " . $this->db->quoteName( "cfex.path" )
                                                                                                        . " AND " . $this->db->quoteName( "ct.filename" ) . " = IFNULL(" . $this->db->quoteName( "cfex.filename" ) . "," . $this->db->quoteName( "ct.filename" ) . ")"
                                                                                                        . " AND " . $this->db->quoteName( "cfex.crc_trustedarchive_id" ) . " = " . $this->id )
                            -> where( $this->db->quoteName( "cf.filename" ) . " IS NULL" )
                            -> where( $this->db->quoteName( "cfex.path" ) . " IS NULL" )
                     );
srcCheckLog::debug( "query = >>" . $query . "<<" );

        $this->db->setQuery($query);
        $this->db->execute();
srcCheckLog::stop();
    }
    
    public function insertCrcCheckHistory()
    {
srcCheckLog::start();
srcCheckLog::debug( "users_id = >" . $this->users_id );

        $query = $this->db->getQuery(true)
                -> insert( $this->db->quoteName( '#__crc_check_history' ) )
                -> columns( $this->db->quoteName( array( 'users_id' ) ) )
                -> values( $this->users_id );

srcCheckLog::debug( "query = >>" . $query . "<<" );

        $this->db->setQuery($query);
        $this->db->execute();

        $this->updateLastCheckId( $this->db->insertid() );
srcCheckLog::stop();
    }

    private function updateLastCheckId( $id )
    {
srcCheckLog::start();
        $this->last_check_history_id = $id;
        $query = $this->db->getQuery(true)
            -> update( $this->db->quoteName( '#__crc_trustedarchive', 'ta' ) )
            -> set( $this->db->quoteName( 'ta.last_check_history_id' ) . ' = ' . $id )
            -> where( $this->db->quoteName( 'ta.id' ) . ' = ' . $this->id );

srcCheckLog::debug( "query = >>" . $query . "<<" );

        $this->db->setQuery($query);
        $this->db->execute();
srcCheckLog::stop();
    }

    public function updateVeryfiedCrcCheck()
    {
srcCheckLog::start();

        $query  = $this->db->getQuery(true)
                -> update( array(   $this->db->quoteName( '#__crc_check', 'ccc' ),
                                    $this->db->quoteName( '#__crc_check', 'ccp' ),
                                    '(' . $this->db->getQuery(true)
                                         ->select( 'MAX(' . $this->db->quoteName( 'cid.id' ) . ') AS ' . $this->db->quoteName( 'c_id' ) )
                                         ->select( 'MAX(' . $this->db->quoteName( 'pid.id' ) . ') AS ' . $this->db->quoteName( 'p_id' ) )
                                         ->from( array(  $this->db->quoteName( '#__crc_check_history', 'cid' ),
                                                         $this->db->quoteName( '#__crc_check_history', 'pid' )
                                                      )
                                               )
                                         ->where( $this->db->quoteName( 'pid.id' ) . ' < ' . $this->db->quoteName( 'cid.id' ) ) . ') AS ' . $this->db->quoteName( 'ids' )
                                )
                         )
                -> set( $this->db->quoteName( 'ccc.veryfied' ) . ' = ' . FILE_STATUS_VERIFIED )
                -> where( array(    $this->db->quoteName( 'ccc.crc_check_history_id' ) . ' = ' . $this->db->quoteName( 'ids.c_id' ),
                                    $this->db->quoteName( 'ccp.crc_check_history_id' ) . ' = ' . $this->db->quoteName('ids.p_id'),
                                    $this->db->quoteName( 'ccc.crc_files_id' ) . ' = ' . $this->db->quoteName( 'ccp.crc_files_id' ),
                                    $this->db->quoteName( 'ccc.crc' ) . ' = ' . $this->db->quoteName( 'ccp.crc' ),
                                    $this->db->quoteName( 'ccp.veryfied' ) . ' = ' . FILE_STATUS_VERIFIED,
                               )
                        );
srcCheckLog::debug( "query = >>" . $query . "<<" );

        $this->db->setQuery($query);
        $this->db->execute();
srcCheckLog::stop();
    }

    public function insertCrcCheck( $veryfied, $mode=TA_MODE_NORMAL )
    {
srcCheckLog::start();
srcCheckLog::debug( " users_id = >" . $this->users_id );
        $query = $this->db->getQuery(true)
                -> insert( $this->db->quoteName( "#__crc_check" ) )
                -> columns( $this->db->quoteName( array( "crc_files_id", "crc", "veryfied", "crc_check_history_id", "ta_localisation", "crc_trustedarchive_id" ) ) )
                -> values( $this->db->getQuery( true )
                                -> select( $this->db->quoteName( array( "cf.id", "ct.crc" ) ) )
                                -> select( array( "IFNULL( " . $this->db->quoteName( "cc.veryfied" ). "," . $veryfied . ")", $this->last_check_history_id ) )
                                -> select( $this->db->quoteName( array( "ct.uuid") ) )
                                -> select( array( $this->id ) )
                                -> from( $this->db->quoteName( "#__crc_files", "cf" ) )
                                -> join( "inner", $this->db->quoteName( "#__crc_tmp", "ct" )    . " ON " . $this->db->quoteName( "cf.filename" ) . " = " . $this->db->quoteName( "ct.filename" )
                                                                                                . " AND " . $this->db->quoteName( "cf.path" ) . " = " . $this->db->quoteName( "ct.path" ) )
                                -> join( "left", $this->db->quoteName( "#__crc_check", "cc" )   . " ON " . $this->db->quoteName( "cc.crc_files_id" ) . " = " . $this->db->quoteName( "cf.id" )
                                                                                                . " AND " . $this->db->quoteName( "cc.crc" ) . " = " . $this->db->quoteName( "ct.crc" )
                                                                                                . " AND (" . $this->db->quoteName( "cc.crc_files_id" ) . "," . $this->db->quoteName( "cc.crc_check_history_id" ) 
                                                                                                      . ") IN (" . $this->db->getQuery( true )
                                                                                                                        ->select( $this->db->quoteName( "crc_files_id" ) )
                                                                                                                        ->select( "MAX(" . $this->db->quoteName( "crc_check_history_id" ) . ")" )																											  
                                                                                                                        ->from( $this->db->quoteName( "#__crc_check" ) )
                                                                                                                        ->group( $this->db->quoteName( "crc_files_id" ) ) . ")"
                                        )
                                -> where( array(    "if(". $mode . "<>" . TA_MODE_INIT . "," . $this->db->quoteName( "cc.crc" ) . "IS NULL,1)",
                                                    $this->db->quoteName( "cf.status" ) . " < " . FILE_STATUS_DELETED
                                               ) 
                                        )
                          );

srcCheckLog::debug( "query = >>" . $query . "<<" );
        $this->db->setQuery($query);
        $this->db->execute();
srcCheckLog::stop();
    }

    public function updateNewCrcFiles()
    {
srcCheckLog::start();

        $query = $this->db->getQuery(true)
                -> update( $this->db->quoteName( '#__crc_files', 'cf' ) )
                -> join( 'inner', '(' . $query = $this->db->getQuery(true)
                                        ->select( $this->db->quoteName( 'cff.id', 'files_id' ) )
                                        ->from( $this->db->quoteName( '#__crc_files', 'cff' ) )
                                        ->join( 'inner', $this->db->quoteName( '#__crc_check', 'cc' ) . 
                                                ' ON ' . $this->db->quoteName( 'cff.id' ) . ' = ' . $this->db->quoteName( 'cc.crc_files_id' )
                                              )
                                        ->where( $this->db->quoteName( 'cff.status' ) . ' = ' . FILE_STATUS_NEW )
                                        ->group( $this->db->quoteName( 'cff.id' ) )
                                        ->having( 'MAX(' . $this->db->quoteName( 'cc.crc_check_history_id' ) . ') < ' . $this->last_check_history_id . ' OR ' . 'COUNT(' . $this->db->quoteName( 'cff.id' ) . ') > 1' ) . 
                        ') AS ' . $this->db->quoteName( 'cx' ) . ' ON ' . $this->db->quoteName( 'cf.id' ) . ' = ' . $this->db->quoteName( 'cx.files_id' )
                        )
                -> set( $this->db->quoteName( 'cf.status' ) . ' = ' . FILE_STATUS_VERIFIED );
srcCheckLog::debug( "query = >>" . $query . "<<" );

        $this->db->setQuery($query);
        $this->db->execute();
srcCheckLog::stop();
    }

    public function updateDeletedCrcFiles()
    {
srcCheckLog::start();
srcCheckLog::debug( " users_id = >" . $this->users_id );

        $query = $this->db->getQuery(true)
                -> update( $this->db->quoteName( '#__crc_files', 'cf' ) )
                -> join( 'left',  $this->db->quoteName( '#__crc_tmp', 'ct' ) . 
                        ' ON '  . $this->db->quoteName( 'cf.path' ) . ' = ' . $this->db->quoteName( 'ct.path' ) . 
                        ' AND ' . $this->db->quoteName( 'cf.filename' ) . ' = ' . $this->db->quoteName( 'ct.filename' )
                        )
                -> set( $this->db->quoteName( 'cf.status' ) . ' = ' . FILE_STATUS_DELETED )
                -> where( $this->db->quoteName( 'ct.crc' ) . ' IS NULL' );

srcCheckLog::debug( "query = >>" . $query . "<<" );

        $this->db->setQuery($query);
        $this->db->execute();
srcCheckLog::stop();
    }
    
    private function getTrustedArchivefromBase()
    {
srcCheckLog::start();
        $query = $this->db->getQuery(true)
                -> select( $this->db->quoteName( array ('id', 'path', 'name', 'filename', 'root', 'users_id', 'last_check_history_id' ) ) )
                -> from( $this->db->quoteName( '#__crc_trustedarchive' ) )
                -> where( $this->db->quoteName('id') . ' = ' . $this->db->quote( $this->id ) );
srcCheckLog::debug( "query = >>" . $query . "<<" );
        $this->db->setQuery($query);
        $this->db->execute();
        
        $result = $this->db->loadAssoc();

        $this->id                       = $result[ 'id' ];
        $this->path                     = $result[ 'path' ];
        $this->name                     = $result[ 'name' ];
        $this->filename                 = $result[ 'filename' ];
        $this->root                     = $result[ 'root' ];
        $this->users_id                 = $result[ 'users_id' ];
        $this->last_check_history_id    = $result[ 'last_check_history_id' ];

srcCheckLog::stop();
    }

    private function registerTrustedArchiveInBase()
    {
srcCheckLog::start();
srcCheckLog::debug( "path                    = >" . $this->path . "\n" .
                    "name                    = >" . $this->name . "\n" .
                    "filename                = >" . $this->filename . "\n" .
                    "root                    = >" . $this->root . "\n" .
                    "user_id                 = >" . $this->user_id . "\n" .
                    "last_check_history_id   = >" . $this->last_check_history_id );

        $query = $this->db->getQuery(true)
                ->insert ( $this->db->quoteName( "#__crc_trustedarchive" ) )
                ->columns( $this->db->quoteName( array ( "path", "name", "filename", "root", "users_id", "last_check_history_id" ) ) )
                ->values ( implode( ",", array( $this->db->quote( $this->path ),
                                                $this->db->quote( $this->name ), 
                                                $this->db->quote( $this->filename ), 
                                                $this->db->quote( $this->root ), 
                                                $this->db->quote( $this->users_id ),
                                                $this->db->quote( $this->last_check_history_id )
                                               ) ) );
srcCheckLog::debug( "query = >>" . $query . "<<" );
        $this->db->setQuery($query);
        $r = $this->db->execute();
srcCheckLog::debug( "query r = >>" . $r . "<<" );

        $this->id = $this->db->insertid();

        $query = $this->db->getQuery(true)
                ->insert ( $this->db->quoteName( "#__crc_files_excluded" ) )
                ->columns( $this->db->quoteName( array ( "path", "filename", "crc_trustedarchive_id" ) ) )
                ->values ( implode( ",", array( $this->db->quote( $this->path ),
                                                "NULL",
                                                $this->db->quote( $this->id )
                                               ) ) );
srcCheckLog::debug( "query = >>" . $query . "<<" );
        $this->db->setQuery($query);
        $r = $this->db->execute();
srcCheckLog::debug( "query r = >>" . $r . "<<" );
srcCheckLog::stop();
    }

    public function insertFilesHasTrustedArchive( $files )
    {
srcCheckLog::start();
        foreach( $files as $i => $file )
        {
//srcCheckLog::debug( " [$i]" . file[ "crc" ] . " [$i+1]" . $files[$i+1][ "crc" ] . "<<" );
            $in_query[]=$file;
//srcCheckLog::debug( " [$i]" . $file . " [$i+1]" . $files[$i+1] . "<<" );
            $in_query_next = '\'' . $files[$i+1] . '\',' ; 
            $query = $this->db->getQuery(true)
                ->insert ( $this->db->quoteName( '#__crc_files_has_trustedarchive' ) )
                ->columns( $this->db->quoteName( array ('crc_files_id', 'crc_trustedarchive_id') ) )
                ->values(
                    $this->db->getQuery( true )
                        -> select( array( $this->db->quoteName( 'cf.id' ), $this->id ) )
                        -> from( $this->db->quoteName( '#__crc_files', 'cf' ) )
                        -> join( 'left', $this->db->quoteName( '#__crc_files_has_trustedarchive', 'cfta' ) . 
                            ' ON ' . $this->db->quoteName( 'cfta.crc_files_id' ) . ' = ' . $this->db->quoteName( 'cf.id' ) .
                            ' AND ' . $this->db->quoteName( 'cfta.crc_trustedarchive_id' ) . ' = ' . $this->id
                                )
                        -> where( $this->db->quoteName( 'cfta.crc_files_id' ) . ' IS NULL ' .
                           ' AND ' . 'concat( path , \''. '\\' . DIRECTORY_SEPARATOR . '\', filename ) IN (\''. implode('\',\'', $in_query) .'\')' )
                );
//srcCheckLog::debug( "in_query=>$in_query<" );
//srcCheckLog::debug( "in_query_next=>$in_query_next<" );
//srcCheckLog::debug( "query=>$query<" );
        if( ( strlen( $query ) + strlen( $in_query_next) ) >= $this->max_allowed_packet )
        {
srcCheckLog::debug( "strlen(values_next)  =>>" . strlen( $values_next ) . "<<\n" .
                    "strlen(query)        =>>" . strlen( $query ) . "<<\n" .
                    "max_allowed_packet   =>>" . $this->max_allowed_packet . "<<\n" .
                    "memory_get_usage     =>>" . memory_get_usage() . "<<\n" .
                    "i                    =>>" . $i . "<<\n" );
srcCheckLog::debug( "query=>$query<" );
            $this->db->setQuery($query);
            $result = $this->db->execute();
            unset( $in_query );
        }
    }
srcCheckLog::debug( "strlen(values_next)  =>>" . strlen( $values_next ) . "<<\n" .
        "strlen(values_next)  =>>" . strlen( $values_next ) . "<<\n" .
                    "strlen(query)        =>>" . strlen( $query ) . "<<\n" .
                    "max_allowed_packet   =>>" . $this->max_allowed_packet . "<<\n" .
                    "memory_get_usage     =>>" . memory_get_usage() . "<<\n" .
                    "i                    =>>" . $i . "<<\n" );
srcCheckLog::debug( "query=>$query<" );
    $this->db->setQuery($query);
    $result = $this->db->execute();
srcCheckLog::stop();
    }

    public function insertFilesHasTrustedArchiveById( $files_id )
    {
srcCheckLog::start();
        $values = implode( ','.$this->id.'), (', $files_id ) . ','.$this->id;
        $in_query = implode( ',', $files_id );
srcCheckLog::debug( "values = >>" . $values . "<<" );
srcCheckLog::debug( "in_query = >>" . $in_query . "<<" );

        $query = $this->db->getQuery(true)
                -> insert ( $this->db->quoteName( '#__crc_files_has_trustedarchive' ) )
                -> columns( $this->db->quoteName( array ( 'crc_files_id', 'crc_trustedarchive_id' ) ) )
                -> values( $this->db->getQuery( true )
                                -> select( array( $this->db->quoteName( 'cf.id' ), $this->id ) )
                                -> from( $this->db->quoteName( '#__crc_files', 'cf' ) )
                                -> join( 'left', $this->db->quoteName( '#__crc_files_has_trustedarchive', 'cfta' ) . 
                                        ' ON ' . $this->db->quoteName( 'cfta.crc_files_id' ) . ' = ' . $this->db->quoteName( 'cf.id' ) .
                                        ' AND ' . $this->db->quoteName( 'cfta.crc_trustedarchive_id' ) . ' = ' . $this->id
                                        )
                                -> where( $this->db->quoteName( 'cfta.crc_files_id' ) . ' IS NULL ' .
                                   ' AND ' . $this->db->quoteName( 'cf.id' ) . ' IN (' . $in_query . ')' )
                         );
srcCheckLog::debug( "query = >>" . $query . "<<" );
        $this->db->setQuery($query);
        $this->db->execute();
srcCheckLog::stop();
    }
    
    public function setLastCrcCheckStatusByFilesId( $files_id, $status )
    {
srcCheckLog::start();
        $in_query = implode( ',', $files_id );
        $query = $this->db->getQuery(true)
                    -> update($this->db->quoteName( '#__crc_check', 'ccu' ))
                    -> join('INNER', '(' . $query = $this->db->getQuery(true)
                                -> select( $this->db->quoteName( 'cc_max.crc_files_id', 'crc_files_id' ) )
                                -> select( 'MAX( ' . $this->db->quoteName( 'cc_max.crc_check_history_id' ) . ' ) AS ' . $this->db->quoteName( 'crc_check_history_id' ) )
                                -> from ( $this->db->quoteName( '#__crc_check', 'cc_max' ) )
                                -> where( $this->db->quoteName( 'cc_max.crc_files_id' ) . ' IN ( ' . $in_query . ' )' )
                                -> group( $this->db->quoteName( 'cc_max.crc_files_id' ) ) .') AS ' . $this->db->quoteName( 'cc' ) .
                            ' ON ' . $this->db->quoteName( 'cc.crc_files_id' ) . ' = ' . $this->db->quoteName( 'ccu.crc_files_id' ) .
                            ' AND ' . $this->db->quoteName( 'cc.crc_check_history_id' ) . ' = ' . $this->db->quoteName( 'ccu.crc_check_history_id') 
                            )
                    -> set( $this->db->quoteName( 'ccu.veryfied' ) . ' = ' . $status );
srcCheckLog::debug( "query = >>" . $query . "<<" );
        $this->db->setQuery($query);
        $this->db->execute();
srcCheckLog::stop();
    }

    public function getCrcFilesByStatus( $status = -1 )
    {
srcCheckLog::start();
srcCheckLog::debug( "status = >>" . $status . "<<" );
        $query = $this->db->getQuery(true)
            -> select( $this->db->quoteName( "cf.id" ) )
            -> from( $this->db->quoteName( "#__crc_files", "cf" ) );
        if( $status <> -1)
        {
            $query-> where( $this->db->quoteName( "cf.status" ) . ' = ' . $status );
        }
srcCheckLog::debug( "query = >>" . $query . "<<" );
        $this->db->setQuery($query);
        $this->db->execute();
        $result = $this->db->loadColumn();
srcCheckLog::stop();
        return $result;
    }

    public function getFilesToEraseById( $files_id )
    {
srcCheckLog::start();
        $in_query = '\'' . implode("','", $files_id) . '\'' ;
srcCheckLog::debug( "in_query = >" . $in_query );
        $query = $this->db->getQuery(true)
                -> select( $this->db->quoteName( array ("cf.id", "cf.path", "cf.filename", "cc.ta_localisation" ) ) )
                -> from( $this->db->quoteName( "#__crc_files", "cf" ) )
                -> join( "inner", $this->db->quoteName( "#__crc_check", "cc" )  . " ON " . $this->db->quoteName( "cc.crc_files_id" ) . " = " . $this->db->quoteName( "cf.id" )
                       )
                -> where( $this->db->quoteName( "cc.crc_trustedarchive_id" ) . " = " . $this->id )
                -> where( $this->db->quoteName( "cf.id" ) . " IN (" . $in_query . ")" );
srcCheckLog::debug( "query = >>" . $query . "<<" );
        $this->db->setQuery($query);
        $this->db->execute();
        $result = $this->db->loadObjectList();

        foreach ( $result as $value )
        {
            $this->chosen_files_id[] = $value->id;
        }
srcCheckLog::stop();
        return $result;
srcCheckLog::stop();
    }

    public function getFilenameById( $files_id ) 
    {
srcCheckLog::start();
        $in_query = '\'' . implode("','", $files_id) . '\'' ;
srcCheckLog::debug( "in_query = >" . $in_query );
        $query = $this->db->getQuery(true);
        $query  -> select( $this->db->quoteName( array ('id', 'path', 'filename' ) ) )
                -> from( $this->db->quoteName( '#__crc_files' ) )
                -> where( $this->db->quoteName('id') . ' IN (' . $in_query . ')' );
srcCheckLog::debug( "query = >>" . $query . "<<" );
        $this->db->setQuery($query);
        $this->db->execute();

        $result = $this->db->loadObjectList();
        foreach ( $result as $value )
        {
            $this->chosen_files_id[] = $value->id;
            $filenames[] = $value->path . DIRECTORY_SEPARATOR . $value->filename;
srcCheckLog::debug( "[" . $value->id . "]-" . $value->path . DIRECTORY_SEPARATOR . $value->filename );
        }
srcCheckLog::stop();
        return $filenames;
    }

    public function updateChosenFilesStatus( $status )
    {
srcCheckLog::start();
        $in_query = '\'' . implode("','", $this->chosen_files_id) . '\'' ;
srcCheckLog::debug(  "in_query = >" . $in_query . "<<" );

        $query = $this->db->getQuery(true)
                -> update( $this->db->quoteName( "#__crc_files", "cf" ) )
                -> set( $this->db->quoteName( "cf.status" ) . " = " . $status )
                -> where( $this->db->quoteName( "cf.id" ) . " IN (" . $in_query . ")" );

srcCheckLog::debug( "query = >>" . $query . "<<" );
        $this->db->setQuery($query);
        $this->db->execute();

srcCheckLog::stop();
    }

    public function deleteChosenFiles()
    {
srcCheckLog::start();

        $this->db->transactionStart();

        $in_query = '\'' . implode("','", $this->chosen_files_id) . '\'' ;
srcCheckLog::debug(  "in_query = >" . $in_query . "<<" );

        $query = $this->db->getQuery(true)
                -> delete($this->db->quoteName( '#__crc_files_has_trustedarchive', 'ctfa' ))
                -> where( $this->db->quoteName('ctfa.crc_files_id') . ' IN (' . $in_query . ')' );

srcCheckLog::debug( "query = >>" . $query . "<<" );
        $this->db->setQuery($query);
        $this->db->execute();

        $query = $this->db->getQuery(true)
                -> delete($this->db->quoteName( '#__crc_check', 'cc' ) )
                -> where( $this->db->quoteName( 'cc.crc_files_id' ) . ' IN (' . $in_query . ')' );
srcCheckLog::debug( "query = >>" . $query . "<<" );
        $this->db->setQuery($query);
        $this->db->execute();

        $query = $this->db->getQuery(true)
                -> delete( $this->db->quoteName( '#__crc_files', 'cf' ) )
                -> where( $this->db->quoteName('cf.id') . ' IN (' . $in_query . ')' );

srcCheckLog::debug( "query = >>" . $query . "<<" );
        $this->db->setQuery($query);
        $this->db->execute();

        $this->db->transactionCommit();
srcCheckLog::stop();        
    }
}