<?php
//Remove key
if ( isset( $_POST[ 'remkey' ] ) ) {
	setcookie( 'key', '', 1, '', '', false, true );
	unset( $_COOKIE[ 'key' ] );
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>
	<meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
	<meta name="description" content="Distributed WPA PSK security audit environment"/>
	<meta name="keywords" content="free, audit, security, online, besside-ng, aircrack-ng, pyrit, wpa, wpa2, crack, cracker, distributed, wordlist"/>

	<title>Distributed WPA auditor</title>

	<!-- BOOTSTRAP CSS START -->
	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<!-- BOOTSTRAP CSS END -->

	<link rel="stylesheet" href="css/style.css">
</head>

<body>
	<nav class="navbar navbar-default">
		<div class="container-fluid">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
         		<span class="sr-only">Toggle navigation</span>
         		<span class="icon-bar"></span>
         		<span class="icon-bar"></span>
         		<span class="icon-bar"></span>
       		</button>
			
				<a class="navbar-brand" href="index.php">Distributed WPA auditor</a>
			</div>

			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Tasks <span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="tasks.php">Show Tasks</a>
							</li>
							<li><a href="#">New Task</a>
							</li>
						</ul>
					</li>
					<li><a href="#">Files</a>
					</li>
					<li><a href="#">Stats</a>
					</li>
				</ul>

				<ul class="nav navbar-nav navbar-right">
					<!-- LOGIN BUTTON -->
					<?php
					//Check if we have key in cookie
					if ( isset( $_COOKIE[ 'key' ] ) ) {
						echo '<p class="navbar-text">Signed in as AtomnijPchelovek</p><form class="navbar-form navbar-left" action="" method="post"><input type="hidden" name="remkey" value="1" /><button type="submit" class="btn btn-default">Log out</button>';
					} else {
						echo '<form class="navbar-form navbar-left" action="" method="post"><div class="form-group">
									<input type="text" class="form-control" placeholder="Key" name="key" maxlength="32">
								  </div>
								  <button type="submit" class="btn btn-default">Log in</button>';
					}
					?>
					</form>
					<!-- LOGIN BUTTON END -->
				</ul>
			</div>
			<!-- /.navbar-collapse -->
		</div>
		<!-- /.container-fluid -->
	</nav>

	<!-- nav bar end -->

	<div class="jumbotron mb0 mx">
		<div class="container">
			<h1 class="display-1">WPA2 Auditor</h1>
			<p>Simple distributed wpa2 handshake bruteforce resistance auditor</p>
		</div>

	</div>
	<div class="container">
		<h1>Our features</h1>
	</div>


	<hr>
	<div class="container">
		<div class="alert alert-neutral">
			<h1>Welcome here!</h1>
		</div>
		<footer>
			Copyright Nick Gant and Atomnijchelovek
		</footer>
	</div>
</body>

</html>