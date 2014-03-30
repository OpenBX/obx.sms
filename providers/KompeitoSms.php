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

class KompeitoSms extends Provider {

	const STATE_QUEUED = 0;
	const STATE_INVALID_DST = 2;
	const STATE_INVALID_SRC = 3;
	const STATE_NO_MONEY = 4;
	const STATE_DIR_BLOCKED = 5;
	const STATE_LIMIT = 7;
	const STATE_MESSAGE_EMPTY = 50;
	const STATE_SERVICE_UNAVAILABLE = 100;

	const DELIVERY_WAITING = 1;
	const DELIVERY_SENT = 2;
	const DELIVERY_DELIVERED = 4;
	const DELIVERY_REJECTED = 5;
	const DELIVERY_FAILED = 6;

	const SOAP_URL = 'https://cabinet.kompeito.ru/api/soap?wsdl';
	const BALANCE_URL = 'https://cabinet.kompeito.ru/api/plain/balance';

	protected $authorized = false;
	protected $soapConn = null;
	protected $arSendStatusCodes = null;


	public function __construct() {
		$this->PROVIDER_ID = 'KOMPEITOSMS';
		$this->PROVIDER_NAME = GetMessage('OBX_SMS_PROVIDER_KOMPEITOSMS_NAME');
		$this->PROVIDER_DESCRIPTION = GetMessage('OBX_SMS_PROVIDER_KOMPEITOSMS_DECRIPTION');
		$this->PROVIDER_HOMEPAGE = 'http://kompeito.ru/';
		$this->_Settings = new Settings(
			'obx.sms',
			'PROVIDER_'.$this->PROVIDER_ID(),
			array(
				'LOGIN' => array(
					'NAME' => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_SETT_LOGIN'),
					'TYPE' => 'STRING',
					'VALUE' => '',
					'INPUT_ATTR' => array(
						'placeholder' => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_SETT_LOGIN_PH')
					),
					'SORT' => 110,
				),
				'PASS' => array(
					'NAME' => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_SETT_PASS'),
					'TYPE' => 'PASSWORD',
					'VALUE' => '',
					'INPUT_ATTR' => array(
						'placeholder' => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_SETT_PASS_PH')
					),
					'SORT' => 120,
				),
				'FROM' => array(
					'NAME' => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_SETT_FROM'),
					'DESCRIPTION' => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_SETT_FROM_DSCR'),
					'TYPE' => 'STRING',
					'VALUE' => '',
					'INPUT_ATTR' => array(
						'placeholder' => GetMessage('OBX_SMS_PROV_LETSADS_SETT_FROM_PH')
					),
					'SORT' => 130
				)
			)
		);
		$this->soapConn = new \SoapClient(self::SOAP_URL, array(
			//'trace' => 1
		));

		$this->arSendStatusCodes = array(
			0 => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_SEND_STATUS_0'),
			2 => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_SEND_STATUS_2'),
			3 => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_SEND_STATUS_3'),
			4 => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_SEND_STATUS_4'),
			5 => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_SEND_STATUS_5'),
			7 => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_SEND_STATUS_7'),
			50 => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_SEND_STATUS_50'),
			100 => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_SEND_STATUS_100'),
		);
		$this->arDeliveryStatusCodes = array(
			1 => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_DELIVERY_STATUS_1'),
			2 => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_DELIVERY_STATUS_2'),
			4 => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_DELIVERY_STATUS_4'),
			5 => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_DELIVERY_STATUS_5'),
			6 => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_DELIVERY_STATUS_6'),
			7 => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_DELIVERY_STATUS_7'),
		);
	}

	protected function _send(&$phoneNumber, &$text, &$countryCode) {
		/** @global \CMain $APPLICATION */
		global $APPLICATION;
		$sms = array(
			'login' => $this->_Settings->getOption('LOGIN'),
			'password' => $this->_Settings->getOption('PASS'),
			'from' => $this->_Settings->getOption('FROM'),
			'to' => $countryCode.$phoneNumber,
			'message' => $text
		);
		if (!defined('BX_UTF') || BX_UTF !== true) {
			$sms['message'] = $APPLICATION->ConvertCharset($sms['message'], LANG_CHARSET, 'UTF-8');
		}
		try {
			$response = $this->soapConn->sendSms($sms['login'], $sms['password'], $sms['from'], $sms['to'], $sms['message']);
		}
		catch(\SoapFault $SoapFault) {
			// Если это произошло, значит неверный логин или пароль
			$this->addError(GetMessage('OBX_SMS_PROV_KOMPEITOSMS_AUTH_ERROR'));
			return false;
		}

		if (!defined('BX_UTF') || BX_UTF !== true) {
			$response = $APPLICATION->ConvertCharsetArray($response, 'UTF-8', LANG_CHARSET);
		}
		if($response->status != 0) {
			$this->addError(GetMessage('OBX_SMS_PROV_KOMPEITOSMS_SEND_ERROR', array(
				'#CODE#' => $response->status,
				'#ERROR#' => $this->arSendStatusCodes[$response->status]
			)));
			return false;
		}
		return $response->id;
	}

	public function getBalance(&$arBalanceData) {
		$request = new Request(self::BALANCE_URL.'?'.http_build_query(array(
			'login' => $this->_Settings->getOption('LOGIN'),
			'pass' => $this->_Settings->getOption('PASS')
		)));
		$result = $request->send();
		list($credits, $money) = explode("\n", $result);
		if($request->getStatus() != 200) {
			$this->addError(GetMessage('OBX_SMS_PROV_KOMPEITOSMS_AUTH_ERROR'));
			$arBalanceData['error'] = GetMessage('OBX_SMS_PROV_KOMPEITOSMS_AUTH_ERROR');
			return false;
		}
		$credits = str_replace('bc:', '', $credits);
		return $credits;
	}
}
