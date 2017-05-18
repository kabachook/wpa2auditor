<?php

include( '../Handshake.class.php' );
include( '../Task.class.php' );

if ( isset( $_FILES[ 'upfile' ] ) ) {
	$HS = new Handshake( $_FILES[ 'upfile' ] );
	$arr = $HS->get_array_of_handshakes();
	foreach ( $arr as $hnsd ) {
		try {
		$tmp = new Task( $hnsd, $user_id = 2, $task_name = "sfdgsdgfsfg" );
		} catch (Exception $e) {
			var_dump($e);
			echo $e->getMessage();
			echo $e->getCode();
		}
		var_dump( $tmp );
	}
}
?>