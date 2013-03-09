<?php
$bConnectEpilog = false;
if(!defined("BX_ROOT")) {
	$bConnectEpilog = true;
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	global $USER;
	if( !$USER->IsAdmin() ) return false;
}

if(!function_exists("A68_CopyDirFilesEx")) {
	function A68_CopyDirFilesEx($path_from, $path_to, $ReWrite = True, $Recursive = False, $bDeleteAfterCopy = False, $strExclude = "") {
		$path_from = str_replace(array("\\", "//"), "/", $path_from);
		$path_to = str_replace(array("\\", "//"), "/", $path_to);
		if(is_file($path_from) && !is_file($path_to)) {
			if( CheckDirPath($path_to) ) {
				$file_name = substr($path_from, strrpos($path_from, "/")+1);
				$path_to .= $file_name;
				return CopyDirFiles($path_from, $path_to, $ReWrite, $Recursive, $bDeleteAfterCopy, $strExclude);
			}
		}
		if( is_dir($path_from) && substr($path_to, strlen($path_to)-1) == "/" ) {
			$folderName = substr($path_from, strrpos($path_from, "/")+1);
			$path_to .= $folderName;
		}
		return CopyDirFiles($path_from, $path_to, $ReWrite, $Recursive, $bDeleteAfterCopy, $strExclude);
	}
}
DeleteDirFilesEx("/bitrix/modules/a68.core/install/php_interface/event.d");
DeleteDirFilesEx("/bitrix/modules/a68.core/install/php_interface");
DeleteDirFilesEx("/bitrix/modules/a68.core/install/js");
DeleteDirFilesEx("/bitrix/modules/a68.core/install/components/a68");
A68_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/event.d/a68.core.debug.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/a68.core/install/php_interface/event.d/", true, true);
A68_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/event.d/a68.core.parse_ini_string.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/a68.core/install/php_interface/event.d/", true, true);
A68_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/run_event.d.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/a68.core/install/php_interface/", true, true);
A68_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/a68.core", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/a68.core/install/js/", true, true);
A68_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/a68/layout", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/a68.core/install/components/a68/", true, true);
if($bConnectEpilog) require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>