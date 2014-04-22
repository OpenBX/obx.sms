<?php
/*******************************************
 ** @product OBX:Sms Bitrix Module        **
 ** @authors                              **
 **         Maksim S. Makarov aka pr0n1x  **
 ** @license Affero GPLv3                 **
 ** @mailto rootfavell@gmail.com          **
 ** @copyright 2013 DevTop                **
 *******************************************/


namespace OBX\Sms;


use OBX\Core\DBSimple;
use OBX\Core\DBSimpleStatic;

class SentLogDBS extends DBSimple
{
	protected $_entityModuleID = 'obx.sms';
	protected $_entityEventsID = 'SentLogRow';

	protected $_mainTable = 'L';
	protected $_mainTablePrimaryKey = 'ID';
	protected $_mainTableAutoIncrement = 'ID';
	protected $_arTableList = array(
		'L'		=> 'obx_sms_log',
	);

	protected $_arTableFields = array(
		'ID' => array('L' => 'ID'),
		'EXTERNAL_ID' => array('L' => 'EXTERNAL_ID'),
		'PROVIDER_ID' => array('L' => 'PROVIDER_ID'),
		'COUNTRY_CODE' => array('L' => 'COUNTRY_CODE'),
		'TEL_NO' => array('L' => 'TEL_NO'),
		'PHONE' => array('L' => 'PHONE'),
		'TEXT' => array('L' => 'TEXT'),
		'TIME_STAMP' => array('L' => 'TIME_STAMP'),
		'DATE' => array('L' => 'DATE'),
		'STATUS' => array('L' => 'STATUS'),
		//KEY TEL_NO(TEL_NO)
	);

	protected $_arTableUnique = array(
		'obx_sms_log' => array('PROVIDER_ID', 'MESSAGE_ID'),
		'obx_sms_log_phone' => array('COUNTRY_CODE', 'TEL_NO')
	);

	protected $_arSortDefault = array('ID' => 'ASC');
	protected $_arSelectDefault = array(
		'ID',
		'EXTERNAL_ID',
		'PROVIDER_ID',
		'COUNTRY_CODE',
		'TEL_NO',
		'TEXT',
		'TIME_STAMP',
		'DATE',
		'STATUS',
	);

	function __construct() {
		$this->_arTableFieldsCheck = array(
			'ID'				=> self::FLD_T_PK_ID,
			'EXTERNAL_ID'		=> self::FLD_T_STRING,
			'PROVIDER_ID'		=> self::FLD_T_PK_ID,
			'MESSAGE_ID'
		);
	}
}

class SentLog extends DBSimpleStatic {}
SentLog::__initDBSimple(SentLogDBS::getInstance());