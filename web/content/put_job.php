<?php
//Connect to db
require( 'db.php' );

//Obtain json from client
$json = json_decode( file_get_contents( 'php://input' ), true );

function getCountAgents( $task_id ) {
	global $mysqli;
	$sql = "SELECT agents FROM tasks WHERE id='" . $task_id . "'";
	$result = $mysqli->query( $sql );
	$result = $result->fetch_array( MYSQLI_ASSOC );
	return $result[ 'agents' ];
}

$status = $json[ 'job_status' ];
$task_id = $json[ 'task_id' ];
$dict_id = $json[ 'dict_id' ];
if ( $status == "started" ) {

	//Change task status
	$sql = "UPDATE tasks SET status='1', agents='" . ( getCountAgents( $task_id ) + 1 ) . "' WHERE id='" . $task_id . "'";
	$mysqli->query( $sql );

	//Change dict status
	$sql = "UPDATE tasks_dicts SET status='1' WHERE net_id='" . $task_id . "'AND dict_id='" . $dict_id . "'";
	$mysqli->query( $sql );

}
if ( $status == "finished" ) {

	$task_status = $json[ 'task_status' ];
	$dict_status = $json[ 'dict_status' ];

	if ( $task_status == 2 ) {
		//Password found
		$net_key = $json[ 'net_key' ];
		$sql = "UPDATE tasks SET net_key='" . $net_key . "' WHERE id='" . $task_id . "'";
		$mysqli->query( $sql );
	}

	//Change task status
	$sql = "UPDATE tasks SET status='" . $task_status . "' WHERE id='" . $task_id . "'";
	$mysqli->query( $sql );

	//Change dict status
	$sql = "UPDATE tasks_dicts SET status='" . $dict_status . "' WHERE net_id='" . $task_id . "'AND dict_id='" . $dict_id . "'";
	$mysqli->query( $sql );
}

if ($status != "started" && $status != "finished") {
	http_response_code(404);
}
?>