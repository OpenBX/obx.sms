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

CUtil::InitJSCore(array('jquery'));

$arProvidersList = Provider::getProvidersList();
$arProvidersSimpleList = array();
foreach($arProvidersList as $Provider) {
	/** @var Provider $Provider */
	$arProvidersSimpleList[$Provider->PROVIDER_ID()] = $Provider->PROVIDER_NAME();
}
$ModuleSettings = new SettingsAdminPage('OpenBXSmsModuleOptions');
$MainSettingsTab = new SettingsTab(
	'obx.sms',
	'COMMON_SETTINGS',
	array(
		'TAB' => GetMessage('OBX_SMS_SETT_MAIN_TAB_NAME'),
		'TITLE' => GetMessage('OBX_SMS_SETT_MAIN_TAB_TITLE'),
		'DESCRIPTION' => GetMessage('OBX_SMS_SETT_MAIN_TAB_DESCRIPTION')
	),
	array(
		'PROVIDER_SELECTED' => array(
			'NAME' => GetMessage('OBX_SMS_SETT_SELECTED_PROVIDER'),
			'TYPE' => 'LIST',
			'VALUES' => $arProvidersSimpleList,
			'VALUE' => 'EMAIL'
		),
//		'DEFAULT_MSG_SYM_LIMIT' => array(
//			'NAME' => GetMessage('OBX_SMS_SETT_DEF_MSG_SYM_LIMIT'),
//			'TYPE' => 'STRING',
//			'VALUE' => '70'
//		)
	)
);
$ModuleSettings->addTab($MainSettingsTab);

final class OBX_SMS_ProviderSettingsTab extends SettingsTab {
	private $providerID = null;
	private $providerHomePage = null;
	private $providerDescription = null;
	public function setProviderID($providerID) {
		$this->providerID = $providerID;
	}
	public function setProviderHomepage($homepage) {
		$this->providerHomePage = $homepage;
	}
	public function setProviderDescription($description) {
		$this->providerDescription = $description;
	}
	public function showTabContent() {

		// Описание провайдера
		if(!empty($this->providerDescription)):?>
		<tr><td colspan="2"><i><?=$this->providerDescription?></i></td></tr>
		<?endif;

		// Официальная страница провайдера
		if(!empty($this->providerHomePage)):?>
		<tr>
			<td><?=GetMessage('OBX_SMS_OPTIONS_PROV_HOMEPAGE')?></td>
			<td><a target="_blank" href="<?=$this->providerHomePage?>"><?=$this->providerHomePage?></a></td>
		</tr>
		<?endif;

		////////////////////////////
		/// Настройки провайдера ///
		parent::showTabContent();
		////////////////////////////

		// Баланс провайдера
		if(!empty($this->providerID) && $this->providerID != 'EMAIL'):?>
		<tr>
			<td><?=GetMessage('OBX_SMS_OPTIONS_PROV_BALANCE')?></td>
			<td>
				<button onclick="return false;" class="obx-sms get-balance" data-provider-id="<?=$this->providerID?>"><?=GetMessage('OBX_SMS_OPTIONS_PROV_BALANCE_GET')?></button>
			</td>
		</tr>
		<?endif;
	}
}
foreach($arProvidersList as $Provider) {
	/** @var Provider $Provider */
	$providerSelected = false;
	if( $MainSettingsTab->getOption('PROVIDER_SELECTED') == $Provider->PROVIDER_ID() ) {
		$providerSelected = true;
	}
	$TabSettings = new OBX_SMS_ProviderSettingsTab(
		'obx.sms',
		'PROV_'.$Provider->PROVIDER_ID(),
		array(
			'TAB' => (($providerSelected)?' = ':'').$Provider->PROVIDER_NAME().(($providerSelected)?' = ':''),
			'TITLE' => $Provider->PROVIDER_NAME(),
			'DESCRIPTION' => $Provider->PROVIDER_DESCRIPTION(),
		),
		$Provider->getSettings()
	);
	$TabSettings->setProviderID($Provider->PROVIDER_ID());
	$TabSettings->setProviderHomepage($Provider->PROVIDER_HOMEPAGE());
	$TabSettings->setProviderDescription($Provider->PROVIDER_DESCRIPTION());
	$ModuleSettings->addTab($TabSettings);
}

if($ModuleSettings->checkSaveRequest()) {
	$ModuleSettings->save();
}
if($ModuleSettings->checkRestoreRequest()) {
	$ModuleSettings->restoreDefaults();
}

?>
<style type="text/css" rel="stylesheet">
	#obx_sms_opt_page td.adm-detail-content-cell-l {
		width: 40%;
	}
</style>
<div id="obx_sms_opt_page">
<?

$ModuleSettings->setRestoreConfirmMessage(GetMessage('OBX_SMS_SETT_RESTORE_DEF_CONFIRM'));
$ModuleSettings->show();

?>
<script type="text/javascript">
	(typeof(jQuery) != 'undefined') && (jQuery(function() {
		var buttonText = {};
		$('button.obx-sms.get-balance').on('click', function() {
			var $this = $(this);
			var providerID = $this.attr('data-provider-id');
			if(typeof(buttonText[providerID]) == 'undefined') {
				buttonText[providerID] = $this.text();
			}
			function success(data) {
				console.log(data);
				if(data.status > 0) {
					alert('obx.sms error '+data.status+': '+data.data);
				}
				else {
					$this.text(buttonText[providerID]+': '+data.balance);
				}
			}
			$.ajax({
				 url: '/bitrix/tools/obx.sms/ajson_prov_balance.php'
				,data: {provider: providerID}
				,success: success
			});
			return false;
		});
	}));
</script>
</div>
<?