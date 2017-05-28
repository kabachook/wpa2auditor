<?php
$sql = "SELECT * FROM users WHERE userkey=UNHEX('" . $_COOKIE['key'] . "')";
$result = $mysqli->query($sql)->fetch_object();

echo "Your userkey is " . bin2hex($result->userkey) . "<br />";
echo "Your invite is " . bin2hex($result->invite) . "<br />";
echo "You invite " . $result->invited_c . " users";

?>