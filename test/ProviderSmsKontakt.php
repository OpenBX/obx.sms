<?php
namespace OBX\Sms\Test;
use OBX\Sms\Provider\Provider;
use OBX\Sms\Provider\SmsKontakt;



class ProviderSmsKontakt extends SmsTestCase {
	public function setUp() {
		$this->provider = SmsKontakt::getProvider();
	}

	public function testSend() {
		$this->send();
	}
}