<?php
//Connect to db
require( 'db.php' );

//CRUTCH

//Find all uncomplete tasks
$sql = "SELECT id FROM tasks WHERE status NOT IN('2')";
$task_list = $mysqli->query( $sql )->fetch_all( MYSQL_ASSOC );
$failed_list = array();
foreach ( $task_list as $task_id ) {

	//Find all tasks which doesn't have dict with status 0
	$sql = "SELECT * FROM tasks_dicts WHERE net_id='" . $task_id[ 'id' ] . "' AND status NOT IN('1')";
	$nr = $mysqli->query( $sql )->num_rows;
	if ( $nr == 0 )
		array_push( $failed_list, $task_id[ 'id' ] );
}
foreach ( array_unique( $failed_list ) as $f_id ) {
	//Change status to FAILED for all tasks without dict
	$sql = "UPDATE tasks SET status='3' WHERE id='" . $f_id . "'";
	$mysqli->query( $sql );
}

//JSON answer
$json = array();

//Get last job from queue
$sql = "SELECT id, name, filename, thash FROM tasks WHERE status=0 ORDER BY id DESC LIMIT 1";
$result = $mysqli->query( $sql );

if ($result->num_rows == 0) {
	//There is no tasks or all done
	$json['id'] = -1;
	echo json_encode($json);
	die(0);
}

$result = $result->fetch_all( MYSQLI_ASSOC );
$task_id = $result[ 0 ][ 'id' ];
$json[ 'id' ] = $task_id;
$json[ 'name' ] = $result[ 0 ][ 'name' ];
$json[ 'url' ] = $cfg_site_url . "tasks//" . $result[ 0 ][ 'filename' ];
$json[ 'hash' ] = bin2hex( $result[ 0 ][ 'thash' ] );

//Get dicts for this task
//Get all dicts which not used 
$sql = "SELECT dict_id FROM tasks_dicts WHERE net_id='" . $task_id . "'";
$result = $mysqli->query( $sql );
$result = $result->fetch_all( MYSQLI_ASSOC );
$dicts_id = array();
foreach ( $result as $row ) {
	array_push( $dicts_id, $row[ 'dict_id' ] );
}

//For all dicts_id get it's filename to generate url to download
$dicts = array();
foreach ( $dicts_id as $id ) {
	$sql = "SELECT dpath, dhash FROM dicts WHERE id='" . $id . "'";
	$result = $mysqli->query( $sql );
	$result = $result->fetch_all( MYSQLI_ASSOC );
	array_push( $dicts, array(
		"dict_id" => $id,
		"dict_url" => $result[ 0 ][ 'dpath' ],
		"dict_hash" => bin2hex( $result[ 0 ][ 'dhash' ] ),
	) );
}

$json[ 'dicts' ] = $dicts;

//Send json
echo json_encode( $json );
?>
