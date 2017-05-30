<?php

//Shut down error reporting
error_reporting( 0 );

include( '../Model/Dictionary.class.php' );
include( '../common.php' );

global $admin;

$error_message = [
	'code' => 0,
	'message' => "All is OK.",
	"type" => "success"
];

//Upload dict
if ( isset( $_POST[ 'buttonUploadDict' ] ) ) {
	try {
		$Dict = Dictionary::get_dict_from_file( $_FILES[ 'upfile' ], $_POST[ 'dict_name' ] );
	} catch ( Exception $e ) {
		$error_message[ 'code' ] = $e->getCode();
		$error_message[ 'message' ] = $e->getMessage();
		$error_message[ 'type' ] = "danger";
	}
}

//Delete dict
if ( $_POST[ 'deleteDictionary' ] == 'true' ) {

	//Id of dict for delete
	$id = $_POST[ 'deleteDictID' ];

	$Dict = Dictionary::get_dict_from_db( $id );
	$Dict->delete_dict();
}

//Ajax get table
if ( $_GET[ 'ajax' ] == 'table' ) {

	header( 'Content-Type: application/json' );

	$ajax = [];

	$sql = "SELECT id FROM dicts";
	$result = $mysqli->query( $sql )->fetch_all( MYSQL_ASSOC );
	foreach ( $result as $dict ) {
		$Dict = Dictionary::get_dict_from_db( $dict[ 'id' ] );
		array_push( $ajax, $Dict->get_all_info() );
	}

	$json[ 'admin' ] = $admin;
	$json[ 'table' ] = $ajax;

	echo json_encode( $json );
	exit();
}

if ( $_GET[ 'ajax' ] == 'statusDictUpload' ) {
	echo json_encode( $error_message );
	exit();
}
?>

<div class="container">
	<div class="row">
		<div class="col-md-8">

			<!-- Header -->
			<h2>Dictionaries</h2>
			<!-- Header end -->

			<!-- Table -->
			<div id="ajaxTableDiv">
			</div>
			<!-- Table end -->

		</div>

		<!-- side bar start -->
		<div class="col-md-4">
			<h2>Add new wordlist</h2>
			<form class="" action="" method="post" enctype="multipart/form-data" onSubmit="Dictionary.ajaxUploadDict(this);" id="formUploadDictionary">
				<input type="hidden" name="source" value="upload">
				<input type="hidden" name="action" value="addfile">
				<div class="panel panel-default">
					<table class="table table-bordered table-nonfluid">
						<tbody>
							<tr>
								<th>Upload files</th>
							</tr>
							<tr>
								<td>
									<input type="text" class="form-control" name="dict_name" required="" placeholder="Enter filename">
								</td>
							</tr>
							<tr>
								<td>
									<input type="file" class="form-control" name="upfile" required="">
								</td>
							</tr>
							<tr>
								<td>
									<input type="submit" class="btn btn-primary" value="Upload files" name="buttonUploadFile">
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</form>
		</div>
		<!-- side bar end -->

	</div>
</div>