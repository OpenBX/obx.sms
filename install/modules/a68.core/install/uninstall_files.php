<?php
$bConnectEpilog = false;
if(!defined("BX_ROOT")) {
	$bConnectEpilog = true;
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	global $USER;
	if( !$USER->IsAdmin() ) return false;
}

DeleteDirFilesEx("/bitrix/php_interface/event.d/a68.core.debug.php");
DeleteDirFilesEx("/bitrix/php_interface/event.d/a68.core.parse_ini_string.php");
DeleteDirFilesEx("/bitrix/js/a68.core");
DeleteDirFilesEx("/bitrix/components/a68/layout");
if($bConnectEpilog) require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>