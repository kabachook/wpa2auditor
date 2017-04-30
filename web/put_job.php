<?php
//Connect to db
require('db.php');

function getCountAgents($task_id) {
	global $mysqli;
	$sql = "SELECT agents FROM tasks WHERE id='" . $task_id . "'";
	$result = $mysqli->query($sql);
	$result = $result->fetch_array(MYSQLI_ASSOC);
	return $result['agents'];
}

$status = $_POST['job_status'];
$task_id = $_POST['task_id'];
$dict_id = $_POST['dict_id'];
if ($status == "start") {
	
	//Change task status
	$sql = "UPDATE tasks SET status='1', agents='" . (getCountAgents($task_id) + 1) . "' WHERE id='" . $task_id . "'";
	$mysqli->query($sql);
	
	//Change dict status
	$sql = "UPDATE tasks_dicts SET status='1' WHERE net_id='" . $task_id . "'AND dict_id='" . $dict_id . "'";
	$mysqli->query($sql);

}
if ($status == "finish") {
	
	$task_status = $_POST['task_status'];
	$dict_status = $_POST['dict_status'];
	
	if($task_status == 2) {
		//Password found
		$net_key = $_POST['net_key'];
		$sql = "UPDATE tasks SET net_key='" . $net_key . "' WHERE id='" . $task_id . "'";
		$mysqli->query($sql);
	}
	
	//Change task status
	$sql = "UPDATE tasks SET status='" . $task_status . "' WHERE id='" . $task_id . "'";
	$mysqli->query($sql);
	
	//Change dict status
	$sql = "UPDATE tasks_dicts SET status='" . $dict_status . "' WHERE net_id='" . $task_id . "'AND dict_id='" . $dict_id . "'";
	$mysqli->query($sql);
}
?>