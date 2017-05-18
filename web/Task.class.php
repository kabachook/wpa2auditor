<?php

include('db.php');

class Task {
	
	private $server_path;
	private $site_path;
	private $task_name;
	private $user_id;
	
	private $essid;
	private $station_mac;
	private $type;
	private $task_hash;
	private $uniq_hash;
	private $uniq;
	
	function __construct($Handshake, $task_name, $user_id) {
		
		//vars
		global $mysqli;
		
		$this->task_name = $task_name;
		
		$this->user_id = $user_id;
		
		$this->get_information_from_handshake($Handshake);
		
		if(!($this->check_uniq($mysqli, $this->uniq_hash)))
			throw new Exception("Hash is not uniq.", 14);
		
		$this->add_task_to_db($mysqli);
		
	}
	
	function check_uniq($mysqli, $hash) {
		$sql = "SELECT * FROM tasks WHERE uniq_hash=UNHEX('" . $hash . "')";
		$result = $mysqli->query($sql)->num_rows;
		if ($result != 0)
			return false;
		return true;
	}
	
	function get_network_key($mysqli, $id) {
		$sql = "SELECT net_key FROM tasks WHERE id='" . $id . "'";
		$net_key = $mysqli->query($sql)->fetch_object()->net_key;
		if ($net_key == 0)
			return false;
		else return $net_key;
	}
	
	function get_information_from_handshake($handshake) {
		
		$type = $handshake['type'];
		
		switch($type) {
			case 0:
				$this->server_path = $handshake['server_path'];
				$this->site_path = $handshake['site_path'];
				$this->essid = $handshake['structure']['essid'];
				$this->station_mac = bin2hex($handshake['structure']['mac_ap']);
				$this->type = $type;
				$this->task_hash = $handshake['task_hash'];
				$this->uniq_hash = $handshake['uniq_hash'];
				break;
			case 1:
				break;
		}
	}
		
	function add_task_to_db($mysqli) {

		switch ($this->type) {
			case 0:
				$sql = "INSERT INTO tasks(task_name, user_id, server_path, site_path, essid, station_mac, type, task_hash, uniq_hash) VALUES('" . $this->user_id . "', '" . $this->task_name . "', '" . $this->server_path . "', '" . $this->site_path . "', '" . $this->essid . "', '" . $this->station_mac . "', '" . $this->type . "', UNHEX('" . $this->task_hash . "'), UNHEX('" . $this->uniq_hash . "'))";
				break;
			case 1:
				break;
		}
		var_dump($sql);
		//Add task to DB
		$mysqli->query($sql);
	}
}




?>