<?php

include( 'conf.php' );
include( 'db.php' );
include( 'Model/File.abstract.php' );

class Dictionary extends File {

	public $server_path;
	public $site_path;

	public $hash;
	
	public $dict_name;
	public $file_name;

	function __construct( $file, $dict_name ) {

		//vars
		global $cfg_dicts_target_folder;
		global $cfg_dicts_max_file_size;
		global $cfg_dicts_allowed_ext;
		global $cfg_site_url;
		global $mysqli;
		
		//filename
		$this->file_name = $this->generate_random_string( 16 );
		
		//Get size and ext
		$this->get_information_from_file( $file );

		$this->target_file = $cfg_dicts_target_folder . $this->file_name . $this->extension;
		
		$this->dict_name = $dict_name;
		
		//Check file exists
		if ( $this->check_file_exists( $this->target_file ) )
			throw new Exception( 'File already exists. Contact AtomicMan', 0 );

		//Check size
		if ( !( $this->check_file_size( $this->size, $cfg_dicts_max_file_size ) ) )
			throw new Exception( 'File is bigger than allowed max file size. ', 1 );

		//Check file format
		if ( !( $this->check_file_extension( $this->extension, $cfg_dicts_allowed_ext ) ) )
			throw new Exception( 'Forbidden file format.', 2 );

		//Try to move file
		if ( !move_uploaded_file( $file[ "tmp_name" ], $this->target_file ) )
			throw new Exception( 'Error while moving file on server from ' . $file[ 'tmp_name' ] . ' to ' . $this->target_file . $this->extension, 3 );
		
		$this->server_path = $cfg_dicts_target_folder . $this->file_name . $this->extension;
		$this->site_path = $cfg_site_url . "dicts/" . $this->file_name . $this->extension;
		$this->hash = hash_file("sha256", $this->server_path);
		
		$this->add_dict_to_db($mysqli);

	}
	
	function add_dict_to_db($mysqli) {
		
		$sql = "INSERT INTO dicts(server_path, site_path, hash, dict_name, file_name, size) VALUES('" . $this->server_path . "', '" . $this->site_path . "', UNHEX('" . $this->hash . "'), '" . $this->dict_name . "', '" . $this->file_name . "', '" . $this->size . "')";
		$mysqli->query($sql);
	}

}

?>