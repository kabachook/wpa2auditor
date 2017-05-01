<?php
//There we want to upload file
$target_file = $cfg_tasks_targetFolder . basename( $_FILES[ "upfile" ][ "name" ] );
$uploadCode = 1;
$uploadFileType = pathinfo( $target_file, PATHINFO_EXTENSION );
$status_file_uploading;

function addTaskToDB( $name, $filename ) {
	global $mysqli;
	global $cfg_site_url;
	//Add task to db
	$sql = "INSERT INTO tasks(name, type, priority, filename) VALUES('" . $name . "', '0', '0', '" . $filename . "')";
	$mysqli->query( $sql );
	
	//Get all dicts id
	$sql = "SELECT id FROM dicts";
	$result = $mysqli->query($sql);
	$result = $result->fetch_all(MYSQLI_ASSOC);
	//Insert into tasks_dicts for last (current) task all dicts
	foreach ($result as $row) {
		$dict_curr_id = $row['id'];
		$sql = "INSERT INTO tasks_dicts(net_id, dict_id, status) VALUES('" . getNetId() . "', '" . $dict_curr_id . "', '0')";
		$mysqli->query($sql);
	}
}

function getNetId() {
	global $mysqli;
	$sql = "SELECT MAX(id) FROM tasks";
	$result = $mysqli->query( $sql );
	$result = $result->fetch_assoc();
	return $result['MAX(id)'];
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
	if ( $uploadFileType != "hccap" ) {
		$uploadCode = 4;
	}

	//If uploadCode != 1 => that's an error
	if ( $uploadCode != 1 ) {
		$status_file_uploading = '<td><div class="alert alert-danger mb0" role="alert"><strong>' . $errors[ $uploadCode ] . '</strong></div></td>';
		// if everything is ok, try to upload file
	} else {
		if ( move_uploaded_file( $_FILES[ "upfile" ][ "tmp_name" ], $target_file ) ) {
			//Only if file uploaded without error, we add it to db
			addTaskToDB( $_POST[ 'filename' ], $_FILES[ "upfile" ][ "name" ] );
			$status_file_uploading = '<td><div class="alert alert-success mb0" role="alert"><strong>OK!</strong> File uploaded sucefully!</div></td>';
		} else {
			$status_file_uploading = '<td><div class="alert alert-danger mb0" role="alert"><strong>Error while moving file on server. Contact Kabachook.</strong></div></td>';
		}
	}
}
?>
<div class="container">
	<div class="col-md-8">
		<h2>Tasks</h2><!--
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
      </div>-->
		<div class="panel panel-default">
			<table class="table table-striped table-bordered table-nonfluid">
				<tbody>
					<tr>
						<th>#</th>
						<th>Name</th>
						<th>Key</th>
						<th>Files</th>
						<th>Agents</th>
						<th>Status</th>
					</tr>
					<?php
					function getStatus($status) {
						$listOfStatus = [
							0 => "IN QUEUE",
							1 => "IN PROGRESS",
							2 => "SUCCESS",
							3 => "FAILED",
						];
						return $listOfStatus[$status];
					}
					//Show dicts from DB
					global $mysqli;
					$sql = "SELECT id, name, filename, status, agents, net_key FROM tasks WHERE 1";
					$result = $mysqli->query($sql);
					$result = $result->fetch_all(MYSQLI_ASSOC);
					
					if ($row['net_key'] == 0) {
						$key = "NOT FOUND";
					} else {
						$key = $row['net_key'];
					}
					$id = 0;
					foreach($result as $row) {
						$id++;
						$str = '<tr><td><strong>' . $id . '</strong></td><td>' . $row['name'] . '</td><td>' . $key . '</td><td><a href="' . $cfg_site_url . "tasks\\" . $row['filename'] . '" class="btn btn-default">DOWNLOAD</a><td>' . $row['agents'] . '</td><td class="status">' . getStatus($row['status']) . '</td></tr>';
						echo $str;
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="col-md-4">
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
								<input type="file" class="form-control" name="upfile">
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
	</div>

</div>