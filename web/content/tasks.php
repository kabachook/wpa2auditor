<?php

//There we want to upload file
$target_file = $cfg_tasks_targetFolder . basename( $_FILES[ "upfile" ][ "name" ] );
$uploadCode = 1;
$uploadFileType = pathinfo( $target_file, PATHINFO_EXTENSION );
$status_file_uploading;

//CRUTCH
$sql = "SELECT * FROM tasks WHERE status='3'";
$tasks_lists = $mysqli->query( $sql )->fetch_all( MYSQL_ASSOC );
foreach ( $tasks_lists as $task ) {
	$sql = "SELECT * FROM tasks_dicts WHERE net_id='" . $task[ 'id' ] . "' AND status NOT IN('1')";
	$res = $mysqli->query( $sql );
	if ( $res->num_rows > 0 ) {
		$sql = "UPDATE tasks SET status='0' WHERE id='" . $task[ 'id' ] . "'";
		$mysqli->query( $sql );
	}
}


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

//HCCAP TO HCCAPX CONVERTER
function hccap2hccapx( $path_in, $path_out ) {

	$hccap = getHandshakeInfo( $path_in, true, "hccap" );

	//Write hccapx
	$hccapx = array();

	$hccapx[ 'signature' ] = pack( "L", 0x58504348 );
	$hccapx[ 'version' ] = pack( "L", 4 );
	$hccapx[ 'message_pair' ] = pack( "C", 0 );
	$hccapx[ 'essid_len' ] = pack( "C", 0x10 );
	$hccapx[ 'essid' ] = pack( "Z32", $hccap[ 'essid' ] );
	$hccapx[ 'keyver' ] = pack( "C", $hccap[ 'keyver' ] );
	$hccapx[ 'keymic' ] = pack( "A16", $hccap[ 'keymic' ] );
	$hccapx[ 'mac_ap' ] = pack( "A6", $hccap[ 'mac1' ] );
	$hccapx[ 'nonce_ap' ] = pack( "Z32", $hccap[ 'nonce2' ] );
	$hccapx[ 'mac_sta' ] = pack( "A6", $hccap[ 'mac2' ] );
	$hccapx[ 'nonce_sta' ] = pack( "Z32", $hccap[ 'nonce1' ] );
	$hccapx[ 'eapol_len' ] = pack( "S", $hccap[ 'eapol_size' ] );
	$hccapx[ 'eapol' ] = pack( "Z256", $hccap[ 'eapol' ] );

	file_put_contents( $path_out, $hccapx );
}

//Get all info from handshake in bin and convert it to hex if raw=false
function getHandshakeInfo( $path, $raw, $ext ) {

	$hccap = file_get_contents( $path );
	$ahccap = array();

	if ( $ext == "hccap" ) {
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
	} else {
		$ahccap[ 'signature' ] = substr( $hccap, 0x00, 4 );
		$ahccap[ 'version' ] = substr( $hccap, 0x04, 4 );
		$ahccap[ 'message_pair' ] = substr( $hccap, 0x08, 1 );
		$ahccap[ 'essid_len' ] = substr( $hccap, 0x09, 1 );

		if ( version_compare( PHP_VERSION, '5.5.0' ) >= 0 ) {
			$ahccap[ 'essid' ] = unpack( 'Z32', substr( $hccap, 0x0a, 32 ) );
		} else {
			$ahccap[ 'essid' ] = unpack( 'a32', substr( $hccap, 0x0a, 32 ) );
		}

		$ahccap[ 'keyver' ] = unpack( 'C', substr( $hccap, 0x2a, 1 ) );

		$ahccap[ 'keymic' ] = substr( $hccap, 0x2b, 16 );
		$ahccap[ 'mac_ap' ] = substr( $hccap, 0x3b, 6 );
		$ahccap[ 'nonce_ap' ] = substr( $hccap, 0x41, 32 );
		$ahccap[ 'mac_sta' ] = substr( $hccap, 0x61, 6 );
		$ahccap[ 'nonce_sta' ] = substr( $hccap, 0x67, 32 );

		$ahccap[ 'eapol_len' ] = unpack( 'v', substr( $hccap, 0x87, 2 ) );

		$ahccap[ 'eapol' ] = substr( $hccap, 0x89, 256 );

		//Fixup unpack

		$ahccap[ 'essid' ] = $ahccap[ 'essid' ][ 1 ];
		$ahccap[ 'eapol_len' ] = $ahccap[ 'eapol_len' ][ 1 ];
		$ahccap[ 'keyver' ] = $ahccap[ 'keyver' ][ 1 ];

		//Cut eapol to right size
		$ahccap[ 'eapol' ] = substr( $ahccap[ 'eapol' ], 0, $ahccap[ 'eapol_len' ] );

		// fix order
		if ( strncmp( $ahccap[ 'mac_ap' ], $ahccap[ 'mac_sta' ], 6 ) < 0 )
			$m = $ahccap[ 'mac_ap' ] . $ahccap[ 'mac_sta' ];
		else
			$m = $ahccap[ 'mac_sta' ] . $ahccap[ 'mac_ap' ];

		if ( strncmp( $ahccap[ 'nonce_ap' ], $ahccap[ 'nonce_sta' ], 6 ) < 0 )
			$n = $ahccap[ 'nonce_ap' ] . $ahccap[ 'nonce_sta' ];
		else
			$n = $ahccap[ 'nonce_sta' ] . $ahccap[ 'nonce_ap' ];

		$ahccap[ 'm' ] = $m;
		$ahccap[ 'n' ] = $n;

		//If raw false
		if ( !$raw ) {
			$ahccap[ 'mac_ap' ] = bin2hex( $ahccap[ 'mac_ap' ] );
		}
	}
	$ahccap[ 'ext' ] = $ext;

	return $ahccap;
}

//Check if key is a valid key for handshake
function check_key( $id, $key ) {
	global $mysqli;

	//Get filename for task
	$sql = "SELECT server_path, ext FROM tasks WHERE id='" . $id . "'";
	$result = $mysqli->query( $sql )->fetch_object();
	$server_path = $result->server_path;
	$ext = $result->ext;

	//Get handshake info 
	$ahccap = getHandshakeInfo( $server_path, true, $ext );
	$m = $ahccap[ 'm' ];
	$n = $ahccap[ 'n' ];
	$block = "Pairwise key expansion\0" . $m . $n . "\0";

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
	$path = $file[ 'server_path_toFile' ];
	$extension = $file[ 'ext' ];
	$output = $file[ 'server_path' ] . $file[ 'fileName' ];

	//Convert cap to hccapx
	if ( $extension == "cap" ) {
		$output = $file[ 'server_path' ] . $file[ 'fileName' ] . ".hccapx";
		//Execute cap2hccapx
		exec( $cfg_tools_cap2hccap . " " . $path . " " . $output );
		unlink( $path );
		$extension = "hccapx";
	}

	/*if ( $extension == "hccap" ) {
		$output .= ".hccapx";
		hccap2hccapx( $path, $output );
		$extension = "hccapx";
	}*/

	$size = filesize( $output );
	$ret = array();

	if ( $extension == "hccap" ) {
		if ( $size == 392 ) {
			array_push( $ret, array( "ext" => "hccap", "path" => $output, "name" => $file[ 'fileName' ] ) );
		} elseif ( $size % 392 == 0 ) {
			//Slice file
			$original = file_get_contents( $output );
			$offset = 0;

			for ( $i = 0; $i < ( $size / 392 ); $i++ ) {
				$sliced = substr( $original, $offset, 392 );
				$offset += 392;
				$out = $output . "_" . ( $i + 1 ) . ".hccap";
				fwrite( fopen( $out, "w" ), $sliced );
				array_push( $ret, array( "ext" => "hccap", "path" => $out, "name" => $file[ 'fileName' ] . ".hccap" . "_" . ( $i + 1 ) . ".hccap" ) );
			}
		} else {
			return NULL;
		}
	} else {
		if ( $size == 393 ) {
			array_push( $ret, array( "ext" => "hccapx", "path" => $output, "name" => $file[ 'fileName' ] . ".hccapx" ) );
		} elseif ( $size % 393 == 0 ) {
			//Slice file
			$original = file_get_contents( $output );
			$offset = 0;

			for ( $i = 0; $i < ( $size / 393 ); $i++ ) {
				$sliced = substr( $original, $offset, 393 );
				$offset += 393;
				$out = $output . "_" . ( $i + 1 ) . ".hccapx";
				fwrite( fopen( $out, "w" ), $sliced );
				array_push( $ret, array( "ext" => "hccapx", "path" => $out, "name" => $file[ 'fileName' ] . ".hccapx" . "_" . ( $i + 1 ) . ".hccapx" ) );
			}
			unlink( $output );
		} else {
			return NULL;
		}
	}

	return $ret;
}

//Get user id
function getUserID() {
	global $mysqli;
	$sql = "SELECT u_id FROM users WHERE userkey=UNHEX('" . $_COOKIE[ 'key' ] . "')";
	$user_id = $mysqli->query( $sql )->fetch_object()->u_id;
	if ( $user_id == null ) {
		//user not loggged in
		return -1;
	}
	return $user_id;
}

function addTaskToDB( $file, $info ) {
	global $mysqli;
	global $status_file_uploading;

	$server_path = $file[ 'server_path_toFile' ];
	$name = $file[ 'taskName' ];
	$filename = $file[ 'fileName' ];
	$site_path = $file[ 'site_path' ] . $filename;

	//Check if handshake uniq

	if ( $info[ 'ext' ] == "hccap" ) {
		$curr_hand_hash = md5( $info[ "keymic" ] . $info[ 'mac1' ] . $info[ 'essid' ] );
	} else {
		$curr_hand_hash = md5( $info[ "keymic" ] . $info[ 'mac_ap' ] . $info[ 'essid' ] );
	}

	$sql = "SELECT * FROM tasks WHERE uniq_hash=UNHEX('" . $curr_hand_hash . "')";
	$result = $mysqli->query( $sql );
	if ( $result->num_rows != 0 ) {
		//Hash is not uniq
		$result = $result->fetch_object();
		$status_file_uploading = '<td><div class="alert alert-danger mb0" role="alert">Hash already in DB. Password : ' . $result->net_key . '</div></td>';
	} else {
		//So hash is uniq

		if ( $info[ 'ext' ] == "hccap" ) {
			$mac = $info[ 'mac1' ];
		} else {
			$mac = $info[ 'mac_ap' ];
		}

		//Get user id
		$user_id = getUserID();

		//Add task to db
		$thash = hash_file( "sha256", $server_path );
		$sql = "INSERT INTO tasks(name, type, priority, filename, thash, essid, station_mac, server_path, site_path, uniq_hash, ext, user_id) VALUES('" . $name . "', '0', '0', '" . $filename . "', UNHEX('" . $thash . "'), '" . $info[ 'essid' ] . "', '" . $mac . "', '" . $server_path . "', '" . $site_path . "', UNHEX('" . $curr_hand_hash . "'), '" . $info[ 'ext' ] . "', '" . $user_id . "')";
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
	$allow_fromat = array( "hccapx", "cap" );
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
				"server_path_toFile" => $path,
				"server_path" => $cfg_tasks_targetFolder,
				"site_path" => $cfg_site_url . "tasks/",
				"ext" => $uploadFileType,
			];

			$conv = handshakeConverter( $file );

			if ( $conv != NULL ) {
				foreach ( $conv as $handshake ) {
					$file[ 'server_path_toFile' ] = $handshake[ "path" ];
					$file[ 'fileName' ] = $handshake[ "name" ];
					$info = getHandshakeInfo( $handshake[ "path" ], false, $handshake[ 'ext' ] );
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
	$user_id = getUserID();
	$task_name = $_POST[ 'taskname' ];
	$username = $_POST[ 'username' ];
	$challenge = $_POST[ 'challenge' ];
	$response = $_POST[ 'response' ];
	$site_path = $cfg_site_url . "tasks/" . $task_name . ".ntlm";
	$server_path = $cfg_tasks_targetFolder . $task_name . ".ntlm";
	$uniq_hash = md5( $username . $challenge . $response );

	$sql = "SELECT * FROM tasks WHERE uniq_hash=UNHEX('" . $uniq_hash . "')";
	$result = $mysqli->query( $sql );
	if ( $result->num_rows != 0 ) {
		//Hash is not uniq
		$result = $result->fetch_object();
		$status_file_uploading = '<td><div class="alert alert-danger mb0" role="alert">Hash already in DB. Password : ' . $result->net_key . '</div></td>';
	} else {
		$sql = "INSERT INTO tasks(name, type, username, challenge, response, user_id, site_path, server_path, ext, uniq_hash) VALUES('" . $task_name . "', '1', '" . $username . "', '" . $challenge . "', '" . $response . "', '" . $user_id . "', '" . $site_path . "', '" . $server_path . "', 'ntlm', UNHEX('" . $uniq_hash . "'))";
		$ans = $mysqli->query( $sql );

		file_put_contents( $cfg_tasks_targetFolder . $task_name . ".ntlm", $username . "::::" . $response . ":" . $challenge );

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

//Delete task by admin panel
if ( isset( $_POST[ 'deleteTask' ] ) && $admin ) {
	$id = $_POST[ 'deleteTaskID' ];

	$sql = "SELECT server_path FROM tasks WHERE id = '" . $id . "'";
	$path = $mysqli->query( $sql )->fetch_object()->server_path;
	unlink( $path );

	$sql = "DELETE FROM tasks WHERE id='" . $id . "'";
	$mysqli->query( $sql );
	$sql = "DELETE FROM tasks_dicts WHERE net_id='" . $id . "'";
	$mysqli->query( $sql );
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
				<input type="submit" value="Show only my networks" class="btn btn-default" name="showOnlyMyNetworks">
			</form>
			<!--
			<div style="overflow: auto;">
				<form style="float: left; padding-right: 5px;" action="" class="form-inline" method="POST">
					<input type="hidden" name="toggleautorefresh" value="On">
					<input type="submit" value="Turn on auto-reload" class="btn btn-success">
				</form>
				

			</div>-->
			<br>
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
							<th>Type</th>
							<th>MAC</th>
							<th>Task name</th>
							<th>Net name</th>
							<th>Key</th>
							<th>Files</th>
							<!-- <th>Agents</th> for better days -->
							<th>Status</th>
							<?php if($admin)echo "<th>Admin</th>"; ?>
						</tr>
						<?php
						// Pagger
						// Find out how many items are in the table
						$total = $mysqli->query( 'SELECT COUNT(*) as count FROM tasks' )->fetch_object()->count;

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

						//Pagger end
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

						$user_id = getUserID();

						//showOnlyMyNetworks
						if ( isset( $_POST[ 'showOnlyMyNetworks' ] ) && $user_id != -1 ) {
							$sql = "SELECT id, name, filename, status, agents, net_key, essid, station_mac, site_path, type FROM tasks WHERE user_id='" . $user_id . "'";
						} else {
							$sql = "SELECT id, name, filename, status, agents, net_key, essid, station_mac, site_path, type FROM tasks ORDER BY id LIMIT " . $limit . " OFFSET " . $offset;
						}

						//Show tasks from DB
						$result = $mysqli->query( $sql );

						if ( $result->num_rows > 0 ) {

							$result = $result->fetch_all( MYSQLI_ASSOC );

							$id = 0;
							foreach ( $result as $row ) {
								if ( $row[ 'net_key' ] == '0' ) {
									$key = '<input type="text" class="form-control" placeholder="Enter wpa key" name="' . $row[ 'id' ] . '">';
								} else {
									$key = "<strong>" . $row[ 'net_key' ] . "</strong>";
								}
								$type;
								if ( $row[ 'type' ] != "0" ) {
									$type = "ntlm";
								} else {
									$type = "handshake";
								}
								$id++;
								//<td>' . $row[ 'agents' ] . '</td>
								$str = '<tr><td><strong>' . $id . '</strong></td><td>' . $type . '</td><td>' . $row[ 'station_mac' ] . '</td><td>' . $row[ 'name' ] . '</td><td>' . $row[ 'essid' ] . '</td><td>' . $key . '</td><td><a href="' . $row[ 'site_path' ] . '" class="btn btn-default"><span class="glyphicon glyphicon-download"></span></a><td class="status">' . getStatus( $row[ 'status' ] ) . '</td>';
								$tasks_admin_panel = '<td><form action="" method="post"><input type="hidden" name="deleteTaskID" value="' . $row[ 'id' ] . '"><button type="submit" class="btn btn-default" name="deleteTask"><span class="glyphicon glyphicon-trash"></span></button></form></td>';
								echo $str;
								if ( $admin )
									echo $tasks_admin_panel;
								echo "</tr>";
							}
						}
						?>
					</tbody>
				</table>
			</div>
			<nav aria-label="Page navigation">
				<ul class="pagination">

					<?php
					// The "back" link
					$prevlink = ( $page > 1 ) ? '<li><a href="?tasks%page=' . ( $page - 1 ) . '" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>': '<li class="disabled"><a href="?tasks&page=1" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
					echo $prevlink;

					for ( $i = 1; $i <= $pages; $i++ ) {
						$element = '<li><a href="?tasks&page=' . $i . '">' . $i . '</a></li>';
						if ( $i == $page ) {
							$element = '<li class="active"><a href="?tasks&page=' . $i . '">' . $i . '</a></li>';
						}
						echo $element;
					}

					// The "forward" link
					$nextlink = ( $page < $pages ) ? '<li><a href="?tasks&page=' . ( $page + 1 ) . '" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>': '<li class="disabled"><a href="?tasks&page=1" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
					echo $nextlink;

					?>

				</ul>
			</nav>
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
							<th>Upload handshake file (cap, hccapx only)</th>
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