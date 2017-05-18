<?php
//DB Configuration
$cfg_db_host = '127.0.0.1';
$cfg_db_user = 'wpa';
$cfg_db_pass = 'wpapass';
$cfg_db_name = 'wpa';

//Dicts settings
//Change to folder where we need to upload dicts
$cfg_dicts_target_folder = "C:/wamp64/www/wpa2auditor-dev/web/dicts/";
$cfg_dicts_maxFileSize = 1e+8;

//Site
$cfg_site_url = 'http://localhost/wpa2auditor-dev/web/';

//Tasks
$cfg_tasks_target_folder = "C:/wamp64/www/wpa2auditor-dev/web/tasks/";
$cfg_tasks_max_file_size = 1e+8;
$cfg_tasks_allowed_formats = ['.cap', '.hccapx'];

//Tools

//CAP2HCCAPX
//Check system on which php is running, if we use win version of cap2hccapx
if ( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' ) {
	$cfg_tools_cap2hccapx = "C:/wamp64/www/wpa2auditor-dev/web/cap2hccap/cap2hccapx.exe";
} else {
	$cfg_tools_cap2hccapx = "C:/wamp64/www/wpa2auditor-dev/web/cap2hccap/cap2hccapx.bin";
}

//WPACLEAN
$cfg_tools_wpaclean = "C:/wamp64/www/wpa2auditor-dev/web/wpaclean/wpaclean";

$cfg_tools_cowpatty = "";

?>