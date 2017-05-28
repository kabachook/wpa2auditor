<?php

//Connect to db
require( 'db.php' );

global $mysqli;

//Obtain json from client
$json = json_decode( file_get_contents( 'php://input' ), true );

if ( isset( $json[ 'alive' ] ) ) {
	$agent_key = $json[ 'alive' ];
	$sql = "UPDATE agents SET status='1' WHERE agent_key=UNHEX('" . $agent_key . "')";
	$mysqli->query( $sql );
	exit();
}

$agent_key = $json[ 'user_key' ];

//Set up nickname
$sql = "SELECT id, nick FROM users WHERE userkey=UNHEX('" . $agent_key . "')";
$result = $mysqli->query( $sql );
if ( $result->num_rows != 0 ) {
	$result = $result->fetch_object();
	$nick = $result->nick;
	$user_id = $result->id;
} else {
	$nick = "Anonymous";
	$user_id = -1;
}

$perf = $json[ 'performance' ];
$sysinfo = $json[ 'system_info' ];

$sql = "INSERT INTO agents(user_id, user_nick, agent_key, os, perf) VALUES('" . $user_id . "', '" . $nick . "', UNHEX('" . $agent_key . "'), '" . $sysinfo . "', '" . $perf . "')";
$mysqli->query( $sql );

?>