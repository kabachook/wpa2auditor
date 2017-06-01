<?php

//Configuration
include( '../conf.php' );

//Connect to DB
include( '../db.php' );

include( 'File.abstract.php' );

class Dictionary extends File {

	private $id;

	private $server_path;
	private $site_path;

	private $hash;

	private $dict_name;
	private $file_name;

	protected $size;

	function __construct( $file, $dict_name ) {

	}

	static function get_dict_from_db( $dict_id ) {

		//vars
		$instance = new self();

		global $mysqli;

		//Get all info from DB
		$sql = "SELECT * FROM dicts WHERE id='" . $dict_id . "'";
		$result = $mysqli->query( $sql );
		if ( $result == false )
			throw new Exception( "Error in handling to DB." );
		$result = $result->fetch_object();

		$instance->id = $dict_id;
		$instance->server_path = $result->server_path;
		$instance->site_path = $result->site_path;
		$instance->dict_name = $result->dict_name;
		$instance->file_name = $result->file_name;
		$instance->size = $result->size;

		return $instance;
	}

	static function get_dict_from_file( $file, $dict_name ) {

		//vars
		global $cfg_dicts_target_folder;
		global $cfg_dicts_max_file_size;
		global $cfg_dicts_allowed_ext;
		global $cfg_site_url;
		global $mysqli;
		$instance = new self();

		//filename
		$instance->file_name = $instance->generate_random_string( 16 );

		//Get size and ext
		$instance->get_information_from_file( $file );
		
		//Where we want to upload file
		$instance->target_file = $cfg_dicts_target_folder . $instance->file_name . $instance->extension;
		
		//Get dict name that user send
		$instance->dict_name = $dict_name;

		//Check file exists
		if ( $instance->check_file_exists( $instance->target_file ) )
			throw new Exception( 'File already exists. Contact AtomicMan', 0 );

		//Check size
		if ( !( $instance->check_file_size( $instance->size, $cfg_dicts_max_file_size ) ) )
			throw new Exception( 'File is bigger than allowed max file size. ', 1 );

		//Check file format
		if ( !( $instance->check_file_extension( $instance->extension, $cfg_dicts_allowed_ext ) ) )
			throw new Exception( 'Forbidden file format.', 2 );

		//Try to move file
		if ( !move_uploaded_file( $file[ "tmp_name" ], $instance->target_file ) )
			throw new Exception( 'Error while moving file on server from ' . $file[ 'tmp_name' ] . ' to ' . $instance->target_file . $instance->extension, 3 );

		$instance->server_path = $cfg_dicts_target_folder . $instance->file_name . $instance->extension;
		$instance->site_path = $cfg_site_url . "dicts/" . $instance->file_name . $instance->extension;
		
		//Get hash of handshake
		$instance->hash = hash_file( "sha256", $instance->server_path );
		
		//Add dict to DB
		$instance->add_dict_to_db( $mysqli );
		
		//Get id for query
		$instance->id = $mysqli->insert_id;
		
		//Add cuurent dict to all uncomlety tasks
		$instance->add_dict_to_tasks( $mysqli );

		return $instance;
	}

	private function add_dict_to_db( $mysqli ) {

		$sql = "INSERT INTO dicts(server_path, site_path, hash, dict_name, file_name, size) VALUES('" . $this->server_path . "', '" . $this->site_path . "', UNHEX('" . $this->hash . "'), '" . $this->dict_name . "', '" . $this->file_name . "', '" . $this->size . "')";
		$mysqli->query( $sql );
	}

	function get_all_info() {

		$info = [];

		$info[ 'server_path' ] = $this->server_path;
		$info[ 'site_path' ] = $this->site_path;
		$info[ 'dict_name' ] = $this->dict_name;
		$info[ 'file_name' ] = $this->file_name;
		$info[ 'size' ] = $this->size;
		$info[ 'id' ] = $this->id;

		return $info;
	}

	function delete_dict() {
		
		global $mysqli;
		
		//Id of dict for delete
		$id = $this->id;

		$path = $this->server_path;
		
		//Delete file
		unlink( $path );

		$sql = "DELETE FROM dicts WHERE id='" . $id . "'";
		$mysqli->query( $sql );
		$sql = "DELETE FROM tasks_dicts WHERE dict_id='" . $id . "'";
		$mysqli->query( $sql );

	}

	private function add_dict_to_tasks( $mysqli ) {
		
		//Get id for all uncomlety tasks
		$sql = "SELECT id FROM tasks WHERE status NOT IN('2')";
		$array_tasks = $mysqli->query( $sql )->fetch_all( MYSQL_ASSOC );
		
		//For all tasks add current dict
		foreach ( $array_tasks as $task ) {
			$sql = "INSERT INTO tasks_dicts(net_id, dict_id, status) VALUES('" . $task[ 'id' ] . "', '" . $this->id . "', '0')";
			$mysqli->query( $sql );
		}
	}
}
?>