<?php

abstract class File {
	
	protected $filename;
	protected $size;
	protected $extension;
	protected $path;
	
	protected $target_file;
	
	function get_information_from_file( $file ) {

		//Size
		$this->size = $file[ 'size' ];

		//Extension with dot (like .cap)
		$this->extension = "." . pathinfo( $file[ 'name' ], PATHINFO_EXTENSION );

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
	
	function check_file_extension( $extension, $allowed_ext ) {
		return in_array( $extension, $allowed_ext );
	}
	
}

?>