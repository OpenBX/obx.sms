<?php
namespace OBX\Sms\Test;
use OBX\Sms\Provider\ByteHand;
use OBX\Sms\Provider\Provider;



class ProviderByteHand extends SmsTestCase {
	/**
	 * @var Provider
	 */
	protected $_Provider = null;
	public function setUp() {
		$this->_Provider = ByteHand::getProvider();
	}
	public function testGetBalance() {
		$balance = $this->_Provider->getBalance();
		$debug=1;
	}
}