<?php

//DB Configuration
$cfg_db_host = '127.0.0.1';
$cfg_db_user = 'wpa';
$cfg_db_pass = 'wpapass';
$cfg_db_name = 'wpa';

//Dicts settings
$cfg_dicts_target_folder = "C:/wamp64/www/wpa2auditor-dev/web/dicts/";
$cfg_dicts_max_file_size = 1e+8;
$cfg_dicts_allowed_ext = ['.txt', '.zip', '.rar', '.7z', '.lst', '.dct', '.gz', '.tar', '.txt.gz'];

//Site
$cfg_site_url = 'http://localhost/wpa2auditor-dev/web/';

//Tasks
$cfg_tasks_target_folder = "C:/wamp64/www/wpa2auditor-dev/web/tasks/";
$cfg_tasks_max_file_size = 1e+8;
$cfg_tasks_allowed_ext = ['.cap', '.hccapx'];

//Tools

//CAP2HCCAPX
if ( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' ) {
	$cfg_tools_cap2hccapx = "C:/wamp64/www/wpa2auditor-dev/web/cap2hccapx/cap2hccapx.exe";
} else {
	$cfg_tools_cap2hccapx = "C:/wamp64/www/wpa2auditor-dev/web/cap2hccapx/cap2hccapx.bin";
}

//WPACLEAN
$cfg_tools_wpaclean = "C:/wamp64/www/wpa2auditor-dev/web/wpaclean/wpaclean";

$cfg_tools_cowpatty = "";

?>