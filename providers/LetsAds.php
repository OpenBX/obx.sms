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

IncludeModuleLangFile(__FILE__);

class LetsAds extends Provider {

	public function __construct() {
		$this->PROVIDER_ID = 'LETSADS';
		$this->PROVIDER_NAME = GetMessage('OBX_SMS_PROVIDER_LETSADS_NAME');
		$this->PROVIDER_DESCRIPTION = GetMessage('OBX_SMS_PROVIDER_LETSADS_DESCRIPTION');
		$this->PROVIDER_HOMEPAGE = 'http://letsads.com/';

		$this->_Settings = new Settings(
			'obx.sms',
			'PROVIDER_'.$this->PROVIDER_ID(),
			array(
				'LOGIN' => array(
					'NAME' => GetMessage('OBX_SMS_PROV_LETSADS_SETT_LOGIN'),
					'TYPE' => 'STRING',
					'VALUE' => '',
					'INPUT_ATTR' => array(
						'placeholder' => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_SETT_LOGIN_PH')
					)
				),
				'PASS' => array(
					'NAME' => GetMessage('OBX_SMS_PROV_LETSADS_SETT_PASS'),
					'TYPE' => 'PASSWORD',
					'VALUE' => '',
					'INPUT_ATTR' => array(
						'placeholder' => GetMessage('OBX_SMS_PROV_KOMPEITOSMS_SETT_PASS_PH')
					)
				),
				'FROM' => array(
					'NAME' => GetMessage('OBX_SMS_PROV_LETSADS_SETT_FROM'),
					'TYPE' => 'STRING',
					'VALUE' => '',
					'INPUT_ATTR' => array(
						'placeholder' => GetMessage('OBX_SMS_PROV_LETSADS_SETT_FROM_PH')
					)
				)
			)
		);
	}


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

	const ADDR = 'http://letsads.com/api';

	/*
		public function __construct($user, $pass, $from=null) {
			$this->from = $from;
			$this->user = $user;
			$this->pass = $pass;
		}
	*/

	public function getMessageStatus($messageID) {
		return $this->getStatus($messageID);
	}

	protected function _send(&$phoneNumber, &$text, &$arFields, &$countryCode) {
		$result = $this->sendEx($this->_Settings->getOption('FROM'), $countryCode.$phoneNumber, $text);
		return (!!$result);
	}

	public function sendSingle($to, $message) {
		$tmp = $this->sendEx($this->from, $to, $message);
		$result = array();
		if (array_key_exists('error', $tmp)) {
			$result['error'] = $tmp['error'];
		}
		if (array_key_exists('count', $tmp)) {
			$result['count'] = $tmp['count'];
		}
		if (array_key_exists('data', $tmp) && is_array($tmp['data']) && count($tmp['data']) > 0) {
			return array_merge($result, $tmp['data'][0]);
		}
		return $result;
	}

	public function getStatusSingle($id) {
		while (is_array($id)) {
			$id = $id[0];
		}
		$result = $this->getStatus($id);
		if (array_key_exists('error', $result)) {
			return $result;
		} else {
			if (count($result) > 0) {
				return $result[0];
			} else {
				return null;
			}
		}
	}

	public function getStatus($ids) {
		$xml = new \SimpleXMLElement('<request></request>');
		$this->_addXmlAuth($xml);
		if (is_array($ids)) {
			foreach ($ids as $i => $id) {
				$xml->sms[$i]['id'] = $id;
			}
		} else {
			$xml->sms['id'] = $ids;
		}
		$response = $this->doSend($xml);
		$result = array();
		if ($this->_startsWith('<?xml', $response)) {
			$xmlRes = new \SimpleXMLElement($response);
			foreach ($xmlRes->sms as $tmp) {
				$tmpResult = array();
				$tmpResult['id'] = (string)$tmp['id'];
				$tmpResult['req'] = date_create($tmp->requestTime);
				$parts = array();
				foreach ($tmp->part as $j => $p) {
					$partData = array();
					$partData['id'] = (string)$p['id'];
					$partData['status'] = (int)$p['status'];
					if ($p->completionTime) {
						$partData['fin'] = date_create($p->completionTime);
					}
					$parts[$j] = $partData;
				}
				$tmpResult['parts'] = $parts;
				array_push($result, $tmpResult);
			}
		} else {
			$result['error'] = (string)$response;
		}
		return $result;
	}

	public function getBalance() {
		$result = $this->requestBalance();
		if(false === $result) {
			return false;
		}
		return $result['money'];
	}

	public function requestBalance() {
		$xml = new \SimpleXMLElement('<request></request>');
		$this->_addXmlAuth($xml);
		$xml->addChild('balance');
		$response = $this->doSend($xml);
		if(false === $response) {
			return false;
		}
		$result = array();
		if ($this->_startsWith('<?xml', $response)) {
			$xmlResult = new \SimpleXMLElement($response);
			$result['money'] = (double)$xmlResult->money;
			$result['credits'] = (double)$xmlResult->credits;
			$result['holdMoney'] = (double)$xmlResult->holdMoney;
			$result['holdCredits'] = (double)$xmlResult->holdCredits;
			$result['overdraft'] = (double)$xmlResult->overdraft;
		} else {
			$result['error'] = (string)$response;
		}
		return $result;
	}

	public function sendEx($from, $to, $message) {
		$xmlResult = new \SimpleXMLElement('<request></request>');
		$this->_addXmlAuth($xmlResult);

		$xmlResult->message->from = $from;

		if (is_array($to)) {
			foreach ($to as $i => $t) {
				$xmlResult->to[$i] = $t;
			}
		} else {
			$xmlResult->message->recipient = $to;
		}

		$xmlResult->message->text = $message;


		$response = $this->doSend($xmlResult);
		$result = array();
		if( $response!== false && $this->_startsWith('<?xml', $response) ) {
			$xml = new \SimpleXMLElement($response);
			$result['count'] = (int)$xml->count;
			$result['data'] = array();
			foreach ($xml->to as $tmp) {
				$a = $tmp->attributes();
				$c = $tmp->children();
				$rep = array(
					'id' => (string)($a['id']),
					'to' => (string)$a['phone'],
					'status' => (int)$a['status'],
					'credits' => (double)$c['credits'],
					'money' => (double)$c['money']
				);
				array_push($result['data'], $rep);
			}
		} else {
			return false;
		}
		return true;
	}

	protected function _startsWith($str, $src) {
		return substr($src, 0, strlen($str)) == $str;
	}

	protected function _addXmlAuth($xml) {
//		$xml->auth->login = $this->arSettings['LOGIN']['VALUE'];
//		$xml->auth->password = $this->arSettings['PASS']['VALUE'];
		$xml->auth->login = $this->_Settings->getOption('LOGIN');
		$xml->auth->password = $this->_Settings->getOption('PASS');
	}

	protected function doSend($xml) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::ADDR);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml->asXML());

		$result = curl_exec($ch);


		$info = curl_getinfo($ch);
		curl_close($ch);

		if( $info['http_code'] != 200 ) {
			$this->addError(GetMessage('OBX_SMS_PROV_LETSADS_ERROR_REQUEST', array('#ERROR#' => $info['http_code'])));
			return false;
		}
		if( !$this->checkError($result) ) {
			return false;
		}

		return $result;
	}

	const E_API_NO_DATA = 1;
	const E_API_WRONG_DATA_FORMAT = 2;
	const E_API_REQUEST_FORMAT = 3;
	const E_API_AUTH_DATA = 4;
	const E_API_API_DISABLED = 5;
	const E_API_USER_NOT_MODERATED = 6;
	const E_API_INCORRECT_FROM = 7;
	const E_API_INVALID_FROM = 8;
	const E_API_MESSAGE_TOO_LONG = 9;
	const E_API_NO_MESSAGE = 10;
	const E_API_MAX_MESSAGES_COUNT = 11;
	const E_API_NOT_ENOUGH_MONEY = 12;
	const E_API_UNKNOWN_ERROR = 13;

	protected function checkError(&$result) {
		$xmlResult = new \SimpleXMLElement($result);
		if($xmlResult->name == 'Error') {
			switch($xmlResult->description) {
				case 'NO_DATA':
					$this->addError(GetMessage('OBX_SMS_PROV_LETSADS_API_ERROR_NO_DATA'), self::E_API_NO_DATA);
					break;
				case 'WRONG_DATA_FORMAT':
					$this->addError(GetMessage('OBX_SMS_PROV_LETSADS_API_ERROR_WRONG_DATA_FORMAT'), self::E_API_WRONG_DATA_FORMAT);
					break;
				case 'REQUEST_FORMAT':
					$this->addError(GetMessage('OBX_SMS_PROV_LETSADS_API_ERROR_REQUEST_FORMAT'), self::E_API_REQUEST_FORMAT);
					break;
				case 'AUTH_DATA':
					$this->addError(GetMessage('OBX_SMS_PROV_LETSADS_API_ERROR_AUTH_DATA'), self::E_API_AUTH_DATA);
					break;
				case 'API_DISABLED':
					$this->addError(GetMessage('OBX_SMS_PROV_LETSADS_API_ERROR_API_DISABLED'), self::E_API_API_DISABLED);
					break;
				case 'USER_NOT_MODERATED':
					$this->addError(GetMessage('OBX_SMS_PROV_LETSADS_API_ERROR_USER_NOT_MODERATED'), self::E_API_USER_NOT_MODERATED);
					break;
				case 'INCORRECT_FROM':
					$this->addError(GetMessage('OBX_SMS_PROV_LETSADS_API_ERROR_INCORRECT_FROM'), self::E_API_INCORRECT_FROM);
					break;
				case 'INVALID_FROM':
					$this->addError(GetMessage('OBX_SMS_PROV_LETSADS_API_ERROR_INVALID_FROM'), self::E_API_INVALID_FROM);
					break;
				case 'MESSAGE_TOO_LONG':
					$this->addError(GetMessage('OBX_SMS_PROV_LETSADS_API_ERROR_MESSAGE_TOO_LONG'), self::E_API_MESSAGE_TOO_LONG);
					break;
				case 'NO_MESSAGE':
					$this->addError(GetMessage('OBX_SMS_PROV_LETSADS_API_ERROR_NO_MESSAGE'), self::E_API_NO_MESSAGE);
					break;
				case 'MAX_MESSAGES_COUNT':
					$this->addError(GetMessage('OBX_SMS_PROV_LETSADS_API_ERROR_MAX_MESSAGES_COUNT'), self::E_API_MAX_MESSAGES_COUNT);
					break;
				case 'NOT_ENOUGH_MONEY':
					$this->addError(GetMessage('OBX_SMS_PROV_LETSADS_API_ERROR_NOT_ENOUGH_MONEY'), self::E_API_NOT_ENOUGH_MONEY);
					break;
				case 'UNKNOWN_ERROR':
				default:
					$this->addError(GetMessage('OBX_SMS_PROV_LETSADS_API_ERROR_UNKNOWN_ERROR'), self::E_API_UNKNOWN_ERROR);
					break;
			}
			return false;
		}
		return true;
	}
}
