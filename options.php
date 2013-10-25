<?php
/***********************************************
 ** @product OpenBX:Sms Bitrix Module         **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

use OBX\Core\Settings\AdminPage as SettingsAdminPage;
use OBX\Core\Settings\Tab as SettingsTab;
use OBX\Sms\Provider\Provider;

IncludeModuleLangFile(__FILE__);

if( !$USER->IsAdmin() ) return;
if( !CModule::IncludeModule('obx.core') ) return;
if( !CModule::IncludeModule('obx.sms') ) return;

$arProvidersList = Provider::getProvidersList();
$arProvidersSimpleList = array();
foreach($arProvidersList as $Provider) {
	/** @var Provider $Provider */
	$arProvidersSimpleList[$Provider->PROVIDER_ID()] = $Provider->PROVIDER_NAME();
}
$ModuleSettings = new SettingsAdminPage('OpenBXSmsModuleOptions');
$ModuleSettings->addTab(new SettingsTab(
	'obx.sms',
	'COMMON_SETTINGS',
	array(
		'TAB' => 'Основные',
		'TITLE' => GetMessage('OBX_SMS_SETT_MAIN_TITLE'),
		'DESCRIPTION' => GetMessage('OBX_SMS_SETT_MAIN_TAB_DESCRIPTION')
	),
	array(
		'PROVIDER_SELECTED' => array(
			'NAME' => GetMessage('OBX_SMS_SETT_SELECTED_PROVIDER'),
			'TYPE' => 'LIST',
			'VALUES' => $arProvidersSimpleList,
			'VALUE' => 'EMAIL'
		),
		'DEFAULT_MSG_SYM_LIMIT' => array(
			'NAME' => GetMessage('OBX_SMS_SETT_DEF_MSG_SYM_LIMIT'),
			'TYPE' => 'STRING',
			'VALUE' => '70'
		)
	)
));
foreach($arProvidersList as $Provider) {
	/** @var Provider $Provider */
	$ModuleSettings->addTab(new SettingsTab(
		'obx.sms',
		'PROV_'.$Provider->PROVIDER_ID(),
		array(
			'TAB' => $Provider->PROVIDER_NAME(),
			'TITLE' => $Provider->PROVIDER_NAME(),
			'DESCRIPTION' => $Provider->PROVIDER_DESCRIPTION(),
		),
		$Provider->getSettings()
	));
}

?>
<style type="text/css" rel="stylesheet">
	#obx_sms_opt_page td.adm-detail-content-cell-l {
		width: 40%;
	}
</style>
<div id="obx_sms_opt_page">
<?

$ModuleSettings->show();

?>
</div>
<?

if($ModuleSettings->checkSaveRequest()) {
	$ModuleSettings->save();
}
if($ModuleSettings->checkRestoreRequest()) {
	$ModuleSettings->restoreDefaults();
}
