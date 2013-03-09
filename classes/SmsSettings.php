<?php
/************************************
 ** @product A68:SMS Bitrix Module **
 ** @vendor A68 Studio             **
 ** @mailto info@a-68.ru           **
 ************************************/

IncludeModuleLangFile(__FILE__);

abstract class OBX_SmsSettings extends OBX_CMessagePool {
	final protected function __construct() {
	}

	final protected function __clone() {
	}

	static protected $_arInstances = array();
	static protected $_arLangList = null;

	public function GetController($tabCode) {
		if (!preg_match('~^[a-zA-Z\_][a-zA-Z0-9\_]*$~', $tabCode)) {
			return null;
		}
		if (!class_exists('OBX_SmsSettings_' . $tabCode)) {
			return null;
		}

		if (empty(self::$_arInstances[$tabCode])) {
			$className = 'OBX_SmsSettings_' . $tabCode;
			$TabContentObject = new $className;
			if ($TabContentObject instanceof self) {
				self::$_arInstances[$tabCode] = $TabContentObject;
			}
		}
		return self::$_arInstances[$tabCode];
	}

	public function showMessages($colspan = -1) {
		$colspan == intval($colspan);
		if ($colspan < 0) {
			$colspan = $this->listTableColumns;
		}
		$arMessagesList = $this->getMessages();
		if (count($arMessagesList) > 0) {
			?>
		<tr>
			<td<?if ($colspan > 1): ?> colspan="<?=$colspan?>"<? endif?>><?
				foreach ($arMessagesList as $arMessage) {
					ShowNote($arMessage["TEXT"]);
				}
				?></td>
		</tr><?
		}
	}

	public function showWarnings($colspan = -1) {
		$colspan == intval($colspan);
		if ($colspan < 0) {
			$colspan = $this->listTableColumns;
		}
		$arWarningsList = $this->getWarnings();
		if (count($arWarningsList) > 0) {
			?>
		<tr>
			<td<?if ($colspan > 1): ?> colspan="<?=$colspan?>"<? endif?>><?
				foreach ($arWarningsList as $arWarning) {
					ShowNote($arWarning["TEXT"]);
				}
				?></td>
		</tr><?
		}
	}

	public function showErrors($colspan = -1) {
		$colspan == intval($colspan);
		if ($colspan < 0) {
			$colspan = $this->listTableColumns;
		}
		$arErrorsList = $this->getErrors();
		if (count($arErrorsList) > 0) {
			?>
		<tr>
			<td<?if ($colspan > 1): ?> colspan="<?=$colspan?>"<? endif?>><?
				foreach ($arErrorsList as $arError) {
					ShowError($arError["TEXT"]);
				}
				?></td>
		</tr><?
		}
	}

	abstract public function showTabContent();

	abstract public function showTabScripts();

	abstract public function saveTabData();
}

class OBX_SmsSettings_BASE extends OBX_SmsSettings {
	public $curProvider;

	public function showTabContent() {
		$arProvidersList = OBX_SmsSender::getProvidersList();
		$curProviderString = COption::GetOptionString("obx.sms", "PROV_SELECTED", "");
		if (!strlen($curProviderString) > 0) {
			$curProviderString = "BASESMS";
		}
		$curProvider = $arProvidersList[$curProviderString];
		?>
	<tr class="heading">
		<td valign="top" colspan="2" align="center"><b><?=GetMessage("OBX_SMS_SETT_PROVIDER_TITLE")?></b></td>
	</tr>
	<tr>
		<td><?=GetMessage("OBX_SMS_SETT_PROVIDER")?></td>
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