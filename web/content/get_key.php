<?php

//Shut down error reporting
error_reporting( 0 );

require( 'db.php' );

$error_reg_message = "";

if ( isset( $_POST[ 'rec_valid' ] ) ) {

	$email = $_POST[ 'email' ];
	$nickname = $_POST[ 'nick' ];
	$invite = $_POST[ 'invite' ];


	if ( checkNick( $nickname ) && checkEmail( $email ) ) {

		if ( $invite != null ) {
			$res = checkInvite( $invite );

			if ( $res[ 'check' ] ) {
				
				$rang = "user";
				if ($res['admin'])
					$rang = "admin";
				
				//set invited
				setInvitedCPlusOne($invite);
				
				$userkey = md5( md5( $nickname ) . md5( $email ) );
				$user_invite = substr(md5(rand()), 0, 16);
				
				//put new key in db
				$sql = "INSERT INTO users(userkey, email, nick, rang, invite) VALUES(UNHEX('" . $userkey . "'), '$email', '$nickname', '$rang', UNHEX('" . $user_invite . "'))
                ON DUPLICATE KEY UPDATE userkey=UNHEX('$userkey'), ts=CURRENT_TIMESTAMP()";

				$result = $mysqli->query( $sql );

				//set cookie
				setcookie( 'key', $userkey, 2147483647, '', '', false, true );
				$_COOKIE[ 'key' ] = $userkey;
			} else {
				//invite error
				$error_reg_message = '<div class="alert alert-danger form-group" role="alert">Wrong invite</div>';
			}
		} else {
				$rang = "user";
				$userkey = md5( md5( $nickname ) . md5( $email ) );
				$user_invite = substr(md5(rand()), 0, 32);
				
				//put new key in db
				$sql = "INSERT INTO users(userkey, email, nick, rang, invite) VALUES(UNHEX('" . $userkey . "'), '$email', '$nickname', '$rang', UNHEX('" . $user_invite . "'))
                ON DUPLICATE KEY UPDATE userkey=UNHEX('$userkey'), ts=CURRENT_TIMESTAMP()";

				$result = $mysqli->query( $sql );

				//set cookie
				setcookie( 'key', $userkey, 2147483647, '', '', false, true );
				$_COOKIE[ 'key' ] = $userkey;
		}


	} else {
		$error_reg_message = '<div class="alert alert-danger form-group" role="alert"><strong>Duplicate nick or email</strong></div>';
	}
}

function setInvitedCPlusOne ($invite) {
	global $mysqli;
	//get invited
	$sql = "SELECT invited_c FROM users WHERE invite=UNHEX('" . $invite . "') ";
	$count = $mysqli->query($sql)->fetch_object()->invited_c;
	//set invited +1
	$sql = "UPDATE users SET invited_c='" . ($count + 1) . "' WHERE invite=UNHEX('" . $invite . "')";
	$mysqli->query($sql);
}

//Check invite
function checkInvite( $invite ) {
	global $mysqli;
	
	if ( $invite == null ) {
		return true;
	}
	
	if ( !valid_key( $invite ) )
		return false;

	$sql = "SELECT * FROM users WHERE invite=UNHEX('" . $invite . "')";

	$result = $mysqli->query( $sql );

	$res = [
		'check' => false,
		'admin' => false,
	];
	if ( $result->num_rows > 0 ) {
		$res[ 'check' ] = true;
	}
	if ( $result->fetch_object()->rang == 'admin' ) {
		$res[ 'admin' ] = true;
	}
	return $res;
}

//Check if this nickname is uniq
function checkNick( $nick ) {
	global $mysqli;
	$sql = "SELECT * FROM users WHERE nick='$nick'";
	$result = $mysqli->query( $sql );

	if ( $result->num_rows == 1 )
		return false;

	return true;
}

//Check if this email is not uniq
function checkEmail( $email ) {
	global $mysqli;
	$sql = "SELECT * FROM users WHERE email='$email'";
	$result = $mysqli->query( $sql );

	if ( $result->num_rows == 1 )
		return false;

	return true;
}

if ( isset( $_COOKIE[ 'key' ] ) ) {
	echo '<div class="alert alert-danger" role="alert">Key already issued. If you forgot it, your key is <strong>' . $_COOKIE[ 'key' ] . '</strong></div>';
} else {
	?>
	<div class="container">
		<div class="col-md-12">
			<div class="modal-dialog" style="margin-bottom:0">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title"><strong>Sign Up</strong></h3>
					</div>
					<div class="modal-body">
						<form role="form" method="post" action="">
							<fieldset>
								<div class="form-group">
									<input class="form-control" placeholder="Nickname" name="nick" type="text" value="" required="">
								</div>
								<div class="form-group">
									<input class="form-control" placeholder="E-mail" name="email" type="email" autofocus="" required="">
								</div>
								<div class="form-group">
									<input class="form-control" placeholder="Invite" name="invite" type="text" autofocus="">
								</div>
								<div class="form-group">
									<input type="hidden" name="rec_valid" value="1"/>
									<button class="btn btn-md btn-success">Sign up</button>
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
	</div> 

<?php
}
?>