<?php

//Shut down error reporting
error_reporting( 0 );

include( '../Handshake.class.php' );
include( '../Task.class.php' );
include( '../NTLM.class.php' );
include( '../db.php' );
include( '../common.php' );

global $mysqli;

$error_message = [
	'code' => 0,
	'message' => "All is OK.",
	"type" => "success"
];

//Get user id
$user_id = getUserID();

if ( $_POST[ 'buttonUploadFile' ] == "true" ) {

	$task_name = $_POST[ 'task_name' ];

	try {
		$HS = new Handshake( $_FILES[ 'upfile' ] );
	} catch ( Exception $e ) {
		$error_message[ 'code' ] = $e->getCode();
		$error_message[ 'message' ] = $e->getMessage();
		$error_message[ 'type' ] = "danger";
	}

	$arr = $HS->get_array_of_handshakes();

	foreach ( $arr as $hnsd ) {

		try {
			$tmp = Task::create_task_from_file( $hnsd, $user_id, $task_name );
		} catch ( Exception $e ) {
			$error_message[ 'code' ] = $e->getCode();
			$error_message[ 'message' ] = $e->getMessage();
			$error_message[ 'type' ] = "danger";
		}

	}
}

if ( $_POST[ 'buttonUploadHash' ] == "true" ) {
	
	$task_name = $_POST[ 'task_name' ];

	try {
		$Hash = new NTLM( $_POST );
	} catch ( Exception $e ) {
		$error_message[ 'code' ] = $e->getCode();
		$error_message[ 'message' ] = $e->getMessage();
		$error_message[ 'type' ] = "danger";
	}

	$hash = $Hash->get_array_info();

	try {
		$tmp = Task::create_task_from_file( $hash, $user_id, $task_name );
	} catch ( Exception $e ) {
		$error_message[ 'code' ] = $e->getCode();
		$error_message[ 'message' ] = $e->getMessage();
		$error_message[ 'type' ] = "danger";
	}

}

if ( $_GET[ 'ajax' ] == "statusHandshakeUpload" || $_GET[ 'ajax' ] == "statusHashUpload") {
	echo json_encode( $error_message );
	exit();
}



//If show only my networks true
$somn = isset( $_GET[ 'somn' ] ) && $user_id != -1 && $_GET[ 'somn' ] == "true" ? true : false;

// Paggination
// Find out how many items are in the table
$sql = $somn ? "SELECT COUNT(*) as count FROM tasks WHERE user_id='" . $user_id . "'": "SELECT COUNT(*) as count FROM tasks";
$total = $mysqli->query( $sql )->fetch_object()->count;

// How many items to list per page
$limit = 20;

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

	$ajax = [];

	if ( $somn ) {

		//Show only my networks, if user are logged in
		$sql = "SELECT * FROM tasks WHERE user_id='" . $user_id . "' ORDER BY id LIMIT " . $limit . " OFFSET " . $offset;

	} else {

		//Else show all networks
		$sql = "SELECT * FROM tasks ORDER BY id LIMIT " . $limit . " OFFSET " . $offset;
	}

	$result = $mysqli->query( $sql )->fetch_all( MYSQL_ASSOC );

	foreach ( $result as $task ) {
		$Task = Task::create_task_from_db( $task[ 'id' ] );
		array_push( $ajax, $Task->get_all_info() );
	}

	$json[ "admin" ] = $admin;
	$json[ 'table' ] = $ajax;

	echo json_encode( $json );
	exit();
}

if ( $_GET[ 'ajax' ] == "pagger" ) {

	header( 'Content-Type: application/json' );

	$ajax = [];

	array_push( $ajax, array(
		"arrow" => true,
		"link" => "back",
		"active" => ( $page > 1 ) ? false : true,
		"page" => ( $page > 1 ) ? ( $page - 1 ) : 1
	) );

	for ( $i = 1; $i <= $pages; $i++ ) {
		array_push( $ajax, array(
			"arrow" => false,
			"active" => ( $i == $page ) ? true : false,
			"page" => $i
		) );
	}

	array_push( $ajax, array(
		"arrow" => true,
		"link" => "forward",
		"active" => false,
		"page" => ( $page < $pages ) ? ( $page + 1 ) : 1
	) );

	echo json_encode( $ajax );
	exit();

}

//Delete task by admin panel
if ( $_POST[ 'deleteTask' ] == "true" && $admin ) {

	//Get id
	$id = $_POST[ 'deleteTaskID' ];

	$Task = Task::create_task_from_db( $id );
	$Task->deleteTask();
}

//Delete task by admin panel
if ( isset( $_POST[ 'deleteTask' ] ) && $_POST[ 'deleteTask' ] == "true" && $admin ) {

	//Get id
	$id = $_POST[ 'deleteTaskID' ];

	//Get path for handshake
	$sql = "SELECT server_path FROM tasks WHERE id = '" . $id . "'";
	$path = $mysqli->query( $sql )->fetch_object()->server_path;

	//Delete file
	unlink( $path );

	//Delete task from tasks
	$sql = "DELETE FROM tasks WHERE id='" . $id . "'";
	$mysqli->query( $sql );

	//Delete task from tasks_dicts
	$sql = "DELETE FROM tasks_dicts WHERE net_id='" . $id . "'";
	$mysqli->query( $sql );
}
?>

<div class="container-fluid">
	<div class="row">
		<div class="col-lg-9 offset-1">

			<!-- Header -->
			<h2>Tasks</h2>

			<div style="overflow: auto;" class="my-2">
				<form style="float: left; padding-right: 5px;" action="" class="form-inline" method="POST" onSubmit="showOnlyMyNetworks(this);">
					<input type="submit" value="Show only my networks" class="btn btn-default" id="buttonShowOnlyMyNetworks">
				</form>

				<div style="overflow: auto;">
					<form style="float: left; padding-right: 5px;" class="form-inline">
						<input type="button" value="Turn on auto-reload" class="btn btn-success" id="buttonTurnOnAutoRefresh">
					</form>
				</div>
			</div>
			<!-- Header end -->

			<!-- Table -->
			<div id="ajaxTableDiv">
			</div>
			<!-- Table end -->
			<!-- Pagger -->
			<div id="ajaxPagger"></div>
			<!-- Pagger end -->
		</div>

		<div class="col-lg-2">

			<!-- Right side Bar -->
			<h2>Add new tasks</h2>
			<form enctype="multipart/form-data" id="formUploadHandshake" onSubmit="ajaxSendForm(this, 'handshake');">
				<input type="hidden" name="source" value="upload">
				<input type="hidden" name="action" value="addfile">
				<div class="panel panel-default">
					<table class="table table-bordered table-nonfluid" id="tableUploadHandshake">
						<tbody>
							<tr>
								<th>Upload handshake file (cap, hccapx only)</th>
							</tr>
							<tr>
								<td>
									<input type="text" class="form-control" name="task_name" required="" placeholder="Enter task name">
								</td>
							</tr>

							<tr>
								<td>
									<input type="file" class="form-control fileinput" name="upfile" required="" id="upfile" accept=".cap, .hccapx">
								</td>
							</tr>
							<tr>
								<td>
									<input type="submit" class="btn btn-primary" value="Upload files" name="buttonUploadFile" id="buttonUploadFile">
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</form>

			<h2>NTLM Hash</h2>
			<form enctype="multipart/form-data" id="formUploadNTLMHash" onSubmit="ajaxSendForm(this, 'ntlm');">
				<input type="hidden" name="source" value="upload">
				<input type="hidden" name="action" value="addfile">
				<div class="panel panel-default">
					<table class="table table-bordered table-nonfluid" id="tableUploadHash">
						<tbody>
							<tr>
								<th>Set username, challenge, response</th>
							</tr>
							<tr>
								<td>
									<input type="text" class="form-control" name="taskname" required="" placeholder="Enter taskname">
								</td>
							</tr>

							<tr>
								<td>
									<input type="text" class="form-control" name="username" required="" placeholder="Username">
								</td>
							</tr>

							<tr>
								<td>
									<input type="text" class="form-control" name="challenge" required="" placeholder="Challenge">
								</td>
							</tr>

							<tr>
								<td>
									<input type="text" class="form-control" name="response" required="" placeholder="Response">
								</td>
							</tr>

							<tr>
								<td>
									<input type="submit" class="btn btn-primary" value="Upload hash" name="buttonUploadHash" id="buttonUploadHash">
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</form>
			<!-- Right side Bar end-->
		</div>
	</div>
</div>