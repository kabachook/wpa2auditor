<?php

//There we want to upload file
$target_file = $cfg_tasks_targetFolder . basename( $_FILES[ "upfile" ][ "name" ] );
$uploadCode = 1;
$uploadFileType = pathinfo( $target_file, PATHINFO_EXTENSION );
$status_file_uploading;

//DB cleaner
function cleanDB() {
	global $mysqli;

	//Clean db
	//get all complete tasks id and delete from tasks_dicts
	$sql = "SELECT id FROM tasks WHERE status IN('2')";
	$task_id = $mysqli->query( $sql )->fetch_all( MYSQL_ASSOC );
	foreach ( $task_id as $tid ) {
		$sql = "DELETE FROM tasks_dicts WHERE net_id='" . $tid[ 'id' ] . "'";
		$mysqli->query( $sql );
	}
}

//Get all info from handshake in bin and convert it to hex if raw=false
function getHandshakeInfo( $path, $raw ) {

	$hccap = file_get_contents( $path );

	$ahccap = array();

	if ( version_compare( PHP_VERSION, '5.5.0' ) >= 0 ) {
		$ahccap[ 'essid' ] = unpack( 'Z36', substr( $hccap, 0x000, 36 ) );
	} else {
		$ahccap[ 'essid' ] = unpack( 'a36', substr( $hccap, 0x000, 36 ) );
	}

	$ahccap[ 'mac1' ] = substr( $hccap, 0x024, 6 );
	$ahccap[ 'mac2' ] = substr( $hccap, 0x02a, 6 );
	$ahccap[ 'nonce1' ] = substr( $hccap, 0x030, 32 );
	$ahccap[ 'nonce2' ] = substr( $hccap, 0x050, 32 );
	$ahccap[ 'eapol' ] = substr( $hccap, 0x070, 256 );
	$ahccap[ 'eapol_size' ] = unpack( 'i', substr( $hccap, 0x170, 4 ) );
	$ahccap[ 'keyver' ] = unpack( 'i', substr( $hccap, 0x174, 4 ) );
	$ahccap[ 'keymic' ] = substr( $hccap, 0x178, 16 );

	// fixup unpack
	$ahccap[ 'essid' ] = $ahccap[ 'essid' ][ 1 ];
	$ahccap[ 'eapol_size' ] = $ahccap[ 'eapol_size' ][ 1 ];
	$ahccap[ 'keyver' ] = $ahccap[ 'keyver' ][ 1 ];

	// cut eapol to right size
	$ahccap[ 'eapol' ] = substr( $ahccap[ 'eapol' ], 0, $ahccap[ 'eapol_size' ] );

	// fix order
	if ( strncmp( $ahccap[ 'mac1' ], $ahccap[ 'mac2' ], 6 ) < 0 )
		$m = $ahccap[ 'mac1' ] . $ahccap[ 'mac2' ];
	else
		$m = $ahccap[ 'mac2' ] . $ahccap[ 'mac1' ];

	if ( strncmp( $ahccap[ 'nonce1' ], $ahccap[ 'nonce2' ], 6 ) < 0 )
		$n = $ahccap[ 'nonce1' ] . $ahccap[ 'nonce2' ];
	else
		$n = $ahccap[ 'nonce2' ] . $ahccap[ 'nonce1' ];

	$ahccap[ 'm' ] = $m;
	$ahccap[ 'n' ] = $n;

	//return result in hex, else return in bin
	if ( !$raw ) {
		$ahccap[ 'mac1' ] = bin2hex( $ahccap[ 'mac1' ] );
		$ahccap[ 'mac2' ] = bin2hex( $ahccap[ 'mac2' ] );
		$ahccap[ 'nonce1' ] = bin2hex( $ahccap[ 'nonce1' ] );
		$ahccap[ 'nonce2' ] = bin2hex( $ahccap[ 'nonce2' ] );
		$ahccap[ 'eapol' ] = bin2hex( $ahccap[ 'eapol' ] );
		$ahccap[ 'keymic' ] = bin2hex( $ahccap[ 'keymic' ] );
	}

	return $ahccap;
}

//Check if key is a valid key for handshake
function check_key( $id, $key ) {
	global $mysqli;

	//Get filename for task
	$sql = "SELECT server_path FROM tasks WHERE id='" . $id . "'";
	$server_path = $mysqli->query( $sql )->fetch_object()->server_path;

	//Get handshake info in raw
	$ahccap = getHandshakeInfo( $server_path, true );
	$block = "Pairwise key expansion\0" . $ahccap[ 'm' ] . $ahccap[ 'n' ] . "\0";

	$pmk = hash_pbkdf2( 'sha1', $key, $ahccap[ 'essid' ], 4096, 32, True );
	$ptk = hash_hmac( 'sha1', $block, $pmk, True );
	if ( $ahccap[ 'keyver' ] == 1 )
		$testmic = hash_hmac( 'md5', $ahccap[ 'eapol' ], substr( $ptk, 0, 16 ), True );
	else
		$testmic = hash_hmac( 'sha1', $ahccap[ 'eapol' ], substr( $ptk, 0, 16 ), True );

	if ( strncmp( $testmic, $ahccap[ 'keymic' ], 16 ) == 0 )
		return $key;

	return NULL;
}

//Convert handshake to hccap
function handshakeConverter( $file ) {
	global $cfg_tools_cap2hccap;

	$name = $file[ 'taskName' ];
	$path = $file[ 'server_path' ];
	$extension = $file[ 'ext' ];
	$output = $path;

	//Convert cap to hccap
	if ( $extension == "cap" ) {

		//Execute hccap
		exec( $cfg_tools_cap2hccap . " " . $path . " " . $output );
	}

	$size = filesize( $path );
	if ( $size == 392 ) {
		return array( $path );
	} elseif ( $size % 392 == 0 ) {
		//Slice file
	} else {
		return NULL;
	}

}

function addTaskToDB( $file, $info ) {
	global $mysqli;

	$server_path = $file[ 'server_path' ];
	$name = $file[ 'taskName' ];
	$filename = $file[ 'fileName' ];
	$site_path = $file[ 'site_path' ] . $filename;

	//Add task to db
	$thash = hash_file( "sha256", $server_path );
	$sql = "INSERT INTO tasks(name, type, priority, filename, thash, essid, station_mac, server_path, site_path) VALUES('" . $name . "', '0', '0', '" . $filename . "', UNHEX('" . $thash . "'), '" . $info[ 'essid' ] . "', '" . $info[ 'mac1' ] . "', '" . $server_path . "', '" . $site_path . "')";
	$mysqli->query( $sql );

	//Get all dicts id
	$sql = "SELECT id FROM dicts";
	$result = $mysqli->query( $sql )->fetch_all( MYSQLI_ASSOC );

	//Insert into tasks_dicts for last (current) task all dicts
	foreach ( $result as $row ) {
		$dict_curr_id = $row[ 'id' ];
		$sql = "INSERT INTO tasks_dicts(net_id, dict_id, status) VALUES('" . getLastNetID() . "', '" . $dict_curr_id . "', '0')";
		$mysqli->query( $sql );
	}
}

function getLastNetID() {
	global $mysqli;
	$sql = "SELECT MAX(id) FROM tasks";
	$result = $mysqli->query( $sql );
	$result = $result->fetch_assoc();
	return $result[ 'MAX(id)' ];
}
//Upoad file to server, check it, move it, add to DB
//List of errors
$errors = [
	1 => "ALL IS OK",
	2 => "FILE ALREADY EXISTS",
	3 => "FILE BIGGER THAN MAX FILE SIZE",
	4 => "FORBIDDEN FILE FORMAT",
];

if ( isset( $_POST[ 'buttonUploadFile' ] ) ) {

	// Check if file already exists
	if ( file_exists( $target_file ) ) {
		$uploadCode = 2;
	}

	// Check file size
	if ( $_FILES[ "upfile" ][ "size" ] > $cfg_tasks_maxFileSize ) {
		$uploadCode = 3;
	}

	//Allow hccap and cap file format only
	$allow_fromat = array( "hccap", "cap" );
	if ( !in_array( $uploadFileType, $allow_fromat ) ) {
		$uploadCode = 4;
	}

	//If uploadCode != 1 => that's an error
	if ( $uploadCode != 1 ) {
		$status_file_uploading = '<td><div class="alert alert-danger mb0" role="alert"><strong>' . $errors[ $uploadCode ] . '</strong></div></td>';

	} else {
		// if everything is ok, try to move file
		if ( move_uploaded_file( $_FILES[ "upfile" ][ "tmp_name" ], $target_file ) ) {

			$status_file_uploading = '<td><div class="alert alert-success mb0" role="alert"><strong>OK!</strong> File uploaded sucefully!</div></td>';

			//Only if file uploaded without error, we add it to db
			$path = $cfg_tasks_targetFolder . $_FILES[ "upfile" ][ "name" ];
			$file = [
				"taskName" => $_POST[ 'task_name' ],
				"fileName" => $_FILES[ "upfile" ][ "name" ],
				"server_path" => $path,
				"site_path" => $cfg_site_url . "tasks/",
				"ext" => $uploadFileType,
			];

			$conv = handshakeConverter( $file );
			if ( $conv != NULL ) {
				foreach ( $conv as $handshake ) {
					$info = getHandshakeInfo( $file[ 'server_path' ], false );
					addTaskToDB( $file, $info );
				}
			}

		} else {
			$status_file_uploading = '<td><div class="alert alert-danger mb0" role="alert"><strong>Error while moving file on server. Contact Kabachook.</strong></div></td>';
		}
	}
	//Clean DB
	cleanDB();
}

//NTLM Hashes
if ( isset( $_POST[ 'buttonUploadHash' ] ) ) {
	$task_name = $_POST[ 'taskname' ];
	$username = $_POST[ 'username' ];
	$challenge = $_POST[ 'challenge' ];
	$response = $_POST[ 'response' ];
	$sql = "INSERT INTO tasks(name, type, username, challenge, response) VALUES('" . $task_name . "', '1', '" . $username . "', '" . $challenge . "', '" . $response . "')";
	$ans = $mysqli->query( $sql );

	if ( $ans ) {
		$status_hash_uploading = '<td><div class="alert alert-success mb0" role="alert"><strong>OK!</strong> Hash uploaded sucefully!</div></td>';
	} else {
		$status_hash_uploading = '<td><div class="alert alert-danger mb0" role="alert"><strong>Failed.</strong></div></td>';
	}

	//Get all dicts id
	$sql = "SELECT id FROM dicts";
	$result = $mysqli->query( $sql )->fetch_all( MYSQLI_ASSOC );

	//Insert into tasks_dicts for last (current) task all dicts
	foreach ( $result as $row ) {
		$dict_curr_id = $row[ 'id' ];
		$sql = "INSERT INTO tasks_dicts(net_id, dict_id, status) VALUES('" . getLastNetID() . "', '" . $dict_curr_id . "', '0')";
		$mysqli->query( $sql );
	}
}

//WPA key
if ( isset( $_POST[ 'buttonWpaKeys' ] ) ) {

	foreach ( $_POST as $task_id => $wpa_key ) {

		//WPA Key must be from 8 to 64 symbols
		//Ignoring POST value of submit button
		if ( strlen( $wpa_key ) < 8 || strlen( $wpa_key ) > 64 || $wpa_key == "Send WPA keys" )
			continue;

		$result = check_key( $task_id, $wpa_key );

		if ( $result == $wpa_key ) {
			//If password is valid, update key and status in db
			$sql = "UPDATE tasks SET status='2', net_key='" . $wpa_key . "' WHERE id='" . $task_id . "'";
			$mysqli->query( $sql );
		}
	}
}
?>
<div class="container-fluid">
	<div class="col-lg-9 col-lg-offset-1">
		<h2>Tasks</h2>
		<?php
		if ( $admin ) {
			?>
		<div style="overflow: auto;">
			<form style="float: left; padding-right: 5px;" action="" class="form-inline" method="POST">
				<input type="hidden" name="action" value="finishedtasksdelete">
				<input type="submit" value="Delete finished" class="btn btn-default">
			</form>

			<div style="overflow: auto;">
				<form style="float: left; padding-right: 5px;" action="" class="form-inline" method="POST">
					<input type="hidden" name="toggleautorefresh" value="On">
					<input type="submit" value="Turn on auto-reload" class="btn btn-success">
				</form>
				<br>

			</div>
			<br>
		</div>
		<?php
		}
		?>
		<form action="" method="post" enctype="multipart/form-data">
			<div class="panel panel-default">
				<table class="table table-striped table-bordered table-nonfluid">
					<tbody>
						<tr>
							<th>#</th>
							<th>MAC</th>
							<th>Task name</th>
							<th>Net name</th>
							<th>Key</th>
							<th>Files</th>
							<th>Agents</th>
							<th>Status</th>
							<?php if($admin)echo "<th>Admin</th>"; ?>
						</tr>
						<?php

						//List of status codes for tasks
						function getStatus( $status ) {
							$listOfStatus = [
								0 => "IN QUEUE",
								1 => "IN PROGRESS",
								2 => "SUCCESS",
								3 => "FAILED",
							];
							return $listOfStatus[ $status ];
						}

						//Show tasks from DB
						$sql = "SELECT id, name, filename, status, agents, net_key, essid, station_mac, site_path FROM tasks WHERE 1";
						$result = $mysqli->query( $sql )->fetch_all( MYSQLI_ASSOC );

						$id = 0;
						foreach ( $result as $row ) {
							if ( $row[ 'net_key' ] == '0' ) {
								$key = '<input type="text" class="form-control" placeholder="Enter wpa key" name="' . $row[ 'id' ] . '">';
							} else {
								$key = "<strong>" . $row[ 'net_key' ] . "</strong>";
							}
							$id++;
							$str = '<tr><td><strong>' . $id . '</strong></td><td>' . $row[ 'station_mac' ] . '</td><td>' . $row[ 'name' ] . '</td><td>' . $row[ 'essid' ] . '</td><td>' . $key . '</td><td><a href="' . $row[ 'site_path' ] . '" class="btn btn-default"><span class="glyphicon glyphicon-download"></span></a><td>' . $row[ 'agents' ] . '</td><td class="status">' . getStatus( $row[ 'status' ] ) . '</td>';
							$tasks_admin_panel = '<td><a class="btn btn-default"><span class="glyphicon glyphicon-trash"></span></a></td>';
							echo $str;
							if ( $admin )
								echo $tasks_admin_panel;
							echo "</tr>";
						}
						?>
					</tbody>
				</table>
			</div>

			<input type="submit" value="Send WPA keys" name="buttonWpaKeys" class="btn btn-default">
		</form>
	</div>
	<div class="col-lg-2">
		<h2>Add new tasks</h2>
		<form class="" action="" method="post" enctype="multipart/form-data">
			<input type="hidden" name="source" value="upload">
			<input type="hidden" name="action" value="addfile">
			<div class="panel panel-default">
				<table class="table table-bordered table-nonfluid">
					<tbody>
						<tr>
							<th>Upload handshake file (cap, hccap only)</th>
						</tr>
						<tr>
							<td>
								<input type="text" class="form-control" name="task_name" required="" placeholder="Enter task name">
							</td>
						</tr>

						<tr>
							<td>
								<input type="file" class="form-control" name="upfile" required="">
							</td>
						</tr>
						<tr>
							<td>
								<input type="submit" class="btn btn-default" value="Upload files" name="buttonUploadFile">
							</td>
						</tr>
						<tr>
							<?php echo $status_file_uploading; ?>
						</tr>
					</tbody>
				</table>
			</div>
		</form>
		<h2>NTLM Hash</h2>
		<form class="" action="" method="post" enctype="multipart/form-data">
			<input type="hidden" name="source" value="upload">
			<input type="hidden" name="action" value="addfile">
			<div class="panel panel-default">
				<table class="table table-bordered table-nonfluid">
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
								<input type="submit" class="btn btn-default" value="Upload hash" name="buttonUploadHash">
							</td>
						</tr>
						<tr>
							<?php echo $status_hash_uploading; ?>
						</tr>
					</tbody>
				</table>
			</div>
		</form>
	</div>
</div>