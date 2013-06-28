<?
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

use OBX\Sms\Provider;
use OBX\Sms\Settings\Settings;

class BaseProvider extends Provider {

	protected $PROVIDER_ID = 'BASESMS';

	protected function __construct() {
		$this->_Settings = new Settings('PROVIDER_'.$this->PROVIDER_ID, array(
			'EMAIL' => array(
				'NAME' => GetMessage('OBX_SMS_BASE_PROV_SETT_EMAIL_NAME'),
				'TYPE' => 'TEXT',
				'VALUE' => 'PASSWORD',
			),
		));
		$this->PROVIDER_NAME = GetMessage('OBX_SMS_BASE_PROVIDER_NAME');
		$this->PROVIDER_DESCRIPTION = GetMessage('OBX_SMS_BASE_PROVIDER_DESCRIPTION');
	}


	public function requestBalance() {
		return 0;
	}

	public function send($telNo, $text) {
		// TODO: Написать тут отправку текст по EMail
	}

	public function requestMessageStatus($messageID) {
		return 1;
	}
}

BaseProvider::registerProvider();