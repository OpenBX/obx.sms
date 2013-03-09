#!/usr/bin/env php
<?php
require dirname(__FILE__)."/../../obx.core/classes/OBX_Build.php";
$build = new OBX_Build("obx.market");
//print_r($build);
//$build->generateBackInstallCode();
$build->generateInstallCode();
//$build->generateUnInstallCode();
//$build->backInstallResources();
?>