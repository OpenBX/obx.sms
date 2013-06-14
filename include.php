<?php
/*****************************************
 ** @vendor A68 Studio                  **
 ** @mailto info@a-68.ru                **
 ** @time 17:46                         **
 ** @user tashiro                       **
 *****************************************/

use OBX\Sms\SmsSender;

if (!CModule::IncludeModule("iblock")) {
	return false;
}

global $DB, $APPLICATION, $MESS, $DBType;

IncludeModuleLangFile(__FILE__);

if (!IsModuleInstalled("obx.core")) {
	$obx_core_path = $_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/obx.core/install/index.php";
	if (!file_exists($obx_core_path)) {
		//$APPLICATION->ThrowException(GetMessage("OBX_SMS_OBX_CORE_NOT_INSTALLED"));
		ShowError(GetMessage("OBX_SMS_OBX_CORE_NOT_INSTALLED"));
		return false;
	}
	require_once($obx_core_path);
	$obx_core = new obx_core();
	$obx_core->DoInstall();
}
if (!CModule::IncludeModule("obx.core")) {
	$APPLICATION->ThrowException(GetMessage("OBX_SMS_OBX_CORE_NOT_INSTALLED"));
	return false;
}


CModule::AddAutoloadClasses(
	"obx.sms",
	array(
	'OBX\Sms\SmsSender' => 'classes/SmsSender.php'
	,'OBX\Sms\SmsSettings' => 'classes/SmsSettings.php'
	)
);
/*
 * Регистрация всех провайдеров в папке ./providers/
 */
SmsSender::includeProviders();