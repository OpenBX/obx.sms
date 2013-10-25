<?php
@include_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/obx.sms/providers/IqSms.php');
OBX\Sms\Provider\IqSms::registerProvider();