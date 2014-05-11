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
use OBX\Core\Exceptions\Curl as CurlEx;
IncludeModuleLangFile(__FILE__);

class ByteHand extends Provider
{
	const SEND_URL = 'http://bytehand.com:3800/send?id=<ID>&key=<KEY>&to=<PHONE>&from=<SIGNATURE>&text=<TEXT>';
	const BALANCE_URL = 'http://bytehand.com:3800/balance?id=<ID>&key=<KEY>';

	public function __construct() {
		$this->PROVIDER_ID = 'BYTEHAND';
		$this->PROVIDER_NAME = 'Byte Hand';
		$this->PROVIDER_DESCRIPTION = GetMessage('OBX_SMS_BYTEHAND_PROV_DESCRIPTION');
		$this->PROVIDER_HOMEPAGE = 'https://bytehand.com/';
		$this->_Settings = new Settings('obx.sms', 'PROVIDER_'.$this->PROVIDER_ID, array(
			'CLID' => array(
				'NAME' => GetMessage('OBX_SMS_BYTEHAND_SETT_CLID_NAME'),
				'TYPE' => 'STRING',
				'VALUE' => '',
				'DESCRIPTION' => '',
				'SORT' => 100
			),
			'API_KEY' => array(
				'NAME' => GetMessage('OBX_SMS_BYTEHAND_SETT_API_KEY_NAME'),
				'TYPE' => 'PASSWORD',
				'VALUE' => '',
				'SORT' => 110
			),
			'SENDER' => array(
				'NAME' => GetMessage('OBX_SMS_BYTEHAND_SETT_SENDER'),
				'TYPE' => 'STRING',
				'DESCRIPTION' => GetMessage('OBX_SMS_BYTEHAND_SETT_SENDER_DESCR'),
				'VALUE' => '',
				'SORT' => 120,
			)
		));
	}

	protected function _send(&$telNo, &$text, &$countryCode){
		/** @global \CMain $APPLICATION */
		global $APPLICATION;
		$sms = array(
			'ID' => $this->_Settings->getOption('CLID'),
			'KEY' => $this->_Settings->getOption('API_KEY'),
			'SIGNATURE' => $this->_Settings->getOption('SENDER'),
			'PHONE' => $countryCode.$telNo,
			'TEXT' => $text
		);
		if (!defined('BX_UTF') || BX_UTF !== true) {
			$sms = $APPLICATION->ConvertCharsetArray($sms, LANG_CHARSET, 'UTF-8');
		}
		$requestUrl = str_replace(
			array('<ID>', '<KEY>', '<SIGNATURE>', '<PHONE>', '<TEXT>'),
			array($sms['ID'], $sms['KEY'], $sms['SIGNATURE'], urlencode($sms['PHONE']), urlencode($sms['TEXT'])),
			self::SEND_URL);
		try {
			$Request = new Request($requestUrl);
			$response = $Request->send();
			$response = json_decode($response, true);
			if (!defined('BX_UTF') || BX_UTF !== true) {
				$response = $APPLICATION->ConvertCharsetArray($response, 'UTF-8', LANG_CHARSET);
			}
			if(empty($response)) {
				$this->addError(GetMessage('OBX_SMS_BYTEHAND_SEND_ERROR_1'));
				return false;
			}
			if( $response['status'] != 0 ) {
				$this->addError(GetMessage('OBX_SMS_BYTEHAND_SEND_ERROR_2', array(
					'#CODE#' => $response['status'],
					'#ERROR#' => $response['description']
				)), $response['status']);
				return false;
			}
		}
		catch (CurlEx\RequestError $e) {
			$this->addErrorException($e);
			return false;
		}

		return true;
	}

	public function getBalance(&$arBalanceData = null) {
		/** @global \CMain $APPLICATION */
		$requestUrl = str_replace(
			array('<ID>', '<KEY>'),
			array($this->_Settings->getOption('CLID'), $this->_Settings->getOption('API_KEY')),
			self::BALANCE_URL
		);
		$Request = new Request($requestUrl);
		$response = $Request->send();
		$response = json_decode($response, true);
		if (!defined('BX_UTF') || BX_UTF !== true) {
			$response = $APPLICATION->ConvertCharsetArray($response, 'UTF-8', LANG_CHARSET);
		}
		if(empty($response)) {
			$arBalanceData = array('error' => GetMessage('OBX_SMS_BYTEHAND_SEND_ERROR_1'));
			$this->addError(GetMessage('OBX_SMS_BYTEHAND_SEND_ERROR_1'));
			return false;
		}
		$arBalanceData = $response;
		if( $response['status'] != 0 ) {
			$this->addError(GetMessage('OBX_SMS_BYTEHAND_SEND_ERROR_2', array(
				'#CODE#' => $response['status'],
				'#ERROR#' => $response['description']
			)), $response['status']);
			return false;
		}
		return $response['description'];
	}

	public function getMessageStatus($messageID) {

	}
}
