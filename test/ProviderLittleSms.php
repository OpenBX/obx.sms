<?php
namespace OBX\Sms\Test;
use OBX\Sms\Provider\LittleSms;

class ProviderLittleSms extends SmsTestCase {
	public function setUp() {
		$this->provider = LittleSms::getProvider();
	}
	public function testSend() {
		$this->send();
	}
}