-- messages
create table if not exists obx_sms_log (
	ID int(11) not null auto_increment,
	EXTERNAL_ID varchar(255) null,
	PROVIDER_ID varchar(16) not null,
	COUNTRY_CODE int(3) not null,
	TEL_NO int(11) not null,
	PHONE int(13) not null,
	STATUS int(2) not null default 1,
	STATUS_EXT varchar(255) not null,
	TEXT varchar(350) null default '',
	SENT_TIME timestamp not null,
	DELIVERED_TIME timestamp null,
	primary key(ID),
	unique uq_obx_sms_log_ext_id(PROVIDER_ID,EXTERNAL_ID),
	key ix_obx_sms_log_phone(COUNTRY_CODE, TEL_NO),
	key ix_obx_sms_log_tel(TEL_NO),
	key ix_obx_sms_log_country(COUNTRY_CODE)
);