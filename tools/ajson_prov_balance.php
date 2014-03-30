<?php
use OBX\Sms\Provider\Provider;
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
IncludeModuleLangFile(__FILE__);
/** @global \CMain $APPLICATION */
$APPLICATION->RestartBuffer();

//Заголовки для предотвращения кеширования и указания типа данных JSON
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json; charset: UTF-8');

function obx_sms_get_provider_balance_json() {
	$arJSON = array(
		'balance' => 0,
		'status' => 0,
		'data' => null
	);

	if(!array_key_exists('provider', $_REQUEST)) {
		$arJSON['status'] = 1;
		$arJSON['data'] = GetMessage('OBX_SMS_AJSON_BALANCE_STATUS_1');
		return $arJSON;
	}

	$providerID = $_REQUEST['provider'];

	if(!CModule::IncludeModule('obx.sms')) {
		$arJSON['status'] = 2;
		$arJSON['data'] = GetMessage('OBX_SMS_AJSON_BALANCE_STATUS_2');
		return $arJSON;
	}

	$Provider = Provider::factory($providerID);
	if(null === $Provider) {
		$arJSON['status'] = 3;
		$arJSON['data'] = GetMessage('OBX_SMS_AJSON_BALANCE_STATUS_3');
		return $arJSON;
	}

	$arBalanceData = null;
	$balance = $Provider->getBalance($arBalanceData);
	if(false === $balance) {
		if(null === $arBalanceData) {
			$arJSON['status'] = 4;
			$arJSON['data'] = GetMessage('OBX_SMS_AJSON_BALANCE_STATUS_4');
		}
		else {
			$arJSON['status'] = 5;
			$arJSON['data'] = GetMessage('OBX_SMS_AJSON_BALANCE_STATUS_5', array(
				'#ERROR#' => $Provider->getLastError()
			));
		}
		return $arJSON;
	}

	$arJSON['balance'] = $balance;
	$arJSON['data'] = $arBalanceData;
	return $arJSON;
}


echo json_encode(obx_sms_get_provider_balance_json());

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
