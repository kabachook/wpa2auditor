<?php

//DB Configuration
$cfg_db_host = '127.0.0.1';
$cfg_db_user = 'wpa';
$cfg_db_pass = 'wpapass';
$cfg_db_name = 'wpa';

//Dicts
$cfg_dicts_max_file_size = 1e+16;
$cfg_dicts_allowed_ext = [ '.txt', '.zip', '.rar', '.7z', '.lst', '.dct', '.gz', '.tar', '.txt.gz' ];

//Tasks
$cfg_tasks_max_file_size = 1e+16;
$cfg_tasks_allowed_ext = [ '.cap', '.hccapx' ];

//Change settings depending on the server (localhost or inlovewith)
if ( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' ) {
	
	//Site
	$cfg_site_url = 'http://localhost/wpa2auditor-dev/web/';
	
	//Dicts settings
	$cfg_dicts_target_folder = "C:/wamp64/www/wpa2auditor-dev/web/dicts/";
	
	//Tools
	//CAP2HCCAPX
	$cfg_tools_cap2hccapx = "C:/wamp64/www/wpa2auditor-dev/web/cap2hccapx/cap2hccapx.exe";

	//Tasks settings
	$cfg_tasks_target_folder = "C:/wamp64/www/wpa2auditor-dev/web/tasks/";
	
} else {
	
	//Tasks settings
	$cfg_tasks_target_folder = "/var/www/html/dev/web/tasks/";
	
	//Dicts settings
	$cfg_dicts_target_folder = "/var/www/html/dev/web/dicts/";
	
	//Site
	$cfg_site_url = 'http://inlovewith.space/dev/web/';
	
	//Tools
	//CAP2HCCAPX
	$cfg_tools_cap2hccapx = "/var/www/html/dev/web/cap2hccapx/cap2hccapx.bin";
	
}
?>