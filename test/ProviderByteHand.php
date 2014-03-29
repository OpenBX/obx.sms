<?php
namespace OBX\Sms\Test;
use OBX\Sms\Provider\ByteHand;

class ProviderByteHand extends SmsTestCase {
	public function setUp() {
		$this->provider = ByteHand::getProvider();
	}
	public function testSendMessage() {
		$this->send();
	}
}