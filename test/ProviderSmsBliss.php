<?php
namespace OBX\Sms\Test;
use OBX\Sms\Provider\SmsBliss;

class ProviderSmsBliss extends SmsTestCase {
	public function setUp() {
		$this->provider = SmsBliss::getProvider();
	}

	public function testSend() {
		$this->send();
	}
}