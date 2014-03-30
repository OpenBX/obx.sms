<?php
/*******************************************
 ** @product OBX:Sms Bitrix Module        **
 ** @authors                              **
 **         Maksim S. Makarov aka pr0n1x  **
 ** @license Affero GPLv3                 **
 ** @mailto rootfavell@gmail.com          **
 ** @copyright 2013 DevTop                **
 *******************************************/

namespace OBX\Sms\Provider;
use OBX\Core\Settings\Settings;
use OBX\Core\Curl\Request;

IncludeModuleLangFile(__FILE__);

class SmsBliss extends Provider {

	const SEND_URL = 'http://gate.smsbliss.ru/send/';
	const BALANCE_URL = 'http://gate.smsbliss.ru/credits/';

	protected function __construct() {
		$this->PROVIDER_ID = 'smsbliss';
		$this->PROVIDER_NAME = GetMessage('OBX_SMS_SMSBLISS_PROV_NAME');
		$this->PROVIDER_DESCRIPTION = GetMessage('OBX_SMS_SMSBLISS_PROV_DSCR');
		$this->PROVIDER_HOMEPAGE = 'https://smsbliss.ru/';
		$this->_Settings = new Settings(
			'obx.sms',
			self::SETTINGS_PREFIX.$this->PROVIDER_ID(),
			array(
				'LOGIN' => array(
					'NAME' => GetMessage('OBX_SMS_PROV_SMSBLISS_SETT_LOGIN'),
					'TYPE' => 'STRING',
					'VALUE' => '',
					'SORT' => 100
				),
				'PASS' => array(
					'NAME' => GetMessage('OBX_SMS_PROV_SMSBLISS_SETT_PASS'),
					'TYPE' => 'PASSWORD',
					'VALUE' => '',
					'SORT' => 110
				),
				'FROM' => array(
					'NAME' => GetMessage('OBX_SMS_PROV_SMSBLISS_SETT_FROM'),
					'DESCRIPTION' => GetMessage('OBX_SMS_PROV_SMSBLISS_SETT_FROM_DESCR'),
					'TYPE' => 'STRING',
					'VALUE' => '',
					'SORT' => 120
				)
			)
		);
	}

	protected function _send(&$telNo, &$text, &$countryCode) {
		/** @global \CMain $APPLICATION */
		global $APPLICATION;
		$sms = array(
			'login' => ''.$this->_Settings->getOption('LOGIN'),
			'password' => ''.$this->_Settings->getOption('PASS'),
			'statusQueueName' => 'defaultQueue',
			'sender' => ''.$this->_Settings->getOption('FROM'),
			'phone' => $countryCode.$telNo,
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
			$this->addError(GetMessage('OBX_SMS_SMSBLISS_SEND_ERROR_1'));
			return false;
		}
		if($status != 'accepted') {
			$this->addError(GetMessage('OBX_SMS_SMSBLISS_SEND_ERROR_2', array(
				'#ERROR#' => $result
			)));
			return false;
		}
		return $messID;
	}

	public function getBalance(&$arBalanceData) {
		/** @global \CMain $APPLICATION */
		global $APPLICATION;
		$request = new Request(self::BALANCE_URL.'?'.http_build_query(array(
				'login' => ''.$this->_Settings->getOption('LOGIN'),
				'password' => ''.$this->_Settings->getOption('PASS')
			)));
		$result = $request->send();
		if(empty($result)) {
			$this->addError(GetMessage('OBX_SMS_SMSBLISS_SEND_ERROR_1'));
			$arBalanceData['error'] = GetMessage('OBX_SMS_SMSBLISS_SEND_ERROR_1');
			return false;
		}
		list($error, $balance) = explode('=', $result);
		if(null === $balance) {
			$error = GetMessage('OBX_SMS_SMSBLISS_SEND_ERROR_2', array('#ERROR#' => $error));
			$this->addError($error);
			$arBalanceData['error'] = $error;
			return false;
		}
		return $balance;
	}
} 