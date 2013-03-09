<?php
/************************************
 ** @product A68:SMS Bitrix Module **
 ** @vendor A68 Studio             **
 ** @mailto info@a-68.ru           **
 ************************************/

abstract class OBX_SmsSender extends OBX_CMessagePool {
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

	final public function PROVIDER_DESCRIPTION() {
		return $this->PROVIDER_DESCRIPTION;
	}

	final public function PROVIDER_NAME() {
		return $this->PROVIDER_NAME;
	}

	final public function PROVIDER_ID() {
		return $this->PROVIDER_ID;
	}

	final static public function registerProvider() {
		$className = get_called_class();
		$im = new $className;
		self::addProvider($im->PROVIDER_ID(), $im);
	}

	final static protected function addProvider($providerID, $Provider) {
		if ($Provider instanceof self) {
			if (!array_key_exists($providerID, self::$_arProvidersList)) {
				$Provider->getSettings();
				self::$_arProvidersList[$providerID] = $Provider;
			}
		}
	}

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

	final static public function getProvidersList() {
		return self::$_arProvidersList;
	}

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
	abstract public function send($telNo, $text);

	public function sendBatch() {
	}

	/*
	 * Персональная отправка сообщений
	 * TODO: to @version 0.5.0
	 * sendMessage = Один номер - один шаблон сообщения
	 * sendMessageBatch = Список персон - один шаблон
	 */
	public function sendMessage($tel, $templateID, $arFields = array()) {
	}

	public function sendMessageBatch() {
	}

	abstract public function requestBalance();

	abstract public function requestMessageStatus($messageID);

	public function getSettings() {
		$curSettings = & $this->arSettings;
		foreach ($curSettings as $id => $setting) {
			$curSettings[$id]["VALUE"] = COption::GetOptionString("obx.sms", "PROV_" . $this->PROVIDER_ID . "_" . $id, $setting["VALUE"]);
		}
		return $curSettings;
	}

	public function saveSettings($arSettings) {
		$curSettings = & $this->arSettings;
		foreach ($arSettings as $id => $setting) {
			if (array_key_exists($id, $curSettings)) {
				COption::SetOptionString("obx.sms", "PROV_" . $this->PROVIDER_ID . "_" . $id, $setting["VALUE"]);
			}
		}
	}

}
