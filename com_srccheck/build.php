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

function addDirToZip( $dir, $zip ){
    $files = scandir($dir);
    
    foreach($files as $key => $value)
    {
        $path = $dir.DIRECTORY_SEPARATOR.$value;
        if( !is_dir($path) ){
            $zip->addFile($path);
	}elseif ($value != '.' && $value != '..') {
            addDirToZip( $path, $zip );
	}
    }
};

function putFileToFtp($conn_id, $file){
// upload a file
    if (ftp_put($conn_id, $file, $file, FTP_BINARY)) {
        echo "successfully uploaded $file\n";
    } else {
        echo "There was a problem while uploading $file\n";
    }
};

function putUpdateToSerwer(){
$ftp_server = "f2y.org";
$ftp_user_name = "joomla@f2y.org";
$ftp_user_pass = "{pL,<lO9*";

// set up basic connection
echo "Update file to serwer: >>".$ftp_server."<< user:>>".$ftp_user_name."<< pass:>>".$ftp_user_pass."<<\n";

$conn_id = ftp_connect($ftp_server); // or die("Couldn't connect to $ftp_server"); 

echo ">>".$conn_id ."<<";

// login with username and password
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

putFileToFtp($conn_id, "./srccheck-updates/info.html");
putFileToFtp($conn_id, "./srccheck-updates/updates.xml");
putFileToFtp($conn_id, "./srccheck-updates/com_srccheck.zip");

// close the connection
ftp_close($conn_id);
};

echo "Start\n";

$zip = new ZipArchive();
$filename = "com_srccheck.zip";

if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE)!==TRUE) {
    exit("cannot open <$filename>\n");
}

addDirToZip( "admin", $zip );

$zip->addFile("srccheck.xml");
$zip->addFile("script.php");
$zip->addFile("index.html");

$zip->close();


if (!copy($filename, "./srccheck-updates/".$filename)) {
    echo "failed to copy $filename...\n";
}

//putUpdateToSerwer();

echo "Stop\n";