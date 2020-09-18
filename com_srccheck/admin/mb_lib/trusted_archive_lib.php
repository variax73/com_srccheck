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

define( 'INSERT_PAGINATION', 500 );

class TrustedArchive extends ZipArchive
{
    private string      $patch;
    private string      $name;
//    private string      $filename;
    private string      $root;
    private string      $base_id;
    private int         $insertPagination  = INSERT_PAGINATION;

/*
 * Constructor for class with parameters:
 *  $r - root path for Archive;
 *  $f - file name of archive.
 */
    public function __construct( $r, string $f, string $n = null) {
echo __CLASS__."::".__FUNCTION__." Start<BR>";
        $this->patch    = substr( $f, 0 , strrpos( $f, DIRECTORY_SEPARATOR ) );
        $tmp_filename = substr( $f, strrpos( $f, DIRECTORY_SEPARATOR )+1 );
        $this->root     = $r;
        if( $n === null )
        {
            $this->name = $tmp_filename;
        }
        else
        {
            $this->name = $n;
        }

echo __CLASS__."::".__FUNCTION__." " . " patch    = >" . $this->patch . "<br>";
echo __CLASS__."::".__FUNCTION__." " . " filename = >" . $this->filename . "<br>";
echo __CLASS__."::".__FUNCTION__." " . " root     = >" . $this->root . "<br>";

//echo __CLASS__."::".__FUNCTION__. " f = >>>" . $f . " ta_filename = >>>" . $this->ta_filename . "<<< <BR>";
        $this->open( $this->patch . DIRECTORY_SEPARATOR . $tmp_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE );
        $this->registerTrustedArchiveInBase();
echo __CLASS__."::".__FUNCTION__." " . " base_id = >" . $this->base_id . "<br>";
//echo __CLASS__."::".__FUNCTION__. " ta_zip_archive = >>> " . var_dump($this) . " " . $this->showStatusMessage($this) . "<<< <BR>";
echo __CLASS__."::".__FUNCTION__." Stop<BR>";
    }

    public function __destruct() {
        $this->close();
    }

    public function openTrustedArchive()
    {
echo __CLASS__."::".__FUNCTION__ . " " . "Start<BR>";
        $this->open( $this->patch . DIRECTORY_SEPARATOR . $this->filename );
echo __CLASS__."::".__FUNCTION__ . " " . "Stop<BR>";
    }

    private function insertToFilesHasTrustedarchive( $db, $files)
    {
//echo __CLASS__."::".__FUNCTION__ . " " . "Start<BR>";
//echo __CLASS__."::".__FUNCTION__ . " " . "in_query = >>" . $files . "<<<BR>";
        $in_query = '\'' . implode("','", $files) . '\'' ;

        $query = $db->getQuery(true);

        $query  ->insert ( $db->quoteName( '#__crc_files_has_TrustedArchive' ) )
                ->columns( $db->quoteName( array ('crc_files_id', 'crc_trustedarchive_id') ) )
                ->values(
                    $db ->getQuery(true)
                        ->select( array( 'id', $this->base_id ) )
                        ->from( '#__crc_files' )
                        ->where( array( 'concat( path , \''. '\\' . DIRECTORY_SEPARATOR . '\', filename ) IN ( ' . $in_query . ')' ) )
                );
//echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";

        $db->setQuery($query);
        $db->execute();
//echo __CLASS__."::".__FUNCTION__ . " " . "Stop<BR>";
    }

    public function addFilesToTrustedArchive( $files ) {
echo __CLASS__."::".__FUNCTION__ . " " . "Start<BR>";
//echo __CLASS__."::".__FUNCTION__ . " " . "OS is " . PHP_OS_FAMILY . " File to add to TA >>" . $files . "<<>>" . str_replace($rootPatch.DIRECTORY_SEPARATOR, "", $files) . "<< <BR>";
// echo __CLASS__."::".__FUNCTION__ . " " . "File to add to TA >>" . var_dump($files) . "<< <BR>";

        $i = 0;
        $db = JFactory::getDbo();
        $db->transactionStart();

        foreach ( $files as $file )
        {
            $this->addFile($file, str_replace( $this->root.DIRECTORY_SEPARATOR, "", $file) );
//echo __CLASS__."::".__FUNCTION__ . " " . " file = >>$file<< (" . str_replace( $this->root.DIRECTORY_SEPARATOR, "", $file) . ")" . " status = " . $this->showStatusMessage() . "<< <BR>";
            if( !$this->status && $i < $this->insertPagination )
            {
                $tmp_files[] = addslashes($file);
                $i++;
            }else
            {
//echo __CLASS__."::".__FUNCTION__ . " " . " Go to INSERT $i<BR>";
                $tmp_files[] = addslashes($file);
//echo __CLASS__."::".__FUNCTION__ . " " . "tmp_files =>>$tmp_files<<<BR>";
                $this->insertToFilesHasTrustedarchive( $db, $tmp_files );
//echo __CLASS__."::".__FUNCTION__ . " " . $tmp_query;
                $i=0;
                unset($tmp_files);
            }
        }

        $this->insertToFilesHasTrustedarchive( $db, $tmp_files );
        $db->transactionCommit();
//die();
echo __CLASS__."::".__FUNCTION__ . " " . "Stop<BR>";
   }

    public function showTrustedArchiveLocalization() {
//echo __CLASS__."::".__FUNCTION__." Start<BR>";
//echo __CLASS__."::".__FUNCTION__." Stop<BR>";
        return $filename;
    }
    
    private function registerTrustedArchiveInBase()
    {
//echo __CLASS__."::".__FUNCTION__." Start<BR>";

        /*
         *  Get data from #__crc_tmp
         */
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query  ->insert ( $db->quoteName( "#__crc_trustedarchive" ) )
                ->columns( $db->quoteName( array ('path', 'name', 'filename', 'root', 'users_id') ) )
                ->values ( implode( ',', array( $db->quote($this->patch), $db->quote($this->name), $db->quote($this->filename), $db->quote($this->root), JFactory::getUser()->get('id') ) ) );
//echo __CLASS__."::".__FUNCTION__. " " . "query = >>" . $query . "<<<br>";

        $db->setQuery($query);
        $db->execute();

//        $db->query();
        $this->base_id = $db->insertid();

//echo __CLASS__."::".__FUNCTION__." Stop<BR>";
    }

    public function showStatusMessage( $s = 0 )
    {
        if( ! func_num_args() ) 
        {
            $s = $this->status;
        }
        switch( (int) $s )
        {
            case ZipArchive::ER_OK           : return 'N No error';
            case ZipArchive::ER_MULTIDISK    : return 'N Multi-disk zip archives not supported';
            case ZipArchive::ER_RENAME       : return 'S Renaming temporary file failed';
            case ZipArchive::ER_CLOSE        : return 'S Closing zip archive failed';
            case ZipArchive::ER_SEEK         : return 'S Seek error';
            case ZipArchive::ER_READ         : return 'S Read error';
            case ZipArchive::ER_WRITE        : return 'S Write error';
            case ZipArchive::ER_CRC          : return 'N CRC error';
            case ZipArchive::ER_ZIPCLOSED    : return 'N Containing zip archive was closed';
            case ZipArchive::ER_NOENT        : return 'N No such file';
            case ZipArchive::ER_EXISTS       : return 'N File already exists';
            case ZipArchive::ER_OPEN         : return 'S Can\'t open file';
            case ZipArchive::ER_TMPOPEN      : return 'S Failure to create temporary file';
            case ZipArchive::ER_ZLIB         : return 'Z Zlib error';
            case ZipArchive::ER_MEMORY       : return 'N Malloc failure';
            case ZipArchive::ER_CHANGED      : return 'N Entry has been changed';
            case ZipArchive::ER_COMPNOTSUPP  : return 'N Compression method not supported';
            case ZipArchive::ER_EOF          : return 'N Premature EOF';
            case ZipArchive::ER_INVAL        : return 'N Invalid argument';
            case ZipArchive::ER_NOZIP        : return 'N Not a zip archive';
            case ZipArchive::ER_INTERNAL     : return 'N Internal error';
            case ZipArchive::ER_INCONS       : return 'N Zip archive inconsistent';
            case ZipArchive::ER_REMOVE       : return 'S Can\'t remove file';
            case ZipArchive::ER_DELETED      : return 'N Entry has been deleted';
       
            default: return sprintf('Unknown status %s', $s );
        }
    }
}