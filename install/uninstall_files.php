<?php
$bConnectEpilog = false;
if(!defined("BX_ROOT")) {
	$bConnectEpilog = true;
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	global $USER;
	if( !$USER->IsAdmin() ) return false;
}

DeleteDirFilesEx("/bitrix/php_interface/obx.sms/ByteHand.php");
DeleteDirFilesEx("/bitrix/php_interface/obx.sms/EMailProvider.php");
DeleteDirFilesEx("/bitrix/php_interface/obx.sms/IqSms.php");
DeleteDirFilesEx("/bitrix/php_interface/obx.sms/KompeitoSms.php");
DeleteDirFilesEx("/bitrix/php_interface/obx.sms/LetsAds.php");
DeleteDirFilesEx("/bitrix/php_interface/obx.sms/LittleSms.php");
DeleteDirFilesEx("/bitrix/php_interface/obx.sms/SmsKontakt.php");
DeleteDirFilesEx("/bitrix/php_interface/obx.sms/TurboSmsUA.php");
DeleteDirFilesEx("/bitrix/tools/obx.sms");
if($bConnectEpilog) require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>