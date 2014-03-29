<?php
namespace OBX\Sms\Test;
use OBX\Sms\Provider\TurboSmsUA;

class ProviderTurboSmsUA extends SmsTestCase {

	public function setUp() {
		$this->provider = TurboSmsUA::getProvider();
	}

	public function testSend() {
		$this->send();
	}
}