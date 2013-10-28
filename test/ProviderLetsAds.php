<?php
namespace OBX\Sms\Test;
use OBX\Sms\Provider\LetsAds;

class ProviderLetsAds extends SmsTestCase {
	/** @var LetsAds */
	protected $_Provider = null;
	public function setUp() {
		$this->_Provider = LetsAds::getProvider();
	}
	public function testGetBalance() {
		$balance = $this->_Provider->getBalance();
		if(false === $balance) {
			$this->fail($this->_Provider->getLastError());
		}
		$debug=1;
	}

	public function testWrongLogin() {
		$login = $this->_Provider->getOption('LOGIN');
		$pass = $this->_Provider->getOption('PASS');
		$this->_Provider->saveSettings(array(
			'LOGIN' => '_some_wrong_login_',
			'PASS' => '_some_wrong_pass_'
		));
		$bSuccess = $this->_Provider->send('+79080158883', 'тест');
		$this->_Provider->saveSettings(array(
			'LOGIN' => $login,
			'PASS' => $pass
		));
		if($bSuccess !== false) {
			$this->fail($this->_Provider->getLastError());
		}
	}
}