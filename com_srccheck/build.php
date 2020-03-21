<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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

echo "Start\n";

$zip = new ZipArchive();
$filename = "./com_srccheck.zip";

if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE)!==TRUE) {
    exit("cannot open <$filename>\n");
}

addDirToZip( "admin", $zip );

$zip->addFile("srccheck.xml");
$zip->addFile("script.php");
$zip->addFile("index.html");

$zip->close();
echo "Stop\n";