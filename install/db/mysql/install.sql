-- messages
create table if not exists obx_sms_log (
	ID int(11) not null auto_increment,
	EXTERNAL_ID varchar(255) null,
	PROVIDER_ID varchar(16) not null,
	COUNTRY_CODE int(3) not null,
	TEL_NO int(10) not null,
	PHONE int(13) not null,
	TEXT varchar(350) null default '',
	SENT_TIME timestamp not null,
	DELIVERED_TIME timestamp null,
	STATUS varchar(255) not null,
	primary key(ID),
	unique obx_sms_log(PROVIDER_ID,MESSAGE_ID),
	key obx_sms_log(COUNTRY_CODE, TEL_NO),
	KEY TEL_NO(TEL_NO)
);