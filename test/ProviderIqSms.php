<?php
namespace OBX\Sms\Test;
use OBX\Sms\Provider\IqSms;
use OBX\Sms\Provider\Provider;

class ProviderIqSms extends SmsTestCase {
	public function setUp() {
		$this->provider = IqSms::getProvider();
	}

	public function testSend() {
		$this->send();
	}
}