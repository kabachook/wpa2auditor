<?php
require( 'db.php' );

$error_reg_message = "";

if ( isset( $_POST[ 'rec_valid' ] ) ) {

	$email = $_POST[ 'email' ];
	$nickname = $_POST[ 'nick' ];
	if ( checkNick( $nickname ) || checkEmail( $email ) ) {
		$error_reg_message = '<div class="alert alert-danger form-group" role="alert"><strong>Duplicate nick or email</strong></div>';
	} else {
		$rang = "user";
		$userkey = md5( md5( $nickname ) . md5( $email ) );

		//put new key in db
		$sql = "INSERT INTO users(userkey, email, nick, rang) VALUES(UNHEX('" . $userkey . "'), '$email', '$nickname', '$rang')
                ON DUPLICATE KEY UPDATE userkey=UNHEX('$userkey'), ts=CURRENT_TIMESTAMP()";

		$result = $mysqli->query( $sql );

		//set cookie
		setcookie( 'key', $userkey, 2147483647, '', '', false, true );
		$_COOKIE[ 'key' ] = $userkey;

	}
}

//Check if this nickname is uniq
function checkNick( $nick ) {
	global $mysqli;
	$sql = "SELECT * FROM users WHERE nick='$nick'";
	$result = $mysqli->query( $sql );

	if ( $result->num_rows == 1 )
		return true;

	return false;
}

//Check if this email is uniq
function checkEmail( $email ) {
	global $mysqli;
	$sql = "SELECT * FROM users WHERE email='$email'";
	$result = $mysqli->query( $sql );

	if ( $result->num_rows == 1 )
		return true;

	return false;
}

if ( isset( $_COOKIE[ 'key' ] ) ) {
	echo '<div class="alert alert-danger" role="alert">Key already issued. If you forgot it, your key is <strong>' . $_COOKIE[ 'key' ] . '</strong></div>';
} else {
	?>
	<div class="container">
		<div class="col-md-12">
			<div class="modal-dialog" style="margin-bottom:0">
				<div class="modal-content">
					<div class="panel-heading">
						<h3 class="panel-title"><strong>Sign Up</strong></h3>
					</div>
					<div class="panel-body">
						<form role="form" method="post" action="">
							<fieldset>
								<div class="form-group">
									<input class="form-control" placeholder="Nickname" name="nick" type="text" value="" required="">
								</div>
								<div class="form-group">
									<input class="form-control" placeholder="E-mail" name="email" type="email" autofocus="" required="">
								</div>
								<div class="form-group">
									<input type="hidden" name="rec_valid" value="1"/>
									<button class="btn btn-sm btn-success">Sign up</button>
								</div>
								<?php echo $error_reg_message; ?>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<br/>
	</div> <
	? php
}

?>