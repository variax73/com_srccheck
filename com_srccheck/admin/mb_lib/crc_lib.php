<?php
/* 
 * Copyright (C) 2020 Your Name <your.name at your.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @package     Joomla.Administrator
 * @subpackage  com_srccheck
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

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

//echo "Załadowano ". $j . " wierszy do tabeli tmp<br>";
}

function update_crc_from_tmp($veryfied=0){

    if(!$veryfied) $veryfied=0;

    // Update CRC information
    // Get a db connection.
    $db = JFactory::getDbo();

    $query = $db->getQuery(true);
    $query = "INSERT INTO #__crc_files (path, filename) SELECT path, filename FROM #__crc_tmp ct LEFT JOIN #__crc_files cf USING (path, filename) WHERE cf.path is NULL;";
    $db->setQuery($query);
    $db->execute();

    $query = $db->getQuery(true);
    $query = "INSERT INTO #__crc_check_history (users_id) VALUES('".JFactory::getUser()->get('id')."');";
    $db->setQuery($query);
    $db->execute();


    $query = $db->getQuery(true);
    $query = "INSERT INTO #__crc_check (crc_files_id, crc, veryfied, crc_check_history_id) SELECT cf.id, ct.crc, ".$veryfied.", LAST_INSERT_ID() FROM #__crc_files cf, #__crc_tmp ct WHERE cf.path = ct.path AND cf.filename = ct.filename;";

    $db->setQuery($query);

    $db->execute();
}

function update_veryfied_crc(){
    // Update CRC information
    // Get a db connection.
    $db = JFactory::getDbo();

    $query = $db->getQuery(true);
    $query = "UPDATE #__crc_check ccc, #__crc_check ccp, (SELECT max(cid.id) c_id, max(pid.id) p_id FROM #__crc_check_history cid, #__crc_check_history pid WHERE pid.id < cid.id) ids SET ccc.veryfied = 1 WHERE ccc.crc_check_history_id = ids.c_id AND ccp.crc_check_history_id = ids.p_id AND ccc.crc_files_id = ccp.crc_files_id AND ccc.crc = ccp.crc AND ccp.veryfied = 1;";
    $db->setQuery($query);
    $db->execute();

}
