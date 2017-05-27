<?php
//Connect to db
require( 'db.php' );

//JSON answer
$json = array();

//Get last job from queue
$sql = "SELECT * FROM tasks WHERE status NOT IN ('2', '3') ORDER BY id DESC LIMIT 1";
$result = $mysqli->query( $sql );

if ( $result->num_rows == 0 ) {
	//There is no tasks or all done
	$json[ 'id' ] = "-1";
	echo json_encode( $json );
	die( 0 );
}

$result = $result->fetch_all( MYSQLI_ASSOC );
$task_id = $result[ 0 ][ 'id' ];
$type = $result[ 0 ][ 'type' ];

$ntlm = false;
if ( $type == 1 )
	$ntlm = true;

$json[ 'id' ] = $task_id;
$json[ 'name' ] = $result[ 0 ][ 'task_name' ];
$json[ 'type' ] = $type;

if ( !$ntlm ) {
	$json[ 'url' ] = $result[ 0 ][ 'site_path' ];
	$json[ 'hash' ] = bin2hex( $result[ 0 ][ 'task_hash' ] );
} else {
	$json[ 'username' ] = $result[ 0 ][ 'username' ];
	$json[ 'challenge' ] = $result[ 0 ][ 'challenge' ];
	$json[ 'response' ] = $result[ 0 ][ 'response' ];
}

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
	$sql = "SELECT site_path, hash FROM dicts WHERE id='" . $id . "'";
	$result = $mysqli->query( $sql );
	$result = $result->fetch_all( MYSQLI_ASSOC );
	array_push( $dicts, array(
		"dict_id" => $id,
		"dict_url" => $result[ 0 ][ 'site_path' ],
		"dict_hash" => bin2hex( $result[ 0 ][ 'hash' ] ),
	) );
}

$json[ 'dicts' ] = $dicts;

//Send json
echo json_encode( $json );
?>