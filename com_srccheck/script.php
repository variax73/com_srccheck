<?php
/**
 **************************************************************************
 Source Files Check - component that verifies the integrity of Joomla files
 **************************************************************************
 * @author    Maciej Bednarski (Green Line) <maciek.bednarski@gmail.com>
 * @copyright Copyright (C) 2020 Green Line. All Rights Reserved.
 * @license     GNU General Public License version 3, or later
 * @version   2.0.0
 **************************************************************************
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
//include_once (JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'srcCheckErr.php');
include_once (__DIR__.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'srcchecklog.php');
include_once (__DIR__.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'trustedarchive.php');

//define( 'TA_LOCALISATION',  JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'archives' );
define( 'TA_LOCALISATION',  JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'archives' );
//define( 'TA_LOCALISATION',  'p:\tmp\xxx' );
define( 'TA_FILENAME',      'joomla.zip' );
define( 'TA_NAME',          'Joomla\'s root' );
define( 'TA_PASSWORD',      ')9*CA^gbaH!oij#kj' );

class com_SrcCheckInstallerScript
{
    /**
     * This method is called after a component is installed.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    private $fromVersion    = null;
    private $toVersion      = null;


    public function install($parent) 
    {
        $sc_log = new srcCheckLog();
srcCheckLog::start();


//include_once (__DIR__.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'TrustedArchive.php');
//        $parent->getParent()->setRedirectURL('index.php?option=com_srccheck');
        $tarchive = new TrustedArchive( Array(  "root"      => JPATH_ROOT,
                                                "filename"  => TA_LOCALISATION.DIRECTORY_SEPARATOR.TA_FILENAME,
                                                "name"      => TA_NAME ),
                                        TA_MODE_INIT );

srcCheckLog::stop();
        return $this->errorsHandle( $tarchive->error );
    }

    /**
     * This method is called after a component is uninstalled.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function uninstall($parent) 
    {
include_once (JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'srcchecklog.php');
$srcCheckLogger = new srcCheckLog();

srcCheckLog::start();
        echo '<p>' . JText::_('COM_SRCCHECK_UNINSTALL_TEXT') . '</p>';
srcCheckLog::stop();
    }

    /**
     * This method is called after a component is updated.
     *
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function update($parent) 
    {
srcCheckLog::start();

//echo __CLASS__."::".__FUNCTION__ . " manifest = " . var_dump( $parent->get('manifest')['type'] ). "<br>";
//echo __CLASS__."::".__FUNCTION__ . " parent->type       = " . $parent->get('manifest')['type'] . "<br>";
//echo __CLASS__."::".__FUNCTION__ . " parent->name       = " . $parent->get('manifest')->name . "<br>";
//echo __CLASS__."::".__FUNCTION__ . " parent->version    = " . $parent->get('manifest')->version . "<br>";
//echo __CLASS__."::".__FUNCTION__ . " parent->creationDate= " . $parent->get('manifest')->creationDate . "<br>";

srcCheckLog::debug( "UPDATE mode fromVersion =>$this->fromVersion< toVersion =>$this->toVersion<" );
//$sc_log->debug( "UPDATE mode fromVersion =>$this->fromVersion< toVersion =>$this->toVersion<" );
        if (!empty($this->fromVersion) && version_compare($this->fromVersion, '2.0.0', 'lt'))
        {
srcCheckLog::debug( "Create first Trusted Archive" );
//$sc_log->debug( "Create first Trusted Archive" );
            /**
            * Register archive in base for old data.
            */
            $taDB = new TrustedArchiveDB( array(    "path"                  => TA_LOCALISATION,
                                                    "name"                  => TA_NAME,
                                                    "filename"              => TA_FILENAME,
                                                    "root"                  => JPATH_ROOT,
                                                    "users_id"              => JFactory::getUser()->get('id')
                                                ) );
            $db = JFactory::getDbo();
            $query  = $db->getQuery(true)
                    -> update( $db->quoteName( '#__crc_check', 'cc' ) )
//                  -> set( $db->quoteName( 'cc.ta_localisation' ) . ' = UUID()' )
                    -> set( $db->quoteName( 'cc.crc_trustedarchive_id' ) . ' = ' . $taDB->id )
                    -> where( $db->quoteName( 'cc.ta_localisation' ) . ' = ""' );
srcCheckLog::debug( "query = >>" . $query . "<<" );
//$sc_log->debug( "query = >>" . $query . "<<" );
            $db->setQuery($query);
            $r = $db->execute();
srcCheckLog::debug( "query r = >>" . $r . "<<" );
//$sc_log->debug( "query r = >>" . $r . "<<" );

            $query  = $db->getQuery(true);
            $query  = "alter table #__crc_check add constraint `fk_crc_check_crc_trustedarchive_id` FOREIGN KEY (`crc_trustedarchive_id`) REFERENCES `#__crc_trustedarchive` (`id`)";
srcCheckLog::debug( "query = >>" . $query . "<<" );
//$sc_log->debug( "query = >>" . $query . "<<" );

            $db->setQuery($query);
            $r = $db->execute();
srcCheckLog::debug( "query r = >>" . $r . "<<" );
//$sc_log->debug( "query r = >>" . $r . "<<" );

            $tarchive = new TrustedArchive( Array( "id" => $taDB->id ), TA_MODE_INIT );

            /**
            * Delete old files
            */
            unlink( JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'crc.php');
            return $this->errorsHandle( $tarchive->error );
        }
        echo '<p>' . JText::sprintf('COM_SRCCHECK_UPDATE_TEXT', $parent->get('manifest')->version) . '</p>';

srcCheckLog::stop();
        $parent->getParent()->setRedirectURL('index.php?option=com_srccheck');
    }

    /**
     * Runs just before any installation action is performed on the component.
     * Verifications and pre-requisites should run in this function.
     *
     * @param  string    $type   - Type of PreFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function preflight($type, $parent) 
    {
//        include_once (__DIR__.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'srcCheckLog.php');
        $srcCheckLogger = new srcCheckLog();
srcCheckLog::debug( "LOAD: " . __FILE__ );

srcCheckLog::start();
        echo '<p>' . JText::_('COM_SRCCHECK_PREFLIGHT_ENVIRONMENT_VERIFICATION') . '</p>';

        /**
         * Set version of this update.
         */
        $this->toVersion = $parent->get('manifest')->version;

        /* 
         * Get from base curent version of component
         */
        $db    = JFactory::getDbo();
	$query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__extensions')
                    ->where( ' type =' . $db->quote( $parent->get('manifest')['type'] )
                            .' AND element=' . $db->quote( $parent->get('manifest')->name )
                            );
srcCheckLog::debug( "query =>$query" );
        $db->setQuery($query);
        $db->execute();
        try
	{
            $installed = $db->loadObject();
//srcCheckLog::debug( "installed =>". var_dump( json_decode($installed->manifest_cache)->version ) );

            $this->fromVersion = json_decode($installed->manifest_cache)->version;
srcCheckLog::debug( " version =>". $this->fromVersion );
	}
	catch (Exception $e)
	{
            echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br />';
            return;
        }

//        $parent->getParent()->setRedirectURL('index.php?option=com_srccheck');
srcCheckLog::stop();
    }

    /**
     * Runs right after any installation action is performed on the component.
     *
     * @param  string    $type   - Type of PostFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    function postflight($type, $parent) 
    {
        $sc_log = new srcCheckLog();
srcCheckLog::start();
        echo '<p>' . JText::_('COM_SRCCHECK_POSTFLIGHT_' . $type . '_TEXT') . '</p>';

//        if($type == "update")
//        {
//srcCheckLog::debug( "UPDATE mode fromVersion =>$this->fromVersion< toVersion =>$this->toVersion<" );
////$sc_log->debug( "UPDATE mode fromVersion =>$this->fromVersion< toVersion =>$this->toVersion<" );
//            if (!empty($this->fromVersion) && version_compare($this->fromVersion, '1.0.3', 'lt'))
//            {
//srcCheckLog::debug( "Create first Trusted Archive" );
////$sc_log->debug( "Create first Trusted Archive" );
//                /**
//                 * Register archive in base for old data.
//                 */
//                $taDB = new TrustedArchiveDB( array(    "path"                  => TA_LOCALISATION,
//                                                        "name"                  => TA_NAME,
//                                                        "filename"              => TA_FILENAME,
//                                                        "root"                  => JPATH_ROOT,
//                                                        "users_id"              => JFactory::getUser()->get('id')
//                                                   ) );
//                $db = JFactory::getDbo();
//                $query  = $db->getQuery(true)
//                        -> update( $db->quoteName( '#__crc_check', 'cc' ) )
////                        -> set( $db->quoteName( 'cc.ta_localisation' ) . ' = UUID()' )
//                        -> set( $db->quoteName( 'cc.crc_trustedarchive_id' ) . ' = ' . $taDB->id )
//                        -> where( $db->quoteName( 'cc.ta_localisation' ) . ' = ""' );
//srcCheckLog::debug( "query = >>" . $query . "<<" );
////$sc_log->debug( "query = >>" . $query . "<<" );
//                $db->setQuery($query);
//                $r = $db->execute();
//srcCheckLog::debug( "query r = >>" . $r . "<<" );
////$sc_log->debug( "query r = >>" . $r . "<<" );
//
//                $query  = $db->getQuery(true);
//                $query  = "alter table #__crc_check add constraint `fk_crc_check_crc_trustedarchive_id` FOREIGN KEY (`crc_trustedarchive_id`) REFERENCES `#__crc_trustedarchive` (`id`)";
//srcCheckLog::debug( "query = >>" . $query . "<<" );
////$sc_log->debug( "query = >>" . $query . "<<" );
//
//                $db->setQuery($query);
//                $r = $db->execute();
//srcCheckLog::debug( "query r = >>" . $r . "<<" );
////$sc_log->debug( "query r = >>" . $r . "<<" );
//
//                $tarchive = new TrustedArchive( Array( "id" => $taDB->id ), TA_MODE_INIT );
//                /**
//                 * Delete old files
//                 */
//                unlink( JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'crc.php');
//            }
//        }
srcCheckLog::stop();
    }

    private function errorsHandle( $error )
    {
srcCheckLog::start();
        $err_msg = null;
        switch( $error )
        {
            case TrustedArchive::ERR_CREATE_ARCHIVE_CATALOG:
                $err_msg = JText::sprintf( "COM_SRCCHECK_ERR_CREATE_ARCHIVE_CATALOG", TA_LOCALISATION ) . '<br />';
                break;
            case TrustedArchive::ERR_CREATE_ARCHIVE:
                $err_msg = JText::sprintf( "COM_SRCCHECK_ERR_CREATE_ARCHIVE", TA_LOCALISATION.DIRECTORY_SEPARATOR.TA_FILENAME ) . '<br />';
                break;
            case TrustedArchive::ERR_COPY_ARCHIVE:
                $err_msg = JText::sprintf( "COM_SRCCHECK_ERR_COPY_ARCHIVE", TA_LOCALISATION.DIRECTORY_SEPARATOR.TA_FILENAME ) . '<br />';
                break;
            case TrustedArchive::ERR_OPEN_ARCHIVE:
                $err_msg = JText::sprintf( "COM_SRCCHECK_ERR_OPEN_ARCHIVE", TA_LOCALISATION.DIRECTORY_SEPARATOR.TA_FILENAME ) . '<br />';
                break;
        }
srcCheckLog::debug( $err_msg );
        if( $err_msg != null )
        {
            srcCheckLog::error( $err_msg );
            srcCheckLog::debug( $err_msg );
            JError::raiseError( $error, $err_msg );
            return false;
        }
srcCheckLog::stop();
        return true;
    }
}
