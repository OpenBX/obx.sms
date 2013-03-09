<?php
/*************************************
 ** @product A68:Core Bitrix Module **
 ** @vendor A68 Studio              **
 ** @mailto info@a-68.ru            **
 *************************************/

class bxutils
{
    
    // Выбрать по формату данные о составе типа список
    // Возможные поля формата: ID - PROPERTY_ID - VALUE - XML_ID - EXTERNAL_ID - PROPERTY_NAME - PROPERTY_CODE ...
    // Например: $format = 'XML_ID=>VALUE|ID' или 'ID=>XML_ID' по умолчанию 'ID=>VALUE'
    //
    // По вормату XML_ID=>VALUE|ID вернется массив:                   По вормату ID=>VALUE вернется массив:
    // Array = (                                                      Array = (
    //  XML_ID => array(                                                 ID => #VALUE#
    //      VALUE=>#VALUE#                                            )
    //      ID=>#ID#
    //    ) 
    // )
    // [rainman]
    static function getPropertyTypeL($idIBlock, $codeProperty, $format){
        if(!is_integer($idIBlock) or is_string($codeProperty)){
            // формат будущего массива
            if(!$format){$format='XML_ID=>VALUE';}
            $format_array = false;
            $format_parts=split("=>", $format);
            $format_array=false;
            if(strstr($format_parts[1], '|')){$format_array=split("\|", $format_parts[1]);}
            // выборка свойства
            $resProperty = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$idIBlock, "CODE"=>$codeProperty));
            while($arProperty = $resProperty->GetNext()){
                if($format_array){
                    foreach($format_array as $key){
                        $property[$arProperty[$format_parts[0]]][$key]=$arProperty[$key];
                    }
                }else{
                    $property[$arProperty[$format_parts[0]]]=$arProperty[$format_parts[1]];
                }
            }
            return $property;
        }else{return false;}
	}
    

    // Исправление неудобства выборки VALUE_XML_ID множественных свойств типа "список (L)":
    // $result = CIBlockElement::GetByID($id);
    // $arElement = $result->GetNextElement();
    // $arElement->GetProperties(); - выборка множественных свойств типа "список (L)" вернет только последний VALUE_XML_ID значений свойства
    // 
    // Для того чтобы не делать запрос к базе для кажного свойства через CIBlockElement::GetProperty() - применить её только в нужном случае
    // [rainman]
    static function FixValueXMLID(&$arElement){
        if(is_array($arElement) and $arElement["PROPERTIES"] and !$arElement["BXUTILS_FIX_BY_VALUEXMLID"]){
            foreach ($arElement["PROPERTIES"] as $key=>$arProp){
                if($arProp['MULTIPLE']=='Y' and $arProp['PROPERTY_TYPE']=='L'){
                    // Исправление VALUE_XML_ID
                    $resProperties = CIBlockElement::GetProperty(
                        $arElement['IBLOCK_ID'],
                        $arElement['ID'],
                        array("sort"=>"asc"),
                        array("CODE"=>$key)
                    );
                    $arElement["PROPERTIES"][$key]['VALUE_XML_ID']=array();
                    $arElement["PROPERTIES"][$key]['VALUE']=array();
                    while($arProperty = $resProperties->Fetch()){
                        $arElement["PROPERTIES"][$key]['VALUE_XML_ID'][]=$arProperty['VALUE_XML_ID'];
                        $arElement["PROPERTIES"][$key]['VALUE'][]=$arProperty['VALUE_ENUM'];
                    }
                    $arElement["BXUTILS_FIX_BY_VALUEXMLID"]=true;
                }
            }
        }
	}

    // Ошибка в публичке в формате битрикса
    // [rainman]
    static function PrintBXError($message){
        if($message){
            $html='<font class="errortext">Ошибка: '.$message.'</font>';
            echo $html;
        }
    }
    
	static function getUrlParam($url, $returnData="query") {

		$arUrl = parse_url($url);
		//d($arUrl);
		if ($returnData == "query") {
			return $arUrl["query"];
		}
		if ($returnData == "path") {
			return $arUrl["path"];
		}
		
		$arUrlStrParams = explode("&", $arUrl["query"]);
		$arUrlParams = array();
		foreach($arUrlStrParams as $k => $urlParam) {
			$artmp = explode("=", $urlParam);
			$arUrlParams[$artmp[0]] = $artmp[1];
		}

		if ($returnData == "arrayDel") {
			return array_keys($arUrlParams);
		}
		if ($returnData == "params") {
			return $arUrlParams;
		}
	}

	static function getPropIdByCode($IBLOCK_ID, $PROP_CODE, &$arProp = array(), &$ERR_MSG = array()) {
		if( !CModule::IncludeModule("iblock") ) {
			$ERR_MSG[] = "Модуль информационнх блоков не установлен.";
			return false;
		}
		$PROP_CODE = strtoupper($PROP_CODE);

		static $arIdToCode = array();

		if( !isset($arIdToCode[$IBLOCK_ID]) ) {
			$arIdToCode[$IBLOCK_ID] = array();
		}

		if( count($arIdToCode[$IBLOCK_ID])<1 ) {
			$properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$IBLOCK_ID));
			while ($prop_fields = $properties->GetNext()) {
				//echo $prop_fields["ID"]." - ".$prop_fields["NAME"]."<br>";
				$arIdToCode[$IBLOCK_ID][strtoupper($prop_fields["CODE"])] = $prop_fields;
			}
		}
		if ( isset($arIdToCode[$IBLOCK_ID][$PROP_CODE]) ) {
			$arProp = $arIdToCode[$IBLOCK_ID][$PROP_CODE];
			return $arIdToCode[$IBLOCK_ID][$PROP_CODE]["ID"];
		}
		else {
			$ERR_MSG[] = "Свойство не найдено для этого ИБ.";
			return false;
		}
		
	}

	static public function getPropCodeById($IBLOCK_ID, $PROP_ID, &$ERR_MSG = array()) {
		if( !CModule::IncludeModule("iblock") ) {
			$ERR_MSG[] = "Модуль информационнх блоков не установлен.";
			return false;
		}

		static $arCodeToId = array();

		if( !isset($arCodeToId[$IBLOCK_ID]) ) {
			$arCodeToId[$IBLOCK_ID] = array();
		}

		if( count($arCodeToId[$IBLOCK_ID])<1 ) {
			$properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$IBLOCK_ID));
			while ($prop_fields = $properties->GetNext())
			{
				//echo $prop_fields["ID"]." - ".$prop_fields["CODE"]."<br>";
				$arCodeToId[$IBLOCK_ID][$prop_fields["ID"]] = $prop_fields["CODE"];
			}
		}
		if ( isset($arCodeToId[$IBLOCK_ID][$PROP_ID]) ) {
			return $arCodeToId[$IBLOCK_ID][$PROP_ID];
		}
		else {
			$ERR_MSG[] = "Свойство не найдено для этого ИБ.";
			return false;
		}
	}

	static function cropString($str, $len, $endOfLine = "") {
		$str = trim($str);
		$len = intval($len);
		if (strlen($str) < 1 || $len < 1) {
			return false;
		}
		if (strlen($str) <= $len) {
			return $str;
		}
		$len++;
		$str = substr($str, 0, strrpos(substr($str, 0, $len), " ")).$endOfLine;
		return $str;
	}
	
	static function rusDays($amount) {
		$amount = abs(intval($amount));
		switch($amount%100) {
			case 11:
			case 12:
			case 13:
			case 14:
				return "дней";
				break;
			default:
				switch($amount%10) {
					case 1:
						return "день";
						break;
					case 2:
					case 3:
					case 4:
						return "дня";
						break;
					default:
						return "дней";
				}
		}
	}
	static function rusHours($amount) {
		$amount = abs(intval($amount));
		switch($amount%100) {
			case 11:
			case 12:
			case 13:
			case 14:
				return "часов";
				break;
			default:
				switch($amount%10) {
					case 1:
						return "час";
						break;
					case 2:
					case 3:
					case 4:
						return "часа";
						break;
					default:
						return "часов";
				}
		}
	}

	static function fixImageSizes(&$arValues, $arLimits, $bModifySrcValues = true) {
		$arNewSizes = array(
			"RATE" => 1,
			"WIDTH" => $arValues["WIDTH"],
			"HEIGHT" => $arValues["HEIGHT"]
		);
		if( $arValues["WIDTH"] > $arLimits["MAX_WIDTH"]) {
			$arNewSizes["RATE"] = round($arLimits["MAX_WIDTH"]/$arValues["WIDTH"], 3);
			$arNewSizes["WIDTH"] = $arLimits["MAX_WIDTH"];
			$arNewSizes["HEIGHT"] = $arValues["HEIGHT"]*$arNewSizes["RATE"];
			if($arNewSizes["HEIGHT"] > $arLimits["MAX_HEIGHT"]) {
				$arNewSizes["RATE"] = round($arLimits["MAX_HEIGHT"]/$arValues["HEIGHT"], 3);
				$arNewSizes["HEIGHT"] = $arLimits["MAX_HEIGHT"];
				$arNewSizes["WIDTH"] = $arValues["WIDTH"]*$arNewSizes["RATE"];
			}
		}
		//d($arNewSizes);
		if( $arNewSizes["HEIGHT"] > $arLimits["MAX_HEIGHT"]) {
			$arNewSizes["RATE"] = round($arLimits["MAX_HEIGHT"]/$arValues["HEIGHT"], 3);
			$arNewSizes["HEIGHT"] = $arLimits["MAX_HEIGHT"];
			$arNewSizes["WIDTH"] = $arValues["WIDTH"]*$arNewSizes["RATE"];
			if($arNewSizes["WIDTH"] > $arLimits["MAX_WIDTH"]) {
				$arNewSizes["RATE"] = round($arLimits["MAX_WIDTH"]/$arValues["WIDTH"], 3);
				$arNewSizes["WIDTH"] = $arLimits["MAX_WIDTH"];
				$arNewSizes["HEIGHT"] = $arValues["HEIGHT"]*$arNewSizes["RATE"];
			}
		}
		//d($arNewSizes);
		
		if($bModifySrcValues) {
			$arValues["WIDTH"] = $arNewSizes["WIDTH"];
			$arValues["HEIGHT"] = $arNewSizes["HEIGHT"];
		}
		return $arNewSizes;
	}

	// get list converted to array indexed by ID
	static function convListToIDIndex(&$arSectionList) {
		$arSectionsIDIndexList = array();
		foreach($arSectionList as &$arSectionList) {
			$arSectionsIDIndexList[$arSectionList['ID']] = $arSectionList;
		}
		return $arSectionsIDIndexList;
	}
	// making parent-child relation table
	static function getRelationTableFromFlatTree(&$arFlatTree, $DEPTH_KEY = 'DEPTH_LEVEL', $CHILDS_KEY = 'CHILDS', $PARENT_KEY = 'PARENT', $bModifySrcArray = false) {
		$iItems = 0;
		$itemsCount = count($arFlatTree);
		$curPointer = &$arTree;
		$curDepth = 1;
		$prevKey = 0;
		$parentKey = 0;
		$arLastKeyInDepth = array();
		$arParents = array();
		$arChilds = array();
		foreach($arFlatTree as $key => &$item) {
			$iItems++;
			if($item[$DEPTH_KEY] > $curDepth) {
				$parentKey = $prevKey;
				$curDepth = $item[$DEPTH_KEY];
			}
			elseif($item[$DEPTH_KEY] < $curDepth) {
				$curDepth = $item[$DEPTH_KEY];
				$parentKey = $arLastKeyInDepth[$curDepth-1];
			}
			$arChilds[$key][$DEPTH_KEY] = $curDepth;
			$arChilds[$key][$PARENT_KEY] = $parentKey;

			if(!$parentKey) $parentKey = 0;
			$arParents[$parentKey][$CHILDS_KEY][] = $key;
			$prevKey = $key;
			$arLastKeyInDepth[$item[$DEPTH_KEY]] = $prevKey;
		}
		//d($arParents, '$arParents');
		//d($arChilds, '$arChilds');
		
		$arRelations = array();
		$arRelations[0] = $arParents[0];
		foreach($arChilds as $childKey => $arChild) {
			$arRelations[$childKey] = $arChild;
			$arRelations[$childKey][$CHILDS_KEY] = array();
			$arRelations[$childKey][$CHILDS_KEY] = $arParents[$childKey][$CHILDS_KEY];

			if($bModifySrcArray) {
				$arFlatTree[$childKey][$PARENT_KEY] = $arChild[$PARENT_KEY];
				$arFlatTree[$childKey][$CHILDS_KEY] = array();
				$arFlatTree[$childKey][$CHILDS_KEY] = $arParents[$childKey][$CHILDS_KEY];
			}
		}
		//d($arRelations, '$arRelations');
		
		return $arRelations;
	}

	static function getParentIDByRelationTable(&$SectionID, &$arRelationTable)
	{
		if(!$SectionID)
			return false;
		if(@isset($arRelationTable[$SectionID])) {
			return $arRelationTable[$SectionID]["PARENT_SECTION_ID"];
		}
		return false;
	}
	
	//////////// SALE ///////////////

	//echo "Сумма 11800.95 руб на текущем языке будет выглядеть так: ";
	//echo bxutils::formatCurrency(11800.95, "RUR");
	function formatCurrency($fSum, $strCurrency)
	{
		if (!isset($fSum) || strlen($fSum)<=0)
			return "";

		$arCurFormat = CCurrencyLang::GetCurrencyFormat($strCurrency);

		if (!isset($arCurFormat["DECIMALS"]))
			$arCurFormat["DECIMALS"] = 2;

		$arCurFormat["DECIMALS"] = IntVal($arCurFormat["DECIMALS"]);

		if (!isset($arCurFormat["DEC_POINT"]))
			$arCurFormat["DEC_POINT"] = ".";

		if (!isset($arCurFormat["THOUSANDS_SEP"]))
			$arCurFormat["THOUSANDS_SEP"] = "\\"."xA0";

		$tmpTHOUSANDS_SEP = $arCurFormat["THOUSANDS_SEP"];
		eval("\$tmpTHOUSANDS_SEP = \"$tmpTHOUSANDS_SEP\";");
		$arCurFormat["THOUSANDS_SEP"] = $tmpTHOUSANDS_SEP;

		if (!isset($arCurFormat["FORMAT_STRING"]))
			$arCurFormat["FORMAT_STRING"] = "#";

		$num = number_format($fSum,
							$arCurFormat["DECIMALS"],
							$arCurFormat["DEC_POINT"],
							$arCurFormat["THOUSANDS_SEP"]);

		return str_replace("#",
						$num,
						$arCurFormat["FORMAT_STRING"]);
	}

	
	
	//////////// РАБОТА С ФАЙЛАМИ И ПАПКАМИ
	
	/**
	 * 
	 * Ф-ия работает опасно. Если указать не глубокий путь,
	 * то можно потереть чужие файлы.
	 * 
	 * Что бы этого изберажть, ниже ТУДУ.
	 * 
	 * TODO: Написать небольшую рекурсию,
	 * которая удаляя содержимое папки удаляла только те имена,
	 * которые есть в $frDir. Т.е. вызывать не DeleteDirFilesEx а саму себя
	 * и при выходе из рекурсии проверять не остлись ли файлы или папки.
	 * Если остались, то не удаляем папку. 
	 * @param String $frDir
	 * @param String $toDir
	 * @param String $arExept
	 */
	static function deleteDirContents($frDir, $toDir, $arExept = array()) {
		if( is_dir($frDir) ) {
			$d = dir($frDir);
			while( $entry = $d->read() ) {
				if( $entry=="." || $entry==".." ) {
					continue;
				}
				if( in_array($entry, $arExept) ) {
					continue;
				}
				if( is_dir($toDir."/".$entry) ) {
					//echo "delete dir: ".$toDir."/".$entry."<br />\n";
					self::deleteDirFilesEx($toDir."/".$entry, true);
				}
				else {
					//echo "delete file: ".$toDir."/".$entry."<br />\n";
					@unlink($toDir."/".$entry);
				}
			}
			$d->close();
			//die();
		}
	}
	
	/**
	 * Работает так же как битриксовская, но в отличие от неё, может принимать полный путь.
	 * @param String $path - путь
	 * @param bool $bIsPathFull - абсолюьный=true, относительный=false
	 * @return boolean
	 */
	function deleteDirFilesEx($path, $bIsPathFull = false)
	{
		if(strlen($path) == 0 || $path == '/')
			return false;
		if(!$bIsPathFull) {
			$full_path = $_SERVER["DOCUMENT_ROOT"].$path;
		}
		else {
			$full_path = $path;
		}
		
		$f = true;
		if(is_file($full_path) || is_link($full_path))
		{
			if(@unlink($full_path))
			return true;
			return false;
		}
		elseif(is_dir($full_path))
		{
			if($handle = opendir($full_path))
			{
				while(($file = readdir($handle)) !== false)
				{
					if($file == "." || $file == "..")
					continue;
	
					if(!self::deleteDirFilesEx($path."/".$file, $bIsPathFull))
					$f = false;
				}
				closedir($handle);
			}
			if(!@rmdir($full_path))
			return false;
			return $f;
		}
		return false;
	}

	/**
	 * Допустим подае такой $FIELDS:
	 * array(
	 * 		"TRACKING_ID" => "D6Jvd38Mpa",
	 * 		"DELIVERY" => array(
	 * 			"ID" => 543,
	 * 			"CHECKED" => "Y",
	 * 		),
	 * 		"key1" => "value1",
	 * 		"key2" => "value2",
	 * 		"key3" => "value3",
	 * 	)
	 * Тогда файлы шаблона выглядят так:
	 * <?php
	 * return <<<HTML
	 * 
	 * Идентификационный номер отправления: 
	 * TRACKING_ID: <b>$TRACKING_ID</b><br />
	 * DELIVERY_ID: <b>$DELIVERY_ID</b><br />
	 * DELIVERY_CHECKED: <b>$DELIVERY_CHECKED</b><br />
	 * key1: <b>$key1</b><br />
	 * key2: <b>$key2</b><br />
	 * key3: <b>$key3</b><br />
	 * 
	 * HTML;
	 * ?>
	 */
	function getTemplateMessage($templateFile, $FIELDS, &$ERR_MSG = array()) {
		if( !is_file($templateFile) ) {
			$ERR_MSG[] ="Неверно указан файл шаблона (".$templateFile.")";
			return '';
		}
		//d($FIELDS, '$FIELDS');
		foreach($FIELDS as $varName => $varValue) {
			if( !is_array($varValue) ) {
				$arNewVar = array($varName => $varValue);
				extract($arNewVar, EXTR_PREFIX_SAME, "TPL_");
			}
			else {
				foreach($varValue as $subVarName => $subVarValue) {
					if( !is_array($subVarValue) ) {
						$arNewSubVar = array($varName."_".$subVarName => $subVarValue);
						extract($arNewSubVar, EXTR_PREFIX_SAME, "TPL_");
					}
				}
			}
		}
		return include $templateFile;
	}

	/////////////////////////////
	/// CONNECTING LESS FILES ///
	static private $_arLessFiles = array();
	static private $_bLessProduction = false;
	static private $_lessJSPath = null;
	static private $_bLessFilesConnected = false;
	static private $_bLessJSHeadConnected = false;
	static private $_bConnectLessJSFileAfterLessFiles = false;
	static public function getLessHead() {
		$returnString = '';
		foreach(self::$_arLessFiles as $lessFilePath) {
			if(!self::$_bLessProduction) {
				$returnString .= '<link rel="stylesheet/less" type="text/css" href="'.$lessFilePath.'">'."\n";
			}
			else {
				$returnString .= '<link rel="stylesheet" type="text/css" href="'.$lessFilePath.'.css">'."\n";
			}
		}
		return $returnString;
	}
	static public function getLessJSHead() {
		$returnString = '';
		if( self::$_lessJSPath ) {
			$returnString .= '<script type="text/javascript"> less = { env: \'development\' }; </script>'."\n";
			$returnString .= '<script type="text/javascript" src="'.self::$_lessJSPath.'"></script>'."\n";
			$returnString .= '<script type="text/javascript">less.watch();</script>'."\n";
		}
		return $returnString;
	}
	static public function showLessHead() {
		global $APPLICATION;
		$APPLICATION->AddBufferContent('bxutils::getLessHead');
		self::$_bLessFilesConnected = true;
		if( self::$_bConnectLessJSFileAfterLessFiles ) {
			$APPLICATION->AddBufferContent('bxutils::getLessJSHead');
			self::$_bConnectLessJSFileAfterLessFiles = false;
			self::$_bLessJSHeadConnected = true;
		}
	}
	static public function showLessJSHead($bWaitWhileLessFilesConnected = true) {
		if( $bWaitWhileLessFilesConnected && !self::$_bLessFilesConnected ) {
			self::$_bConnectLessJSFileAfterLessFiles = true;
			return;
		}
		global $APPLICATION;
		$APPLICATION->AddBufferContent("bxutils::getLessJSHead");
		self::$_bLessJSHeadConnected = true;
	}
	static public function setLessJSPath($lessJSPath, $bShowLessHead = true) {
		if( strpos($lessJSPath, 'less')===false || substr($lessJSPath, -3)!=".js" ) {
			return false;
		}
		if( is_file($_SERVER["DOCUMENT_ROOT"].$lessJSPath) ) {
			self::$_lessJSPath = $lessJSPath;
		}
		elseif( is_file($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/".$lessJSPath) ) {
			$lessJSPath = str_replace(
				array('//', '///'), array('/', '/'),
				SITE_TEMPLATE_PATH."/".$lessJSPath
			);
			self::$_lessJSPath = $lessJSPath;
		}
		if( $bShowLessHead ) {
			if( !self::$_bLessFilesConnected ) {
				self::$_bConnectLessJSFileAfterLessFiles = false;
				self::showLessHead();
			}
			if( !self::$_bLessJSHeadConnected ) {
				self::showLessJSHead();
			}
		}
		return true;
	}
	static public function getLessJSPath() {
		return self::$_lessJSPath;
	}
	static public function addLess($lessFilePath) {
		if( !in_array($lessFilePath, self::$_arLessFiles) ) {
			if( substr($lessFilePath, -5) == ".less" ) {
				if( is_file($_SERVER["DOCUMENT_ROOT"].$lessFilePath)
					|| (
						is_file($_SERVER["DOCUMENT_ROOT"].$lessFilePath.".css")
							&& self::$_bLessProduction)
				) {
					self::$_arLessFiles[] = $lessFilePath;
					return true;
				}
				elseif(
					is_file($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/".$lessFilePath)
					|| (
						is_file($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/".$lessFilePath.".css")
						&& self::$_bLessProduction
					)
				) {
					self::$_arLessFiles[] = SITE_TEMPLATE_PATH."/".$lessFilePath;
					return true;
				}
			}
		}
		return false;
	}
	static public function getLessFilesList() {
		return self::$_arLessFiles;
	}
	/**
	 * @static
	 * @param $component
	 * @param null $lessFilePath
	 */
	static public function addComponentLess($component, $lessFilePath = null) {
		/**
		 * @var CMain $APPLICATION
		 * @var CBitrixComponent $component
		 */
		$templateFolder = null;
		if($component instanceof CBitrixComponent) {
			$templateFolder = $component->__template->__folder;
		}
		elseif($component instanceof CBitrixComponentTemplate) {
			$template = &$component;
			$templateFolder = $template->__folder;
		}
		elseif( is_string($component) ) {
			if(
				($bxrootpos = strpos($component, BX_ROOT."/templates")) !== false
				||
				($bxrootpos = strpos($component, BX_ROOT."/components")) !== false
			) {
				$component = substr($component, $bxrootpos);
			}
			if( ($extpos = strrpos($component, ".php")) !== false
				|| ($extpos = strrpos($component, ".less")) !== false
			) {
				if( $dirseppos = strrpos($component, "/") ) {
					$templateFolder = substr($component, 0, $dirseppos);
					if($lessFilePath == null && strrpos($component, ".less") !== false) {
						$lessFilePath = substr($component, $dirseppos);
						while( substr($lessFilePath, 0, 1) == "/" ) {
							$lessFilePath = substr($lessFilePath, 1);
						}
					}
				}
			}
			else {
				$templateFolder = $component;
			}
		}
		if( $lessFilePath == null ) {
			if( is_file($_SERVER["DOCUMENT_ROOT"].$templateFolder."/style.less")
				|| (is_file($_SERVER["DOCUMENT_ROOT"].$templateFolder."/style.less.css")
					&& self::$_bLessProduction)
			) {
				$lessFilePath = str_replace(
					array('//', '///'), array('/', '/'),
					$templateFolder."/style.less"
				);
				if( !in_array($lessFilePath, self::$_arLessFiles) ) {
					self::$_arLessFiles[] = $lessFilePath;
					return true;
				}
				return true;
			}
		}
		elseif( is_file($_SERVER["DOCUMENT_ROOT"].$templateFolder."/".$lessFilePath)
			|| (is_file($_SERVER["DOCUMENT_ROOT"].$templateFolder."/".$lessFilePath.".css")
				&& self::$_bLessProduction )
		) {
			$lessFilePath = str_replace(
				array('//', '///'), array('/', '/'),
				$templateFolder."/".$lessFilePath
			);
			if( substr($lessFilePath, -5) == ".less" ) {
				if( !in_array($lessFilePath, self::$_arLessFiles) ) {
					self::$_arLessFiles[] = $lessFilePath;
					return true;
				}
			}
		}
		return false;
	}
	static public function setLessProductionReady($bCompiled = true) {
		self::$_bLessProduction = ($bCompiled)?true:false;
	}

	///////////////////////////////////
	/// CONNECTNG DEFERRED JS FILES ///
	static private $_arDeferredJSFiles = array();
	static public function addDeferredJS($jsFilePath) {
		if( !in_array($jsFilePath, self::$_arDeferredJSFiles) ) {
			if( substr($jsFilePath, -3) == ".js" ) {
				if( is_file($_SERVER["DOCUMENT_ROOT"].$jsFilePath) ) {
					self::$_arDeferredJSFiles[] = $jsFilePath;
					return true;
				}
				elseif( is_file($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/".$jsFilePath) ) {
					self::$_arDeferredJSFiles[] = SITE_TEMPLATE_PATH."/".$jsFilePath;
					return true;
				}
			}
		}
		return false;
	}
	static public function getDeferredJS() {
		$returnString = '';
		foreach(self::$_arDeferredJSFiles as $jsFilePath) {
			$returnString .= '<script type="text/javascript" src="'.$jsFilePath.'"></script>'."\n";
		}
		return $returnString;
	}
	static public function getDeferredJSFilesList() {
		return self::$_arDeferredJSFiles;
	}
	static public function showDeferredJS() {
		global $APPLICATION;
		$APPLICATION->AddBufferContent('bxutils::getDeferredJS');
	}
	/**
	 * @static
	 * @param CBitrixComponent $component
	 * @param null $jsFilePath
	 */
	static public function addComponentDeferredJS($component, $jsFilePath = null) {
		/**
		 * @var CMain $APPLICATION
		 * @var CBitrixComponent $component
		 */
		$templateFolder = null;
		if($component instanceof CBitrixComponent) {
			$templateFolder = $component->__template->__folder;
		}
		elseif($component instanceof CBitrixComponentTemplate) {
			$template = &$component;
			$templateFolder = $template->__folder;
		}
		elseif( is_string($component) ) {
			if(
				($bxrootpos = strpos($component, BX_ROOT."/templates")) !== false
				||
				($bxrootpos = strpos($component, BX_ROOT."/components")) !== false
			) {
				$component = substr($component, $bxrootpos);
			}
			if( ($extpos = strrpos($component, ".php")) !== false
				|| ($extpos = strrpos($component, ".js")) !== false
			) {
				if( $dirseppos = strrpos($component, "/") ) {
					$templateFolder = substr($component, 0, $dirseppos);
					if($jsFilePath == null && strrpos($component, ".js") !== false) {
						$jsFilePath = substr($component, $dirseppos);
						while( substr($jsFilePath, 0, 1) == "/" ) {
							$jsFilePath = substr($jsFilePath, 1);
						}
					}
				}
			}
			else {
				$templateFolder = $component;
			}
		}
		if( $jsFilePath == null ) {
			if( is_file($_SERVER["DOCUMENT_ROOT"].$templateFolder."/script_deferred.js") ) {
				$jsFilePath = str_replace(
					array('//', '///'), array('/', '/'),
					$templateFolder."/script_deferred.js"
				);
				if( !in_array($jsFilePath, self::$_arDeferredJSFiles) ) {
					self::$_arDeferredJSFiles[] = $jsFilePath;
					return true;
				}
				return true;
			}
		}
		elseif( is_file($_SERVER["DOCUMENT_ROOT"].$templateFolder."/".$jsFilePath) ) {
			$jsFilePath = str_replace(
				array('//', '///'), array('/', '/'),
				$templateFolder."/".$jsFilePath
			);
			if( substr($jsFilePath, -3) == ".js" ) {
				if( !in_array($jsFilePath, self::$_arDeferredJSFiles) ) {
					self::$_arDeferredJSFiles[] = $jsFilePath;
					return true;
				}
			}
		}
		return false;
	}

	static public function bxDateToArray($bxDateString, $dayStep = 0) {
		if ($bxDateString == 'today') {
			$bxDateString = ConvertTimeStamp(time(), "FULL");
		}
		$itemDate = array();
		$itemDate["STRING"] = $bxDateString;
		$itemDate["Year"] = ConvertDateTime($itemDate["STRING"], "YYYY");
		$itemDate["Month"] = ConvertDateTime($itemDate["STRING"], "MM");
		$itemDate["Day"] = ConvertDateTime($itemDate["STRING"], "DD");
		$itemDate["Hour"] = ConvertDateTime($itemDate["STRING"], "HH");
		$itemDate["Minute"] = ConvertDateTime($itemDate["STRING"], "MI");
		$itemDate["Second"] = ConvertDateTime($itemDate["STRING"], "SS");

		$itemDate["Day"] = $itemDate["Day"] + $dayStep;

		$itemDate["TIME_STAMP"] = mktime(
			$itemDate["Hour"],
			$itemDate["Minute"],
			$itemDate["Second"],
			$itemDate["Month"],
			$itemDate["Day"],
			$itemDate["Year"]
		);
		$itemDate["STRING"] = ConvertTimeStamp($itemDate["TIME_STAMP"], "SHORT");
		$itemDate["STRING_FULL"] = ConvertTimeStamp($itemDate["TIME_STAMP"], "FULL");
		//echo $itemTimeStamp."<br />";
		//echo "|".$arItem["DATE"]."|".date("d.m.Y", $itemTimeStamp)."<br />";

		/************************************************************************************
		 *** Постфиксы для названий ключей чассивов дней недели и месяцев для русского языка
		 ***
		 ***	Именительный	Номинатив (Nominative)		Кто? Что?		Ru, RuN
		 ***	Родительный 	Генитив (Genitive)			Кого? Чего?		RuG
		 ***	Дательный		Датив (Dative)		 		Кому? Чему?		RuD
		 ***	Винительный		Аккузатив (Accusative)		Кого? Что?		RuA
		 ***	Творительный	Аблатив (Instrumentative)	Кем? Чем?		RuI
		 ***	Предложный		Препозитив (Preposition)	О ком? О чём?	RuP
		 ***/

		switch($itemDate["Month"]) {
			case 1:
				$itemDate["MonthEn"] = "January";
				$itemDate["MonthRu"] = $itemDate["MonthRuN"] = "Январь";
				$itemDate["MonthRuG"] = "Января";
				break;
			case 2:
				$itemDate["MonthEn"] = "February";
				$itemDate["MonthRu"] = $itemDate["MonthRuN"] = "Февраль";
				$itemDate["MonthRuG"] = "Февраля";
				break;
			case 3:
				$itemDate["MonthEn"] = "March";
				$itemDate["MonthRu"] = $itemDate["MonthRuN"] = "Март";
				$itemDate["MonthRuG"] = "Марта";
				break;
			case 4:
				$itemDate["MonthEn"] = "April";
				$itemDate["MonthRu"] = $itemDate["MonthRuN"] = "Апрель";
				$itemDate["MonthRuG"] = "Апреля";
				break;
			case 5:
				$itemDate["MonthEn"] = "May";
				$itemDate["MonthRu"] = $itemDate["MonthRuN"] = "Май";
				$itemDate["MonthRuG"] = "Мая";
				break;
			case 6:
				$itemDate["MonthEn"] = "June";
				$itemDate["MonthRu"] = $itemDate["MonthRuN"] = "Июнь";
				$itemDate["MonthRuG"] = "Июня";
				break;
			case 7:
				$itemDate["MonthEn"] = "July";
				$itemDate["MonthRu"] = $itemDate["MonthRuN"] = "Июль";
				$itemDate["MonthRuG"] = "Июля";
				break;
			case 8:
				$itemDate["MonthEn"] = "August";
				$itemDate["MonthRu"] = $itemDate["MonthRuN"] = "Август";
				$itemDate["MonthRuG"] = "Августа";
				break;
			case 9:
				$itemDate["MonthEn"] = "September";
				$itemDate["MonthRu"] = $itemDate["MonthRuN"] = "Сентябрь";
				$itemDate["MonthRuG"] = "Сентября";
				break;
			case 10:
				$itemDate["MonthEn"] = "October";
				$itemDate["MonthRu"] = $itemDate["MonthRuN"] = "Октябрь";
				$itemDate["MonthRuG"] = "Октября";
				break;
			case 11:
				$itemDate["MonthEn"] = "November";
				$itemDate["MonthRu"] = $itemDate["MonthRuN"] = "Ноябрь";
				$itemDate["MonthRuG"] = "Ноября";
				break;
			case 12:
				$itemDate["MonthEn"] = "December";
				$itemDate["MonthRu"] = $itemDate["MonthRuN"] = "Декабрь";
				$itemDate["MonthRuG"] = "Декабря";
				break;
		}

		$itemDate["DayOfWeek"] = date("w", $itemDate["TIME_STAMP"]);
		switch ($itemDate["DayOfWeek"]) {
			case 1:
				$itemDate["DayOfWeekRu"] = $itemDate["DayOfWeekRuN"] = "Понедельник";
				$itemDate["DayOfWeekRuA"] = "Понедельник";
				$itemDate["DayOfWeekEn"] = "Monday";
				break;
			case 2:
				$itemDate["DayOfWeekRu"] = "Вторник";
				$itemDate["DayOfWeekRuA"] = "Вторник";
				$itemDate["DayOfWeekEn"] = "Tuesday";
				break;
			case 3:
				$itemDate["DayOfWeekRu"] = "Среда";
				$itemDate["DayOfWeekRuA"] = "Среду";
				$itemDate["DayOfWeekEn"] = "Wednesday";
				break;
			case 4:
				$itemDate["DayOfWeekRu"] = "Четверг";
				$itemDate["DayOfWeekRuA"] = "Четверг";
				$itemDate["DayOfWeekEn"] = "Thursday";
				break;
			case 5:
				$itemDate["DayOfWeekRu"] = "Пятница";
				$itemDate["DayOfWeekRuA"] = "Пятницу";
				$itemDate["DayOfWeekEn"] = "Friday";
				break;
			case 6:
				$itemDate["DayOfWeekRu"] = "Суббота";
				$itemDate["DayOfWeekRuA"] = "Субботу";
				$itemDate["DayOfWeekEn"] = "Saturday";
				break;
			case 7:
				$itemDate["DayOfWeekRu"] = "Воскресенье";
				$itemDate["DayOfWeekRuA"] = "Воскресенье";
				$itemDate["DayOfWeekEn"] = "Sunday";
		}

		return $itemDate;
	}

	/****************************************************
	 * Эти ф-ии позволяют работать с двумя				*
	 * массивами, в которых есть соотношения			*
	 * значений по поряковому номеру.					*
	 * Удобно использоват для разработки фотогалереи	*
	 * на множественных свойствах.						*
	 * т.е. есть три множ. св-ва						*
	 * ORIGINAL, DETAIL, PREVIEW.						*
	 * По сути это одни и те же фотки только с			*
	 * разным разрешением. Потому необходимо			*
	 * определять номер множ. значения в одном			*
	 * свойстве, по номеру множ. значения (фотки)		*
	 * в другом свойсте									*
	 ****************************************************/
	/**
	 *	Ф-ия показывает порядкоывай номер ключа массива
	 * @param <array> $array	- массив
	 * @param <string> $key		- искомый ключ
	 * @return <int | false>	- проядковый номер ключа
	 */
	static public function getNumOfKey($array, $key) {
		$array = array_keys($array);
		$array = array_flip($array);
		if ( array_key_exists($key, $array) ) {
			return $array[$key];
		}
		else return false;
	}

	/**
	 * Ф-ия получает имя ключа по порядковому номеру элемента
	 * @param <array> $array			- масстив
	 * @param <int> $num				- порядковый номер элемента
	 * @return <string | int |false>	- ключ элемента под искомым порядковым номером
	 */
	static public function getKeyBySeqNum($array, $num) {
		$arrayNums = array_keys($array);
		if ( array_key_exists($num, $arrayNums) ) {
			return $arrayNums[$num];
		}
		else return false;
	}

	/**
	 *	Ф-ия находит имя ключа в массиве $arrayHaystack порядковый номер которого
	 *	тот же что и у ключа $needleKey в массиве $arrayWithKey
	 * @param <array> $arrayHaystack
	 * @param <string | int> $needleKey
	 * @param <array> $arrayWithKey
	 * @return <string | int | false>
	 */
	static public function getKeyOfSameNumber($arrayHaystack, $needleKey, $arrayWithKey) {
		//d($arrayHaystack, true);
		//d($arrayWithKey, true);
		//d($needleKey);
		//d(getNumOfKey($arrayWithKey, $needleKey));
		return self::getKeyBySeqNum(
			$arrayHaystack,
			self::getNumOfKey($arrayWithKey, $needleKey)
		);
	}
	static public function getKeyOfSameNumberEx(&$arrayHaystack, $needleKey, $arrayWithKey) {
		//	d($arrayHaystack, true);
		//	d($arrayWithKey, true);
		//	d($needleKey);
		//	d(getNumOfKey($arrayWithKey, $needleKey));
		if (count($arrayWithKey) <= count($arrayHaystack)) {
			return self::getKeyBySeqNum(
				$arrayHaystack,
				self::getNumOfKey($arrayWithKey, $needleKey)
			);
		}
		else {
			return $needleKey;
		}
	}

	/**
	 * Debug data print
	 * @param mixed $mixed
	 * @param mixed $collapse
	 */
	static public function debug($mixed, $collapse = null, $bPrint = true) {
		if(!$bPrint) {
			return;
		}
		static $arCountFuncCall = 0;
		static $arCountFuncCallWithTitleKey = array();
		$arCountFuncCall++;

		$bCollapse = false;
		if($collapse !== null) {
			$bCollapse = true;
			if( is_string($collapse) && strlen($collapse)>0) {
				if( !@isset($arCountFuncCallWithTitleKey[$collapse]) ) {
					$arCountFuncCallWithTitleKey[$collapse] = 0;
				}
				$arCountFuncCallWithTitleKey[$collapse]++;

				$elemTitle = $collapse."#".$arCountFuncCallWithTitleKey[$collapse];
				$elemId = rand(1,500).$collapse."#".$arCountFuncCallWithTitleKey[$collapse];
			}
			else {
				$elemTitle = "dData#".$arCountFuncCall;
				$elemId = rand(1,500).$arCountFuncCall;
			}
			$elemId = str_replace(array("'", '"'), "_", $elemId);
		}
		?>
	<?php if($bCollapse):?>
		<a	href="javascript:void(0)"
			  style="display: block;background: white; border:1px dotted #5A82CE;padding:3px; text-shadow: none; color: #5A82CE;"
			  onclick="document.getElementById('<?php echo $elemId?>').style.display = ( document.getElementById('<?php echo $elemId?>').style.display == 'none')?'block':'none'"
				>
			<?php echo $elemTitle?>
		</a>
			<div id="<?php echo $elemId?>" style="text-align: left; display:none; background-color: #b1cdef; position: absolute; z-index: 10000;">
		<?php endif?>

		<pre style="text-align: left; text-shadow: none; color: black;"><?php print_r($mixed);?></pre>

		<?php if ($bCollapse):?>
			</div>
		<?php endif;
	}
}
?>