<?
/*******************************************
 ** @product OBX:Market Bitrix Module     **
 ** @authors                              **
 **         Maksim S. Makarov aka pr0n1x  **
 **         Morozov P. Artem aka tashiro  **
 ** @license Affero GPLv3                 **
 ** @mailto rootfavell@gmail.com          **
 ** @mailto tashiro@yandex.ru             **
 ** @copyright 2013 DevTop                **
 *******************************************/

namespace OBX\Sms\Provider;
use OBX\Core\Settings\Settings;

IncludeModuleLangFile(__FILE__);

class EMailProvider extends Provider {

	protected $PROVIDER_ID = 'EMAIL';
	protected $SORT = 10000;

	protected function __construct() {
		$this->PROVIDER_NAME = GetMessage('OBX_SMS_PROVIDER_EMAIL_NAME');
		$this->PROVIDER_DESCRIPTION = GetMessage('OBX_SMS_PROVIDER_EMAIL_DESCRIPTION');
		$this->_Settings = new Settings(
			'obx.sms',
			'PROVIDER_'.$this->PROVIDER_ID,
			array(
				'EMAIL' => array(
					'NAME' => GetMessage('OBX_SMS_PROV_EMAIL_SETT_EMAIL'),
					'TYPE' => 'STRING',
					'VALUE' => '',
					'SORT' => 100,
					'INPUT_ATTR' => array(
						'placeholder' => GetMessage('OBX_SMS_PROV_EMAIL_SETT_EMAIL_PH')
					),
				),
				'FROM' => array(
					'NAME' => GetMessage('OBX_SMS_PROV_EMAIL_SETT_FROM'),
					'TYPE' => 'STRING',
					'VALUE' => '',
					'SORT' => 110,
					'INPUT_ATTR' => array(
						'placeholder' => GetMessage('OBX_SMS_PROV_EMAIL_SETT_FROM_PH'),
						'style' => 'width: 270px;'
					)
				)
			)
		);
	}


	public function getBalance() {
		return 0;
	}

	protected function _send(&$phoneNumber, &$text, &$arFields, &$countryCode) {
		$this->_Settings->syncSettings();
		$email = $this->_Settings->getOption('EMAIL');
		if(empty($email)) {
			$this->addError(GetMessage(
					'OBX_SMS_PROV_EMAIL_ERROR_1',
					array('#NAME#' => $this->PROVIDER_NAME())
				), 1);
			return false;
		}
		$from = trim($this->getSettings()->getOption('FROM'));
		$charset = LANG_CHARSET;
		$additional_headers = "Content-type: text/plain; charset=$charset\n\r";
		if(!empty($from)) {
			$additional_headers .= "From: $from\n\r";
		}
		$bSuccess = mail(
			$email,
			GetMessage('OBX_SMS_PROV_EMAIL_SEND_SUBJ', array('#NUMBER#' => $phoneNumber)),
			GetMessage('OBX_SMS_PROB_EMAIL_SEND_TEXT')."\n".$text,
			$additional_headers
		);
		if(!$bSuccess) {
			$this->addError(GetMessage('OBX_SMS_PROV_EMAIL_ERROR_2'), 2);
			return false;
		}
		return true;
	}

	public function getMessageStatus($messageID) {
		return 1;
	}
}
