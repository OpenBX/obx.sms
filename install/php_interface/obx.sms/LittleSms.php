<?php
@include_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/obx.sms/providers/LittleSms.php');
OBX\Sms\Provider\LittleSms::registerProvider();