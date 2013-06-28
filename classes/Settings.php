<?php
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

namespace OBX\Sms\Settings;

IncludeModuleLangFile(__FILE__);

use OBX\Core\CMessagePoolDecorator;
use OBX\Sms\Provider;

class Settings extends CMessagePoolDecorator {
	/**
	 * @var array
	 * array(
	 * 		'OPT_ID' => array(
	 * 			'NAME' => GetMessage(...)
	 * 			'DESCRIPTION' => GetMessage(...)
	 * 			'VALUE' => ...
	 * 		)
	 * 	....
	 * )
	 */
	protected $_arSettings = array();
	protected $_settingsID = null;
	protected $_bSettingsInit = false;
	public function __construct($settingsID, $arSettings) {
		if (!preg_match('~^[a-zA-Z\_][a-zA-Z0-9\_]*$~', $settingsID)) {
			return;
		}
		foreach($arSettings as $optionCode => &$arOption) {
			if (!preg_match('~^[a-zA-Z0-9\_]*$~', $optionCode)) {
				continue;
			}
			if( array_key_exists('NAME', $arOption) && !empty($arOption['NAME']) ) {
				$this->_arSettings[$optionCode] = array(
					'NAME' => $arOption['NAME'],
					'DESCRIPTION' => (array_key_exists('DESCRIPTION', $arOption)?$arOption['DESCRIPTION']:''),
					'VALUE' => (array_key_exists('VALUE', $arOption)?$arOption['VALUE']:'')
				);
			}
		}
	}
	final protected function __clone() {}

	/**
	 * ! Желательно переопределять этот метод
	 * @return string
	 */
	public function getSettingsID() {
		return $this->_settingsID;
	}

	protected function syncSettings() {
		foreach($this->_arSettings as $optionCode => &$arOption) {
			if( strlen($arOption['NAME']) > 0 ) {
				if( !array_key_exists('VALUE', $arOption) ) $arOption['VALUE'] = '';
				$arOption['VALUE'] = \COption::GetOptionString('obx.sms', $this->getSettingsID().'_'.$optionCode, $arOption['VALUE']);
			}
			else {
				unset($this->_arSettings[$optionCode]);
			}
		}
	}

	/**
	 * @return array
	 */
	public function getSettings() {
		$this->syncSettings();
		return $this->_arSettings;
	}

	/**
	 * @param $arSettings
	 */
	public function saveSettings($arSettings) {
		foreach ($arSettings as $optionCode => &$optionValue) {
			if( array_key_exists($optionCode, $this->arSettings) ) {
				if( is_array($optionValue) ) {
					if( array_key_exists('VALUE', $optionValue) ) {
						$optionValue = $optionValue['VALUE'];
					}
					else {
						$optionValue = null;
					}
				}
				if(!empty($optionValue)) {
					\COption::SetOptionString(
						'obx.sms',
						$this->getSettingsID().'_'.$optionCode,
						$optionValue,
						$this->_arSettings['DESCRIPTION']
					);
					$this->_arSettings[$optionCode] = $optionValue;
				}
			}
		}
	}
}

abstract class Tab {
	/**
	 * @param $tabClassName
	 * @return null | self
	 */
	static final public function GetTabController($tabClassName) {
		if (!preg_match('~^[a-zA-Z\_][a-zA-Z0-9\_]*$~', $tabClassName)) {
			return null;
		}
		if (!class_exists($tabClassName)) {
			return null;
		}
		/**
		 * @var self $TabContentObject
		 */
		if (empty(self::$_arInstances[$tabClassName])) {
			$TabContentObject = new $tabClassName;
			if ($TabContentObject instanceof self) {
				self::$_arInstances[$tabClassName] = $TabContentObject;
			}
			else {
				return null;
			}
		}
		return self::$_arInstances[$tabClassName];
	}

	abstract public function showTabContent();
	abstract public function showTabScripts();
	abstract public function saveTabData();

	public function showMessages($colspan = -1) {
		$colspan = intval($colspan);
		if ($colspan < 0) {
			$colspan = $this->listTableColumns;
		}
		$arMessagesList = $this->getMessages();
		if (count($arMessagesList) > 0) {
			?>
			<tr>
			<td<?if ($colspan > 1): ?> colspan="<?=$colspan?>"<? endif?>><?
				foreach ($arMessagesList as $arMessage) {
					ShowNote($arMessage['TEXT']);
				}
				?></td>
			</tr><?
		}
	}

	public function showWarnings($colspan = -1) {
		$colspan = intval($colspan);
		if ($colspan < 0) {
			$colspan = $this->listTableColumns;
		}
		$arWarningsList = $this->getWarnings();
		if (count($arWarningsList) > 0) {
			?>
			<tr>
			<td<?if ($colspan > 1): ?> colspan="<?=$colspan?>"<? endif?>><?
				foreach ($arWarningsList as $arWarning) {
					ShowNote($arWarning['TEXT']);
				}
				?></td>
			</tr><?
		}
	}

	public function showErrors($colspan = -1) {
		$colspan = intval($colspan);
		if ($colspan < 0) {
			$colspan = $this->listTableColumns;
		}
		$arErrorsList = $this->getErrors();
		if (count($arErrorsList) > 0) {
			?>
			<tr>
			<td<?if ($colspan > 1): ?> colspan="<?=$colspan?>"<? endif?>><?
				foreach ($arErrorsList as $arError) {
					ShowError($arError['TEXT']);
				}
				?></td>
			</tr><?
		}
	}
}

class ModuleSettingsMainTab extends Tab {
	public $curProvider;

	public function showTabContent() {
		$arProvidersList = Provider::getProvidersList();
		$curProviderString = \COption::GetOptionString('obx.sms', 'PROV_SELECTED', '');
		if (!strlen($curProviderString) > 0) {
			$curProviderString = 'BASESMS';
		}
		$curProvider = $arProvidersList[$curProviderString];
		?>
	<tr class="heading">
		<td valign="top" colspan="2" align="center"><b><?=GetMessage('OBX_SMS_SETT_PROVIDER_TITLE')?></b></td>
	</tr>
	<tr>
		<td><?=GetMessage('OBX_SMS_SETT_PROVIDER')?></td>
		<td width="65%">
			<select name="PROV_SELECTED" id="provider_list">
				<?foreach ($arProvidersList as $Provider): ?>
				<option <?if ($Provider->PROVIDER_ID() == $curProviderString): ?>selected<? endif;?>
						value="<?=$Provider->PROVIDER_ID()?>"><?=$Provider->PROVIDER_NAME() . " (" . $Provider->PROVIDER_ID() . ")"?></option>
				<? endforeach;?>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<?
			foreach ($arProvidersList as $Provider):
				?>
				<table cellpadding="0" cellspacing="0" border="0" class="edit-table provider_settings"
					   id="obx_sms_settings_provider_<?=$Provider->PROVIDER_ID()?>"
					   style="display: <?if ($Provider->PROVIDER_ID() == $curProvider->PROVIDER_ID()): ?>table<? else: ?>none<?endif;?>">
					<tr>
						<td></td>
						<td width="65%">
							<small>
								<?=$Provider->PROVIDER_DESCRIPTION()?>
							</small>
						</td>
					</tr>
					<?
					$curSettings = $Provider->getSettings();
					if (is_array($curSettings) && count($curSettings) > 0):?>
						<? foreach ($curSettings as $id => $setting): ?>
							<tr>
								<td class="field-name">
									<?=$setting["NAME"]?>
								</td>
								<td width="65%">
									<?switch ($setting["TYPE"]) {
									case "TEXT":
										echo '<input type="text" name="PROV_' . $Provider->PROVIDER_ID() . '_' . $id . '" value="' . $setting["VALUE"] . '">';
										break;
									case "BOOL":
										echo '<input type="checkbox" name"PROV_' . $Provider->PROVIDER_ID() . '_' . $id . '" value="' . $setting["VALUE"] . '">';
										break;
									default:
										break;
								} //endswitch?>
								</td>
							</tr>
							<? endforeach; ?>
						<? endif; ?>
				</table>
				<? endforeach; ?>
		</td>
	</tr>
	</td>
	<?
	}

	public function showTabScripts() {
		?>
	<script type="text/javascript">
		if (typeof(jQuery) == 'undefined') jQuery = false;
		(function ($) {
			$select = $("#provider_list");
			$allSettings = $("#obx_sms_settings_base_edit_table .provider_settings");
			$select.on("change", function () {
				$allSettings.hide();
				$curProv = $allSettings.closest("#obx_sms_settings_provider_" + $(this).val());
				$curProv.show();
			})
		})(jQuery)
	</script>
	<?
	}

	public function saveTabData() {
		// TODO: Implement saveTabData() method.
	}

}
