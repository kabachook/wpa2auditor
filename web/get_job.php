<?php
//Connect to db
require('db.php');

//JSON Ответ
$json = array();

//Get last job from queue
$sql = "SELECT id, name, filename FROM tasks WHERE status=0 ORDER BY id DESC LIMIT 1";
$result = $mysqli->query( $sql );
$result = $result->fetch_all(MYSQLI_ASSOC);
$task_id = $result[0]['id'];
$json['id'] = $task_id;
$json['name'] = $result[0]['name'];
$json['url'] = $cfg_site_url . "tasks//" . $result[0]['filename'];

//Get dicts for this task
//Get all dicts which not used 
$sql = "SELECT dict_id FROM tasks_dicts WHERE net_id='" . $task_id . "'";
$result = $mysqli->query($sql);
$result = $result->fetch_all(MYSQLI_ASSOC);
$dicts_id = array();
foreach ($result as $row) {
	array_push($dicts_id, $row['dict_id']);
}

//For all dicts_id get it's filename to generate url to download
$dicts = array();
foreach ($dicts_id as $id) {
	$sql = "SELECT dpath, dhash FROM dicts WHERE id='" . $id . "'";
	$result = $mysqli->query($sql);
	$result = $result->fetch_all(MYSQLI_ASSOC);
	array_push($dicts, array($id, $result[0]['dpath'], bin2hex($result[0]['dhash'])));
}

$json['dicts'] = $dicts;

//Send json
echo json_encode($json);
?>