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
     * 0 - new file;
     * 1 - checked file;
     * 2 - changed file;
     * 3 - deleted file;
*/

define( 'FILE_STATUS_NEW'       ,'0');
define( 'FILE_STATUS_VERIFIED'  ,'1');
define( 'FILE_STATUS_DELETED'   ,'2');

define( 'FILE_CHECKED_STATUS_INVALID'   ,'0');
define( 'FILE_CHECKED_STATUS_VALID'     ,'1');

function listFilesTree( $dir, &$result = array() ){
    $files = scandir($dir);
    foreach($files as $key => $value)
    {
        $path = $dir.DIRECTORY_SEPARATOR.$value;
        if( !is_dir($path) ){
            $result[] = array(dirname($path), $value, md5_file($path) );
	}elseif ($value != '.' && $value != '..') {
            listFilesTree( $path, $result );
	}
    }
    return $result;
}

function clear_crc_tmp(){
    // Update CRC information
    // Get a db connection.
    $db = JFactory::getDbo();

    $query = $db->getQuery(true);
    $query = "DELETE FROM #__crc_tmp;";
    $db->setQuery($query);
    $db->execute();
}

function generate_crc_tmp( $dir ){

    clear_crc_tmp();
    
    $lft = listFilesTree( $dir );

    // Get a db connection.
    $db = JFactory::getDbo();

    // Create a new query object.
    $query = $db->getQuery(true);

    // Insert columns.
    $columns = array('path', 'filename', 'crc');

    $query
        ->insert($db->quoteName('#__crc_tmp'))
        ->columns($db->quoteName($columns));

    $i=0;
    $j=0;

    foreach($lft as $v)
    {
        // Insert values.
        $v_query = array( $db->quote($v[0]), $db->quote($v[1]), $db->quote($v[2]) );

        // Prepare the insert query.
        $query
            ->values(implode(',', $v_query));
        if($i++ > 400)
        {
            $db->setQuery($query);
            $db->execute();

            // Recreate a new query object.
            $query = $db->getQuery(true);

            $query
                ->insert($db->quoteName('#__crc_tmp'))
                ->columns($db->quoteName($columns));

            $i=0;
        }
        $j++;
    }

    // Set the query using our newly populated query object and execute it.
    $db->setQuery($query);
    $db->execute();
}

function update_crc_from_tmp($veryfied=0){

    if(!$veryfied) $veryfied=FILE_STATUS_NEW;

    // Update CRC information
    // Get a db connection.
    $db = JFactory::getDbo();

    /*
     * Add new files
     */
    $query = $db->getQuery(true);
    $query = "INSERT INTO #__crc_files (path, filename, status) SELECT path, filename,".$veryfied." FROM #__crc_tmp ct LEFT JOIN #__crc_files cf USING (path, filename) WHERE cf.filename is NULL;";
    $db->setQuery($query);
    $db->execute();

    /*
     * Add date check to history
     */
    $query = $db->getQuery(true);
    $query = "INSERT INTO #__crc_check_history (users_id) VALUES('".JFactory::getUser()->get('id')."');";
    $db->setQuery($query);
    $db->execute();

    /*
     * Write check data from tmp table to check table
     */
    $query = $db->getQuery(true);
    $query = "INSERT INTO #__crc_check (crc_files_id, crc, veryfied, crc_check_history_id) SELECT cf.id, ct.crc, ".$veryfied.", LAST_INSERT_ID() FROM #__crc_files cf, #__crc_tmp ct WHERE cf.path = ct.path AND cf.filename = ct.filename;";
    $db->setQuery($query);
    $db->execute();

    /*
     * Update deleted files.
     */
    if(!$veryfied) {
        $query = $db->getQuery(true);
        $query = "UPDATE #__crc_files cf LEFT JOIN #__crc_check cc ON cf.id = cc.crc_files_id AND cc.crc_check_history_id = (SELECT max(cch.id) FROM #__crc_check_history cch) SET cf.status = " .FILE_STATUS_DELETED. " WHERE cc.id is NULL;";
        $db->setQuery($query);
        $db->execute();
    }
    
}

function update_veryfied_crc(){
    // Update CRC information
    // Get a db connection.
    $db = JFactory::getDbo();

    $query = $db->getQuery(true);
    $query = "UPDATE #__crc_check ccc, #__crc_check ccp, (SELECT max(cid.id) c_id, max(pid.id) p_id FROM #__crc_check_history cid, #__crc_check_history pid WHERE pid.id < cid.id) ids SET ccc.veryfied = ".FILE_STATUS_VERIFIED." WHERE ccc.crc_check_history_id = ids.c_id AND ccp.crc_check_history_id = ids.p_id AND ccc.crc_files_id = ccp.crc_files_id AND ccc.crc = ccp.crc AND ccp.veryfied = ".FILE_STATUS_VERIFIED.";";
    $db->setQuery($query);
    $db->execute();
}

function erase_checked_files( $crc_files_id )
{
//echo "Start Function: erase_checked_files <br>";
    // Get a db connection.
    $db = JFactory::getDbo();
    $db->transactionStart();

    $i=0;
    foreach ($crc_files_id AS $cci)
    {
        $tmp_cci[i]=$cci;
        
        if( $i++ > 400 )
        {
            // Delete crc_check records.
            $query = $db->getQuery(true);
            $query  -> delete($db->quoteName( '#__crc_check', 'cc' ))
                    -> where( $db->quoteName( 'cc.crc_files_id' ) . ' IN (' . implode(',', array_map(fn($n) => $db->q($n), $crc_files_id)) . ') ' );
//echo "<br>111.1 " . $query . "<br>";
            $db->setQuery($query);
            $db->execute();

            // Delete crc_files records.
            $query = $db->getQuery(true);
            $query  -> delete($db->quoteName( '#__crc_files', 'cf' ))
                    -> where( $db->quoteName('cf.id') . ' IN (' . implode(',', array_map(fn($n) => $db->q($n), $crc_files_id)) . ') ' );
//echo "<br>111.2 " . $query . "<br>";
            $db->setQuery($query);
            $db->execute();
            $i=0;
        }
    }

    // Delete crc_check records.
    $query = $db->getQuery(true);
    $query  -> delete($db->quoteName( '#__crc_check', 'cc' ))
            -> where( $db->quoteName( 'cc.crc_files_id' ) . ' IN (' . implode(',', array_map(fn($n) => $db->q($n), $crc_files_id)) . ') ' );
//echo "<br>222.1 " . $query . "<br>";
    $db->setQuery($query);
    $db->execute();

    // Delete crc_files records.
    $query = $db->getQuery(true);
    $query  -> delete($db->quoteName( '#__crc_files', 'cf' ))
            -> where( $db->quoteName('cf.id') . ' IN (' . implode(',', array_map(fn($n) => $db->q($n), $crc_files_id)) . ') ' );
//echo "<br>222.2 " . $query . "<br>";
    $db->setQuery($query);
    $db->execute();

    $db->transactionCommit();
echo "End Function: erase_checked_files <br>";
};

function validate_checked_files( $crc_files_id )
{
//echo "Start Function: validate_checked_files <br>";
    // Get a db connection.
    $db = JFactory::getDbo();
    $db->transactionStart();

    $i=0;
    foreach ($crc_files_id AS $cci)
    {
        $tmp_cci[i]=$cci;
        
        if( $i++ > 400 )
        {

            // Create a new query object.
            $query = $db->getQuery(true);
            $query  -> update($db->quoteName( '#__crc_check', 'ccu' ))
                    -> join('INNER', '(SELECT cc_max.crc_files_id AS crc_files_id, MAX(cc_max.crc_check_history_id) AS crc_check_history_id FROM #__crc_check AS cc_max WHERE cc_max.crc_files_id IN (' . implode(',', array_map(fn($n) => $db->q($n), $crc_files_id)) . ') GROUP BY cc_max.crc_files_id) AS cc ON cc.crc_files_id = ccu.crc_files_id AND cc.crc_check_history_id = ccu.crc_check_history_id' )
                    -> set($db->quoteName('ccu.veryfied') . " = " . FILE_CHECKED_STATUS_VALID );

//echo "<br>111 " . $query . "<br>";
            $db->setQuery($query);
            $db->execute();
            $i=0;
        }
    }
    // Create a new query object.
    $query = $db->getQuery(true);
    $query  -> update($db->quoteName( '#__crc_check', 'ccu' ))
            -> join('INNER', '(SELECT cc_max.crc_files_id AS crc_files_id, MAX(cc_max.crc_check_history_id) AS crc_check_history_id FROM #__crc_check AS cc_max WHERE cc_max.crc_files_id IN (' . implode(',', array_map(fn($n) => $db->q($n), $crc_files_id)) . ') GROUP BY cc_max.crc_files_id) AS cc ON cc.crc_files_id = ccu.crc_files_id AND cc.crc_check_history_id = ccu.crc_check_history_id' )
            -> set($db->quoteName('ccu.veryfied') . " = " . FILE_CHECKED_STATUS_VALID );
//echo "<br>222 " . $query . "<br>";
    $db->setQuery($query);

    $db->execute();
    $db->transactionCommit();
//echo "End Function: validate_checked_files <br>";
}
