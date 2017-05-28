<?php

/*

AJAX codes for tasks.php
- table = return only table with tasks
- right = return only right side bar
- statusHandshakeUpload = return status of Handshake Upload
- statusHashUpload = return status of hash upload

JSON structure for status_file\hash_uploading
- type
- error
- message

*/

//Shut down error reporting
error_reporting(0);

//Include all we need
include("../common.php");

//There we want to upload file
$target_file = $cfg_tasks_targetFolder . basename($_FILES["upfile"]["name"]);

//Status code of file
$uploadCode = 1;

//File type
$uploadFileType = pathinfo($target_file, PATHINFO_EXTENSION);

//Statuses
$status_file_uploading;
$status_hash_uploading;

//CRUTCH
$sql = "SELECT * FROM tasks WHERE status='3'";
$tasks_lists = $mysqli->query($sql)->fetch_all(MYSQL_ASSOC);
foreach ($tasks_lists as $task) {
	$sql = "SELECT * FROM tasks_dicts WHERE net_id='" . $task['id'] . "' AND status NOT IN('1')";
	$res = $mysqli->query($sql);
	if ($res->num_rows > 0) {
		$sql = "UPDATE tasks SET status='0' WHERE id='" . $task['id'] . "'";
		$mysqli->query($sql);
	}
}

//DB cleaner
function cleanDB() {
	global $mysqli;

	//Clean db
	//get all complete tasks id and delete from tasks_dicts
	$sql = "SELECT id FROM tasks WHERE status IN('2')";
	$task_id = $mysqli->query($sql)->fetch_all(MYSQL_ASSOC);
	foreach ($task_id as $tid) {
		$sql = "DELETE FROM tasks_dicts WHERE net_id='" . $tid['id'] . "'";
		$mysqli->query($sql);
	}
}

//Get all info from handshake in bin and convert it to hex if raw=false
function getHandshakeInfo($path, $raw, $ext) {

	//Open handshake to read
	$hccap = file_get_contents($path);
	$ahccap = array();

	//Follow official hccapx format
	$ahccap['signature'] = substr($hccap, 0x00, 4);
	$ahccap['version'] = substr($hccap, 0x04, 4);
	$ahccap['message_pair'] = substr($hccap, 0x08, 1);
	$ahccap['essid_len'] = substr($hccap, 0x09, 1);

	//In php < 5.5.0 we don't have Z
	if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
		$ahccap['essid'] = unpack('Z32', substr($hccap, 0x0a, 32));
	} else {
		$ahccap['essid'] = unpack('a32', substr($hccap, 0x0a, 32));
	}

	$ahccap['keyver'] = unpack('C', substr($hccap, 0x2a, 1));

	$ahccap['keymic'] = substr($hccap, 0x2b, 16);
	$ahccap['mac_ap'] = substr($hccap, 0x3b, 6);
	$ahccap['nonce_ap'] = substr($hccap, 0x41, 32);
	$ahccap['mac_sta'] = substr($hccap, 0x61, 6);
	$ahccap['nonce_sta'] = substr($hccap, 0x67, 32);

	$ahccap['eapol_len'] = unpack('v', substr($hccap, 0x87, 2));

	$ahccap['eapol'] = substr($hccap, 0x89, 256);

	//Fixup unpack

	$ahccap['essid'] = $ahccap['essid'][1];
	$ahccap['eapol_len'] = $ahccap['eapol_len'][1];
	$ahccap['keyver'] = $ahccap['keyver'][1];

	//Cut eapol to right size
	$ahccap['eapol'] = substr($ahccap['eapol'], 0, $ahccap['eapol_len']);

	// fix order
	// m = mac adress ap + mac adress station. Need only for check_key
	if (strncmp($ahccap['mac_ap'], $ahccap['mac_sta'], 6) < 0)
		$m = $ahccap['mac_ap'] . $ahccap['mac_sta'];
	else
		$m = $ahccap['mac_sta'] . $ahccap['mac_ap'];

	//n = noonce_ap + nonce_sta. Need only for check_key
	if (strncmp($ahccap['nonce_ap'], $ahccap['nonce_sta'], 6) < 0)
		$n = $ahccap['nonce_ap'] . $ahccap['nonce_sta'];
	else
		$n = $ahccap['nonce_sta'] . $ahccap['nonce_ap'];

	$ahccap['m'] = $m;
	$ahccap['n'] = $n;

	//If raw false
	if (!$raw) {
		$ahccap['mac_ap'] = bin2hex($ahccap['mac_ap']);
	}

	$ahccap['ext'] = $ext;

	return $ahccap;
}

//Check if key is a valid key for handshake
function check_key($id, $key) {
	global $mysqli;

	//Get filename for task
	$sql = "SELECT server_path, ext FROM tasks WHERE id='" . $id . "'";
	$result = $mysqli->query($sql)->fetch_object();

	//Path to file on server
	$server_path = $result->server_path;

	//Extension
	$ext = $result->ext;

	//Get handshake info 
	$ahccap = getHandshakeInfo($server_path, true, $ext);

	$m = $ahccap['m'];
	$n = $ahccap['n'];
	//Need only for check key
	$block = "Pairwise key expansion\0" . $m . $n . "\0";

	$pmk = hash_pbkdf2('sha1', $key, $ahccap['essid'], 4096, 32, True);
	$ptk = hash_hmac('sha1', $block, $pmk, True);

	if ($ahccap['keyver'] == 1)
		$testmic = hash_hmac('md5', $ahccap['eapol'], substr($ptk, 0, 16), True);
	else
		$testmic = hash_hmac('sha1', $ahccap['eapol'], substr($ptk, 0, 16), True);

	//If mic whick we get with our key match with keymic in our handshake
	if (strncmp($testmic, $ahccap['keymic'], 16) == 0)
		return $key;

	return NULL;
}

//Convert handshake to hccapx
function handshakeConverter($file) {
	global $cfg_tools_cap2hccapx;

	//Task name
	$name = $file['taskName'];

	//Path to file on server
	$path = $file['server_path_toFile'];

	//Extension
	$extension = $file['ext'];

	//Output to file which we will add to db
	$output = $file['server_path'] . $file['fileName'];

	//Convert cap to hccapx
	if ($extension == "cap") {

		$output = $file['server_path'] . $file['fileName'] . ".hccapx";

		//Execute cap2hccapx
		exec($cfg_tools_cap2hccapx . " " . $path . " " . $output);

		//Delete cap file
		unlink($path);
		$extension = "hccapx";
	}

	//Get size of hccapx
	$size = filesize($output);
	$ret = array();

	//Size of one handshake in hccapx format is 393 bytes ALWAYS
	if ($size == 393) {
		array_push($ret, array("ext" => "hccapx", "path" => $output, "name" => $file['fileName'] . ".hccapx"));
	}
	//If file size is a multiple 393
	elseif ($size % 393 == 0) {

		//cut the file into parts of 393 bytes
		//Open file
		$original = file_get_contents($output);

		$offset = 0;
		for ($i = 0; $i < ($size / 393); $i++) {

			//Take 393 bytes
			$sliced = substr($original, $offset, 393);

			//Set new offset to next slice
			$offset += 393;

			//Set name to new output file
			$out = $output . "_" . ($i + 1) . ".hccapx";

			//Write file
			fwrite(fopen($out, "w"), $sliced);

			//Add to array
			array_push($ret, array("ext" => "hccapx", "path" => $out, "name" => $file['fileName'] . ".hccapx" . "_" . ($i + 1) . ".hccapx"));
		}

		//Delete original hccapx file
		unlink($output);
	} else {
		return NULL;
	}

	return $ret;
}

//Get user id
function getUserID() {
	global $mysqli;

	//Get user by the key
	$sql = "SELECT u_id FROM users WHERE userkey=UNHEX('" . $_COOKIE['key'] . "')";
	$user_id = $mysqli->query($sql)->fetch_object()->u_id;

	if ($user_id == null) {
		//Key doesn't exists, so user not loggged in
		//Return universal id
		return -1;
	}

	//Return user id
	return $user_id;
}

function addTaskToDB($file, $info) {
	global $mysqli;
	global $status_file_uploading;

	$server_path = $file['server_path_toFile'];
	$name = $file['taskName'];
	$filename = $file['fileName'];
	$site_path = $file['site_path'] . $filename;

	//Check if handshake uniq
	//MD5 only for uniq check
	$curr_hand_hash = md5($info["keymic"] . $info['mac_ap'] . $info['essid']);

	//Find out in db
	$sql = "SELECT * FROM tasks WHERE uniq_hash=UNHEX('" . $curr_hand_hash . "')";
	$result = $mysqli->query($sql);

	//If not uniq
	if ($result->num_rows != 0) {

		//Get key of existing handshake
		$result = $result->fetch_object();

		//Change status
		$status_file_uploading = [
			'type' => 'danger',
			'error' => true,
			'message' => 'Hash already in DB. Password is ' . ($result->net_key == 0 ? 'not found yet' : $result->net_key),
		];
		
	} else {
		//Hash is uniq

		$mac = $info['mac_ap'];

		//Get user id
		$user_id = getUserID();

		//Add task to db
		$thash = hash_file("sha256", $server_path);
		$sql = "INSERT INTO tasks(name, type, priority, filename, thash, essid, station_mac, server_path, site_path, uniq_hash, ext, user_id) VALUES('" . $name . "', '0', '0', '" . $filename . "', UNHEX('" . $thash . "'), '" . $info['essid'] . "', '" . $mac . "', '" . $server_path . "', '" . $site_path . "', UNHEX('" . $curr_hand_hash . "'), '" . $info['ext'] . "', '" . $user_id . "')";
		$mysqli->query($sql);

		//Get all dicts id
		$sql = "SELECT id FROM dicts";
		$result = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);

		//Insert into tasks_dicts for last (current) task all dicts
		foreach ($result as $row) {
			$dict_curr_id = $row['id'];
			$sql = "INSERT INTO tasks_dicts(net_id, dict_id, status) VALUES('" . getLastNetID() . "', '" . $dict_curr_id . "', '0')";
			$mysqli->query($sql);
		}
	}
}

function getLastNetID() {
	global $mysqli;
	$sql = "SELECT MAX(id) FROM tasks";
	$result = $mysqli->query($sql);
	$result = $result->fetch_assoc();
	return $result['MAX(id)'];
}

//Upoad file to server, check it, move it, add to DB
//List of errors
$errors = [
	1 => "ALL IS OK",
	2 => "FILE ALREADY EXISTS",
	3 => "FILE BIGGER THAN MAX FILE SIZE",
	4 => "FORBIDDEN FILE FORMAT",
];

if (isset($_POST['buttonUploadFile'])) {

	// Check if file already exists
	if (file_exists($target_file)) {
		$uploadCode = 2;
	}

	// Check file size
	if ($_FILES["upfile"]["size"] > $cfg_tasks_maxFileSize) {
		$uploadCode = 3;
	}

	//Allow hccapx and cap file format only
	//Recheck after js
	$allow_fromat = array("hccapx", "cap");
	if (!in_array($uploadFileType, $allow_fromat)) {
		$uploadCode = 4;
	}

	//If uploadCode != 1 => that's an error
	if ($uploadCode != 1) {

		//Change status to error
		$status_file_uploading = [
			'type' => 'danger',
			'error' => true,
			'message' => '<strong>' . $errors[$uploadCode] . '</strong>',
		];
		
	} else {

		// if everything is ok, try to move file
		if (move_uploaded_file($_FILES["upfile"]["tmp_name"], $target_file)) {

			$status_file_uploading = [
				'type' => 'success',
				'error' => false,
				'message' => '<strong>OK!</strong> Handshake uploaded sucefully!'
			];

			//Only if file uploaded without error, we add it to db
			$path = $cfg_tasks_targetFolder . $_FILES["upfile"]["name"];
			$file = [
				"taskName" => $_POST['task_name'],
				"fileName" => $_FILES["upfile"]["name"],
				"server_path_toFile" => $path,
				"server_path" => $cfg_tasks_targetFolder,
				"site_path" => $cfg_site_url . "tasks/",
				"ext" => $uploadFileType,
			];

			//Return hccapx always
			$conv = handshakeConverter($file);

			if ($conv != NULL) {
				foreach ($conv as $handshake) {
					$file['server_path_toFile'] = $handshake["path"];
					$file['fileName'] = $handshake["name"];
					$info = getHandshakeInfo($handshake["path"], false, $handshake['ext']);
					addTaskToDB($file, $info);
				}
			}

		} else {
			//Failed while moving file
			$status_file_uploading = [
				'type' => 'danger',
				'error' => true,
				'message' => '<strong>Error while moving file on server. Contact Kabachook.</strong>'
			];
		}
	}

	//Clean DB
	cleanDB();
}

//NTLM Hashes
if (isset($_POST['buttonUploadHash']) && $_POST['buttonUploadHash'] == "true") {

	$user_id = getUserID();

	//NTLM credential
	$task_name = $_POST['taskname'];
	$username = $_POST['username'];
	$challenge = $_POST['challenge'];
	$response = $_POST['response'];

	//Setup site path to output file
	$site_path = $cfg_site_url . "tasks/" . $task_name . ".ntlm";

	//Setup server path to output file
	$server_path = $cfg_tasks_targetFolder . $task_name . ".ntlm";

	//Chekc NTLM uniq
	//It doesn't work. Need only for adding to db
	$uniq_hash = md5($username . $challenge . $response);

	$sql = "SELECT * FROM tasks WHERE uniq_hash=UNHEX('" . $uniq_hash . "')";
	$result = $mysqli->query($sql);
	if ($result->num_rows != 0) {
		//Hash is not uniq

		//Get the key
		$result = $result->fetch_object();

		$status_hash_uploading = [
			'type' => 'danger',
			'error' => true,
			'message' => 'Hash already in DB. Password is ' . ($result->net_key == 0 ? 'not found yet' : $result->net_key),
		];
	} else {

		//Add hash to DB
		$sql = "INSERT INTO tasks(name, type, username, challenge, response, user_id, site_path, server_path, ext, uniq_hash) VALUES('" . $task_name . "', '1', '" . $username . "', '" . $challenge . "', '" . $response . "', '" . $user_id . "', '" . $site_path . "', '" . $server_path . "', 'ntlm', UNHEX('" . $uniq_hash . "'))";
		$ans = $mysqli->query($sql);

		//Write ntlm hash file on server
		file_put_contents($cfg_tasks_targetFolder . $task_name . ".ntlm", $username . "::::" . $response . ":" . $challenge);

		//Check for error while adding to db	
		$status_hash_uploading = [
			'type' => $ans ? 'success' : 'danger',
			'error' => !$ans,
			'message' => $ans ? '<strong>OK!</strong> Hash uploaded sucefully!' : '<strong>Failed.</strong>',
		];

		//Get all dicts id
		$sql = "SELECT id FROM dicts";
		$result = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);

		//Insert into tasks_dicts for last (current) task all dicts
		foreach ($result as $row) {
			$dict_curr_id = $row['id'];
			$sql = "INSERT INTO tasks_dicts(net_id, dict_id, status) VALUES('" . getLastNetID() . "', '" . $dict_curr_id . "', '0')";
			$mysqli->query($sql);
		}
	}
}

//WPA key
if (isset($_POST['sendWPAKey']) && $_POST['sendWPAKey'] == "true") {

	foreach ($_POST as $task_id => $wpa_key) {

		//Skipping unnecessary fild
		if ($task_id == "deleteTaskID")
			continue;

		//WPA Key must be from 8 to 64 symbols
		//Ignoring POST value of submit button

		//Check key lenght
		if (strlen($wpa_key) < 8 || strlen($wpa_key) > 64 || $wpa_key == "Send WPA keys")
			continue;

		//Check key
		$result = check_key($task_id, $wpa_key);

		//If check_key return our key, key is valid
		if ($result == $wpa_key) {

			//Update key and status in db
			$sql = "UPDATE tasks SET status='2', net_key='" . $wpa_key . "' WHERE id='" . $task_id . "'";
			$mysqli->query($sql);
		}
	}
}

//Delete task by admin panel
if (isset($_POST['deleteTask']) && $_POST['deleteTask'] == "true" && $admin) {

	//Get id
	$id = $_POST['deleteTaskID'];

	//Get path for handshake
	$sql = "SELECT server_path FROM tasks WHERE id = '" . $id . "'";
	$path = $mysqli->query($sql)->fetch_object()->server_path;

	//Delete file
	unlink($path);

	//Delete task from tasks
	$sql = "DELETE FROM tasks WHERE id='" . $id . "'";
	$mysqli->query($sql);

	//Delete task from tasks_dicts
	$sql = "DELETE FROM tasks_dicts WHERE net_id='" . $id . "'";
	$mysqli->query($sql);
}

?>

<?php

//AJAX

if ($_GET['ajax'] == "statusHandshakeUpload") {
	echo json_encode($status_file_uploading);
	exit();
}
if ($_GET['ajax'] == "statusHashUpload") {
	echo json_encode($status_hash_uploading);
	exit();
}

?>

<?php

//If ajax is unset, return only upper buttons
if (!isset($_GET['ajax'])) {

?>

<div class="container-fluid">

	<!-- Upper buttons (autoreload, show only my networks) -->
	<div class="col-lg-9 col-lg-offset-1">
		<h2>Tasks</h2>

		<div style="overflow: auto;">

			<form style="float: left; padding-right: 5px;" action="" class="form-inline" method="POST" onSubmit="showOnlyMyNetworks(this);">
				<input type="submit" value="Show only my networks" class="btn btn-default" id="buttonShowOnlyMyNetworks">
			</form>

			<div style="overflow: auto;">
				<form style="float: left; padding-right: 5px;" class="form-inline">
					<input type="button" value="Turn on auto-reload" class="btn btn-success" id="buttonTurnOnAutoRefresh">
				</form>
			</div>
			<br>
			<br>
		</div>

		<!-- Div for table. If change id, change in tasks.js too-->
		<div id="ajaxLoadTable"></div>
	</div>
	<div class="col-lg-2">

		<!-- Div for right side bar. If change id, change in tasks.js too -->
		<div id="ajaxLoadRightNavBar"></div>

		<?php
		//If ajax have value
		} else {
			?>


		<?php
		//If client requset table
		if ($_GET['ajax'] == "table") {
			?>

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
					
					//Get user id
					$user_id = getUserID();
			
					//If show only my networks true
					$somn = isset($_GET['somn']) && $user_id != -1 && $_GET['somn'] == "true" ? true : false;
					
					// Paggination
					// Find out how many items are in the table
					$sql = $somn ? "SELECT COUNT(*) as count FROM tasks WHERE user_id='" . $user_id . "'" : "SELECT COUNT(*) as count FROM tasks";

					$total = $mysqli->query($sql)->fetch_object()->count;
				
					// How many items to list per page
					$limit = 20;

					// How many pages will there be
					$pages = ceil($total / $limit);

					// What page are we currently on?
					$page = min($pages, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array(
						'options' => array(
							'default' => 1,
							'min_range' => 1,
						),
					)));

					// Calculate the offset for the query
					$offset = ($page - 1) * $limit;

					// Some information to display to the user
					$start = $offset + 1;
					$end = min(($offset + $limit), $total);

					//Pagger end
					//List of status codes for tasks
					function getStatus($status) {
						$listOfStatus = [
							0 => "IN QUEUE",
							1 => "IN PROGRESS",
							2 => "SUCCESS",
							3 => "FAILED",
						];
						return $listOfStatus[$status];
					}
			
					if ($somn) {
						
						//Show only my networks, if user are logged in
						$sql = "SELECT id, name, filename, status, agents, net_key, essid, station_mac, site_path, type FROM tasks WHERE user_id='" . $user_id . "' ORDER BY id LIMIT " . $limit . " OFFSET " . $offset;
						
					} else {
						
						//Else show all networks
						$sql = "SELECT id, name, filename, status, agents, net_key, essid, station_mac, site_path, type FROM tasks ORDER BY id LIMIT " . $limit . " OFFSET " . $offset;
					}

					//Show tasks from DB
					$result = $mysqli->query($sql);

					if ($result->num_rows > 0) {

						$result = $result->fetch_all(MYSQLI_ASSOC);

						$id = 0;
						foreach ($result as $row) {
							$type;
							if ($row['type'] != "0") {
								$type = "ntlm";
							} else {
								$type = "handshake";
							}
							if ($row['net_key'] == '0' && $type != "ntlm") {
								$key = '<form action="" method="post" enctype="multipart/form-data" class="wpaKeysTable"><input type="text" class="form-control" placeholder="Enter wpa key" name="' . $row['id'] . '"></form>';
							} else {
								$key = "<strong>" . $row['net_key'] . "</strong>";
							}

							$id++;
							//<td>' . $row[ 'agents' ] . '</td>
							$str = '<tr><td><strong>' . $id . '</strong></td><td>' . $type . '</td><td>' . $row['station_mac'] . '</td><td>' . $row['name'] . '</td><td>' . $row['essid'] . '</td><td>' . $key . '</td><td><a href="' . $row['site_path'] . '" class="btn btn-default"><span class="glyphicon glyphicon-download"></span></a><td class="status">' . getStatus($row['status']) . '</td>';

							$tasks_admin_panel = '<td><form action="" method="get" onSubmit="ajaxDeleteTask(this);"><input type="hidden" name="deleteTaskID" value="' . $row['id'] . '"><button type="submit" class="btn btn-default" name="deleteTask"><span class="glyphicon glyphicon-trash"></span></button></form></td>';
							echo $str;
							if ($admin)
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
				$prevlink = ($page > 1) ? '<li><a onClick="ajaxGetPage(' . ($page - 1) . ');" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>': '<li class="disabled"><a aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
				echo $prevlink;

				for ($i = 1; $i <= $pages; $i++) {
					$element = '<li><a onClick="ajaxGetPage(' . $i . ');">' . $i . '</a></li>';
					if ($i == $page) {
						$element = '<li class="active"><a>' . $i . '</a></li>';
					}
					echo $element;
				}

				// The "forward" link
				$nextlink = ($page < $pages) ? '<li><a onClick="ajaxGetPage(' . ($page + 1) . ');" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>': '<li class="disabled"><a aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
				echo $nextlink;

				?>

			</ul>
		</nav>
		<form>
			<input type="button" value="Send WPA keys" name="buttonWpaKeys" class="btn btn-default" onClick="ajaxSendWPAKeys();">
		</form>
		<?php
		//If user requset right side bar
		} else if ($_GET['ajax'] == "right") {
		?>

		<script type="application/javascript">
			$('.fileinput').change(function() {
				file = this.files[0];
			});
		</script>
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
								<input type="submit" class="btn btn-default" value="Upload files" name="buttonUploadFile" id="buttonUploadFile">
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
								<input type="submit" class="btn btn-default" value="Upload hash" name="buttonUploadHash" id="buttonUploadHash">
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</form>
	</div>
</div>
<?php
	}
?>
<?php
}
?>
</div>
</div>