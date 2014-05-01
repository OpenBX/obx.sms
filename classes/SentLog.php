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
		'ID'				=> array('L' => 'ID'),
		'EXTERNAL_ID'		=> array('L' => 'EXTERNAL_ID'),
		'PROVIDER_ID'		=> array('L' => 'PROVIDER_ID'),
		'COUNTRY_CODE'		=> array('L' => 'COUNTRY_CODE'),
		'TEL_NO'			=> array('L' => 'TEL_NO'),
		'PHONE'				=> array('L' => 'PHONE'),
		'STATUS'			=> array('L' => 'STATUS'),
		'STATUS_EXT'		=> array('L' => 'STATUS_EXT'),
		'TEXT'				=> array('L' => 'TEXT'),
		'SENT_TIME'			=> array('L' => 'SENT_TIME'),
		'DELIVERED_TIME'	=> array('L' => 'DELIVERED_TIME'),
	);

	protected $_arTableUnique = array(
		'uq_obx_sms_log_ext_id' => array('PROVIDER_ID', 'EXTERNAL_ID'),
		'ix_obx_sms_log_phone' => array('COUNTRY_CODE', 'TEL_NO'),
		'ix_obx_sms_log_tel' => array('TEL_NO'),
		'ix_obx_sms_log_country' => array('COUNTRY_CODE')
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
			'EXTERNAL_ID'		=> self::FLD_T_IDENT,
			'PROVIDER_ID'		=> self::FLD_T_PK_ID,
			'COUNTRY_CODE'		=> self::FLD_T_INT,
			'TEL_NO'			=> self::FLD_T_INT,
			'PHONE'				=> self::FLD_T_INT,
			'STATUS'			=> self::FLD_T_INT,
			'STATUS_EXT'		=> self::FLD_T_STRING,
			'TEXT'				=> self::FLD_T_STRING,
			'SENT_TIME'			=> self::FLD_T_NO_CHECK,
			'DELIVERED_TIME'	=> self::FLD_T_NO_CHECK
		);
	}
}

class SentLog extends DBSimpleStatic {}
SentLog::__initDBSimple(SentLogDBS::getInstance());

