<?php
$bConnectEpilog = false;
if(!defined("BX_ROOT")) {
	$bConnectEpilog = true;
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	global $USER;
	if( !$USER->IsAdmin() ) return false;
}

if(!function_exists("OBX_CopyDirFilesEx")) {
	function OBX_CopyDirFilesEx($path_from, $path_to, $ReWrite = True, $Recursive = False, $bDeleteAfterCopy = False, $strExclude = "") {
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
DeleteDirFilesEx("/bitrix/modules/obx.sms/install/php_interface/obx.sms/ByteHand.php");
DeleteDirFilesEx("/bitrix/modules/obx.sms/install/php_interface/obx.sms/EMailProvider.php");
DeleteDirFilesEx("/bitrix/modules/obx.sms/install/php_interface/obx.sms/IqSms.php");
DeleteDirFilesEx("/bitrix/modules/obx.sms/install/php_interface/obx.sms/KompeitoSms.php");
DeleteDirFilesEx("/bitrix/modules/obx.sms/install/php_interface/obx.sms/LetsAds.php");
DeleteDirFilesEx("/bitrix/modules/obx.sms/install/php_interface/obx.sms/LittleSms.php");
DeleteDirFilesEx("/bitrix/modules/obx.sms/install/php_interface/obx.sms/SmsBliss.php");
DeleteDirFilesEx("/bitrix/modules/obx.sms/install/php_interface/obx.sms/SmsKontakt.php");
DeleteDirFilesEx("/bitrix/modules/obx.sms/install/php_interface/obx.sms/TurboSmsUA.php");
DeleteDirFilesEx("/bitrix/modules/obx.sms/install/tools/obx.sms");
if( ! is_dir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.sms/install/php_interface/obx.sms") ) {
	@mkdir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.sms/install/php_interface/obx.sms", BX_DIR_PERMISSIONS, true);
}
OBX_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/obx.sms/ByteHand.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.sms/install/php_interface/obx.sms/", true, true);
OBX_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/obx.sms/EMailProvider.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.sms/install/php_interface/obx.sms/", true, true);
OBX_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/obx.sms/IqSms.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.sms/install/php_interface/obx.sms/", true, true);
OBX_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/obx.sms/KompeitoSms.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.sms/install/php_interface/obx.sms/", true, true);
OBX_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/obx.sms/LetsAds.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.sms/install/php_interface/obx.sms/", true, true);
OBX_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/obx.sms/LittleSms.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.sms/install/php_interface/obx.sms/", true, true);
OBX_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/obx.sms/SmsBliss.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.sms/install/php_interface/obx.sms/", true, true);
OBX_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/obx.sms/SmsKontakt.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.sms/install/php_interface/obx.sms/", true, true);
OBX_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/obx.sms/TurboSmsUA.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.sms/install/php_interface/obx.sms/", true, true);
if( ! is_dir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.sms/install/tools") ) {
	@mkdir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.sms/install/tools", BX_DIR_PERMISSIONS, true);
}
OBX_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/tools/obx.sms", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.sms/install/tools/", true, true);
if($bConnectEpilog) require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>