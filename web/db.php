<?php
require('conf.php');
//connect to db
$mysqli = new mysqli($cfg_db_host, $cfg_db_user, $cfg_db_pass, $cfg_db_name);
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
?>