<?php
echo "LOAD: " . __FILE__ . "<BR>";
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
define( 'FILE_STATUS_NEW'       ,'0' );
define( 'FILE_STATUS_VERIFIED'  ,'1' );
define( 'FILE_STATUS_DELETED'   ,'2' );

define( 'FILE_CHECKED_STATUS_INVALID'   ,'0' );
define( 'FILE_CHECKED_STATUS_VALID'     ,'1' );

define( 'INSERT_PAGINATION'     , 500 );

define( 'MYSQL_LONG_BLOB_SIZE'  ,4294967296 );

class TrustedArchiveDB
{
    /**
     *
     * Atributes in table #__crc_trustedarchive
     */
    public int      $id;
    public string   $path;
    public string   $name;
    public string   $filename;
    public string   $root;
    public int      $users_id;
    private         $chosen_files_id;
    private int     $last_check_history_id=0;
    private int     $max_allowed_packet=4194304;

    /**
     * Connection to joomla base.
     */
    public          $db;

    public function __construct( $p )
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";
echo __CLASS__."::".__FUNCTION__." id       = " . $p[ "id" ] . "<br>";
echo __CLASS__."::".__FUNCTION__." path     = " . $p[ "path" ] . "<br>";
echo __CLASS__."::".__FUNCTION__." name     = " . $p[ "name" ] . "<br>";
echo __CLASS__."::".__FUNCTION__." filename = " . $p[ "filename" ] . "<br>";
echo __CLASS__."::".__FUNCTION__." root     = " . $p[ "root" ] . "<br>";
echo __CLASS__."::".__FUNCTION__." users_id = " . $p[ "users_id" ] . "<br>";
        $this->db = JFactory::getDbo();
        $this->db->getQuery( true );
        $query = "SHOW VARIABLES WHERE Variable_name='max_allowed_packet'";
        $this->db->setQuery($query);
        $this->db->execute();
        $result = $this->db->loadObjectList();
        $max_allowed_packet = $result[0]->Value;
echo __CLASS__."::".__FUNCTION__." max_allowed_packet = >>" . $result[0]->Value . "<<<br>";

        if( $p[ "id" ] == null )
        {
echo __CLASS__."::".__FUNCTION__." New archive<BR>";
            $this->path     = $p[ "path" ];
            $this->name     = $p[ "name" ];
            $this->filename = $p[ "filename" ];
            $this->root     = $p[ "root" ];
            $this->users_id = $p[ "users_id" ];

            $this->registerTrustedArchiveInBase();
        }
        else
        {
echo __CLASS__."::".__FUNCTION__." Existing archive<BR>";
            $this->id = $p[ "id" ];

            $this->getTrustedArchivefromBase();
        }

//echo __CLASS__."::".__FUNCTION__." id       = >" . $this->id ."<BR>";
//echo __CLASS__."::".__FUNCTION__." path     = >" . $this->path."<BR>";
//echo __CLASS__."::".__FUNCTION__." name     = >" . $this->name."<BR>";
//echo __CLASS__."::".__FUNCTION__." filename = >" . $this->filename."<BR>";
//echo __CLASS__."::".__FUNCTION__." root     = >" . $this->root."<BR>";
//echo __CLASS__."::".__FUNCTION__." users_id = >" . $this->users_id."<BR>";

echo __CLASS__."::".__FUNCTION__." Stop<BR>";
    }

    public function lastCheckId()
    {
//echo __CLASS__."::".__FUNCTION__." Start<BR>";
//echo __CLASS__."::".__FUNCTION__." Stop<BR>";
        return $this->last_check_history_id;
    }

    public function clearCrcTmp()
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";
        $query = $this->db->getQuery(true);
        $query->delete($this->db->quoteName('#__crc_tmp') );
echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";
        $this->db->setQuery($query);
        $this->db->execute();
echo __CLASS__."::".__FUNCTION__." Stop<BR>";
    }

    public function selectCrcTmp()
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";
        $query = $this->db->getQuery(true)
                    -> select( $this->db->quoteName( array( 'path', 'filename', 'crc' ) ) )
                    -> from( $this->db->quoteName( '#__crc_tmp' ) );
echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";

        $this->db->setQuery($query);
        $this->db->execute();
echo __CLASS__."::".__FUNCTION__." Stop<BR>";
        return $this->db->loadObjectList();
    }

    private function getDataFromFile( $filename )
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";
echo __CLASS__."::".__FUNCTION__." filename = >>$filename<<<BR>";
echo __CLASS__."::".__FUNCTION__." filesize = >>" . filesize( $filename ) . "<<<BR>";
        $file = fopen( $filename, "rb" );
        $dataFromFile = fread( $file, filesize( $filename ) );
        
//echo __CLASS__."::".__FUNCTION__." dataFromFile = >>$dataFromFile<<<BR>";
echo __CLASS__."::".__FUNCTION__." strlen of dataFromFile = >>" . strlen( $dataFromFile) . "<<<BR>";

        fclose( $file );
echo __CLASS__."::".__FUNCTION__." Stop<BR>";
        return $dataFromFile;
    }

    public function insertCrcTmp( $records )
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";
        $insert_b       = "INSERT INTO `dev_crc_tmp` (`path`,`filename`,`crc`,`file`) VALUES (";
        $insert_e       = ")";
        $insert_size    = strlen( $insert_b.$insert_e );
        $insert_packet  = ($this->max_allowed_packet - $insert_size)/2;

echo __CLASS__."::".__FUNCTION__." insert_size   = $insert_size<BR>";

        foreach( $records as $v )
        {
            $filename = $v[0].DIRECTORY_SEPARATOR.$v[1];
            $file = fopen( $filename, "rb" );
            $dataFromFile_len = filesize( $filename );
            $dataFromFile = fread( $file, $dataFromFile_len );
echo __CLASS__."::".__FUNCTION__." strlen of dataFromFile = >>" . $dataFromFile_len . "<<<BR>";
echo __CLASS__."::".__FUNCTION__." md5 = >>" . md5( $dataFromFile ) . " md5_file = >>" . $v[2] . "<<<BR>";

            $query = $this->db->getQuery(true);
            $query = $insert_b.$this->db->quote( $v[0] ).",".$this->db->quote( $v[1] ).",".$this->db->quote( md5( $dataFromFile ) ).","
                    .$this->db->quote( substr( $dataFromFile, 0, $insert_packet ) ).$insert_e;
echo __CLASS__."::".__FUNCTION__. " " . "query=>$query<<BR>";
//die();
            fclose( $file );
            $this->db->setQuery($query);
            $result = $this->db->execute();
        }
echo __CLASS__."::".__FUNCTION__." Stop<BR>";
    }

    public function insertCrcFiles( $veryfied )
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";

        $query = $this->db->getQuery(true)
                -> insert( $this->db->quoteName( '#__crc_files' ) )
                -> columns( $this->db->quoteName( array( 'path', 'filename', 'status' ) ) )
                -> values( $this->db->getQuery(true)
                                -> select( $this->db->quoteName( array( 'path', 'filename' ) ) )
                                -> select( $veryfied )
                                -> from( $this->db->quoteName( '#__crc_tmp' ) )
                                -> join( 'LEFT', $this->db->quoteName( '#__crc_files', 'cf' ) . ' USING ( path, filename )' )
                                -> where( $this->db->quoteName( 'cf.filename' ) . ' IS NULL' ) );

//        SELECT path, filename,".$veryfied." FROM #__crc_tmp ct LEFT JOIN #__crc_files cf USING (path, filename) WHERE cf.filename is NULL;";
echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";

        $this->db->setQuery($query);
        $this->db->execute();
echo __CLASS__."::".__FUNCTION__." Stop<BR>";
    }
    
    public function insertCrcCheckHistory()
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";
echo __CLASS__."::".__FUNCTION__." users_id = >" . $this->users_id."<BR>";

        $query = $this->db->getQuery(true)
                -> insert( $this->db->quoteName( '#__crc_check_history' ) )
                -> columns( $this->db->quoteName( array( 'users_id' ) ) )
                -> values( $this->users_id );

echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";

        $this->db->setQuery($query);
        $this->db->execute();

        $this->updateLastCheckId( $this->db->insertid() );
echo __CLASS__."::".__FUNCTION__." Stop<BR>";
    }

    private function updateLastCheckId( $id )
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";
        $this->last_check_history_id = $id;
        $query = $this->db->getQuery(true)
            -> update( $this->db->quoteName( '#__crc_trustedarchive', 'ta' ) )
            -> set( $this->db->quoteName( 'ta.last_check_history_id' ) . ' = ' . $id )
            -> where( $this->db->quoteName( 'ta.id' ) . ' = ' . $this->id );

echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";

        $this->db->setQuery($query);
        $this->db->execute();
echo __CLASS__."::".__FUNCTION__." Stop<BR>";
    }

    public function updateVeryfiedCrcCheck()
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";

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
echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";

        $this->db->setQuery($query);
        $this->db->execute();
echo __CLASS__."::".__FUNCTION__." Stop<BR>";
    }

    public function insertCrcCheck( $veryfied )
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";
echo __CLASS__."::".__FUNCTION__." users_id = >" . $this->users_id."<BR>";

        $query = $this->db->getQuery(true)
                -> insert( $this->db->quoteName( '#__crc_check' ) )
                -> columns( $this->db->quoteName( array( 'crc_files_id', 'crc', 'veryfied', 'crc_check_history_id', 'file' ) ) )
                -> values( $this->db->getQuery( true )
                                -> select( $this->db->quoteName( array( 'cf.id', 'ct.crc' ) ) )
                                -> select( array( $veryfied, $this->last_check_history_id ) )
                                -> select( $this->db->quoteName( array( 'file' ) ) )
                                -> from( $this->db->quoteName( '#__crc_files', 'cf' ) )
                                -> from( $this->db->quoteName( '#__crc_tmp', 'ct' ) )
                                -> where( array( 
                                                    $this->db->quoteName( 'cf.path' ) . ' = ' . $this->db->quoteName( 'ct.path'),
                                                    $this->db->quoteName( 'cf.filename' ) . ' = ' . $this->db->quoteName( 'ct.filename' ) 
                                                ) 
                                        ) 
                         );

echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";

        $this->db->setQuery($query);
        $this->db->execute();
echo __CLASS__."::".__FUNCTION__." Stop<BR>";
    }

    public function updateDeletedCrcFiles()
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";
echo __CLASS__."::".__FUNCTION__." users_id = >" . $this->users_id."<BR>";

        $query = $this->db->getQuery(true)
                -> update( $this->db->quoteName( '#__crc_files', 'cf' ) )
                -> join( 'left',  $this->db->quoteName( '#__crc_check', 'cc' ) . 
                        ' ON '  . $this->db->quoteName( 'cf.id' ) . ' = ' . $this->db->quoteName( 'cc.crc_files_id' ) . 
                        ' AND ' . $this->db->quoteName( 'cc.crc_check_history_id' ) . ' = ( ' .
                        $this   ->db->getQuery(true)
                                ->select( 'MAX( ' . $this->db->quoteName( 'cch.id' ) . ' )' )
                                ->from( $this->db->quoteName( '#__crc_check_history', 'cch') ) . ' )'
                        ) 
                -> set( $this->db->quoteName( 'cf.status' ) . ' = ' . FILE_STATUS_DELETED )
                -> where( $this->db->quoteName( 'cc.id' ) . ' IS NULL' );

echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";

        $this->db->setQuery($query);
        $this->db->execute();
echo __CLASS__."::".__FUNCTION__." Stop<BR>";
    }
    
    private function getTrustedArchivefromBase()
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";
        $query = $this->db->getQuery(true)
                -> select( $this->db->quoteName( array ('id', 'path', 'name', 'filename', 'root', 'users_id', 'last_check_history_id' ) ) )
                -> from( $this->db->quoteName( '#__crc_trustedarchive' ) )
                -> where( $this->db->quoteName('id') . ' = ' . $this->db->quote( $this->id ) );
echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";
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

echo __CLASS__."::".__FUNCTION__." Stop<BR>";
    }

    private function registerTrustedArchiveInBase()
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";
        $query = $this->db->getQuery(true);

echo __CLASS__."::".__FUNCTION__. " path                    = >" . $this->path."<BR>";
echo __CLASS__."::".__FUNCTION__. " name                    = >" . $this->name."<BR>";
echo __CLASS__."::".__FUNCTION__. " filename                = >" . $this->filename."<BR>";
echo __CLASS__."::".__FUNCTION__. " root                    = >" . $this->root."<BR>";
echo __CLASS__."::".__FUNCTION__. " user_id                 = >" . $this->user_id."<BR>";
echo __CLASS__."::".__FUNCTION__. " last_check_history_id   = >" . $this->last_check_history_id."<BR>";

        $query  ->insert ( $this->db->quoteName( "#__crc_trustedarchive" ) )
                ->columns( $this->db->quoteName( array ('path', 'name', 'filename', 'root', 'users_id', 'last_check_history_id' ) ) )
                ->values ( implode( ',', array( $this->db->quote($this->path),
                                                $this->db->quote($this->name), 
                                                $this->db->quote($this->filename), 
                                                $this->db->quote($this->root), 
                                                $this->db->quote($this->users_id),
                                                $this->db->quote($this->last_check_history_id)
                                               ) ) );
echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";
        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();

echo __CLASS__."::".__FUNCTION__." Stop<BR>";
    }

    public function insertToFilesHasTrustedarchive( $files )
    {
//echo __CLASS__."::".__FUNCTION__ . " " . "Start<BR>";
//echo __CLASS__."::".__FUNCTION__ . " " . "in_query = >>" . $files . "<<<BR>";
        $in_query = '\'' . implode("','", $files) . '\'' ;

        $query = $this->db->getQuery(true);

        $query  ->insert ( $this->db->quoteName( '#__crc_files_has_TrustedArchive' ) )
                ->columns( $this->db->quoteName( array ('crc_files_id', 'crc_trustedarchive_id') ) )
                ->values(
                    $this->db->getQuery( true )
                        -> select( array( $this->db->quoteName( 'cf.id' ), $this->id ) )
                        -> from( $this->db->quoteName( '#__crc_files', 'cf' ) )
                        -> join( 'left', $this->db->quoteName( '#__crc_files_has_TrustedArchive', 'cfta' ) . 
                            ' ON ' . $this->db->quoteName( 'cfta.crc_files_id' ) . ' = ' . $this->db->quoteName( 'cf.id' ) .
                            ' AND ' . $this->db->quoteName( 'cfta.crc_trustedarchive_id' ) . ' = ' . $this->id
                                )
                        -> where( $this->db->quoteName( 'cfta.crc_files_id' ) . ' IS NULL ' .
                           ' AND ' . 'concat( path , \''. '\\' . DIRECTORY_SEPARATOR . '\', filename ) IN ( ' . $in_query . ')' )
                );
echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";
//die();
        $this->db->setQuery($query);
        $this->db->execute();
//echo __CLASS__."::".__FUNCTION__ . " " . "Stop<BR>";
    }

    public function insertToFilesHasTrustedarchiveById( $files_id )
    {
echo __CLASS__."::".__FUNCTION__ . " " . "Start<BR>";
//        $values = implode( ','. $this->id .',', $files_id[ 'id' ] ) . ','. $this->id ;
        $values = implode( ','.$this->id.'), (', $files_id ) . ','.$this->id;
        $in_query = implode( ',', $files_id );
echo __CLASS__."::".__FUNCTION__ . " " . "values = >>" . $values . "<<<BR>";
echo __CLASS__."::".__FUNCTION__ . " " . "in_query = >>" . $in_query . "<<<BR>";

        $query = $this->db->getQuery(true)
                -> insert ( $this->db->quoteName( '#__crc_files_has_TrustedArchive' ) )
                -> columns( $this->db->quoteName( array ( 'crc_files_id', 'crc_trustedarchive_id' ) ) )
                -> values( $this->db->getQuery( true )
                                -> select( array( $this->db->quoteName( 'cf.id' ), $this->id ) )
                                -> from( $this->db->quoteName( '#__crc_files', 'cf' ) )
                                -> join( 'left', $this->db->quoteName( '#__crc_files_has_TrustedArchive', 'cfta' ) . 
                                        ' ON ' . $this->db->quoteName( 'cfta.crc_files_id' ) . ' = ' . $this->db->quoteName( 'cf.id' ) .
                                        ' AND ' . $this->db->quoteName( 'cfta.crc_trustedarchive_id' ) . ' = ' . $this->id
                                        )
                                -> where( $this->db->quoteName( 'cfta.crc_files_id' ) . ' IS NULL ' .
                                   ' AND ' . $this->db->quoteName( 'cf.id' ) . ' IN (' . $in_query . ')' )
                         );
echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";
//die();
        $this->db->setQuery($query);
        $this->db->execute();
echo __CLASS__."::".__FUNCTION__ . " " . "Stop<BR>";
    }
    
    public function setLastCrcCheckStatusByFilesId( $files_id, $status )
    {
echo __CLASS__."::".__FUNCTION__ . " " . "Start<BR>";
        $in_query = implode( ',', $files_id );
echo __CLASS__."::".__FUNCTION__ . " " . "values = >>" . $values . "<<<BR>";
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
echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";
//die();
        $this->db->setQuery($query);
        $this->db->execute();
    }

    public function getFilesIdByStatus( $status )
    {
echo __CLASS__."::".__FUNCTION__ . " " . "Start<BR>";
        $query = $this->db->getQuery(true)
            -> select( $this->db->quoteName( 'id' ) )
            -> from( $this->db->quoteName( '#__crc_files' ) )
            -> where( $this->db->quoteName('status') . ' = ' . $status );
//echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";
        $this->db->setQuery($query);
        $this->db->execute();

        $this->chosen_files_id = $this->db->loadColumn();
var_dump( $this->chosen_files_id );
echo __CLASS__."::".__FUNCTION__ . " " . "Stop<BR>";
        return $this->chosen_files_id;
    }

    public function getFilenameById( $files_id ) 
    {
echo __CLASS__."::".__FUNCTION__ . " " . "Start<BR>";
        $in_query = '\'' . implode("','", $files_id) . '\'' ;
//echo __CLASS__."::".__FUNCTION__ . " " . "in_query = >" . $in_query . "<<<BR>";
        $query = $this->db->getQuery(true);
        $query  -> select( $this->db->quoteName( array ('id', 'path', 'filename' ) ) )
                -> from( $this->db->quoteName( '#__crc_files' ) )
                -> where( $this->db->quoteName('id') . ' IN (' . $in_query . ')' );
//echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";
        $this->db->setQuery($query);
        $this->db->execute();

        $result = $this->db->loadObjectList();
        foreach ( $result as $value )
        {
            $this->chosen_files_id[] = $value->id;
            $filenames[] = $value->path . DIRECTORY_SEPARATOR . $value->filename;
//echo __CLASS__."::".__FUNCTION__ . " " . $value->id . $value->path . $value->filename . "<BR>";
        }

//echo var_dump( $this->geted_files_id );
//echo "<BR>";
//echo var_dump( $filenames );
        return $filenames;
echo __CLASS__."::".__FUNCTION__ . " " . "Stop<BR>";
    }

    public function deleteChosenFiles()
    {
echo __CLASS__."::".__FUNCTION__ . " " . "Start<BR>";

        $this->db->transactionStart();

        $in_query = '\'' . implode("','", $this->chosen_files_id) . '\'' ;
echo __CLASS__."::".__FUNCTION__ . " " . "in_query = >" . $in_query . "<<<BR>";

        $query = $this->db->getQuery(true)
                -> delete($this->db->quoteName( '#__crc_files_has_trustedarchive', 'ctfa' ))
                -> where( $this->db->quoteName('ctfa.crc_files_id') . ' IN (' . $in_query . ')' );

echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";
        $this->db->setQuery($query);
        $this->db->execute();

        $query = $this->db->getQuery(true)
                -> delete($this->db->quoteName( '#__crc_check', 'cc' ) )
                -> where( $this->db->quoteName( 'cc.crc_files_id' ) . ' IN (' . $in_query . ')' );
echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";
        $this->db->setQuery($query);
        $this->db->execute();

        $query = $this->db->getQuery(true)
                -> delete( $this->db->quoteName( '#__crc_files', 'cf' ))
                -> where( $this->db->quoteName('cf.id') . ' IN (' . $in_query . ')' );

echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";
        $this->db->setQuery($query);
        $this->db->execute();

        $this->db->transactionCommit();
echo __CLASS__."::".__FUNCTION__ . " " . "Stop<BR>";        
    }
}