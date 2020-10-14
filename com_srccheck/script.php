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

//define( 'TA_LOCALISATION',  JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'mb_lib' );
define( 'TA_LOCALISATION',  'p:\tmp' );
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
echo "Script.php install: START";
//        $parent->getParent()->setRedirectURL('index.php?option=com_srccheck');
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
echo "Script.php uninstall: START";
        echo '<p>' . JText::_('COM_SRCCHECK_UNINSTALL_TEXT') . '</p>';
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
echo __CLASS__."::".__FUNCTION__ . " Start<br>";

//echo __CLASS__."::".__FUNCTION__ . " manifest = " . var_dump( $parent->get('manifest')['type'] ). "<br>";
//echo __CLASS__."::".__FUNCTION__ . " parent->type       = " . $parent->get('manifest')['type'] . "<br>";
//echo __CLASS__."::".__FUNCTION__ . " parent->name       = " . $parent->get('manifest')->name . "<br>";
//echo __CLASS__."::".__FUNCTION__ . " parent->version    = " . $parent->get('manifest')->version . "<br>";
//echo __CLASS__."::".__FUNCTION__ . " parent->creationDate= " . $parent->get('manifest')->creationDate . "<br>";


        echo '<p>' . JText::sprintf('COM_SRCCHECK_UPDATE_TEXT', $parent->get('manifest')->version) . '</p>';

        $parent->getParent()->setRedirectURL('index.php?option=com_srccheck');
echo __CLASS__."::".__FUNCTION__ . " Stop<br>";
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
echo __CLASS__."::".__FUNCTION__ . " Start<br>";
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
echo __CLASS__."::".__FUNCTION__ . " query =>$query<<br>";
        $db->setQuery($query);

        try
	{
            $installed = $db->loadObject();
//echo __CLASS__."::".__FUNCTION__ . " installed =>". var_dump( json_decode($installed->manifest_cache)->version ) ."<<br>";

            $this->fromVersion = json_decode($installed->manifest_cache)->version;
echo __CLASS__."::".__FUNCTION__ . " version =>". $this->fromVersion ."<<br>";
	}
	catch (Exception $e)
	{
            echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br />';
            return;
        }

//        $parent->getParent()->setRedirectURL('index.php?option=com_srccheck');
echo __CLASS__."::".__FUNCTION__ . " Stop<br>";
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
echo __CLASS__."::".__FUNCTION__ . " Start<br>";
        include_once (JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'TrustedArchive.php');
echo __CLASS__."::".__FUNCTION__ . " type =>$type<, parent=>$parent<<br>";

        if( $type == "install" )
        {
echo __CLASS__."::".__FUNCTION__ . " Install mode<br>";

            $tarchive = new TrustedArchive( Array(  "root"      => JPATH_ROOT,
                                                    "filename"  => TA_LOCALISATION.DIRECTORY_SEPARATOR.TA_FILENAME,
                                                    "name"      => TA_NAME ) );
        }
        if($type == "update")
        {
echo __CLASS__."::".__FUNCTION__ . " UPDATE mode<br>";
echo __CLASS__."::".__FUNCTION__ . " " . "fromVersion =>$this->fromVersion<  toVersion =>$this->toVersion<<BR>";
            if (!empty($this->fromVersion) && version_compare($this->fromVersion, '1.0.3', 'lt'))
            {
echo __CLASS__."::".__FUNCTION__ . " " . " Create first Trusted Archive<BR>";
            $tarchive = new TrustedArchive( Array(  "root"      => JPATH_ROOT,
                                                    "filename"  => TA_LOCALISATION.DIRECTORY_SEPARATOR.TA_FILENAME,
                                                    "name"      => TA_NAME ) );
            /**
             * Delete old files
             */
                unlink( JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'crc.php');
            }
        }

        
        echo '<p>' . JText::_('COM_SRCCHECK_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
echo __CLASS__."::".__FUNCTION__ . " Stop<br>";
    }
}
