<?php
namespace OBX\Sms\Test;
use OBX\Sms\Provider\LetsAds;

class ProviderLetsAds extends SmsTestCase {
	public function setUp() {
		$this->provider = LetsAds::getProvider();
	}

	public function testWrongLogin() {
		$login = $this->provider->getOption('LOGIN');
		$pass = $this->provider->getOption('PASS');
		$this->provider->saveSettings(array(
			'LOGIN' => '_some_wrong_login_',
			'PASS' => '_some_wrong_pass_'
		));
		$bSuccess = $this->provider->send('+79080158883', 'тест');
		$this->provider->saveSettings(array(
			'LOGIN' => $login,
			'PASS' => $pass
		));
		if($bSuccess !== false) {
			$this->fail($this->provider->getLastError());
		}
	}

	public function testGetBalance() {
		$balance = $this->provider->getBalance();
		if(false === $balance) {
			$this->fail($this->provider->getLastError());
		}
		$debug=1;
	}
}