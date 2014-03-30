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

class LittleSms extends Provider
{
	const SEND_URL = 'https://littlesms.ru/api/message/send';
	const BALANCE_URL = 'https://littlesms.ru/api/user/balance';

	public function __construct() {
		$this->PROVIDER_ID = 'LittleSms';
		$this->PROVIDER_NAME = 'LittleSms';
		$this->PROVIDER_DESCRIPTION = '';
		$this->PROVIDER_HOMEPAGE = 'http://littlesms.ru/';
		$this->_Settings = new Settings('obx.sms', 'PROVIDER_'.$this->PROVIDER_ID, array(
			'LOGIN' => array(
				'NAME' => GetMessage('OBX_SMS_SETT_LITTLESMS_LOGIN'),
				'TYPE' => 'STRING',
				'DESCRIPTION' => GetMessage('OBX_SMS_SETT_LITTLESMS_LOGIN_DESCR'),
				'SORT' => 100,
			),
			'API_KEY' => array(
				'NAME' => GetMessage('OBX_SMS_SETT_LITTLESMS_API_KEY'),
				'TYPE' => 'STRING',
				'VALUE' => '',
				'SORT' => 110,
			),
			'SENDER' => array(
				'NAME' => GetMessage('OBX_SMS_SETT_LITTLESMS_SENDER'),
				'TYPE' => 'STRING',
				'DESCRIPTION' => GetMessage('OBX_SMS_SETT_LITTLESMS_SENDER_DESCR'),
				'SORT' => 120,
			),
			'TEST_MODE' => array(
				'NAME' => GetMessage('OBX_SMS_SETT_LITTLESMS_TEST_MODE'),
				'DESCRIPTION' => GetMessage('OBX_SMS_SETT_LITTLESMS_TEST_MODE_DESCR'),
				'TYPE' => 'CHECKBOX',
				'VALUE' => 'N',
				'SORT' => 130
			)
		));
	}

	protected function _send(&$telNo, &$text, &$countryCode){
		/** @global \CMain $APPLICATION */
		global $APPLICATION;
		$request = new Request(self::SEND_URL);
		$sms = array(
			'user' => $this->_Settings->getOption('LOGIN'),
			'apikey' => $this->_Settings->getOption('API_KEY'),
			'sender' => $this->_Settings->getOption('SENDER'),
			'recipients' => $countryCode.$telNo,
			'encoding' => 'utf-8',
			'message' => $text,
			'type' => '0',
			'test' => (($this->_Settings->getOption('TEST_MODE')=='Y')?'1':'0')
		);
		if (!defined('BX_UTF') || BX_UTF !== true) {
			$sms = $APPLICATION->ConvertCharsetArray($sms, LANG_CHARSET, 'UTF-8');
		}
		$request->setPost($sms);
		$result = $request->send();
		$result = json_decode($result, true);
		if (!defined('BX_UTF') || BX_UTF !== true) {
			$result = $APPLICATION->ConvertCharsetArray($result, 'UTF-8', LANG_CHARSET);
		}
		if(empty($result)) {
			$this->addError(GetMessage('OBX_SMS_LITTLESMS_SEND_ERROR_1'));
			return false;
		}
		if($result['status'] != 'success') {
			$this->addError(GetMessage('OBX_SMS_LITTLESMS_SEND_ERROR_2', array('#ERROR#' => $result['message'])));
			return false;
		}
		return true;
	}

	public function getBalance(&$arBalanceData) {
		/** @global \CMain $APPLICATION */
		global $APPLICATION;
		$request = new Request(self::BALANCE_URL);
		$request->setPost(array(
			'user' => $this->_Settings->getOption('LOGIN'),
			'apikey' => $this->_Settings->getOption('API_KEY'),
		));
		$result = $request->send();
		$result = json_decode($result, true);
		if (!defined('BX_UTF') || BX_UTF !== true) {
			$result = $APPLICATION->ConvertCharsetArray($result, 'UTF-8', LANG_CHARSET);
		}
		if(empty($result)) {
			$arBalanceData['error'] = GetMessage('OBX_SMS_LITTLESMS_SEND_ERROR_1');
			$this->addError($arBalanceData['error']);
			return false;
		}
		if($result['status'] != 'success') {
			$arBalanceData['error'] = GetMessage('OBX_SMS_LITTLESMS_SEND_ERROR_2', array('#ERROR#' => $result['message']));
			$this->addError($arBalanceData['error']);
			return false;
		}
		return $result['balance'];
	}
}
