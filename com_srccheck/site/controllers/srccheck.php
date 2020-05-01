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

include_once (JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'srcchecks.php');

class SrcCheckControllerSrcCheck extends SrcCheckControllerSrcChecks
{
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    public function cverify()
    {
        $this->verify( SILENCE_MODE );
    }
}   
                
