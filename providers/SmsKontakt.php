<?php
/************************************
 ** @product A68:SMS Bitrix Module **
 ** @vendor A68 Studio             **
 ** @mailto info@a-68.ru           **
 ************************************/

class OBX_SmsKontakt extends OBX_SmsSender {

	/*
	 * Объявление провайдера
	 */
	protected $PROVIDER_ID = "SMSKONTAKT";
	protected $PROVIDER_NAME = "СМС-Контакт";
	protected $PROVIDER_DESCRIPTION = "Необходима регистрация на <a href='http://sms-kontakt.ru/'>сайте поставщика</a>
	<br>
	<font color='red'>ОБЯЗАТЕЛЬНО УКАЖИТЕ user_phone и api_key</font>";

	/*
	 * Параметры
	 */
	protected $arSettings = array(
		"USER_PHONE" => array(
			"NAME" => "Номер телефона в формате 9xxxxxxxxx",
			"TYPE" => "TEXT",
			"VALUE" => "9135295396"
		),
		"API_KEY" => array(
			"NAME" => "API KEY (указан на сайте в личном кабинете)",
			"TYPE" => "TEXT",
			"VALUE" => "pdayzntcxs",
		),
		"SENDER_ID" => array(
			"NAME" => "Имя или номер отправителя",
			"TYPE" => "TEXT",
			"VALUE" => "SMS-kontakt"
		),
		"TEST" => array(
			"NAME" => "Тестовый режим (1 - вкл, 0 - выкл)",
			"TYPE" => "TEXT",
			"VALUE" => "0"

		)
	);

	const URL_INFO = "http://sms-kontakt.ru/api/get_info/";
	const URL_SEND = "http://sms-kontakt.ru/api/message/send/";

	public function requestBalance() {
		$arResult = json_decode($this->getBallance(), true);
		if ($arResult[0]["result"] == "success") {
			return $arResult[0]["describe"];
		} else {
			$this->addError($arResult[0]["describe"], self::SEND_STATUS_FAIL);
			return self::SEND_STATUS_FAIL;
		}
	}

	public function requestMessageStatus($messageID) {
		return 1;
	}

	public function send($telNo, $text) {
		$result = $this->MessageSend($telNo, $text);
		$arResult = json_decode($result, true);
		if ($arResult[0]["result"] == "success") {
			return true;
		} else {
			$this->addError($arResult[0]["describe"], self::SEND_STATUS_FAIL);
			return self::SEND_STATUS_FAIL;
		}
	}

	protected $sign;

	protected function __construct() {
		$this->sign = md5($this->arSettings["USER_PHONE"]["VALUE"] . $this->arSettings["API_KEY"]["VALUE"]);
	}

	function SendPostRequest($url, $headers, $post_body) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url); // урл страницы
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body); // передаём post-данные
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //
		$result = curl_exec($ch); // получить результат в переменную
		curl_close($ch);
		return $result;
	}

	protected function MessageSend($phone_to, $message) {
		$curSettings = $this->arSettings;

		$user_phone = $curSettings['USER_PHONE']['VALUE'];
		$sender_id = $curSettings['SENDER_ID']['VALUE'];
		$test = $curSettings['TEST']['VALUE'];


		$http_body = 'user_phone=' . $user_phone . '&sign=' .
			$this->sign . '&phone_to=' . $phone_to . '&message=' . $message . '&sender_id=' . $sender_id . '&test=' . $test;
		$headers[] = 'Content-Type: text/xml; charset=utf-8';
		$headers[] = 'Content-Length: ' . strlen($http_body);
		$server_answer = $this->SendPostRequest(self::URL_SEND, $headers, $http_body);
		return $server_answer;
	}

	protected function getBallance() {
		//?user_phone=<номер_телефона>&sign=<подпись_сообщения>&info=balance
		$curSettings = $this->arSettings;

		$user_phone = $curSettings['USER_PHONE']['VALUE'];
		$sender_id = $curSettings['SENDER_ID']['VALUE'];
		$test = $curSettings['TEST']['VALUE'];


		$http_body = 'user_phone=' . $user_phone . '&sign=' .
			$this->sign . "&info=balance";
		$headers[] = 'Content-Type: text/xml; charset=utf-8';
		$headers[] = 'Content-Length: ' . strlen($http_body);
		$server_answer = $this->SendPostRequest(self::URL_INFO, $headers, $http_body);
		return $server_answer;
	}
}

OBX_SmsKontakt::registerProvider();