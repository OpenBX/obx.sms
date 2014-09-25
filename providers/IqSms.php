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
use OBX\Core\Exceptions\Curl\RequestError;

IncludeModuleLangFile(__FILE__);

class IqSms extends Provider {

	const ERROR_EMPTY_API_LOGIN = 'Empty api login not allowed';
	const ERROR_EMPTY_API_PASSWORD = 'Empty api password not allowed';
	const ERROR_EMPTY_RESPONSE = 'errorEmptyResponse';

	const SEND_URL = 'http://gate.iqsms.ru/send/';
	const BALANCE_URL = 'http://gate.iqsms.ru/credits/';


	public function __construct() {
		$this->PROVIDER_ID = 'IQSMS';
		$this->PROVIDER_NAME = GetMessage('OBX_SMS_PROVIDER_IQSMS_NAME');
		$this->PROVIDER_DESCRIPTION = GetMessage('OBX_SMS_PROVIDER_IQSMS_DESCRIPTION');
		$this->PROVIDER_HOMEPAGE = 'http://iqsms.ru/';
		$this->_Settings = new Settings(
			'obx.sms',
			self::SETTINGS_PREFIX.$this->PROVIDER_ID(),
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



	protected function _send(&$telNo, &$text, &$countryCode) {
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
		try {
			$request = new Request(self::SEND_URL.'?'.$sms);
			$result = $request->send();
			if(empty($result)) {
				$this->addError(GetMessage('OBX_SMS_IQSMS_SEND_ERROR_1'));
				return false;
			}
			list($messID, $status) = explode('=', $result);
			if($status != 'accepted') {
				$this->addError(GetMessage('OBX_SMS_IQSMS_SEND_ERROR_2', array(
					'#ERROR#' => $status
				)));
				return false;
			}
		}
		catch(RequestError $e) {
			$this->addErrorException($e);
			return false;
		}

		return $messID;
	}

	public function getBalance(&$arBalanceData) {
		/** @global \CMain $APPLICATION */
		global $APPLICATION;

		$arPost = array(
			'login' => $this->_Settings->getOption('LOGIN'),
			'password' => $this->_Settings->getOption('PASS')
		);

		$request = new Request(self::BALANCE_URL);

		//$this->setPostJson($arPost, $request);
		$request->setPost(json_encode($arPost));

		$result = $request->send();

		if( $result ) {
			$arResult = json_decode($result);
			if( $arResult['status'] == 'ok' ) {
				//TODO: Вернуть нормальный массив
				return $arResult['balance'][0]['balance'] . ' ' . $arResult['balance'][0]['type'];
			}
			else {
				$arBalanceData['error'] = '';
				if( array_key_exists('description', $arResult) ) {
					$arBalanceData['error'] = GetMessage('OBX_SMS_IQSMS_SEND_ERROR_2', array('#ERROR#' => $arResult['description']));
				}
				$this->addError($arBalanceData['error']);
				return false;
			}
		}
		else {
			$arBalanceData['error'] = GetMessage('OBX_SMS_IQSMS_SEND_ERROR_1');
			$this->addError($arBalanceData['error']);
			return false;
		}
	}
}
