<?php

//Shut down error reporting
error_reporting( 0 );

include( '../db.php' );
include( '../Model/Agent.class.php' );

global $mysqli;

// Paggination
// Find out how many items are in the table
$sql = "SELECT COUNT(*) as count FROM agents";
$total = $mysqli->query( $sql )->fetch_object()->count;

// How many items to list per page
$limit = 10;

// How many pages will there be
$pages = ceil( $total / $limit );

// What page are we currently on?
$page = min( $pages, filter_input( INPUT_GET, 'page', FILTER_VALIDATE_INT, array(
	'options' => array(
		'default' => 1,
		'min_range' => 1,
	),
) ) );

// Calculate the offset for the query
$offset = ( $page - 1 ) * $limit;

// Some information to display to the user
$start = $offset + 1;
$end = min( ( $offset + $limit ), $total );

if ( $_GET[ 'ajax' ] == 'table' ) {

	header( 'Content-Type: application/json' );

	$json = [];

	$sql = "SELECT * FROM agents ORDER BY id LIMIT " . $limit . " OFFSET " . $offset;
	$result = $mysqli->query( $sql )->fetch_all( MYSQL_ASSOC );
	foreach ( $result as $agent ) {
		$Agent = Agent::get_agent_from_db( $agent[ 'id' ] );
		$info = $Agent->get_all_info();
		array_push( $json, $info );
	}
	echo json_encode( $json );
	exit();
}

?>

<div class="container">

	<h2>Agents</h2>

	<!-- Table start -->
	<div id="ajaxTableDiv">
	</div>
	<!-- Table end -->

</div>