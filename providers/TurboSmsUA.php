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

IncludeModuleLangFile(__FILE__);

class TurboSmsUA extends Provider
{
	const SOAP_URL = 'http://turbosms.in.ua/api/wsdl.html';

	protected $soapConn = null;
	protected $bAuthorized = false;
	protected $responseTextCheck = null;

	protected function __construct() {
		$this->PROVIDER_ID = 'TurboSmsUA';
		$this->PROVIDER_NAME = 'TurboSMS.ua';
		$this->PROVIDER_DESCRIPTION = GetMessage('OBX_SMS_TURBOSMSUA_PROV_DESCRIPTION');
		$this->PROVIDER_HOMEPAGE = 'https://turbosms.ua/';
		$this->_Settings = new Settings('obx.sms', 'PROVIDER_'.$this->PROVIDER_ID, array(
			'GATE_LOGIN' => array(
				'NAME' => GetMessage('OBX_SMS_TURBOSMSUA_GATE_LOGIN'),
				'TYPE' => 'STRING',
				'DESCRIPTION' => GetMessage('OBX_SMS_TURBOSMSUA_GATE_LOGIN_DESCR'),
				'VALUE' => '',
				'SORT' => 100,
			),
			'GATE_PASS' => array(
				'NAME' => GetMessage('OBX_SMS_TURBOSMSUA_GATE_PASS'),
				'TYPE' => 'PASSWORD',
				'VALUE' => '',
				'SORT' => 110
			),
			'SENDER' => array(
				'NAME' => GetMessage('OBX_SMS_TURBOSMSUA_SENDER'),
				'TYPE' => 'STRING',
				'DESCRIPTION' => GetMessage('OBX_SMS_TURBOSMSUA_SENDER_DESCR'),
				'SORT' => 120
			),
			'DEF_COUNTRY_CODE' => array(
				'NAME' => GetMessage('OBX_SMS_TURBOSMSUA_DEF_CTRY_CODE'),
				'TYPE' => 'STRING',
				'VALUE' => 38,
				'SORT' => 130,
			)
		));
	}

	protected function _initSoapConnection($throwException = false) {
		if(null === $this->soapConn) {
			try {
				$this->soapConn = new \SoapClient(self::SOAP_URL, array(
					//'trace' => 1,
				));
			}
			catch(\SoapFault $e) {
				if(true === $throwException) {
					throw $e;
				}
				$this->addErrorException($e);
			}
			return true;
		}
		return false;
	}

	protected function checkResponse($checkCode, $response) {
		if(null === $this->responseTextCheck) {
			$this->responseTextCheck = array(
				'auth_success' => GetMessage('OBX_SMS_TURBOSMSUA_RESP_AUTH_SUCCESS'),
				'auth_error' => GetMessage('OBX_SMS_TURBOSMSUA_RESP_AUTH_ERR'),
				'send_success' => GetMessage('OBX_SMS_TURBOSMSUA_RESP_SEND_SUCCESS'),
				'send_err_1' => GetMessage('OBX_SMS_TURBOSMSUA_RESP_SEND_E_1')
			);
			if(!defined('BX_UTF') || BX_UTF !== true) {
				global $APPLICATION;
				$this->responseTextCheck = $APPLICATION->ConvertCharsetArray(
					$this->responseTextCheck, LANG_CHARSET, 'UTF-8'
				);
			}
		}

		if(!array_key_exists($checkCode, $this->responseTextCheck)) {
			return false;
		}
		if($this->responseTextCheck[$checkCode] == $response) {
			return true;
		}
		return false;
	}

	protected function _turbosms_authorize() {
		if(true === $this->bAuthorized) return true;
		$auth = array(
			'login' => $this->_Settings->getOption('GATE_LOGIN'),
			'password' => $this->_Settings->getOption('GATE_PASS')
		);
		if (empty($auth['login']) || empty($auth['password'])) return false;
		try {
			$this->_initSoapConnection(true);
			$authResultText = $this->soapConn->Auth($auth)->AuthResult;
		}
		catch(\SoapFault $SoapFault) {
			$this->addError(GetMessage('OBX_SMS_TURBOSMSUA_RESP_SEND_ERROR', array(
				'#ERROR#' => $SoapFault->getMessage()
			)));
		}
		if($this->checkResponse('auth_success', $authResultText)) {
			$this->bAuthorized = true;
			return true;
		}
		return false;
	}

	protected function _send(&$telNo, &$text, &$countryCode){
		if(!$this->_turbosms_authorize()) return false;
		/** @global \CMain $APPLICATION */
		global $APPLICATION;
		if(empty($countryCode)) {
			$countryCode = $this->_Settings->getOption('DEF_COUNTRY_CODE');
		}
		$sms = array(
			'sender' => $this->_Settings->getOption('SENDER'),
			'destination' => $countryCode.$telNo,
			'text' => $text
		);
		if(empty($sms['sender'])) {
			$this->addError(GetMessage('OBX_SMS_TURBOSMSUA_E_1'));
			return false;
		}

		if (!defined('BX_UTF') || BX_UTF !== true) {
			$sms = $APPLICATION->ConvertCharsetArray($sms, LANG_CHARSET, 'UTF-8');
		}
		try {
			$this->_initSoapConnection(true);
			$smsResponse = $this->soapConn->SendSMS($sms)->SendSMSResult->ResultArray;
		}
		catch(\SoapFault $SoapFault) {
			$this->addError(GetMessage('OBX_SMS_TURBOSMSUA_RESP_SEND_ERROR', array(
				'#ERROR#' => $SoapFault->getMessage()
			)));
		}
		if(is_array($smsResponse) && $this->checkResponse('send_success', $smsResponse[0])) {
			return $smsResponse[1];
		}
		$this->addError(GetMessage('OBX_SMS_TURBOSMSUA_RESP_SEND_ERROR', array(
			'#ERROR#' => $smsResponse
		)));
		return false;
	}

	public function getBalance(&$arBalanceData) {
		if( !$this->_turbosms_authorize() ) {
			$arBalanceData['error'] = $this->getLastError();
			return false;
		}
		try {
			$response = $this->soapConn->GetCreditBalance();
		}
		catch(\SoapFault $SoapFault) {
			$this->addError(GetMessage('OBX_SMS_TURBOSMSUA_RESP_SEND_ERROR', array(
				'#ERROR#' => $SoapFault->getMessage()
			)));
		}
		$arBalanceData['CreditBalance'] = $response->GetCreditBalanceResult;
		return $response->GetCreditBalanceResult;
	}
}
