<?php

include( '../conf.php' );
include( '../db.php');
include( '../Model/File.abstract.php' );

class Handshake extends File {

	private $array_of_handshakes = [];

	private $type = 0;

	function __construct() {

	}

	static
	function get_handshake_from_db( $task_id ) {

		//vars
		global $mysqli;
		$instance = new self();

		//Get all info from DB
		$sql = "SELECT * FROM tasks WHERE id='" . $task_id . "'";
		$result = $mysqli->query( $sql );
		if ( $result == false )
			throw new Exception( "Error in handling to DB." );
		$result = $result->fetch_object();

		$instance->server_path = $result->server_path;
		$info = $instance->get_handshake_info( $instance->server_path );

		array_push( $instance->array_of_handshakes, array( "structure" => $info ) );

		return $instance;

	}

	static
	function get_handshake_from_file( $file ) {
		
		//vars
		global $cfg_tasks_target_folder;
		global $cfg_tasks_max_file_size;
		global $cfg_tasks_allowed_ext;
		global $cfg_tools_cap2hccapx;
		global $cfg_site_url;

		$instance = new self();

		//Generate filename
		$instance->filename = $instance->generate_random_string( 16 );

		//Get size and ext from file
		$instance->get_information_from_file( $file );

		$instance->target_file = $cfg_tasks_target_folder . $instance->filename . $instance->extension;

		//Check file exists
		if ( $instance->check_file_exists( $instance->target_file ) )
			throw new Exception( 'File already exists. Contact AtomicMan', 0 );

		//Check size
		if ( !( $instance->check_file_size( $instance->size, $cfg_tasks_max_file_size ) ) )
			throw new Exception( 'File is bigger than allowed max file size. ', 1 );

		//Check file format
		if ( !( $instance->check_file_extension( $instance->extension, $cfg_tasks_allowed_ext ) ) )
			throw new Exception( 'Forbidden file format.', 2 );

		//Try to move file
		if ( !move_uploaded_file( $file[ "tmp_name" ], $instance->target_file ) )
			throw new Exception( 'Error while moving file on server from ' . $file[ 'tmp_name' ] . ' to ' . $instance->target_file . $instance->extension, 3 );

		if ( $instance->extension == ".cap" ) {

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
			$instance->extension = ".hccapx";
			$tmp = $instance->cap_converter( $instance->target_file, $cfg_tasks_target_folder . $instance->filename . $instance->extension, $cfg_tools_cap2hccapx );
			if ( $tmp == false )
				throw new Exception( 'Fail while convert handshake.', 6 );
			$instance->size = 393;

		}

		//Handshake slice
		$sliced = $instance->slice_handshake( $cfg_tasks_target_folder, $instance->filename, $instance->extension, $instance->size, $cfg_site_url . "tasks/" );
		if ( $sliced == false )
			throw new Exception( 'Fail while slice handshake.', 7 );

		$instance->array_of_handshakes = $sliced;

		//get handshake info and add to array
		foreach ( $instance->array_of_handshakes as & $hndshk ) {
			$info = $instance->get_handshake_info( $hndshk[ 'server_path' ] );
			$hndshk[ "structure" ] = $info;
			$hndshk[ "uniq_hash" ] = $instance->uniq_hash( $info[ 'keymic' ], $info[ 'mac_ap' ], $info[ 'essid' ] );
		}

		return $instance;

	}

	function check_key( $key ) {
		
		$ahccap = $this->array_of_handshakes[0]['structure'];

		$m = $ahccap[ 'm' ];
		$n = $ahccap[ 'n' ];
		//Need only for check key
		$block = "Pairwise key expansion\0" . $m . $n . "\0";

		$pmk = hash_pbkdf2( 'sha1', $key, $ahccap[ 'essid' ], 4096, 32, True );
		$ptk = hash_hmac( 'sha1', $block, $pmk, True );

		if ( $ahccap[ 'keyver' ] == 1 )
			$testmic = hash_hmac( 'md5', $ahccap[ 'eapol' ], substr( $ptk, 0, 16 ), True );
		else
			$testmic = hash_hmac( 'sha1', $ahccap[ 'eapol' ], substr( $ptk, 0, 16 ), True );

		//If mic whick we get with our key match with keymic in our handshake
		if ( strncmp( $testmic, $ahccap[ 'keymic' ], 16 ) == 0 )
			return $key;

		return false;
	}

	function uniq_hash( $keymic, $mac_ap, $essid ) {
		return md5( $keymic . $mac_ap . $essid );
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

				$task_hash = hash_file( "sha256", $base . $filename );
				$server_path = $base . $filename;
				$site_path = $cfg_site_url . $filename;
				$filesize = filesize( $base . $filename );

				array_push( $array, array( "task_hash" => $task_hash, "type" => $this->type, "server_path" => $server_path, "site_path" => $site_path, "extension" => $ext, "size" => $filesize ) );
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
?>