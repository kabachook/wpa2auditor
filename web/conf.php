<?php
//DB Configuration
$cfg_db_host = '127.0.0.1';
$cfg_db_user = 'wpa';
$cfg_db_pass = 'wpapass';
$cfg_db_name = 'wpa';

//Dicts settings
//Change to folder where we need to upload dicts
$cfg_dicts_targetFolder = "C:/wamp64/www/wpa2auditor/web/dicts/";
$cfg_dicts_maxFileSize = 1e+8;

//Site
$cfg_site_url = 'http://localhost/wpa2auditor/web/';

//Tasks
$cfg_tasks_targetFolder = "C:/wamp64/www/wpa2auditor/web/tasks/";
$cfg_tasks_maxFileSize = 1e+8;

//Tools
//Check system on whick php is running, if win use win version of cap2hccapx
if ( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' ) {
	$cfg_tools_cap2hccap = "C:/wamp64/www/wpa2auditor/web/cap2hccap/cap2hccapx.exe";
} else {
	$cfg_tools_cap2hccap = "C:/wamp64/www/wpa2auditor/web/cap2hccap/cap2hccapx.bin";
}


?>