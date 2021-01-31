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

function copyDir($source, $dest){
    if(is_dir($source)) {
        $dir_handle=opendir($source);
        while($file=readdir($dir_handle)){
            if($file!="." && $file!=".."){
                if(is_dir($source."/".$file)){
                    if(!is_dir($dest."/".$file)){
                        mkdir($dest."/".$file,0777,true);
                    }
                    copyDir($source."/".$file, $dest."/".$file);
                } else {
                    copy($source."/".$file, $dest."/".$file);
                }
            }
        }
        closedir($dir_handle);
    } else {
        copy($source, $dest);
    }
}

function addToUpdate( $filename )
{
    if (!copy($filename, "./srccheck-updates/".$filename)) {
       echo "failed to copy $filename...\n";
    }
}

echo "Start\n";

//prepare plugin
/**************
$filename  = "plg_system_srccheckverify";

unlink($filename.".zip");
echo "7z.exe a -tzip ".$filename.".zip ". $filename."\n";
exec("7z.exe a -tzip ".$filename.".zip ". $filename);
addToUpdate( $filename.".zip" );
*************/

//Prepare componnet
$filename = "com_srccheck";

unlink($filename.".zip");
echo "7z.exe a -tzip ".$filename.".zip ".$filename."\n";
exec("7z.exe a -tzip ".$filename.".zip admin");
exec("7z.exe a -tzip ".$filename.".zip srccheck.xml");
exec("7z.exe a -tzip ".$filename.".zip script.php");
exec("7z.exe a -tzip ".$filename.".zip index.html");
addToUpdate( $filename.".zip" );


//Prepare package
/*********
$filename = "pkg_srccheck";

unlink($filename.".zip");
echo "7z.exe a -tzip ".$filename.".zip ".$filename."\n";
exec("7z.exe a -tzip ".$filename.".zip pkg_srccheck.xml");
exec("7z.exe a -tzip ".$filename.".zip com_srccheck.zip");
exec("7z.exe a -tzip ".$filename.".zip plg_system_srccheckverify.zip");
addToUpdate( $filename.".zip" );
************/
echo "Stop\n";