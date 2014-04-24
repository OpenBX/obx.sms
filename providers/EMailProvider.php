<?
/*******************************************
 ** @product OBX:Sms Bitrix Module        **
 ** @authors                              **
 **         Maksim S. Makarov aka pr0n1x  **
 ** @license Affero GPLv3                 **
 ** @mailto rootfavell@gmail.com          **
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
			self::SETTINGS_PREFIX.$this->PROVIDER_ID,
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

	protected function _send(&$phoneNumber, &$text, &$countryCode) {
		$this->_Settings->syncSettings();
		$email_to = $this->_Settings->getOption('EMAIL');
		$CAllEvent = new \CAllEvent();
		$eol = $CAllEvent->GetMailEOL();
		if(empty($email_to)) {
			$this->addError(GetMessage(
					'OBX_SMS_PROV_EMAIL_ERROR_1',
					array('#NAME#' => $this->PROVIDER_NAME())
				), 1);
			return false;
		}
		$from = trim($this->getSettings()->getOption('FROM'));
		$charset = LANG_CHARSET;
		$arMailHeaders = array(
			'Content-type' => 'text/plain; charset='.$charset
		);
		if(!empty($from)) {
			$arMailHeaders['From'] = $from;
		}
		$bConvertMailHeader = (\COption::GetOptionString("main", "convert_mail_header", "Y")=="Y")?true:false;
		$bMsSmtp = (defined("BX_MS_SMTP") && BX_MS_SMTP===true)?true:false;
		$bConvertNewLine2Win = (\COption::GetOptionString("main", "CONVERT_UNIX_NEWLINE_2_WINDOWS", "N")=="Y")?true:false;

		$subject = GetMessage('OBX_SMS_PROV_EMAIL_SEND_SUBJ', array('#NUMBER#' => $phoneNumber));
		$text = GetMessage('OBX_SMS_PROB_EMAIL_SEND_TEXT')."\n".$text;
		$text = str_replace("\r\n", "\n", $text);
		if(true === $bConvertNewLine2Win) {
			$text = str_replace("\n", "\r\n", $text);
		}

		$additional_headers = '';
		foreach($arMailHeaders as $hKey => &$hValue) {
			if(true === $bConvertMailHeader) {
				if($hKey == 'From' || $hKey == 'CC') {
					$hValue = $CAllEvent->EncodeHeaderFrom($hValue, $charset);
				}
				else {
					$hValue = $CAllEvent->EncodeMimeString($hValue, $charset);
				}
				if(true === $bMsSmtp) {
					if( ($hKey=='From'||$hKey=='To') && $hValue != '') {
						$hValue = preg_replace("/(.*)\\<(.*)\\>/i", '$2', $hValue);
					}
				}
			}
			$additional_headers .= $hKey.': '.$hValue.$eol;
		}
		if($bConvertMailHeader) {
			$email_to = $CAllEvent->EncodeHeaderFrom($email_to, $charset);
			$subject = $CAllEvent->EncodeMimeString($subject, $charset);
		}

		if(defined("BX_MS_SMTP") && BX_MS_SMTP===true) {
			$email_to = preg_replace("/(.*)\\<(.*)\\>/i", '$2', $email_to);
		}

		$bSuccess = bxmail($email_to, $subject, $text, $additional_headers);
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
