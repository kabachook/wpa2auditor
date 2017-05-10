<?php
//There we want to upload file
$target_file = $cfg_dicts_targetFolder . basename( $_FILES[ "upfile" ][ "name" ] );
$uploadCode = 1;
$uploadFileType = pathinfo( $target_file, PATHINFO_EXTENSION );
$status_file_uploading;

function getSizeHum ($size) {
	//get size in bytes
	
	$sizes = [
		'k', 'M', 'G', 'T'
	];
	
	$res;
	$i = -1;
	while ($size > 100) {
		$size /= 1024;
		$i++;
	}
	return round($size, 2) . " " . $sizes[$i] . "B";
}

function addDictToDB( $dServerPath, $dname, $dfilename, $dFileSize ) {
	global $mysqli;
	global $cfg_site_url;

	//Path to download
	$dpath = $cfg_site_url . 'dicts/' . $dfilename;

	//Sha256 of file
	$dhash = hash_file( "sha256", $dServerPath );

	$sql = "INSERT INTO dicts(dpath, dhash, dname, size, filename) VALUES('" . $dpath . "', UNHEX('" . $dhash . "'), '" . $dname . "', '" . $dFileSize . "', '" . $dfilename . "')";
	$mysqli->query( $sql );

	//For all tasks add this dict to tasks_dicts
	$sql = "SELECT id FROM dicts WHERE dhash=UNHEX('" . $dhash . "')";
	$dict_id = $mysqli->query( $sql )->fetch_all( MYSQL_ASSOC )[ 0 ][ 'id' ];
	$sql = "SELECT id FROM tasks WHERE status NOT IN ('2')";
	$tasks_id = $mysqli->query( $sql )->fetch_all( MYSQL_ASSOC );
	foreach ( $tasks_id as $tid ) {
		$sql = "INSERT INTO tasks_dicts(net_id, dict_id, status) VALUES('" . $tid[ 'id' ] . "', '" . $dict_id . "', '0')";
		$mysqli->query( $sql );
	}
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
	if ( $_FILES[ "upfile" ][ "size" ] > $cfg_dicts_maxFileSize ) {
		$uploadCode = 3;
	}

	//Allow file formats only exists in list
	//White list of allowed file formats
	$whiteList = array( 'txt', 'zip', 'rar', '7z', 'lst', 'dct', 'gz', 'tar', 'txt.gz' );
	if ( !in_array( $uploadFileType, $whiteList ) ) {
		$uploadCode = 4;
	}

	//If uploadCode != 1 => that's an error
	if ( $uploadCode != 1 ) {
		$status_file_uploading = '<td><div class="alert alert-danger mb0" role="alert"><strong>' . $errors[ $uploadCode ] . '</strong></div></td>';
		// if everything is ok, try to upload file
	} else {
		if ( move_uploaded_file( $_FILES[ "upfile" ][ "tmp_name" ], $target_file ) ) {
			//Only if file uploaded without error, we add it to db
			addDictToDB( $target_file, $_POST[ 'filename' ], $_FILES[ "upfile" ][ "name" ], $_FILES[ "upfile" ][ "size" ] );
			$status_file_uploading = '<td><div class="alert alert-success mb0" role="alert"><strong>OK!</strong> File uploaded sucefully!</div></td>';
		} else {
			$status_file_uploading = '<td><div class="alert alert-danger mb0" role="alert"><strong>Error while moving file on server. Contact Kabachook.</strong></div></td>';
		}
	}
}
//Delete task by admin panel
if ( isset( $_POST[ 'deleteDict' ] ) && $admin ) {
	$id = $_POST[ 'deleteDictID' ];
	
	$sql = "SELECT filename FROM dicts WHERE id = '" . $id . "'";
	$path = $cfg_dicts_targetFolder . $mysqli->query($sql)->fetch_object()->filename;
	unlink($path);
	
	$sql = "DELETE FROM dicts WHERE id='" . $id . "'";
	$mysqli->query( $sql );
	$sql = "DELETE FROM tasks_dicts WHERE dict_id='" . $id . "'";
	$mysqli->query( $sql );
}
?>
<div class="container">
	<div class="col-md-8">
		<h2>Wordlist</h2>
		<div class="panel panel-default">
			<table class="table table-striped table-bordered table-nonfluid">
				<tbody align="center">
					<tr>
						<th>Name</th>
						<th>Size</th>
						<th>Download</th>
						<?php if($admin) echo "<th>admin</th>"; ?>
					</tr>
					<?php
					//Show dicts from DB 
					$sql = "SELECT id, dname, dpath, size FROM dicts WHERE 1";
					$result = $mysqli->query( $sql );
					$result = $result->fetch_all( MYSQLI_ASSOC );

					foreach ( $result as $row ) {
						$str = '<tr><td><strong>' . $row[ 'dname' ] . '</strong></td><td>' . getSizeHum($row[ 'size' ]) . '</td><td><a href="' . $row[ 'dpath' ] . '" class="btn btn-default">DOWNLOAD</a></td>';
						$adm_str = '<td><form action="" method="post"><input type="hidden" name="deleteDictID" value="' . $row[ 'id' ] . '"><button type="submit" class="btn btn-default" name="deleteDict"><span class="glyphicon glyphicon-trash"></span></button></form></td>';
						echo $str;
						if ( $admin )
							echo $adm_str;
						echo "</tr>";
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="col-md-4">
		<h3>Add new wordlist</h3>
		<form class="" action="" method="post" enctype="multipart/form-data">
			<input type="hidden" name="source" value="upload">
			<input type="hidden" name="action" value="addfile">
			<div class="panel panel-default">
				<table class="table table-bordered table-nonfluid">
					<tbody>
						<tr>
							<th>Upload files</th>
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
	</div>

</div>