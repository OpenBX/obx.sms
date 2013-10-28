<?php
namespace OBX\Sms\Test;
use OBX\Sms\Provider\IqSms;
use OBX\Sms\Provider\Provider;

class ProviderIqSms extends SmsTestCase {
	/**
	 * @var Provider
	 */
	protected $_Provider = null;
	public function setUp() {
		$this->_Provider = IqSms::getProvider();
	}
	public function testGetBalance() {
		$balance = $this->_Provider->getBalance();
		$debug=1;
	}
}