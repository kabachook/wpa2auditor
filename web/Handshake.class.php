<?php

include( 'conf.php' );

class Handshake {

	private $filename;
	private $size;
	private $extension;
	private $path;

	public $target_file;

	public $array_of_handshakes;

	function __construct( $file ) {

		//vars
		global $cfg_tasks_target_folder;
		global $cfg_tasks_max_file_size;
		global $cfg_tasks_allowed_formats;
		global $cfg_tasks_allowed_formats;
		global $cfg_tools_cap2hccapx;
		global $cfg_site_url;

		//filename
		$this->filename = $this->generate_random_string( 16 );

		$this->get_information_from_file( $file );

		$this->target_file = $cfg_tasks_target_folder . $this->filename . $this->extension;

		//Check file exists
		if ( $this->check_file_exists( $this->target_file ) )
			throw new Exception( 'File already exists. Contact AtomicMan', 0 );

		//Check size
		if ( !( $this->check_file_size( $this->size, $cfg_tasks_max_file_size ) ) )
			throw new Exception( 'File is bigger than allowed max file size. ', 1 );

		//Check file format
		if ( !( $this->check_file_extension( $this->extension, $cfg_tasks_allowed_formats ) ) )
			throw new Exception( 'Forbidden file format.', 2 );

		//Try to move file
		if ( !move_uploaded_file( $file[ "tmp_name" ], $this->target_file ) )
			throw new Exception( 'Error while moving file on server from ' . $file[ 'tmp_name' ] . ' to ' . $this->target_file . $this->extension, 3 );

		if ( $this->extension == ".cap" ) {

			/*	//Integrity check
				$tmp = check_handshake_integrity();
				if ( $tmp == false )
					throw new Exception( 'Fail to check handshake integrity.', 4 );
				else
					$target_file = $tmp;
				
				//Clean handshake
				$tmp = clean_handshake();
				if ( $tmp == false )
					throw new Exception( 'Fail while clean handshake.', 5 );
				else
					$target_file = $tmp;
			*/

			//Cap converter
			$this->extension = ".hccapx";
			$tmp = $this->cap_converter( $this->target_file, $cfg_tasks_target_folder . $this -> filename . $this->extension, $cfg_tools_cap2hccapx );
			if ( $tmp == false )
				throw new Exception( 'Fail while convert handshake.', 6 );
			$this->size = 393;
			


		}

		//CRUCTH
		//Handshake slice
		$sliced = $this->slice_handshake( $cfg_tasks_target_folder, $this->filename, $this->extension, $this->size, $cfg_site_url . "tasks/" );
		if ( $sliced == false )
			throw new Exception( 'Fail while slice handshake.', 7 );

		$this->array_of_handshakes = $sliced;

		//get handshake info and add to array
		foreach ( $this->array_of_handshakes as & $hndshk ) {
			$info = $this->get_handshake_info( $hndshk[ 'server_path' ] );
			$hndshk[ "structure" ] = $info;
			$hndshk[ "uniq_hash" ] = $this->uniq_hash( $info[ 'keymic' ], $info[ 'mac_ap' ], $info[ 'essid' ] );
		}

	}


	function get_information_from_file( $file ) {

		//Size
		$this->size = $file[ 'size' ];

		//Extension with dot (like .cap)
		$this->extension = "." . pathinfo( $file[ 'name' ], PATHINFO_EXTENSION );

	}

	function uniq_hash( $keymic, $mac_ap, $essid ) {
		return md5( $keymic . $mac_ap . $essid );
	}

	function generate_random_string( $length ) {
		return substr( str_shuffle( str_repeat( $x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil( $length / strlen( $x ) ) ) ), 1, $length );
	}

	function check_file_exists( $path ) {
		return file_exists( $path );
	}

	function check_file_size( $size, $max ) {
		if ( $size > $max )
			return false;
		return true;
	}

	function check_file_extension( $extension, $allowed_formats ) {
		return in_array( $extension, $allowed_formats );
	}

	function check_handshake_integrity() {
		//TODO
		//coWPAtty here
		return false;
	}

	function clean_handshake() {
		//TOOD
		//WPACLEAN
		return false;
	}

	function cap_converter( $in, $out, $cfg_tools_cap2hccapx ) {

		//Run cap2hccapx tool
		exec( $cfg_tools_cap2hccapx . " " . $in . " " . $out );

		//Delete input file
		unlink( $in );

		//Return size
		return filesize( $out );
	}

	function slice_handshake( $base, $filename, $ext, $size, $cfg_site_url ) {

		$in = $base . $filename . $ext;

		$array = [];

		if ( $size % 393 == 0 ) {
			$original = file_get_contents( $in );

			for ( $i = 0; $i < $size; $i += 393 ) {
				$sliced = substr( $original, $i, 393 );
				$filename = $this->generate_random_string( 16 ) . $ext;
				fwrite( fopen( $base . $filename, "w" ), $sliced );
				array_push( $array, array( "task_hash" => hash_file("sha256", $base . $filename),"type" => 0, "server_path" => $base . $filename, "site_path" => $cfg_site_url . $filename, "extension" => $ext, "size" => filesize( $base . $filename ) ) );
			}

			unlink( $in );

			return $array;
		}

		return false;
	}

	function get_handshake_info( $file ) {

		//Open and read handshake file from $file
		$hccapx = file_get_contents( $file );
		$ahccapx = array();

		//Extract info from $hccapx follow official wiki
		$ahccapx[ 'signature' ] = substr( $hccapx, 0x00, 4 );
		$ahccapx[ 'version' ] = substr( $hccapx, 0x04, 4 );
		$ahccapx[ 'message_pair' ] = substr( $hccapx, 0x08, 1 );
		$ahccapx[ 'essid_len' ] = substr( $hccapx, 0x09, 1 );

		//In php < 5.5.0 we don't have Z
		if ( version_compare( PHP_VERSION, '5.5.0' ) >= 0 ) {
			$ahccapx[ 'essid' ] = unpack( 'Z32', substr( $hccapx, 0x0a, 32 ) );
		} else {
			$ahccapx[ 'essid' ] = unpack( 'a32', substr( $hccapx, 0x0a, 32 ) );
		}

		$ahccapx[ 'keyver' ] = unpack( 'C', substr( $hccapx, 0x2a, 1 ) );

		$ahccapx[ 'keymic' ] = substr( $hccapx, 0x2b, 16 );
		$ahccapx[ 'mac_ap' ] = substr( $hccapx, 0x3b, 6 );
		$ahccapx[ 'nonce_ap' ] = substr( $hccapx, 0x41, 32 );
		$ahccapx[ 'mac_sta' ] = substr( $hccapx, 0x61, 6 );
		$ahccapx[ 'nonce_sta' ] = substr( $hccapx, 0x67, 32 );

		$ahccapx[ 'eapol_len' ] = unpack( 'v', substr( $hccapx, 0x87, 2 ) );

		$ahccapx[ 'eapol' ] = substr( $hccapx, 0x89, 256 );

		//Fixup unpack

		$ahccapx[ 'essid' ] = $ahccapx[ 'essid' ][ 1 ];
		$ahccapx[ 'eapol_len' ] = $ahccapx[ 'eapol_len' ][ 1 ];
		$ahccapx[ 'keyver' ] = $ahccapx[ 'keyver' ][ 1 ];

		//Cut eapol to right size
		$ahccapx[ 'eapol' ] = substr( $ahccapx[ 'eapol' ], 0, $ahccapx[ 'eapol_len' ] );

		// fix order
		// m = mac adress ap + mac adress station. Need only for check_key
		if ( strncmp( $ahccapx[ 'mac_ap' ], $ahccapx[ 'mac_sta' ], 6 ) < 0 )
			$m = $ahccapx[ 'mac_ap' ] . $ahccapx[ 'mac_sta' ];
		else
			$m = $ahccapx[ 'mac_sta' ] . $ahccapx[ 'mac_ap' ];

		//n = noonce_ap + nonce_sta. Need only for check_key
		if ( strncmp( $ahccapx[ 'nonce_ap' ], $ahccapx[ 'nonce_sta' ], 6 ) < 0 )
			$n = $ahccapx[ 'nonce_ap' ] . $ahccapx[ 'nonce_sta' ];
		else
			$n = $ahccapx[ 'nonce_sta' ] . $ahccapx[ 'nonce_ap' ];

		$ahccapx[ 'm' ] = $m;
		$ahccapx[ 'n' ] = $n;

		return $ahccapx;

	}

	function get_array_of_handshakes() {
		return $this->array_of_handshakes;
	}

}

//NTLM Hashes
if ( isset( $_POST[ 'buttonUploadHash' ] ) && $_POST[ 'buttonUploadHash' ] == "true" ) {

	$user_id = getUserID();

	//NTLM credential
	$task_name = $_POST[ 'taskname' ];
	$username = $_POST[ 'username' ];
	$challenge = $_POST[ 'challenge' ];
	$response = $_POST[ 'response' ];

	//Setup site path to output file
	$site_path = $cfg_site_url . "tasks/" . $task_name . ".ntlm";

	//Setup server path to output file
	$server_path = $cfg_tasks_targetFolder . $task_name . ".ntlm";

	//Chekc NTLM uniq
	//It doesn't work. Need only for adding to db
	$uniq_hash = md5( $username . $challenge . $response );

	$sql = "SELECT * FROM tasks WHERE uniq_hash=UNHEX('" . $uniq_hash . "')";
	$result = $mysqli->query( $sql );
	if ( $result->num_rows != 0 ) {
		//Hash is not uniq

		//Get the key
		$result = $result->fetch_object();

		$status_hash_uploading = [
			'type' => 'danger',
			'error' => true,
			'message' => 'Hash already in DB. Password is ' . ( $result->net_key == 0 ? 'not found yet' : $result->net_key ),
		];
	} else {

		//Add hash to DB
		$sql = "INSERT INTO tasks(name, type, username, challenge, response, user_id, site_path, server_path, ext, uniq_hash) VALUES('" . $task_name . "', '1', '" . $username . "', '" . $challenge . "', '" . $response . "', '" . $user_id . "', '" . $site_path . "', '" . $server_path . "', 'ntlm', UNHEX('" . $uniq_hash . "'))";
		$ans = $mysqli->query( $sql );

		//Write ntlm hash file on server
		file_put_contents( $cfg_tasks_targetFolder . $task_name . ".ntlm", $username . "::::" . $response . ":" . $challenge );

		//Check for error while adding to db	
		$status_hash_uploading = [
			'type' => $ans ? 'success' : 'danger',
			'error' => !$ans,
			'message' => $ans ? '<strong>OK!</strong> Hash uploaded sucefully!' : '<strong>Failed.</strong>',
		];

		//Get all dicts id
		$sql = "SELECT id FROM dicts";
		$result = $mysqli->query( $sql )->fetch_all( MYSQLI_ASSOC );

		//Insert into tasks_dicts for last (current) task all dicts
		foreach ( $result as $row ) {
			$dict_curr_id = $row[ 'id' ];
			$sql = "INSERT INTO tasks_dicts(net_id, dict_id, status) VALUES('" . getLastNetID() . "', '" . $dict_curr_id . "', '0')";
			$mysqli->query( $sql );
		}
	}
}
?>