<?php
//require
require( 'conf.php' );

//validate 32 char key
function valid_key( $key ) {
	//simple check md5 format
	return preg_match( '/^[a-f0-9]{32}$/', strtolower( $key ) );
}

function check_cookie( $key ) {
	require( 'db.php' );
	$sql = "SELECT nick FROM users WHERE userkey=UNHEX('" . $key . "')";
	$result = $mysqli->query( $sql );
	if ( $result->num_rows == 1 ) {
		return true;
	} else {
		return false;
	}
}

//------------------- Actions with user --------------------
if ( isset( $_POST[ 'rec_valid' ] ) ) {
	require_once( 'db.php' );
	
	//if we have email, validate it
	/*$mail = Null;
	if (isset($_POST['mail']))
	    if (validEmail($_POST['mail']))
	        $mail = trim($_POST['mail']);*/
	
	$mail = $_POST[ 'email' ];
	$nickname = $_POST[ 'nick' ];
	$rang = "admin";
	$userkey = md5( $nickname );
	
	//put new key in db
	$sql = "INSERT INTO users(userkey, mail, nick, rang) VALUES(UNHEX('" . $userkey . "'), '$mail', '$nickname', '$rang')
                ON DUPLICATE KEY UPDATE userkey=UNHEX('$userkey'), ts=CURRENT_TIMESTAMP()";

	$result = $mysqli->query( $sql );

	//set cookie
	setcookie( 'key', $userkey, 2147483647, '', '', false, true );
	$_COOKIE[ 'key' ] = $userkey;
}

//Set key
if ( isset( $_POST[ 'key' ] ) ) {
	if ( valid_key( $_POST[ 'key' ] ) ) {
		require_once( 'db.php' );

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
//Get nick
if ( isset( $_COOKIE[ 'key' ] ) ) {
	require_once( 'db.php' );
	$sql = "SELECT nick FROM users WHERE userkey=UNHEX('" . $_COOKIE[ 'key' ] . "')";

	$result = $mysqli->query( $sql );
	$obj = $result->fetch_object();
	$nick = $obj->nick;
}

//Remove key
if ( isset( $_POST[ 'remkey' ] ) ) {
	setcookie( 'key', '', 1, '', '', false, true );
	unset( $_COOKIE[ 'key' ] );
}
//------------------- Actions with user end --------------------

//CMS
$content = 'content/';
$keys = array( 'home', 'tasks', 'dicts', 'get_key' );
$keys_if = array( 'get_work', 'put_work' );

list( $key ) = each( $_GET );
if ( !in_array( $key, $keys ) )
	$key = 'home';

if ( in_array( $key, $keys_if ) ) {
	require( $content . $key . '.php' );
	exit;
}

$cont = $content . $key . '.php';
?>