<?
/************************************
 ** @product A68:SMS Bitrix Module **
 ** @vendor A68 Studio             **
 ** @mailto info@a-68.ru           **
 ************************************/
use OBX\Sms\SmsSender;

class BaseProvider extends SmsSender {

	protected $PROVIDER_ID = "BASESMS";
	protected $PROVIDER_NAME = "Базовый провайдер";
	protected $PROVIDER_DESCRIPTION = "Dummy provider class <a href='javascript:void(0)'>test link</a>";

	protected $arSettings = array(
		"LOGIN" => array(
			"NAME" => "Имя пользователя",
			"TYPE" => "TEXT",
			"VALUE" => "BASE_SMS"
		),
		"PASS" => array(
			"NAME" => "Пароль",
			"TYPE" => "TEXT",
			"VALUE" => "PASSWORD",
		),
		"FROM" => array(
			"NAME" => "Имя или номер отправителя",
			"TYPE" => "TEXT",
			"VALUE" => "BASE_TEST"
		)
	);


	public function requestBalance() {
		return 0;
	}

	public function send($telNo, $text) {

	}

	public function requestMessageStatus($messageID) {
		return 1;
	}
}

BaseProvider::registerProvider();