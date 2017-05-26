<?php 
//Shut down error reporting
//FOR DEV ONLY
//error_reporting(0);
include('Dictionary.class.php');


$error_message = [
	'code' => 0,
	'message' => "All is OK.",
	"type" => "success"
];

if (isset($_POST['buttonUploadFile'])) {
	
	try {
	
		$Dict = new Dictionary($_FILES['upfile'], $_POST['filename']);
	} catch(Exception $e) {
		$error_message[ 'code' ] = $e->getCode();
			$error_message[ 'message' ] = $e->getMessage();
			$error_message[ 'type' ] = "danger";
	}
	
}

function getSizeHum($size) {
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

?>

<div class="container">
	<div class="row">
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
						$sql = "SELECT * FROM dicts WHERE 1";
						$result = $mysqli->query($sql);
						$result = $result->fetch_all(MYSQLI_ASSOC);

						foreach ($result as $row) {
							$str = '<tr><td><strong>' . $row['dict_name'] . '</strong></td><td>' . getSizeHum($row['size']) . '</td><td><a href="' . $row['site_path'] . '" class="btn btn-default">DOWNLOAD</a></td>';
							$adm_str = '<td><form action="" method="post"><input type="hidden" name="deleteDictID" value="' . $row['id'] . '"><button type="submit" class="btn btn-default" name="deleteDict"><span class="glyphicon glyphicon-trash"></span></button></form></td>';
							echo $str;
							if ($admin)
								echo $adm_str;
							echo "</tr>";
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="col-md-4">
			<h2>Add new wordlist</h2>
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
									<input type="submit" class="btn btn-secondary" value="Upload files" name="buttonUploadFile">
								</td>
							</tr>
														<tr>
								<?php echo $error_message['message']; ?>
							</tr>
						</tbody>
					</table>
				</div>
			</form>
		</div>
	</div>
</div>