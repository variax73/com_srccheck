<?php
/**
 ************************************************************************
 Source Files Check - component that verifies the integrity of Joomla files
 ************************************************************************
 * @author    Maciej Bednarski (Green Line) <maciek.bednarski@gmail.com>
 * @copyright Copyright (C) 2020 Green Line. All Rights Reserved.
 * @license   GNU General Public License version 3, or later
 * @version   1.0.1
 ************************************************************************
 */

defined('_JEXEC') or die('Restricted access');

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
    $db = JFactory::getDbo();

    $query = $db->getQuery(true);
    $query = "DELETE FROM #__crc_tmp;";
    $db->setQuery($query);
    $db->execute();
}

function generate_crc_tmp( $dir ){

    clear_crc_tmp();
    
    $lft = listFilesTree( $dir );
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $columns = array('path', 'filename', 'crc');
    $query
        ->insert($db->quoteName('#__crc_tmp'))
        ->columns($db->quoteName($columns));

    $i=0;
    $j=0;

    foreach($lft as $v)
    {
        $v_query = array( $db->quote($v[0]), $db->quote($v[1]), $db->quote($v[2]) );
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
    $db->setQuery($query);
    $db->execute();
}

function update_crc_from_tmp($veryfied=0){

    if(!$veryfied) $veryfied=FILE_STATUS_NEW;
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query = "INSERT INTO #__crc_files (path, filename, status) SELECT path, filename,".$veryfied." FROM #__crc_tmp ct LEFT JOIN #__crc_files cf USING (path, filename) WHERE cf.filename is NULL;";
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
    if(!$veryfied) {
        $query = $db->getQuery(true);
        $query = "UPDATE #__crc_files cf LEFT JOIN #__crc_check cc ON cf.id = cc.crc_files_id AND cc.crc_check_history_id = (SELECT max(cch.id) FROM #__crc_check_history cch) SET cf.status = " .FILE_STATUS_DELETED. " WHERE cc.id is NULL;";
        $db->setQuery($query);
        $db->execute();
    }
}

function update_veryfied_crc(){
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query = "UPDATE #__crc_check ccc, #__crc_check ccp, (SELECT max(cid.id) c_id, max(pid.id) p_id FROM #__crc_check_history cid, #__crc_check_history pid WHERE pid.id < cid.id) ids SET ccc.veryfied = ".FILE_STATUS_VERIFIED." WHERE ccc.crc_check_history_id = ids.c_id AND ccp.crc_check_history_id = ids.p_id AND ccc.crc_files_id = ccp.crc_files_id AND ccc.crc = ccp.crc AND ccp.veryfied = ".FILE_STATUS_VERIFIED.";";
    $db->setQuery($query);
    $db->execute();
}

function erase_checked_files( $crc_files_id )
{
    $db = JFactory::getDbo();
    $db->transactionStart();
    $i=0;
    foreach ($crc_files_id AS $cci)
    {
        $tmp_cci[$i]=$cci;
        
        if( $i++ > 400 )
        {
            $query = $db->getQuery(true);
            $query  -> delete($db->quoteName( '#__crc_check', 'cc' ))
                    -> where( $db->quoteName( 'cc.crc_files_id' ) . ' IN (' . implode(',',$tmp_cci) . ') ' );
            $db->setQuery($query);
            $db->execute();
            $query = $db->getQuery(true);
            $query  -> delete($db->quoteName( '#__crc_files', 'cf' ))
                    -> where( $db->quoteName('cf.id') . ' IN (' . implode(',',$tmp_cci) . ') ' );
            $db->setQuery($query);
            $db->execute();
            $i=0;
        }
    }
    $query = $db->getQuery(true);
    $query  -> delete($db->quoteName( '#__crc_check'))
            -> where( $db->quoteName( 'crc_files_id' ) . ' IN (' . implode(',',$tmp_cci) . ') ' );
    $db->setQuery($query);
    $db->execute();
    $query = $db->getQuery(true);
    $query  -> delete($db->quoteName( '#__crc_files'))
            -> where( $db->quoteName('id') . ' IN (' . implode(',',$tmp_cci) . ') ' );
    $db->setQuery($query);
    $db->execute();
    $db->transactionCommit();
};

function validate_checked_files( $crc_files_id )
{
    $db = JFactory::getDbo();
    $db->transactionStart();
    $i=0;
    foreach ($crc_files_id AS $cci)
    {
        $tmp_cci[$i]=$cci;
        
        if( $i++ > 400 )
        {
            $query = $db->getQuery(true);
            $query  -> update($db->quoteName( '#__crc_check', 'ccu' ))
                    -> join('INNER', '(SELECT cc_max.crc_files_id AS crc_files_id, MAX(cc_max.crc_check_history_id) AS crc_check_history_id FROM #__crc_check AS cc_max WHERE cc_max.crc_files_id IN (' . implode(',',$tmp_cci) . ') GROUP BY cc_max.crc_files_id) AS cc ON cc.crc_files_id = ccu.crc_files_id AND cc.crc_check_history_id = ccu.crc_check_history_id' )
                    -> set($db->quoteName('ccu.veryfied') . " = " . FILE_CHECKED_STATUS_VALID );
            $db->setQuery($query);
            $db->execute();
            $i=0;
        }
    }
    $query = $db->getQuery(true);
    $query  -> update($db->quoteName( '#__crc_check', 'ccu' ))
            -> join('INNER', '(SELECT cc_max.crc_files_id AS crc_files_id, MAX(cc_max.crc_check_history_id) AS crc_check_history_id FROM #__crc_check AS cc_max WHERE cc_max.crc_files_id IN (' . implode(',',$tmp_cci) . ') GROUP BY cc_max.crc_files_id) AS cc ON cc.crc_files_id = ccu.crc_files_id AND cc.crc_check_history_id = ccu.crc_check_history_id' )
            -> set($db->quoteName('ccu.veryfied') . " = " . FILE_CHECKED_STATUS_VALID );
    $db->setQuery($query);
    $db->execute();
    $db->transactionCommit();
}
