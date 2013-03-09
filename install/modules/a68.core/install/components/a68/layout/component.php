<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if( !CModule::IncludeModule('a68.core') ) {
	ShowError(GetMessage('A68_CORE_IS_NOT_INSTALLED'));
	return;
}

$this->IncludeComponentTemplate();
?>