<?php
	session_start();

/* Author: Lisa Percival
*  Date Created: 5/2/15
*  Description: Display the list of authorized users as links so their details can be
*  viewed. Also make sure someone's logged in and 
*  redirect to signIn if not. */
	include 'configuration.php';
	
	//ensure someone's logged in
	if (!isset($_SESSION['user'])) {
		header("Location: signIn.html");
	}
	
	//connect to database
	$mysqli = new mysqli("mysql.corvallisrecycles.org", "osubenny_sql", 
					$myPwd, "corvallisrr_app");
	if (!$mysqli || $mysqli->connect_errno) {
		echo "Connection to DB failed: (" . $mysqli->connect_errno . ")"
				. $mysqli->connect_error;
	}
	
	//get the list of all users
	$users = $mysqli->query("SELECT user_id, user_login, user_fname, user_lname 
		FROM AuthorizedUsers ORDER BY user_fname ASC");
?>

<html>
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<!-- http://startbootstrap.com/template-overviews/logo-nav/ -->
		<!-- Bootstrap Core CSS -->
		<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
	
		<!-- Custom CSS -->
		<link href="bootstrap/css/logo-nav.css" rel="stylesheet">
		
		<title>Authorized Users</title>
		<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link href="myStyle.css" rel="stylesheet"> <!--can override boostrap-->
		
	</head>
	<body>
		<!-- Navigation -->
		<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
			<div class="container">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="index.php">
						<!-- http://stackoverflow.com/questions/20502040/bootstrap-3-text-next-to-brand -->
						<span><img src="images/ecogreen.png" alt="logo" height="50" width="50"></span>
						Corvallis Reuse & Repair Web Management Interface
					</a>
				</div>
				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
					<ul class="nav navbar-nav">
						<li>
							<a href="companies.php">Companies</a>
						</li>
						<li>
							<a href="categories.php">Categories</a>
						</li>
						<li>
							<a href="users.php">Users</a>
						</li>
						<li>
							<a href="logout.php">Logout</a>
						</li>
					</ul>
				</div>
				<!-- /.navbar-collapse -->
			</div>
			<!-- /.container -->
		</nav>
	
		<!-- Page Content -->
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
				
					<h1> Authorized Users </h1>
					
					<nav role="navigation">
						<a href="add_user.php" class="green-button"> Add </a>
					</nav>
					
					<ul class="well">
						<?php
						if (count($users) == 0) {
							echo "<p> There are currently no companies.</p>";
						}
						else {
							$users->data_seek(0);		//start at 1st row
							while ($user = $users->fetch_assoc()) {
								echo "<li>";
								echo '<a href="view_user.php?ID=' . $user['user_id'] . '">' . $user['user_fname'] . ' ' . $user['user_lname'] . ' (' . $user['user_login'] . ')' . '</a>';
								echo "</li>";
							}
						}
						?>
					</ul>
					
				</div>
			</div>
		</div>
		<!-- /.container -->
	
		<!-- jQuery -->
		<script src="bootstrap/js/jquery.js"></script>
	
		<!-- Bootstrap Core JavaScript -->
		<script src="bootstrap/js/bootstrap.min.js"></script>
	</body>
</html>