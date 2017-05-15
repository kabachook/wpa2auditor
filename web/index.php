<?php include( 'common.php' ); ?>

<!DOCTYPE html>
<html><head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Distributed WPA PSK security audit environment"/>
	<meta name="keywords" content="free, audit, security, online, besside-ng, aircrack-ng, pyrit, wpa, wpa2, crack, cracker, distributed, wordlist"/>

	<title>Distributed WPA/WPA2 auditor</title>

	<!--[if IE]>
   		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  	<![endif]-->

	<!-- BOOTSTRAP START -->
	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
	<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
	<!-- BOOTSTRAP END-->
	
	<link rel="stylesheet" href="css/style.css">
	<script src="js/tasks.js" async=""></script>
	<script src="js/stat.js" async=""></script>
</head>

<body>
	<nav class="navbar navbar-default mb0">
		<div class="container-fluid">
			<!-- Toggle get grouped for better mobile display -->
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
         		<span class="sr-only">Toggle navigation</span>
         		<span class="icon-bar"></span>
         		<span class="icon-bar"></span>
         		<span class="icon-bar"></span>
       		</button>
			


				<a class="navbar-brand" href="?">Distributed WPA auditor</a>
			</div>

			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
					<li><a href="?tasks">Tasks</a>
					</li>
					<li><a href="?dicts">Dicts</a>
					</li>
					<!--<li><a href="?stat">Stats</a>
					</li>-->
				</ul>

				<ul class="nav navbar-nav navbar-right">
					<!-- LOGIN BUTTON -->
					<form class="navbar-form navbar-left" action="?search" method="post">
						<div class="form-group">
							<input type="text" class="form-control" placeholder="Search by ESSID" name="search_query">
							<button type="submit" class="btn btn-default">Search</button>
						</div>
					</form>
					<?php

					//Check if we have key in cookie
					if ( isset( $_COOKIE[ 'key' ] ) ) {

						?>

						
						<p class="navbar-text">Signed in as <strong><a href="?profile"><?php echo getNickname(); ?> </a></strong></p><form class="navbar-form navbar-left" action="" method="post"><input type="hidden" name="remkey" value="1" /><button type="submit" class="btn btn-default">Log out</button></form>
						<?php
					} else {
					//If false - login and signup button
						?>
						<form class="navbar-form navbar-left" action="" method="post">
								  <div class="form-group">
									<input type="text" class="form-control" placeholder="Key" name="key" maxlength="32">
									<button type="submit" class="btn btn-default">Log in</button> or <a href="?get_key" class="btn btn-default">Sign up</a>
								  </div>
							  </form>
							  <?php
					}
					?>

					<!-- LOGIN BUTTON END -->
				</ul>
			</div>
			<!-- /.navbar-collapse -->
		</div>
		<!-- /.container-fluid -->
	</nav>
	<!-- nav bar end -->

	<?php @include($cont) ?>

	<!-- FOOTER -->
	<hr>
	<div class="container">
		<footer>
			Copyright Nick Gant and AtomnijPchelovek
		</footer>
	</div>
	<!-- FOOTER END -->

</body>

</html>