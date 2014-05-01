<?php
/*******************************************
 ** @product OBX:Sms Bitrix Module        **
 ** @authors                              **
 **         Maksim S. Makarov aka pr0n1x  **
 ** @license Affero GPLv3                 **
 ** @mailto rootfavell@gmail.com          **
 ** @copyright 2013 DevTop                **
 *******************************************/

$arModuleClasses = array(
	 'OBX\Sms\Settings\ModuleSettingsMainTab'	=> 'classes/Settings.php'
	,'OBX\Sms\Provider\Provider'				=> 'classes/Provider.php'
	,'OBX\Sms\Provider\SentLog'					=> 'classes/SentLog.php'
);
return $arModuleClasses;