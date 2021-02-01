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

class SrcCheckControllerSrcChecks extends JControllerAdmin
{
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    public function verify( $mode = NORMAL_MODE )
    {
srcCheckLog::start();;
        $tarchive   = new TrustedArchive( array( "id" => JFactory::getApplication()->input->get( "scat" ) ) );
        $tarchive->verifyCrc();

        $tarchive->updateArchive();
srcCheckLog::stop();
        parent::display($tpl);
    }

    public function valid()
    {
srcCheckLog::start();;
        $tarchive   = new TrustedArchive( array( "id" => JFactory::getApplication()->input->get( "scat" ) ) );

        $tarchive->validFilesInTrustedArchiveById( $this->input->get('cid', array(), 'array') );
srcCheckLog::stop();
        parent::display($tpl);
    }

    public function erase()
    {
srcCheckLog::start();;
        $tarchive   = new TrustedArchive( array( "id" => JFactory::getApplication()->input->get( "scat" ) ) );
        $tarchive->eraseFilesInTrustedArchiveById( $this->input->get('cid', array(), 'array') );

srcCheckLog::stop();
        parent::display($tpl);
    }

    public function EraseCheckedFromTrashcan()
    {
srcCheckLog::start();;
        $tarchive   = new TrustedArchive( array( "id" => JFactory::getApplication()->input->get( "scat" ) ) );
        $tarchive->eraseFilesFromTrashcanById( $this->input->get('cid', array(), 'array') );
srcCheckLog::stop();
        parent::display($tpl);
    }   

    public function EraseAllFromTrashcan()
    {
srcCheckLog::start();;
        $tarchive   = new TrustedArchive( array( "id" => JFactory::getApplication()->input->get( "scat" ) ) );
        $tarchive->eraseFilesFromTrashcanById();
srcCheckLog::stop();
        parent::display($tpl);
    }   
}