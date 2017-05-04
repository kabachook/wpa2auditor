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
	$ahccap[ 'eapol' ] = substr( $ahccap[ 'eapol' ], 0, $ahccap[ 'eapol_size' ] );
	//var_dump($ahccap);
	//
	$ahccap['mac1'] = bin2hex($ahccap['mac1']);
	$ahccap['mac2'] = bin2hex($ahccap['mac2']);
	$ahccap['nonce1'] = bin2hex($ahccap['nonce1']);
	$ahccap['nonce2'] = bin2hex($ahccap['nonce2']);
	$ahccap['eapol'] = bin2hex($ahccap['eapol']);
	$ahccap['keymic'] = bin2hex($ahccap['keymic']);
	//var_dump($ahccap);
	return $ahccap;
}

function getHandshakeInfo( $file, $extension ) {
	global $cfg_tools_cap2hccap;
	global $cfg_tools_cap2hccap_tempFilename;
	global $cfg_tasks_targetFolder;

	$hccap['path'] = $cfg_tasks_targetFolder . $file['name'];
	$hccap['name'] = $file['name'];
	
	//var_dump($hccap);
	//var_dump($extension);
	if ( $extension == "cap" ) {
		//cap to hccap
		//var_dump($cfg_tools_cap2hccap . " " . $hccap[ 'path' ] . " " . $cfg_tasks_targetFolder . $cfg_tools_cap2hccap_tempFilename);
		exec( $cfg_tools_cap2hccap . " " . $hccap[ 'path' ] . " " . $cfg_tasks_targetFolder . $cfg_tools_cap2hccap_tempFilename );
		$hccap[ 'name' ] = $cfg_tools_cap2hccap_tempFilename;
		$hccap['path'] = $cfg_tasks_targetFolder . $hccap['name'];
		
	}
	$hccap[ 'size' ] = filesize( $hccap[ 'path' ] );
	//var_dump($hccap);
	//Check hccap size (392 byte = 1 handshake)
	//var_dump($hccap);
	if ( $hccap[ 'size' ] == 392 ) {
		return array(inf(file_get_contents($hccap['path'])));
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
	$handshake_info = getHandshakeInfo($_FILES['upfile'], $ext)[0];
	//var_dump($handshake_info[0]);
	
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
	$sql = "INSERT INTO tasks(name, type, priority, filename, thash, essid, station_mac) VALUES('" . $name . "', '0', '0', '" . $filename . "', UNHEX('" . $thash . "'), '" . $handshake_info['essid'] . "', '" . $handshake_info['mac1'] . "')";
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
	$allow_fromat = array("hccap", "cap");
	if ( !in_array($uploadFileType, $allow_fromat)) {
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
if (isset($_POST['buttonUploadHash'])) {
	$task_name = $_POST['taskname'];
	$username = $_POST['username'];
	$challenge = $_POST['challenge'];
	$respone = $_POST['respone'];
	$sql = "INSERT INTO tasks(name, type, username, challenge, respone) VALUES('" . $task_name . "', '1', '" . $username . "', '" . $challenge . "', '" . $respone . "')";
	$ans = $mysqli->query($sql);
	
	if ($ans) {
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
?>
<div class="container">
	<div class="col-lg-8">
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
							$key = "NOT FOUND";
						} else {
							$key = "<strong>" . $row[ 'net_key' ] . "</strong>";
						}
						$id++;
						$str = '<tr><td><strong>' . $id . '</strong></td><td>' . $row['station_mac'] . '</td><td>' . $row[ 'name' ] . '</td><td>' . $row['essid'] . '</td><td>' . $key . '</td><td><a href="' . $cfg_site_url . "tasks\\" . $row[ 'filename' ] . '" class="btn btn-default"><span class="glyphicon glyphicon-download"></span></a><td>' . $row[ 'agents' ] . '</td><td class="status">' . getStatus( $row[ 'status' ] ) . '</td>';
						$admin_pan_str = '<td><a class="btn btn-default"><span class="glyphicon glyphicon-trash"></span></a></td></tr>';
						echo $str;
						if ( $admin )
							echo $admin_pan_str;
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="col-lg-4">
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