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

namespace OBX\Sms;

use OBX\Core\CMessagePoolDecorator;

abstract class SmsSender extends CMessagePoolDecorator {
	static protected $_arProvidersList;

	protected $PROVIDER_ID = "";
	protected $PROVIDER_NAME = "";
	protected $PROVIDER_DESCRIPTION = "";

	/*
	 * Константы для опредления ответа сервера
	 */
	const SEND_STATUS_SUCCESS = "1";
	const SEND_STATUS_FAIL = "fail";

	/*
	 * Параметры
	 */
	protected $arSettings = array(
		"LOGIN" => array(
			"NAME" => "Имя пользователя",
			"TYPE" => "TEXT",
			"VALUE" => ""
		),
		"PASS" => array(
			"NAME" => "Пароль",
			"TYPE" => "TEXT",
			"VALUE" => "",
		),
		"FROM" => array(
			"NAME" => "Имя или номер отправителя",
			"TYPE" => "TEXT",
			"VALUE" => "sms_test"
		)
	);

	protected function __construct() {
	}

	final protected function __clone() {
	}

	/**
	 * @return string
	 */
	final public function PROVIDER_DESCRIPTION() {
		return $this->PROVIDER_DESCRIPTION;
	}

	/**
	 * @return string
	 */
	final public function PROVIDER_NAME() {
		return $this->PROVIDER_NAME;
	}

	/**
	 * @return string
	 */
	final public function PROVIDER_ID() {
		return $this->PROVIDER_ID;
	}

	/**
	 *
	 */
	final static public function registerProvider() {
		$className = get_called_class();
		$im = new $className;
		self::addProvider($im->PROVIDER_ID(), $im);
	}

	/**
	 * @param $providerID
	 * @param $Provider
	 */
	final static protected function addProvider($providerID, $Provider) {
		if ($Provider instanceof self) {
			if (!array_key_exists($providerID, self::$_arProvidersList)) {
				$Provider->getSettings();
				self::$_arProvidersList[$providerID] = $Provider;
			}
		}
	}

	/**
	 * @return bool
	 */
	final static public function includeProviders() {
		$_providerDir = $_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/obx.sms/providers";

		if (!is_dir($_providerDir)) {
			return false;
		}

		$dir = opendir($_providerDir);
		while ($elementOfDir = readdir($dir)) {
			if (
					$elementOfDir != ".."
					&& $elementOfDir != "."
					&& substr($elementOfDir, strlen($elementOfDir) - 4, strlen($elementOfDir)) == ".php"
			) {
				$arFilesList[] = $elementOfDir;
			}
		}

		foreach ($arFilesList as $providerFileName) {
			$eventFilePath = $_providerDir . "/" . $providerFileName;
			@include $eventFilePath;
		}
		return self::$_arProvidersList;
	}

	/**
	 * @return mixed
	 */
	final static public function getProvidersList() {
		return self::$_arProvidersList;
	}

	/**
	 * @param $providerID
	 * @return null
	 */
	final static public function factory($providerID) {
		if (array_key_exists($providerID, self::$_arProvidersList)) {
			return self::$_arProvidersList[$providerID];
		} else {
			self::addError(GetMessage("OBX_SMS_PROVIDER_NOT_FOUND"));
			return null;
		}
	}


	/*
	 * Простая отправка сообщений
	 * send = один номер - один текст сообщения
	 * sendBatch =  список номеров - один текст сообщения
	 */
	/**
	 * @param $telNo
	 * @param $text
	 * @return mixed
	 */
	abstract public function send($telNo, $text);

	/**
	 *
	 */
	public function sendBatch() {
	}

	/*
	 * Персональная отправка сообщений
	 * TODO: to @version 0.5.0
	 * sendMessage = Один номер - один шаблон сообщения
	 * sendMessageBatch = Список персон - один шаблон
	 */
	/**
	 * @param $tel
	 * @param $templateID
	 * @param array $arFields
	 */
	public function sendMessage($tel, $templateID, $arFields = array()) {
	}

	/**
	 *
	 */
	public function sendMessageBatch() {
	}

	/**
	 * @return mixed
	 */
	abstract public function requestBalance();

	/**
	 * @param $messageID
	 * @return mixed
	 */
	abstract public function requestMessageStatus($messageID);

	/**
	 * @return array
	 */
	public function getSettings() {
		$curSettings = & $this->arSettings;
		foreach ($curSettings as $id => $setting) {
			$curSettings[$id]["VALUE"] = \COption::GetOptionString("obx.sms", "PROV_" . $this->PROVIDER_ID . "_" . $id, $setting["VALUE"]);
		}
		return $curSettings;
	}

	/**
	 * @param $arSettings
	 */
	public function saveSettings($arSettings) {
		$curSettings = & $this->arSettings;
		foreach ($arSettings as $id => $setting) {
			if (array_key_exists($id, $curSettings)) {
				\COption::SetOptionString("obx.sms", "PROV_" . $this->PROVIDER_ID . "_" . $id, $setting["VALUE"]);
			}
		}
	}

	final static public function getCurrent() {
		$curProvID = \COption::GetOptionString("obx.sms", "PROV_SELECTED");
		if (strlen($curProvID) > 0) {
			return self::factory($curProvID);
		}
	}

}
