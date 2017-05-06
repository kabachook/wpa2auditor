<?php

//There we want to upload file
$target_file = $cfg_tasks_targetFolder . basename( $_FILES[ "upfile" ][ "name" ] );
$uploadCode = 1;
$uploadFileType = pathinfo( $target_file, PATHINFO_EXTENSION );
$status_file_uploading;



function inf( $hccap ) {
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
    $ahccap['eapol'] = substr($ahccap['eapol'], 0, $ahccap['eapol_size']);

    // fix order
    if (strncmp($ahccap['mac1'], $ahccap['mac2'], 6) < 0)
        $m = $ahccap['mac1'].$ahccap['mac2'];
    else
        $m = $ahccap['mac2'].$ahccap['mac1'];

    if (strncmp($ahccap['nonce1'], $ahccap['nonce2'], 6) < 0)
        $n = $ahccap['nonce1'].$ahccap['nonce2'];
    else
        $n = $ahccap['nonce2'].$ahccap['nonce1'];
	
	$ahccap['m'] = $m;
	$ahccap['n'] = $n;

    
	//var_dump($ahccap);
	//
	/*$ahccap[ 'mac1' ] = bin2hex( $ahccap[ 'mac1' ] );
	$ahccap[ 'mac2' ] = bin2hex( $ahccap[ 'mac2' ] );
	$ahccap[ 'nonce1' ] = bin2hex( $ahccap[ 'nonce1' ] );
	$ahccap[ 'nonce2' ] = bin2hex( $ahccap[ 'nonce2' ] );
	$ahccap[ 'eapol' ] = bin2hex( $ahccap[ 'eapol' ] );
	$ahccap[ 'keymic' ] = bin2hex( $ahccap[ 'keymic' ] );*/
	//var_dump($ahccap);
	return $ahccap;
}

function check_key( $id, $key ) {
	global $cfg_tasks_targetFolder;
	global $mysqli;
	
	$sql = "SELECT filename FROM tasks WHERE id='" . $id . "'";
	$filename = $mysqli->query( $sql )->fetch_object()->filename;
	//var_dump($cfg_tasks_targetFolder . $filename);
	$ahccap = inf( file_get_contents( $cfg_tasks_targetFolder . $filename ) );
	//var_dump( $ahccap );
	
	$block = "Pairwise key expansion\0".$ahccap['m'].$ahccap['n']."\0";
	
	//var_dump($key);
        $kl = strlen($key);
        //if (($kl < 8) || ($kl > 64))
         //   return "LOL";
	//
        $pmk = hash_pbkdf2('sha1', $key, $ahccap['essid'], 4096, 32, True);
	//var_dump($pmk);
        $ptk = hash_hmac('sha1', $block, $pmk, True);
	//var_dump($ptk);
        if ($ahccap['keyver'] == 1)
            $testmic = hash_hmac('md5',  $ahccap['eapol'], substr($ptk, 0, 16), True);
        else
            $testmic = hash_hmac('sha1', $ahccap['eapol'], substr($ptk, 0, 16), True);
	//var_dump(bin2hex($testmic), bin2hex($ahccap['keymic']));
        if (strncmp($testmic, $ahccap['keymic'], 16) == 0)
            return $key;
    
    return "NOT A KEY";
}

function getHandshakeInfo( $file, $extension ) {
	global $cfg_tools_cap2hccap;
	global $cfg_tools_cap2hccap_tempFilename;
	global $cfg_tasks_targetFolder;

	$hccap[ 'path' ] = $cfg_tasks_targetFolder . $file[ 'name' ];
	$hccap[ 'name' ] = $file[ 'name' ];

	//var_dump($hccap);
	//var_dump($extension);
	if ( $extension == "cap" ) {
		//cap to hccap
		//var_dump($cfg_tools_cap2hccap . " " . $hccap[ 'path' ] . " " . $cfg_tasks_targetFolder . $cfg_tools_cap2hccap_tempFilename);
		exec( $cfg_tools_cap2hccap . " " . $hccap[ 'path' ] . " " . $cfg_tasks_targetFolder . $cfg_tools_cap2hccap_tempFilename );
		$hccap[ 'name' ] = $cfg_tools_cap2hccap_tempFilename;
		$hccap[ 'path' ] = $cfg_tasks_targetFolder . $hccap[ 'name' ];

	}
	$hccap[ 'size' ] = filesize( $hccap[ 'path' ] );
	//var_dump($hccap);
	//Check hccap size (392 byte = 1 handshake)
	//var_dump($hccap);
	if ( $hccap[ 'size' ] == 392 ) {
		/*$ahccap[ 'mac1' ] = bin2hex( $ahccap[ 'mac1' ] );
	$ahccap[ 'mac2' ] = bin2hex( $ahccap[ 'mac2' ] );
	$ahccap[ 'nonce1' ] = bin2hex( $ahccap[ 'nonce1' ] );
	$ahccap[ 'nonce2' ] = bin2hex( $ahccap[ 'nonce2' ] );
	$ahccap[ 'eapol' ] = bin2hex( $ahccap[ 'eapol' ] );
	$ahccap[ 'keymic' ] = bin2hex( $ahccap[ 'keymic' ] );*/
		$final = inf( file_get_contents( $hccap[ 'path' ] ) ) ;
		$final[ 'mac1' ] = bin2hex( $final[ 'mac1' ] );
		$final[ 'mac2' ] = bin2hex( $final[ 'mac2' ] );
		$final[ 'nonce1' ] = bin2hex( $final[ 'nonce1' ] );
		$final[ 'nonce2' ] = bin2hex( $final[ 'nonce2' ] );
		$final[ 'eapol' ] = bin2hex( $final[ 'eapol' ] );
		$final[ 'keymic' ] = bin2hex( $final[ 'keymic' ] );
		//var_dump($final);
		return array( $final );
	}
	/*} elseif ( $hccap[ 'size' ] % 392 == 0 ) {
		//Делим файл по 392 байта
		$count = $hccap['size'] / 392;
		$temp_array = array();
		for ($i = 0; i < $count; $i++) {
			
		}
		
		$hccap_array = array();
		array_push($hccap_array, inf())
	}*/
}

function addTaskToDB( $name, $filename, $ext ) {
	global $mysqli;
	global $cfg_site_url;
	global $cfg_tasks_targetFolder;

	//Get info from handshake
	//var_dump($ext);
	$handshake_info = getHandshakeInfo( $_FILES[ 'upfile' ], $ext )[ 0 ];
	//var_dump($handshake_info);

	//Clean db
	//get all complete tasks id 
	$sql = "SELECT id FROM tasks WHERE status IN('2')";
	$task_id = $mysqli->query( $sql )->fetch_all( MYSQL_ASSOC );
	foreach ( $task_id as $tid ) {
		$sql = "DELETE FROM tasks_dicts WHERE net_id='" . $tid[ 'id' ] . "'";
		$mysqli->query( $sql );
	}

	//Add task to db
	$thash = hash_file( "sha256", $cfg_tasks_targetFolder . $filename );
	$sql = "INSERT INTO tasks(name, type, priority, filename, thash, essid, station_mac) VALUES('" . $name . "', '0', '0', '" . $filename . "', UNHEX('" . $thash . "'), '" . $handshake_info[ 'essid' ] . "', '" . $handshake_info[ 'mac1' ] . "')";
	//var_dump($sql);
	$mysqli->query( $sql );

	//Get all dicts id
	$sql = "SELECT id FROM dicts";
	$result = $mysqli->query( $sql );
	$result = $result->fetch_all( MYSQLI_ASSOC );
	//Insert into tasks_dicts for last (current) task all dicts
	foreach ( $result as $row ) {
		$dict_curr_id = $row[ 'id' ];
		$sql = "INSERT INTO tasks_dicts(net_id, dict_id, status) VALUES('" . getNetId() . "', '" . $dict_curr_id . "', '0')";
		$mysqli->query( $sql );
	}
}

function getNetId() {
	global $mysqli;
	$sql = "SELECT MAX(id) FROM tasks";
	$result = $mysqli->query( $sql );
	$result = $result->fetch_assoc();
	return $result[ 'MAX(id)' ];
}

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

	//Allow .hccap file format only
	$allow_fromat = array( "hccap", "cap" );
	if ( !in_array( $uploadFileType, $allow_fromat ) ) {
		$uploadCode = 4;
	}

	//If uploadCode != 1 => that's an error
	if ( $uploadCode != 1 ) {
		$status_file_uploading = '<td><div class="alert alert-danger mb0" role="alert"><strong>' . $errors[ $uploadCode ] . '</strong></div></td>';
		// if everything is ok, try to upload file
	} else {
		if ( move_uploaded_file( $_FILES[ "upfile" ][ "tmp_name" ], $target_file ) ) {
			//Only if file uploaded without error, we add it to db
			//var_dump($uploadFileType);
			addTaskToDB( $_POST[ 'filename' ], $_FILES[ "upfile" ][ "name" ], $uploadFileType );
			$status_file_uploading = '<td><div class="alert alert-success mb0" role="alert"><strong>OK!</strong> File uploaded sucefully!</div></td>';
		} else {
			$status_file_uploading = '<td><div class="alert alert-danger mb0" role="alert"><strong>Error while moving file on server. Contact Kabachook.</strong></div></td>';
		}
	}
}

//NTLM
if ( isset( $_POST[ 'buttonUploadHash' ] ) ) {
	$task_name = $_POST[ 'taskname' ];
	$username = $_POST[ 'username' ];
	$challenge = $_POST[ 'challenge' ];
	$respone = $_POST[ 'respone' ];
	$sql = "INSERT INTO tasks(name, type, username, challenge, respone) VALUES('" . $task_name . "', '1', '" . $username . "', '" . $challenge . "', '" . $respone . "')";
	$ans = $mysqli->query( $sql );

	if ( $ans ) {
		$status_hash_uploading = '<td><div class="alert alert-success mb0" role="alert"><strong>OK!</strong> Hash uploaded sucefully!</div></td>';
	} else {
		$status_hash_uploading = '<td><div class="alert alert-danger mb0" role="alert"><strong>Failed.</strong></div></td>';
	}
	//Get all dicts id
	$sql = "SELECT id FROM dicts";
	$result = $mysqli->query( $sql );
	$result = $result->fetch_all( MYSQLI_ASSOC );
	//Insert into tasks_dicts for last (current) task all dicts
	foreach ( $result as $row ) {
		$dict_curr_id = $row[ 'id' ];
		$sql = "INSERT INTO tasks_dicts(net_id, dict_id, status) VALUES('" . getNetId() . "', '" . $dict_curr_id . "', '0')";
		$mysqli->query( $sql );
	}
}

//Wpa key
if ( isset( $_POST[ 'buttonWpaKeys' ] ) ) {
	//var_dump($_POST);
	foreach ( $_POST as $task_id => $wpa_key ) {
		//check keys
		//var_dump(strlen($wpa_key));
		
		if (strlen($wpa_key) < 8 || strlen($wpa_key) > 63 || $wpa_key == "Send WPA keys" )
			continue;
		//var_dump($wpa_key);
		$result = check_key( $task_id, $wpa_key );
		//var_dump($result);
		if ($result == $wpa_key) {
			//var_dump($wpa_key);
			$sql = "UPDATE tasks SET status='2', net_key='" . $wpa_key . "' WHERE id='" . $task_id . "'";
			$mysqli->query($sql);
		}
		
	}

}
?>
<div class="container">
	<div class="col-lg-9 col-lg-offset-1">
		<h2>Tasks</h2>
		<?php if($admin) echo '<div style="overflow: auto;">
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
      </div>'; ?>
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

						function getStatus( $status ) {
							$listOfStatus = [
								0 => "IN QUEUE",
								1 => "IN PROGRESS",
								2 => "SUCCESS",
								3 => "FAILED",
							];
							return $listOfStatus[ $status ];
						}
						//Show dicts from DB
						global $mysqli;
						$sql = "SELECT id, name, filename, status, agents, net_key, essid, station_mac FROM tasks WHERE 1";
						$result = $mysqli->query( $sql );
						$result = $result->fetch_all( MYSQLI_ASSOC );


						$id = 0;
						foreach ( $result as $row ) {
							if ( $row[ 'net_key' ] == '0' ) {
								$key = '<input type="text" class="form-control" placeholder="Enter wpa key" name="' . $row[ 'id' ] . '">';
							} else {
								$key = "<strong>" . $row[ 'net_key' ] . "</strong>";
							}
							$id++;
							$str = '<tr><td><strong>' . $id . '</strong></td><td>' . $row[ 'station_mac' ] . '</td><td>' . $row[ 'name' ] . '</td><td>' . $row[ 'essid' ] . '</td><td>' . $key . '</td><td><a href="' . $cfg_site_url . "tasks\\" . $row[ 'filename' ] . '" class="btn btn-default"><span class="glyphicon glyphicon-download"></span></a><td>' . $row[ 'agents' ] . '</td><td class="status">' . getStatus( $row[ 'status' ] ) . '</td>';
							$admin_pan_str = '<td><a class="btn btn-default"><span class="glyphicon glyphicon-trash"></span></a></td></tr>';
							echo $str;
							if ( $admin )
								echo $admin_pan_str;
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
							<th>Upload handshake file (.hccap only)</th>
						</tr>
						<tr>
							<td>
								<input type="text" class="form-control" name="filename" required="" placeholder="Enter filename">
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
							<th>Set username, challenge, respone</th>
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
								<input type="text" class="form-control" name="respone" required="" placeholder="Respone">
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