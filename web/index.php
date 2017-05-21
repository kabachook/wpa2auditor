<?php include( 'common.php' ); ?>

<!DOCTYPE html>
<html>

<head>

	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<meta name="description" content="Distributed WPA PSK security audit environment"/>
	<meta name="keywords" content="free, audit, security, online, besside-ng, aircrack-ng, pyrit, wpa, wpa2, crack, cracker, distributed, wordlist"/>

	<title>Distributed WPA/WPA2 auditor</title>

	<!--[if IE]>
   		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  	<![endif]-->

	<!-- BOOTSTRAP START 
	
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<!-- BOOTSTRAP END-->
<script src="http://code.jquery.com/jquery-latest.js"></script>
	<!-- PLUGINS START -->
	<script src="js/bootstrap-notify.min.js" type="application/javascript"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
	<!-- PLUGINS END -->

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>

	<script type="text/javascript" src="js/bootstrap-filestyle.min.js">
	</script>
	<link rel="stylesheet" href="css/style.css">
	<script src="js/tasks.js" async=""></script>
	<script src="js/stat.js" async=""></script>
</head>

<body>
	<nav class="navbar navbar-toggleable-md navbar-light bg-faded mb-3 justify-content-between">
		<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
    		<span class="navbar-toggler-icon"></span>
  		</button>
	
		<a class="navbar-brand" href="?">Distributed WPA auditor</a>


		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse" id="navbarNavDropdown">
			<ul class="navbar-nav mr-auto">
				<li class="nav-item"><a class="nav-link" href="?tasks">Tasks</a>
				</li>
				<li class="nav-item"><a class="nav-link" href="?dicts">Dicts</a>
				</li>
				<!--<li><a href="?stat">Stats</a>
					</li>-->
			</ul>

			
				<!-- LOGIN BUTTON -->
				
				<form class="form-inline my-2 my-lg-0 m-2 mr-4" action="?search" method="post">
				<div class="input-group">
						<input type="text" class="form-control mr-1" placeholder="Search by ESSID" name="search_query">
						<button type="submit" class="btn btn-secondary">Search</button>
					</div>
				</form>
				<?php

				//Check if we have key in cookie
				if (isset($_COOKIE['key'])) {

					?>
					<form class="form-inline my-2 my-lg-0" action="" method="post"><div class="input-group">
				<span class="navbar-text  mb-2 mr-sm-2 mb-sm-0">Signed in as <strong><a href="?profile"><?php echo getNickname(); ?> </a></strong>
				</span>
				<input type="hidden" name="remkey" value="1"/><button type="submit" class="btn btn-secondary">Log out</button></div>
				</form>

				<?php
				} else {
					//If false - login and signup button
					?>
				<form class="form-inline my-2 my-lg-0" action="" method="post">
					<div class="form-group">
						<input type="text" class="form-control mr-1" placeholder="Key" name="key" maxlength="32">
						<button type="submit" class="btn btn-secondary">Log in</button> <span class="mx-1">or</span> <a href="?get_key" class="btn btn-secondary">Sign up</a>
					</div>
				</form>
				<?php
				}
				?>
			
				<!-- LOGIN BUTTON END -->
			
		</div>
		<!-- /.navbar-collapse -->
		</div>
		<!-- /.container-fluid -->
	</nav>
	<!-- nav bar end -->
	<div id="content">
		<?php include($cont) ?>
	</div>
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