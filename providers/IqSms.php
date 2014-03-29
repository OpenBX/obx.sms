<?php
/*******************************************
 ** @product OBX:Market Bitrix Module     **
 ** @authors                              **
 **         Maksim S. Makarov aka pr0n1x  **
 **         Morozov P. Artem aka tashiro  **
 ** @license Affero GPLv3                 **
 ** @mailto rootfavell@gmail.com          **
 ** @mailto tashiro@yandex.ru             **
 ** @copyright 2013 DevTop                **
 *******************************************/

namespace OBX\Sms\Provider;

use OBX\Core\Settings\Settings;
use OBX\Core\Curl\Request;

IncludeModuleLangFile(__FILE__);

class IqSms extends Provider {

	const ERROR_EMPTY_API_LOGIN = 'Empty api login not allowed';
	const ERROR_EMPTY_API_PASSWORD = 'Empty api password not allowed';
	const ERROR_EMPTY_RESPONSE = 'errorEmptyResponse';

	const SEND_URL = 'http://gate.iqsms.ru/send/';


	public function __construct() {
		$this->PROVIDER_ID = 'IQSMS';
		$this->PROVIDER_NAME = GetMessage('OBX_SMS_PROVIDER_IQSMS_NAME');
		$this->PROVIDER_DESCRIPTION = GetMessage('OBX_SMS_PROVIDER_IQSMS_DESCRIPTION');
		$this->PROVIDER_HOMEPAGE = 'http://iqsms.ru/';
		$this->_Settings = new Settings(
			'obx.sms',
			'PROVIDER_'.$this->PROVIDER_ID(),
			array(
				'LOGIN' => array(
					'NAME' => GetMessage('OBX_SMS_PROV_IQSMS_SETT_LOGIN'),
					'TYPE' => 'STRING',
					'VALUE' => '',
					'SORT' => 100
				),
				'PASS' => array(
					'NAME' => GetMessage('OBX_SMS_PROV_IQSMS_SETT_PASS'),
					'TYPE' => 'PASSWORD',
					'VALUE' => '',
					'SORT' => 110
				),
				'FROM' => array(
					'NAME' => GetMessage('OBX_SMS_PROV_IQSMS_SETT_FROM'),
					'DESCRIPTION' => GetMessage('OBX_SMS_PROV_IQSMS_SETT_FROM_DESCR'),
					'TYPE' => 'STRING',
					'VALUE' => '',
					'SORT' => 120
				)
			)
		);
	}



	protected function _send(&$telNo, &$text, &$arFields, &$countryCode) {
		/** @global \CMain $APPLICATION */
		global $APPLICATION;
		$sms = array(
			'login' => ''.$this->_Settings->getOption('LOGIN'),
			'password' => ''.$this->_Settings->getOption('PASS'),
			'statusQueueName' => 'defaultQueue',
			'sender' => ''.$this->_Settings->getOption('FROM'),
			'phone' => ''.$countryCode.$telNo,
			'text' => ''.$text,
		);
		if (!defined('BX_UTF') || BX_UTF !== true) {
			$sms = $APPLICATION->ConvertCharsetArray($sms, LANG_CHARSET, 'UTF-8');
		}
		$sms = http_build_query($sms);
		$request = new Request(self::SEND_URL.'?'.$sms);
		$result = $request->send();
		list($messID, $status) = explode('=', $result);
		if(empty($result)) {
			$this->addError(GetMessage('OBX_SMS_IQSMS_SEND_ERROR_1'));
			return false;
		}
		if($status != 'accepted') {
			$this->addError(GetMessage('OBX_SMS_IQSMS_SEND_ERROR_2', array(
				'#ERROR#' => $status
			)));
			return false;
		}
		return $messID;
	}

	public function getBalance() {
		return 0;
	}

	public function getMessageStatus($messageID) {
		return 1;
	}


}
