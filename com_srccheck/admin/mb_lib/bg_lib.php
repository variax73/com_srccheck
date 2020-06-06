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

/* 
 * A function that runs a process asynchronously in Windows or Linux
 */
function bg_run(
    $command,
    $stdin = null,
    $redirectStdout = null,
    $redirectStderr = null,
    $cwd = null,
    $env = null,
    $other_options = null
) {

echo "command = ".$command. "<br>";
echo "stdin = ".$stdin. "<br>";
echo "redirectStdout = ".$redirectStdout. "<br>";
echo "redirectStdout = ".$redirectStdout. "<br>";
echo "cwd = ".$cwd. "<br>";
echo "env = ".$env. "<br>";
echo "other_options = ".$other_options. "<br>";

    $descriptorspec = array(
        1 => is_string($redirectStdout) ? array('file', $redirectStdout, 'w') : array('pipe', 'w'),
        2 => is_string($redirectStderr) ? array('file', $redirectStderr, 'w') : array('pipe', 'w'),
    );
    if (is_string($stdin)) {
        $descriptorspec[0] = array('pipe', 'r');
    }
    $proc = proc_open($command, $descriptorspec, $pipes, $cwd, $env, $other_options);
    if (!is_resource($proc)) {
        throw new \Exception("Failed to start background process by command: $command");
    }
    if (is_string($stdin)) {
        fwrite($pipes[0], $stdin);
        fclose($pipes[0]);
    }
    if (!is_string($redirectStdout)) {
        fclose($pipes[1]);
    }
    if (!is_string($redirectStderr)) {
        fclose($pipes[2]);
    }
    return $proc;
}

function bg_run_old( $command )
{
echo "BG_RUN: START<br>";

    /*
     * Veryfication of oprating system
     */
    switch (PHP_OS)
    {
        case "WIN32":
        case "WINNT":
        case "Windows":
echo "BG_RUN: Windows case<br>";
//            $command = 'start start cmd /c "'. $command . '"';
//            $command = 'start start '. $command . ' >> php_out.txt';
            break;
        case "Linux":
        case "Unix":
echo "BG_RUN: Unix case<br>";
            $command = $command .' &';
            break;
        default:
echo "BG_RUN: other case<br>";
            break;
    }
echo "BG_RUN: command = " . $command . "<br>";
    $handle = popen($command, 'r');
echo "BG_RUN: handle = " . $handle . gettype($handle) . "<br>";
    $read = fread($handle, 2096);
echo "BG_RUN: read = " . $read . "<br>";

    if($handle!==false){
echo "BG_RUN: handle TRUE <br>";
        pclose($handle);
        return true;
    } else {
echo "BG_RUN: handle FALSE<br>";
        return false;
    }
echo "BG_RUN: STOP<br>";
}