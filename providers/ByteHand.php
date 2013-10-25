<?php
namespace OBX\Sms\Provider;
use OBX\Core\Settings\Settings;

class ByteHand extends Provider
{
	public function __construct() {
		$this->PROVIDER_ID = 'BYTEHAND';
		$this->PROVIDER_NAME = 'Byte Hand';
		$this->PROVIDER_DESCRIPTION = 'Byte Hand';
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
