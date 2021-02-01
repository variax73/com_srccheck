<?php
srcCheckLog::debug( "LOAD: " . __FILE__ );
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



defined('_JEXEC') or die('Restricted access');

define( "TA_MODE_INIT",         0 );
define( "TA_MODE_NORMAL",       1 );

define( "TA_TRASH_NAME",        "trash" );

include_once (__DIR__.DIRECTORY_SEPARATOR.'trustedarchivedb.php');

class TrustedArchive extends ZipArchive
{
    const ERR_CREATE_ARCHIVE_CATALOG    = 1000;
    const ERR_CREATE_ARCHIVE            = 1001;
    const ERR_COPY_ARCHIVE              = 1002;
    const ERR_OPEN_ARCHIVE              = 1003;

    private                     $path;
    private                     $name;
    private                     $root;
    private                     $base_id;
    private                     $open_result;
    private                     $users_id;
    private                     $taDB;
    private                     $trashName          = TA_TRASH_NAME;
    public                      $error  = null;

    public function __construct( $p, $mode = TA_MODE_NORMAL )
    {
srcCheckLog::start();

srcCheckLog::debug(
    "id       = ". $p["id"] . "\n".
    "root     = ". $p["root"] . "\n".
    "filename = ". $p["filename"] . "\n".
    "name     = ". $p["name"] . "\n" .
    "ta_mode  = ". $mode );

        $this->base_id  = $p[ "id" ];
        $this->path     = substr( $p["filename"], 0 , strrpos( $p["filename"], DIRECTORY_SEPARATOR ) );
        $tmp_filename   = substr( $p["filename"], strrpos( $p["filename"], DIRECTORY_SEPARATOR )+1 );
        $this->root     = $p["root"];
        $this->users_id = JFactory::getUser()->get('id');

srcCheckLog::debug( "users_id       = >" . $this->users_id . "<>" . JFactory::getUser()->get('id') );

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

        if( $mode == TA_MODE_INIT )
        {
srcCheckLog::info( "New Archive initialisation..." );
srcCheckLog::debug( "New Archive initialisation..." );
            switch ( $this->open_result = $this->open( $this->path . DIRECTORY_SEPARATOR . $tmp_filename, ZipArchive::EXCL ) )
            {
                case ZipArchive::ER_EXISTS:
srcCheckLog::info( "Archive file exist and will be copy (err: $this->open_result)" );
srcCheckLog::debug( "Archive file exist and will be copy (err: $this->open_result)" );

                    if( !copy( $this->path . DIRECTORY_SEPARATOR . $tmp_filename, $this->path . DIRECTORY_SEPARATOR . date("YmdHis") .$tmp_filename.".bup") )
                    {
srcCheckLog::debug( "Problem with copy existing archive (err: $this->open_result)" );
srcCheckLog::error( "Problem with copy existing archive (err: $this->open_result)" );

                        $this->error = $this::ERR_COPY_ARCHIVE;
                        return;
                    };

                    unlink($this->path . DIRECTORY_SEPARATOR . $tmp_filename);
                case ZipArchive::ER_NOENT:
srcCheckLog::info( "Create new archive $tmp_filename in path $this->path (err: $this->open_result)" );
srcCheckLog::debug( "Create new archive $tmp_filename in path $this->path (err: $this->open_result)" );

                    $this->close();
                    if( !file_exists( $this->path ))
                    {
                        if( !( $this->open_result = mkdir( $this->path ) ) )
                        {
srcCheckLog::debug( "Problem with create new directory: $this->path (err: $this->open_result)" );
srcCheckLog::error( "Problem with create new directory: $this->path (err: $this->open_result)" );

                            $this->error = $this::ERR_CREATE_ARCHIVE_CATALOG;
                            return;
                        }
                    }

                    if( !( $this->open_result = $this->open( $this->path . DIRECTORY_SEPARATOR . $tmp_filename, ZipArchive::CREATE ) ) )
                    {
srcCheckLog::debug( "Problem with create new archive: $this->path" . DIRECTORY_SEPARATOR . "$tmp_filename (err: $this->open_result)" );
srcCheckLog::error( "Problem with create new archive: $this->path" . DIRECTORY_SEPARATOR . "$tmp_filename (err: $this->open_result)" );

                        $this->error = $this::ERR_CREATE_ARCHIVE;
                        return;
                    };
                    break;
                default:
srcCheckLog::debug( "Unexpected problem with create new archive: $this->path" . DIRECTORY_SEPARATOR . "$tmp_filename (err: $this->open_result)" );
srcCheckLog::error( "Unexpected problem with create new archive: $this->path" . DIRECTORY_SEPARATOR . "$tmp_filename (err: $this->open_result)" );

                        $this->error = $this::ERR_CREATE_ARCHIVE;
                        return;
                    break;
            }

            $this->open_result = $this->addEmptyDir( $this->trashName );
srcCheckLog::debug( "open_result [Trash CREATE]   = >" . $this->open_result );

            $this->verifyCrc( $mode );

            $this->addFilesToTrustedArchive( $mode );
        }
        else
        {
srcCheckLog::debug( " Get Access to existed Archive" );
            if( !($this->open_result = $this->open( $this->path . DIRECTORY_SEPARATOR . $tmp_filename ) ) )
            {
srcCheckLog::debug( "Problem with open archive: $this->path" . DIRECTORY_SEPARATOR . "$tmp_filename (err: $this->open_result)" );
srcCheckLog::error( "Problem with open archive: $this->path" . DIRECTORY_SEPARATOR . "$tmp_filename (err: $this->open_result)" );

                $this->error = $this::ERR_OPEN_ARCHIVE;
                return;
            }
        }

srcCheckLog::debug( 
        "base_id        = >" . $this->base_id . "\n".
        "path           = >" . $this->path . "\n".
        "tmp_filename   = >" . $tmp_filename . "\n".
        "root           = >" . $this->root . "\n".
        "name           = >" . $this->name . "\n".
        "filename       = >" . $this->filename . "\n".
        "open_result    = >" . $this->open_result . "\n".
        "status         = >" . $this->showStatusMessage() . "\n".
        "status         = >" . $this->status . "\n".
        "statusSys      = >" . $this->statusSys );

srcCheckLog::stop();
    }

    public function __destruct()
    {
        $this->close();
    }

    public function updateArchive()
    {
srcCheckLog::start();
        $this->taDB->updateVeryfiedCrcCheck();
        $this->addFilesToTrustedArchive( );
srcCheckLog::stop();
    }

    public function verifyCrc( $mode = TA_MODE_NORMAL )
    {
srcCheckLog::start();
        $file_status = FILE_STATUS_NEW;
        if( $mode == TA_MODE_INIT ) $file_status = FILE_STATUS_VERIFIED;
        $this->taDB->db->transactionStart();
        $this->taDB->clearCrcTmp();
        $lft = $this->listFilesTreeCrc( $this->root );
srcCheckLog::debug( "Number of files read sizeof( lft ) =>" . sizeof( $lft ) . "<br>" );
        $this->taDB->insertCrcTmp( $lft );
        $this->taDB->insertCrcFiles( $file_status );
        $this->taDB->insertCrcCheckHistory();
        $this->taDB->insertCrcCheck( $file_status, $mode );
        $this->taDB->updateDeletedCrcFiles();
        $this->taDB->updateNewCrcFiles();
        $this->taDB->db->transactionCommit();
srcCheckLog::stop();
    }

    private function listFilesTreeCrc( $dir, &$result = array() )
    {
        $files = scandir($dir);
        foreach($files as $key => $value)
        {
            $path = $dir.DIRECTORY_SEPARATOR.$value;
            if( !is_dir($path) ){
                $result[] = array(  "path" => dirname($path), "filename" => $value, "crc" => md5_file($path) );
            }elseif ($value != '.' && $value != "..") {
                $this->listFilesTreeCrc( $path, $result );
            }
        }
        return $result;
    }

    public function addFilesToTrustedArchive( $mode = TA_MODE_NORMAL )
    {
srcCheckLog::start();
srcCheckLog::debug( "Last check is = >>" . $this->taDB->lastCheckId() . "<<" );
        $qFilesAdded = 0;
        $files = $this->taDB->selectCrcFilesToAddToTa( $mode );

//srcCheckLog::debug(var_dump( $files ) );
        foreach ( $files as $file )
        {
//            $this->close();
//            $this->open_result = $this->open( $this->path . DIRECTORY_SEPARATOR . $this->filename );
            if( $file <> null )
            {
                $f = $file->path.DIRECTORY_SEPARATOR.$file->filename;
                $result = $this->addFile(  $f, $file->ta_localisation );
srcCheckLog::debug( "filename           =>" . $f . "\n" .
                    "ta_localizsation   =>" . $file->ta_localisation . "\n" .
                    "result             =>" . $result . "\n" .
                    "status             =>" . $this->status
                  );
                if( !$this->status )
                {
                    $tmp_files[] = addslashes( $f );
                    $qFilesAdded ++;
                }
            }
        }
srcCheckLog::debug( "Added $qFilesAdded files to archive [$this->name($this->base_id)]" );
        $this->taDB->insertFilesHasTrustedArchive( $tmp_files );
srcCheckLog::stop();
    }

    public function validFilesInTrustedArchiveById( $files_id )
    {
srcCheckLog::start();
        $this->taDB->setLastCrcCheckStatusByFilesId( $files_id, FILE_CHECKED_STATUS_VALID );
srcCheckLog::stop();
    }

    public function eraseFilesInTrustedArchiveById( $files_id )
    {
srcCheckLog::start();
        $filesToDelete = $this->taDB->getFilesToEraseById( $files_id );
        foreach ( $filesToDelete as $file )
        {
srcCheckLog::debug( "id = >>" . $file->id . "<<path = >>" . $file->path . "<<filename = >>" . $file->filename . "<<ta_localisation = >>" . $file->ta_localisation ); 
            $result = $this->renameName( $file->ta_localisation, $this->trashName . DIRECTORY_SEPARATOR . $file->ta_localisation );
srcCheckLog::debug( "result = >>" . $result );
//            if( !$this->renameName( $file->ta_localisation, $this->trashName . DIRECTORY_SEPARATOR . $file->ta_localisation ) )
            if( !$result )
            {
                $err_msg = JText::sprintf( "COM_SRCCHECK_ERR_ARC_PUT_TO_TRASHCAN", $file->filename . "[" . $file->ta_localisation . "](" . $this->showStatusMessage() . ")" );
                srcCheckLog::error( $err_msg );
                JError::raiseError( null, $err_msg );
            }
        }
        $this->taDB->updateChosenFilesStatus( FILE_STATUS_IN_TRASHCAN );
srcCheckLog::stop();
    }

    public function eraseFilesFromTrashcanById( $files_id = null )
    {
srcCheckLog::start();
        if( $files_id == null )
        {
srcCheckLog::debug( "Delete All files in Trashcan" );
            $files_id = $this->taDB->getCrcFilesByStatus( FILE_STATUS_IN_TRASHCAN );
        }
        $filesToDelete = $this->taDB->getFilesToEraseById( $files_id );
        foreach ( $filesToDelete as $file )
        {
srcCheckLog::debug( "id = >>" . $file->id . "<<path = >>" . $file->path . "<<filename = >>" . $file->filename . "<<ta_localisation = >>" . $file->ta_localisation ); 
            $result = $this->deleteName( $this->trashName . DIRECTORY_SEPARATOR . $file->ta_localisation );
srcCheckLog::debug( "result = >>" . $result );
//            if( !$this->deleteName( $this->trashName . DIRECTORY_SEPARATOR . $file->ta_localisation ) )
            if( !$result )
            {
                $err_msg = JText::sprintf( "COM_SRCCHECK_ERR_ARC_EMPTY_TRASHCAN", $file->filename . "[" . $file->ta_localisation . "](" . $this->showStatusMessage() . ")" );
                srcCheckLog::error( $err_msg );
                JError::raiseError( null, $err_msg );
            }
        }
        $this->taDB->deleteChosenFiles();
srcCheckLog::stop();
    }

    public function showTrustedArchiveLocalization()
    {
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
