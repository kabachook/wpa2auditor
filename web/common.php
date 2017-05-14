<?php

//Connect to db
require( 'db.php' );

//Check user rang
$admin = false;
if ( isset( $_COOKIE[ 'key' ] ) ) {
	$sql = "SELECT rang FROM users WHERE userkey=UNHEX('" . $_COOKIE[ 'key' ] . "')";
	$result = $mysqli->query( $sql )->fetch_all( MYSQLI_ASSOC );
	if ( $result[ 0 ][ 'rang' ] == "admin" )
		$admin = true;
}

//validate 32 char key (simple check for md5 format)
function valid_key( $key ) {
	return preg_match( '/^[a-f0-9]{32}$/', strtolower( $key ) );
}

//Set key
if ( isset( $_POST[ 'key' ] ) ) {
	if ( valid_key( $_POST[ 'key' ] ) ) {
		$key = $_POST[ 'key' ];
		$sql = "SELECT HEX(userkey) FROM users WHERE userkey=UNHEX('" . $key . "')";
		$result = $mysqli->query( $sql );

		if ( $result->num_rows == 1 ) {
			setcookie( 'key', $_POST[ 'key' ], 2147483647, '', '', false, true );
			$_COOKIE[ 'key' ] = $_POST[ 'key' ];
		} else
			$_POST[ 'remkey' ] = '1';
	}
}

//Get nickname
function getNickname() {
	global $mysqli;
	if ( isset( $_COOKIE[ 'key' ] ) && valid_key( $_COOKIE[ 'key' ] ) ) {
		$sql = "SELECT nick FROM users WHERE userkey=UNHEX('" . $_COOKIE[ 'key' ] . "')";
		$nick = $mysqli->query( $sql )->fetch_object()->nick;
		return $nick;
	}
}

//Remove key
if ( isset( $_POST[ 'remkey' ] ) ) {
	setcookie( 'key', '', 1, '', '', false, true );
	unset( $_COOKIE[ 'key' ] );
}

//CMS
$content = 'content/';
$keys = array( 'home', 'tasks', 'dicts', 'get_key', 'dicts', 'get_job', 'put_job', 'stat', 'search', 'profile' );
$keys_if = array( 'get_job', 'put_job' );

list( $key ) = each( $_GET );
if ( !in_array( $key, $keys ) )
	$key = 'home';

if ( in_array( $key, $keys_if ) ) {
	require( $content . $key . '.php' );
	exit;
}

$cont = $content . $key . '.php';
?>