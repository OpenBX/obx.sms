<?php
/***********************************************
 ** @product OBX:Market Bitrix Module         **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/
namespace OBX\Sms\Test;

use OBX\Sms\Provider;

class FirstTest extends TestCase {
	public function testOne() {
		$this->assertTrue(true);
		$arProviders = Provider::getProvidersList();
		print_r($arProviders);
	}
}
