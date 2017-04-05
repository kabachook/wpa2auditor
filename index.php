<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
	<meta name="description" content="Distributed WPA PSK security audit environment" />
	<meta name="keywords" content="free, audit, security, online, besside-ng, aircrack-ng, pyrit, wpa, wpa2, crack, cracker, distributed, wordlist" />

	<title>Distributed WPA auditor</title>

	<!--<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />-->
	<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body>
<!-- script -->
<script src="http://code.jquery.com/jquery-latest.js"></script>
<script src="js/bootstrap.min.js"></script>
<!-- scripts end -->

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
      <a class="navbar-brand" href="#">Distributed WPA auditor</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li><a href="agents.html">Agents</a></li>
        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Tasks <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="#">Show Tasks</a></li>
            <li><a href="#">New Task</a></li>
            <li><a href="#">Pre-conf Tasks</a></li>
          </ul>
        </li>
        <li><a href="#">Files</a></li>
        <li><a href="#">Stats</a></li>
      </ul>
      
      <ul class="nav navbar-nav navbar-right">
       <?php
			if(isset($_COOKIE['key'])) {
				echo '<p class="navbar-text">' . $username . '</p>';
				echo '
				<form class="navbar-form navbar-left" action="login.php?logout=1">
  					<button type="submit" class="btn btn-default">Log out</button>
				</form>
				';
			} else {
				echo '
				<form class="navbar-form navbar-left" action="login.php">
  					<button type="submit" class="btn btn-default">Log in</button>
				</form>';
			}
		?>     
      </ul>
      <form class="navbar-form navbar-right">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Search">
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
      </form>
      
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

<!-- nav bar end -->

<div class="page-header">
  <h1>Как стать частью команды?</h1>
	<p>Нажимаем кнопку Sign up и регистрируемся</p>
</div>
</body>
</html>
