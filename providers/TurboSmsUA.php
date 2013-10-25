<?php
namespace OBX\Sms\Provider;
use OBX\Core\Settings\Settings;

class TurboSmsUA extends Provider
{
	public function __construct() {
		$this->PROVIDER_ID = 'TurboSmsUA';
		$this->PROVIDER_NAME = 'TurboSMS.ua';
		$this->PROVIDER_DESCRIPTION = 'TurboSMS.ua';
		$this->_Settings = new Settings('obx.core', 'PROVIDER_'.$this->PROVIDER_ID, array(
				'API_KEY' => array(
					'NAME' => 'API_KEY',
					'TYPE' => 'STRING',
					'VALUE' => '',
				),
			));
	}

	protected function _send(&$telNo, &$text, &$arFields, &$countryCode){

	}

	public function getBalance() {

	}

	public function getMessageStatus($messageID) {

	}
}
