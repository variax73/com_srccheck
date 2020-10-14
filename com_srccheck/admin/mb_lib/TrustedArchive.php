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

define( 'INSERT_PAGINATION',    500 );

define( 'TA_MODE_INIT',         0 );
define( 'TA_MODE_NORMAL',       1 );

include_once (JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'TrustedArchiveDB.php');

class TrustedArchive extends ZipArchive
{
    private                     $path;
    private                     $name;
    private                     $root;
    private                     $base_id;
    private int                 $insertPagination  = INSERT_PAGINATION;
    private int                 $open_result;
    private int                 $users_id;
    private TrustedArchiveDB    $taDB;
    private                     $validName          = 'valid';
    private                     $trashName          = 'trash';
    private                     $historyName        = 'history';
//    private                     $newName            = 'new';

    public function __construct( $p )
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";

echo __CLASS__."::".__FUNCTION__." id       = ". $p["id"] . "<BR>";
echo __CLASS__."::".__FUNCTION__." root     = ". $p["root"] . "<BR>";
echo __CLASS__."::".__FUNCTION__." filename = ". $p["filename"] . "<BR>";
echo __CLASS__."::".__FUNCTION__." name     = ". $p["name"] . "<BR>";

        $this->base_id  = $p[ "id" ];
        $this->path     = substr( $p["filename"], 0 , strrpos( $p["filename"], DIRECTORY_SEPARATOR ) );
        $tmp_filename   = substr( $p["filename"], strrpos( $p["filename"], DIRECTORY_SEPARATOR )+1 );
        $this->root     = $p["root"];
        $this->users_id = JFactory::getUser()->get('id');

echo __CLASS__."::".__FUNCTION__." " . " users_id       = >" . $this->users_id . "<>" . JFactory::getUser()->get('id') . "<br>";

        if( $p["name"] == null )
        {
            $this->name = $tmp_filename;
        }
        else
        {
            $this->name = $p["name"];
        }

        $this->taDB = new TrustedArchiveDB( array(  "id"        => $this->base_id,
                                                    "path"      => $this->path,
                                                    "name"      => $this->name,
                                                    "filename"  => $tmp_filename,
                                                    "root"      => $this->root,
                                                    "users_id"  => $this->users_id
                                                 ) );
        $this->path     = $this->taDB->path;
        $this->name     = $this->taDB->name;
        $this->root     = $this->taDB->root;
        $this->base_id  = $this->taDB->id;
        $tmp_filename   = $this->taDB->filename;
        $this->users_id = $this->taDB->users_id;

        if( $p["id"] == null )
        {
echo __CLASS__."::".__FUNCTION__." " . " New Archive initialisation...<br>";
            if( ( $this->open_result = $this->open( $this->path . DIRECTORY_SEPARATOR . $tmp_filename, ZipArchive::EXCL ) ) == ZipArchive::ER_EXISTS )
            {
echo __CLASS__."::".__FUNCTION__." " . " Archive file exist and will be copy<br>";
echo __CLASS__."::".__FUNCTION__." " . " open_result    = >" . $this->open_result . "<br>";

                $this->close();
                if( !copy( $this->path . DIRECTORY_SEPARATOR . $tmp_filename, $this->path . DIRECTORY_SEPARATOR . date("YmdHis") .$tmp_filename.".bup") )
                {
echo __CLASS__."::".__FUNCTION__." " . " Problem with copy existing archive<br>";
                };
                unlink($this->path . DIRECTORY_SEPARATOR . $tmp_filename);
            }
echo __CLASS__."::".__FUNCTION__." " . " open_result    = >" . $this->open_result . "<br>";
            /**
             * Prepare new archive structure.
             */
            $this->open_result = $this->open( $this->path . DIRECTORY_SEPARATOR . $tmp_filename, ZipArchive::CREATE );
echo __CLASS__."::".__FUNCTION__." " . " open_result [CREATE]   = >" . $this->open_result . "<br>";

            $this->addEmptyDir( $this->newName );
            $this->addEmptyDir( $this->validName );
            $this->addEmptyDir( $this->trashName );
//            $this->addEmptyDir( $this->historyName );

            $this->verifyCrc( TA_MODE_INIT );
            
            /**
             * Initialise files in archive.
             */
            $crcTmp = $this->taDB->selectCrcTmp();
            foreach ( $crcTmp as $i => $row )
            {
                $fileListToArchive[] = $row->path.DIRECTORY_SEPARATOR.$row->filename;
            }
            $this->addFilesToTrustedArchive( $fileListToArchive );
        }
        else
        {
            /**
             * Get access to existed archive
             */
echo __CLASS__."::".__FUNCTION__." " . " Get Access to existed Archive<br>";
            if( !($this->open_result = $this->open( $this->path . DIRECTORY_SEPARATOR . $tmp_filename ) ) )
            {
echo __CLASS__."::".__FUNCTION__." " . " error with access to archive";
            }
        }

echo __CLASS__."::".__FUNCTION__." " . " base_id        = >" . $this->base_id . "<br>";
echo __CLASS__."::".__FUNCTION__." " . " path           = >" . $this->path . "<br>";
echo __CLASS__."::".__FUNCTION__." " . " tmp_filename   = >" . $tmp_filename . "<br>";
echo __CLASS__."::".__FUNCTION__." " . " root           = >" . $this->root . "<br>";
echo __CLASS__."::".__FUNCTION__." " . " name           = >" . $this->name . "<br>";
echo __CLASS__."::".__FUNCTION__." " . " filename       = >" . $this->filename . "<br>";
echo __CLASS__."::".__FUNCTION__." " . " open_result    = >" . $this->open_result . "<br>";
echo __CLASS__."::".__FUNCTION__." " . " status         = >" . $this->showStatusMessage() . "<br>";
echo __CLASS__."::".__FUNCTION__." " . " status         = >" . $this->status . "<br>";
echo __CLASS__."::".__FUNCTION__." " . " statusSys      = >" . $this->statusSys . "<br>";

echo __CLASS__."::".__FUNCTION__." Stop<BR>";
    }

    public function __destruct()
    {
        $this->close();
    }

    public function updateArchive()
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";
        $this->taDB->updateVeryfiedCrcCheck();
        /**
         * Add new files to archive
         */
        $this->taDB->insertToFilesHasTrustedarchiveById( $this->taDB->getFilesIdByStatus( FILE_STATUS_NEW ) );
        
echo __CLASS__."::".__FUNCTION__." Stop<BR>";
    }

    public function verifyCrc( $mode = TA_MODE_NORMAL )
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";

        $file_status = FILE_STATUS_NEW;
        if( $mode == TA_MODE_INIT ) $file_status = FILE_STATUS_VERIFIED;
//        $this->taDB->db->transactionStart();
        $this->taDB->clearCrcTmp();
        $lft = $this->listFilesTreeCrc( $this->root );
        $this->taDB->insertCrcTmp($lft);
        $this->taDB->insertCrcFiles( $file_status );
        $this->taDB->insertCrcCheckHistory();
        $this->taDB->insertCrcCheck( $file_status );
        $this->taDB->updateDeletedCrcFiles();
//        $this->taDB->db->transactionCommit();
        
echo __CLASS__."::".__FUNCTION__." Stop<BR>";
    }

    private function listFilesTreeCrc( $dir, &$result = array() )
    {
       $files = scandir($dir);
        foreach($files as $key => $value)
        {
            $path = $dir.DIRECTORY_SEPARATOR.$value;
            if( !is_dir($path) ){
                $result[] = array(dirname($path), $value, md5_file($path) );
            }elseif ($value != '.' && $value != '..') {
                $this->listFilesTreeCrc( $path, $result );
            }
        }
        return $result;
    }

    private function addFilesToHistory( $file )
    {
echo __CLASS__."::".__FUNCTION__ . " " . "Start<BR>";
//var_dump( $files );
//        foreach ( $files as $file )
//        {
echo __CLASS__."::".__FUNCTION__ . " " . "file = >" .  $file . '<<br>';
echo __CLASS__."::".__FUNCTION__ . " " . "src file = >" . str_replace( $this->root, $this->validName, $file ) . "< dest file = >" . str_replace( $this->root, $this->historyName . DIRECTORY_SEPARATOR . date( 'Ymd' ) . '[' . $this->taDB->lastCheckId() . ']', $file ) . '<<br>';
            $this->renameName( str_replace( $this->root, $this->validName, $file ), str_replace( $this->root, $this->historyName . DIRECTORY_SEPARATOR . date( 'Ymd' ) . '[' . $this->taDB->lastCheckId() . ']', $file ) );
//        }
echo __CLASS__."::".__FUNCTION__ . " " . "Stop<BR>";
    }

    public function addFilesToTrustedArchive( $files )
    {
echo __CLASS__."::".__FUNCTION__ . " " . "Start<BR>";
//echo __CLASS__."::".__FUNCTION__ . " " . "OS is " . PHP_OS_FAMILY . " File to add to TA >>" . $files . "<<>>" . str_replace($rootPatch.DIRECTORY_SEPARATOR, "", $files) . "<< <BR>";
// echo __CLASS__."::".__FUNCTION__ . " " . "File to add to TA >>" . var_dump($files) . "<< <BR>";
echo __CLASS__."::".__FUNCTION__ . " " . "Last check is = >>" . $this->taDB->lastCheckId() . "<< <BR>";

        $i = 0;
        $this->taDB->db->transactionStart();

        foreach ( $files as $file )
        {
            $this->addFilesToHistory( $file );
            $this->addFile( $file, str_replace( $this->root, $this->validName, $file) );

//echo __CLASS__."::".__FUNCTION__ . " " . " file = >>$file<< (" . str_replace( $this->root, $this->validName, $file) . ")" . " status = " . $this->showStatusMessage() . "<< <BR>";
            if( !$this->status && $i < $this->insertPagination )
            {
                $tmp_files[] = addslashes($file);
                $i++;
            }else
            {
//echo __CLASS__."::".__FUNCTION__ . " " . " Go to INSERT $i<BR>";
                $tmp_files[] = addslashes($file);
//echo __CLASS__."::".__FUNCTION__ . " " . "tmp_files =>>$tmp_files<<<BR>";
                $this->taDB->insertToFilesHasTrustedarchive( $tmp_files );
//echo __CLASS__."::".__FUNCTION__ . " " . $tmp_query;
                $i=0;
                unset($tmp_files);
            }
        }

        $this->taDB->insertToFilesHasTrustedarchive( $tmp_files );
        $this->taDB->db->transactionCommit();
echo __CLASS__."::".__FUNCTION__ . " " . "Stop<BR>";
    }

    public function addFilesInTrustedArchiveById( $files_id )
    {
echo __CLASS__."::".__FUNCTION__ . " " . "Start<BR>";
        $filesToAdd = $this->taDB->getFilenameById( $files_id );
        $this->addFilesToTrustedArchive( $filesToAdd );

echo __CLASS__."::".__FUNCTION__ . " " . "Stop<BR>";
    }

    public function validFilesInTrustedArchiveById( $files_id )
    {
echo __CLASS__."::".__FUNCTION__ . " " . "Start<BR>";

        $this->addFilesInTrustedArchiveById( $files_id );
        $this->taDB->setLastCrcCheckStatusByFilesId( $files_id, FILE_CHECKED_STATUS_VALID );
echo __CLASS__."::".__FUNCTION__ . " " . "Stop<BR>";
    }

    public function eraseFilesInTrustedArchiveById( $files_id )
    {
echo __CLASS__."::".__FUNCTION__ . " " . "Start<BR>";

        $filesToDelete = $this->taDB->getFilenameById( $files_id );

        $i = 0;
        foreach ( $filesToDelete as $file )
        {
//echo __CLASS__."::".__FUNCTION__ . " " . " file = >>" . $file . "<|>" . str_replace( $this->root, $this->validName, $file ) . "<< <BR>"; 
            $this->addFilesToHistory( $file );
            $this->renameName( str_replace( $this->root, $this->validName, $file ), str_replace( $this->root, $this->trashName, $file ) );
        }
        $this->taDB->deleteChosenFiles();
echo __CLASS__."::".__FUNCTION__ . " " . "Stop<BR>";
   }
   
    public function showTrustedArchiveLocalization() {
//echo __CLASS__."::".__FUNCTION__." Start<BR>";
//echo __CLASS__."::".__FUNCTION__." Stop<BR>";
        return $filename;
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
};
