<?php

/**
 ************************************************************************
 Source Check - module that verifies the integrity of Joomla files
 ************************************************************************
 * @author    Maciej Bednarski (Green Line) <maciek.bednarski@gmail.com>
 * @copyright Copyright (C) 2020 Green Line. All Rights Reserved.
 * @license   GNU General Public License version 3, or later
 * @version   HEAD
 ************************************************************************
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

include_once (JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'mb_lib'.DIRECTORY_SEPARATOR.'crc_lib.php');

class SrcCheckControllerSrcChecks extends JControllerAdmin
{
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    public function verify( $mode = NORMAL_MODE )
    {
        $db = JFactory::getDbo();
        $db->transactionStart();

        generate_crc_tmp( JPATH_ROOT );
        update_crc_from_tmp(false);
        update_veryfied_crc();
        $db->transactionCommit();

        // Display the view
        if( $mode != SILENCE_MODE )
        {
            parent::display($tpl);
        }
    }

    public function valid()
    {
        $ids  = $this->input->get('cid', array(), 'array');
        
        validate_checked_files( $ids );

        parent::display($tpl);
    }

    public function erase()
    {
        $ids  = $this->input->get('cid', array(), 'array');
        
        erase_checked_files( $ids );

        parent::display($tpl);
    }
}   
                
