<?php

//Connect to DB
include( '../db.php' );

class Agent {

	private $id;

	private $user_id;
	private $user_nick;

	private $agent_key;
	private $os;
	private $perf;
	private $ts;
	private $status;

	function __construct() {

	}

	static function get_agent_from_db( $agent_id ) {

		//vars
		global $mysqli;
		$instance = new self();

		$sql = "SELECT * FROM agents WHERE id='" . $agent_id . "'";
		$result = $mysqli->query( $sql )->fetch_object();

		$instance->id = $result->id;
		$instance->user_id = $result->user_id;
		$instance->agent_key = $result->agent_key;
		$instance->os = $result->os;
		$instance->perf = $result->perf;
		$instance->ts = $result->ts;
		$instance->status = $result->status;
		$instance->user_nick = $result->user_nick;

		return $instance;

	}

	function get_all_info() {

		$array = [];

		$array[ 'id' ] = $this->id;
		$array[ 'user_id' ] = $this->user_id;
		$array[ 'agent_key' ] = bin2hex( $this->agent_key );
		$array[ 'os' ] = $this->os;
		$array[ 'perf' ] = $this->perf;
		$array[ 'ts' ] = $this->ts;
		$array[ 'status' ] = $this->status;
		$array[ 'nick' ] = $this->user_nick;

		return $array;
	}
}
?>