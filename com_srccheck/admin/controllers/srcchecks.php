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

include_once (JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'TrustedArchive.php');
use Joomla\CMS\Factory;

class SrcCheckControllerSrcChecks extends JControllerAdmin
{
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    public function verify( $mode = NORMAL_MODE )
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";
        $tarchive   = new TrustedArchive( array( "id" => JFactory::getApplication()->input->get( "scat" ) ) );
        $tarchive->verifyCrc();
        $tarchive->updateArchive();

echo __CLASS__."::".__FUNCTION__." Stop<BR>";
//        // Display the view
//        if( $mode != SILENCE_MODE )
//        {
            parent::display($tpl);
//        }
    }

    public function valid()
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";
        $ids  = $this->input->get('cid', array(), 'array');

        $tarchive   = new TrustedArchive( array( "id" => JFactory::getApplication()->input->get( "scat" ) ) );

        $tarchive->validFilesInTrustedArchiveById( $this->input->get('cid', array(), 'array') );

echo __CLASS__."::".__FUNCTION__." Stop<BR>";
        parent::display($tpl);
    }

    public function erase()
    {
echo __CLASS__."::".__FUNCTION__." Start<BR>";
        $tarchive   = new TrustedArchive( array( "id" => JFactory::getApplication()->input->get( "scat" ) ) );
        $tarchive->eraseFilesInTrustedArchiveById( $this->input->get('cid', array(), 'array') );

echo __CLASS__."::".__FUNCTION__." Stop<BR>";
        parent::display($tpl);
    }
}   
                
