<?php

include( '../conf.php' );

class NTLM {

	private $type = 1;

	private $username;
	private $challenge;
	private $response;

	private $uniq_hash; //Hash of NTLM username:challenge:response
	private $task_hash; //Hash of file with NTLM hash

	private $server_path;
	private $site_path;

	function __construct( $ntlm ) {

		global $cfg_tasks_target_folder;
		global $cfg_site_url;

		$this->username = $ntlm[ 'username' ];
		$this->challenge = $ntlm[ 'challenge' ];
		$this->response = $ntlm[ 'response' ];

		$this->server_path = $this->write_hash_to_file( $cfg_tasks_target_folder );
		$this->site_path = $cfg_site_url . "tasks/" . basename( $this->server_path );

		$this->uniq_hash = md5( $this->challenge . $this->response );
		$this->task_hash = $this->get_file_hash( $this->server_path );

	}

	private function write_hash_to_file( $target_folder ) {

		//Generate random filename
		$path = $target_folder . $this->generate_random_string( 16 ) . ".ntlm";

		//Write file
		file_put_contents( $path, $this->username . "::::" . $this->response . ":" . $this->challenge );

		//Return path to this file
		return $path;
	}

	protected function generate_random_string( $length ) {
		return substr( str_shuffle( str_repeat( $x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil( $length / strlen( $x ) ) ) ), 1, $length );
	}

	//Get SHA256 hash of file
	protected function get_file_hash( $path ) {
		return hash_file( "sha256", $path );
	}

	function get_array_info() {
		$array = [];
		$array[ 'username' ] = $this->username;
		$array[ 'challenge' ] = $this->challenge;
		$array[ 'response' ] = $this->response;
		$array[ 'uniq_hash' ] = $this->uniq_hash;
		$array[ 'server_path' ] = $this->server_path;
		$array[ 'site_path' ] = $this->site_path;
		$array[ 'type' ] = $this->type;
		$array[ 'task_hash' ] = $this->task_hash;
		return $array;
	}
}
?>