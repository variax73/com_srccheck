<?php
//echo "LOAD " . __FILE__ . "<br>";
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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class stack
{
    private $stack;
    private $stackSize;


    public function __construct()
    {
        $this->stackSize = 0;
    }

    public function push( $element )
    {
        $this->stack[ $this->stackSize ] = $element;
        $this->stackSize++;
    }

    public function pop()
    {
        $ret = $this->stack[ $this->stackSize-1 ];
        unset( $this->stack[ $this->stackSize-1 ] );
        $this->stackSize--;
        return $ret;
    }

    public function peek()
    {
        return $this->stack[ $this->stackSize-1 ];
    }
    
}