<?php
@include_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/obx.sms/providers/SmsBliss.php');
OBX\Sms\Provider\SmsBliss::registerProvider();