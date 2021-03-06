<?php
	session_start();

/* Author: Lisa Percival
*  Date Created: 5/15/14
*  Description: If someone is logged in, let them fill out a form that will
*  be used to update an authorized user. Form submittal is done with a call to a
*  JavaScript function that makes a request to another PHP file. Form fields are
*  pre-populated with the existing values of the given user. */
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
		
		<title>Edit User</title>
		<script src="edit_user.js"></script>
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
				
					<h1> Update User </h1>
					
					<div id="forErrors">
					</div>
					<form method="post" class="form-horizontal">	<!-- no action necessary -->
						<fieldset>
							<legend> User Details </legend>
							<div class="form-group">
								<label for="username" class="col-sm-2 control-label"> Username*: </label>
								<div class="col-sm-10">
									<?php
									echo '<input type="text" id="username" name="username" value="' . $dbUsername . '" class="form-control"/>';
									?>
								</div>
							</div>
							<div class="form-group">
								<label for="fname" class="col-sm-2 control-label"> First Name*: </label>
								<div class="col-sm-10">
									<?php
									echo '<input type="text" id="fname" name="fname" value="' . $dbFname . '" class="form-control"/>';
									?>
								</div>
							</div>
							<div class="form-group">
								<label for="lname" class="col-sm-2 control-label"> Last Name*: </label>
								<div class="col-sm-10">
									<?php
									echo '<input type="text" id="lname" name="lname" value="' . $dbLname . '" class="form-control"/>';
									?>
								</div>
							</div>
							<!-- hidden ID field so gets passed to the JS and other PHP -->
							<?php
							echo '<input type="hidden" name="ID" id="ID" value="' . $_GET['ID'] . '">';
							?>
							<div class="form-group">
								<div class="col-sm-offset-2 col-sm-10">
									<button type="button" name="create" onclick="editUser()" class="btn btn-default"> Save Changes </button>
								</div>
							</div>
						</fieldset>
					</form>
					
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