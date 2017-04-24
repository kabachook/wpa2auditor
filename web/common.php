<?php
require('conf.php');

//validate 32 char key
function valid_key($key) {
    //
    return preg_match('/^[a-f0-9]{32}$/', strtolower($key));
}

//Set key
if (isset($_POST['key'])) {
    if (valid_key($_POST['key'])) {
        require_once('db.php');
        $sql = 'SELECT HEX(userkey) FROM users WHERE userkey=UNHEX(?)';
        $stmt = $mysql->stmt_init();
        $stmt->prepare($sql);
        $stmt->bind_param('s', $_POST['key']);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 1) {
            setcookie('key', $_POST['key'], 2147483647, '', '', false, true);
            $_COOKIE['key'] = $_POST['key'];
        } else
            $_POST['remkey'] = '1';
        $stmt->close();
    }
}

//Get nick
if(isset($_COOKIE['key'])) {
	require_once('db.php');
	$sql = "SELECT nick FROM users WHERE userkey=UNHEX('" . $_COOKIE['key'] . "')";
	$mysqli = new mysqli($cfg_db_host, $cfg_db_user, $cfg_db_pass, $cfg_db_name);
	$result = $mysqli->query($sql);
	$obj = $result->fetch_object();
	$nick = $obj->nick;	
}

//Remove key
if ( isset( $_POST[ 'remkey' ] ) ) {
	setcookie( 'key', '', 1, '', '', false, true );
	unset( $_COOKIE[ 'key' ] );
}

//CMS
$content = 'content/';
$keys = array('home', 'tasks', 'dicts');
$keys_if = array('get_work', 'put_work');

list($key) = each($_GET);
if (!in_array($key,$keys))
	$key = 'home';

if (in_array($key, $keys_if)) {
    require($content.$key.'.php');
    exit;
}

$cont = $content.$key.'.php';
?>