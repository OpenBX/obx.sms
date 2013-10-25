<?php
namespace OBX\Sms\Provider;
use OBX\Core\Settings\Settings;

class LittleSms extends Provider
{
	public function __construct() {
		$this->PROVIDER_ID = 'LittleSms';
		$this->PROVIDER_NAME = 'LittleSms';
		$this->PROVIDER_DESCRIPTION = 'LittleSms';
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
