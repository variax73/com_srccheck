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

include_once ( "stack.php" );

use Joomla\CMS\Log\Log;

$GLOBALS[ "start_time" ] = new stack();

class srcCheckLog extends Log
{
    public function __construct( $p = Array(    "scLog"         => "com_srccheck.log",
                                                "scDebug"       => "com_srccheck_debug.log"
                                           )
                               )
    {
        $this->scErrStack = $p[ "scErrStack" ];
        $this->addLogger(   array( "text_file" => $p[ "scLog" ] ), // "com_srccheck.log" ),
                            Log::ALL & ~Log::DEBUG,
                            array("com_srccheck")
                        );
        $this->addLogger(   array( "text_file" => $p[ "scDebug" ] ), //"com_srccheck_debug.log" ),
                            Log::DEBUG,
                            array("com_srccheck")
                        );
    }

    public function addEntryToLog( $category, $entry, $context = 2)
    {
        if( !($category == Log::DEBUG) or JDEBUG )
        {
            $msg = srcCheckLog::addLocation( $context ) . "\n" . $entry;
            Log::add( $msg, $category, "com_srccheck" );
        }
    }

    public function start( $entry = "Start" )
    {
        $st = gettimeofday( true );
        $GLOBALS[ "start_time" ]->push( gettimeofday( true ) );
        srcCheckLog::addEntryToLog( Log::DEBUG, $entry, 3 );
    }

    public function currentDuration( $entry = "Current duration ")
    {
        $duration = gettimeofday(true) - $GLOBALS[ "start_time" ]->peek();
        srcCheckLog::addEntryToLog( Log::DEBUG, $entry . "(" . $duration . ")", 3 );
    }

    public function stop( $entry = "Stop" )
    {
        $duration = gettimeofday(true) - $GLOBALS[ "start_time" ]->pop();
        srcCheckLog::addEntryToLog( Log::DEBUG, $entry . " (" . $duration . ")", 3 );
    }

    public function debug( $entry )
    {
        srcCheckLog::addEntryToLog( Log::DEBUG, $entry, 3 );
    }

    public function info( $entry )
    {
        srcCheckLog::addEntryToLog( Log::INFO, $entry, 3 );
        srcCheckLog::addEntryToLog( Log::DEBUG, $entry, 3 );
    }

    public function error( $entry )
    {
        srcCheckLog::addEntryToLog( Log::ERROR, $entry, 3 );
        srcCheckLog::addEntryToLog( Log::DEBUG, $entry, 3 );
    }

    private function addLocation( $c )
    {
        $caller = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, $c+2 );
        $result = substr( $caller[ $c-1 ]["file"], strrpos( $caller[ $c-1 ]["file"], DIRECTORY_SEPARATOR )+1 ) . "(" . $caller[ $c-1 ]["line"] . ") ";
        if( $caller[ $c ]["class"] != '' )
            $result .= $caller[ $c ]["class"].$caller[ $c ]["type"];
        $result .= $caller[ $c ]["function"];

        return $result;
    }
}