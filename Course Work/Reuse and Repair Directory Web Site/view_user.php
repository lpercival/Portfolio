<?php
	session_start();

/* Author: Lisa Percival
*  Date Created: 5/10/14
*  Description: Display the details for a particular user, which is passed
*  in the GET parameter. Make sure someone's logged in. */
	include 'configuration.php';
	
	//ensure someone's logged in
	if (!isset($_SESSION['user'])) {
		header("Location: signIn.html");
	}
	
	//check the ID is set as a GET parameter, otherwise redirect
	if (!isset($_GET['ID'])) {
		header("Location: users.php");
	}
	
	//connect to database
	$mysqli = new mysqli("mysql.corvallisrecycles.org", "osubenny_sql", 
					$myPwd, "corvallisrr_app");
	if (!$mysqli || $mysqli->connect_errno) {
		echo "Connection to DB failed: (" . $mysqli->connect_errno . ")"
				. $mysqli->connect_error;
	}
	
	//check if it was POSTed to by delete button & if so do deletion and redirect
	if (isset($_POST['delete'])) {
		if (!$stmnt = $mysqli->prepare("DELETE FROM AuthorizedUsers WHERE user_id = ?")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("i", $_GET['ID'])) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$stmnt->close();

		//go to users page
		header("Location: users.php");
	}
	
	//get info for the requested user, using prepared statement
	if (!$stmnt = $mysqli->prepare("SELECT user_login, user_fname, user_lname
			FROM AuthorizedUsers WHERE user_id = ?")) {
		echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
	}
	if (!$stmnt->bind_param("i", $_GET['ID'])) {
		echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	if (!$stmnt->execute()) {
		echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	$dbUsername = NULL;	//to hold results
	$dbFname = NULL;
	$dbLname = NULL;
	if (!$stmnt->bind_result($dbUsername, $dbFname, $dbLname)) {
		echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	$stmnt->fetch(); 	//get results
	$stmnt->close();
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
		
		<title>View Authorized User</title>
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
				
					<?php
					echo "<h1> Authorized User: $dbUsername </h1>";
					?>
					
					<div id="forErrors">
					</div>
					<div class="well">
					<ul class="list-group">
						<li class="list-group-item">
							<?php
							echo "<div><p> First Name: $dbFname </p></div>";
							?>
						</li>
						<li class="list-group-item">
							<?php
							echo "<div><p> Last Name: $dbLname </p></div>";
							?>
						</li>
					</ul>
					<?php
					//small form for update button- send GET ID parameter to new page
					echo '<form method="post" action="edit_user.php?ID=' . $_GET['ID'] . '">';
					echo "<div>";
					echo '<button type="submit" name="update" class="green-button"> Update </button>';
					echo "</div>";
					echo "</form>";
					//small form for pwd change button- send GET ID parameter to new page
					echo '<form method="post" action="edit_pwd.php?ID=' . $_GET['ID'] . '">';
					echo "<div>";
					echo '<button type="submit" name="pwd" class="green-button"> Change Password </button>';
					echo "</div>";
					echo "</form>";
					//small form for delete button- maintain GET parameter when reload page
					echo '<form method="post" action="view_user.php?ID=' . $_GET['ID'] . '">';
					echo "<div>";
					echo '<button type="submit" name="delete" class="blue-button"> Delete </button>';
					echo "</div>";
					echo "</form>";
					?>
					</div>
					
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