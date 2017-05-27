<?php

include( '..\db.php' );

class Task {
	
	private $server_path;
	private $site_path;
	private $task_name;
	private $user_id;
	private $type;

	//Handshake
	private $essid;
	private $station_mac;
	private $task_hash;

	//NTLM
	private $username;
	private $challenge;
	private $response;

	private $uniq_hash;

	private $status;
	private $net_key;
	private $id;
	
	private $task_id;

	function __construct() {

	}

	static function create_task_from_db( $task_id ) {

		//vars
		global $mysqli;
		$instance = new self();

		//Get all info from DB
		$sql = "SELECT * FROM tasks WHERE id='" . $task_id . "'";
		$result = $mysqli->query($sql);
		if ($result == false)
			throw new Exception("Error in handling to DB.");
		$result = $result->fetch_object();

		$instance->server_path = $result->server_path;
		$instance->site_path = $result->site_path;
		$instance->task_name = $result->task_name;
		$instance->user_id = $result->user_id;
		$instance->essid = $result->essid;
		$instance->station_mac = $result->station_mac;
		$instance->type = $result->type;
		$instance->task_hash = $result->task_hash;
		$instance->uniq_hash = $result->uniq_hash;
		$instance->status = $result->status;
		$instance->net_key = $result->net_key;
		$instance->id = $result->id;

		return $instance;

	}

	static function create_task_from_file( $Handshake, $task_name, $user_id ) {

		//vars
		global $mysqli;
		$instance = new self();

		$instance->task_name = $task_name;
		$instance->user_id = $user_id;
		
		$tmp = $instance->get_information_from_handshake( $Handshake );
		if($tmp == false)
			throw new Exception("Error while getting info from handshake", 88);

		if ( !( $instance->check_uniq( $mysqli, $instance->uniq_hash ) ) )
			throw new Exception( "Hash is not uniq.", 14 );

		if( $instance->add_task_to_db( $mysqli ) == false)
			throw new Exception("Error while adding task to DB.");
		
		$instance->task_id = $mysqli->insert_id;
		
		$instance->add_dicts_to_task($mysqli);

		return $instance;

	}

	function get_all_info() {

		$info[ 'server_path' ] = $this->server_path;
		$info[ 'site_path' ] = $this->site_path;
		$info[ 'task_name' ] = $this->task_name;
		$info[ 'user_id' ] = $this->user_id;
		$info[ 'essid' ] = $this->essid;
		$info[ 'station_mac' ] = $this->station_mac;
		$info[ 'type' ] = $this->type;
		$info[ 'status' ] = $this->status;
		$info[ 'net_key' ] = $this->net_key;
		$info[ 'id' ] = $this->id;

		return $info;
	}

	function check_uniq( $mysqli, $hash ) {
		$sql = "SELECT * FROM tasks WHERE uniq_hash=UNHEX('" . $hash . "')";
		$result = $mysqli->query( $sql )->num_rows;
		
		if ( $result != 0 )
			return false;
		
		return true;
	}

	function get_network_key( $mysqli, $id ) {
		$sql = "SELECT net_key FROM tasks WHERE id='" . $id . "'";
		$net_key = $mysqli->query( $sql )->fetch_object()->net_key;
		
		if ( $net_key == 0 )
			return false;
		else 
			return $net_key;
	}
	
	function check_network_key($net_key) {
		
	}

	function deleteTask() {
		global $mysqli;
		
		//Delete file
		unlink( $this->server_path );

		//Delete task from tasks
		$sql = "DELETE FROM tasks WHERE id='" . $id . "'";
		$mysqli->query( $sql );
	}

	function get_information_from_handshake( $handshake ) {

		$type = $handshake[ 'type' ];

		$this->type = $type;
		$this->task_hash = $handshake[ 'task_hash' ];
		$this->uniq_hash = $handshake[ 'uniq_hash' ];
		$this->server_path = $handshake[ 'server_path' ];
		$this->site_path = $handshake[ 'site_path' ];

		switch ( $type ) {
			case 0:
				//Handshake
				$this->essid = $handshake[ 'structure' ][ 'essid' ];
				$this->station_mac = bin2hex( $handshake[ 'structure' ][ 'mac_ap' ] );
				break;
			case 1:
				//NTLM
				$this->username = $handshake[ 'username' ];
				$this->challenge = $handshake[ 'challenge' ];
				$this->response = $handshake[ 'response' ];
				break;
			default:
				//In case of error
				return false;
		}
		
		return true;
	}

	function add_task_to_db( $mysqli ) {

		switch ( $this->type ) {
			case 0:
				//Handshake
				$sql = "INSERT INTO tasks(task_name, user_id, server_path, site_path, essid, station_mac, type, task_hash, uniq_hash) VALUES('" . $this->user_id . "', '" . $this->task_name . "', '" . $this->server_path . "', '" . $this->site_path . "', '" . $this->essid . "', '" . $this->station_mac . "', '" . $this->type . "', UNHEX('" . $this->task_hash . "'), UNHEX('" . $this->uniq_hash . "'))";
				break;
			case 1:
				//NTLM
				$sql = "INSERT INTO tasks(task_name, user_id, server_path, site_path, type, task_hash, uniq_hash, username, challenge, response) VALUES('" . $this->user_id . "', '" . $this->task_name . "', '" . $this->server_path . "', '" . $this->site_path . "', '1', UNHEX('" . $this->task_hash . "'), UNHEX('" . $this->uniq_hash . "'), '" . $this->username . "', '" . $this->challenge . "', '" . $this->response . "')";
				break;
			default:
				//In case of error
				return false;
		}

		//Add task to DB
		return $mysqli->query( $sql );
	}
	
	function add_dicts_to_task($mysqli) {
		
		//Get all dicts id
		$sql = "SELECT id FROM dicts";
		$result = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);

		//Insert into tasks_dicts for last (current) task all dicts
		foreach ($result as $row) {
			$dict_curr_id = $row['id'];
			$sql = "INSERT INTO tasks_dicts(net_id, dict_id, status) VALUES('" . $this->task_id . "', '" . $dict_curr_id . "', '0')";
			$mysqli->query($sql);
		}
		
	}
}
?>