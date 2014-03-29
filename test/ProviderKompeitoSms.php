<?php
namespace OBX\Sms\Test;
use OBX\Sms\Provider\KompeitoSms;

class ProviderKompeitoSms extends SmsTestCase {
	public function setUp() {
		$this->provider = KompeitoSms::getProvider();
	}

	public function testSend() {
		$this->send();
	}
}