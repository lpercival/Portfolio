<?php
	session_start();

/* Author: Lisa Percival
*  Date Created: 5/9/14
*  Description: If someone is logged in, let them fill out a form that will
*  be used to create a new company. Form submittal is done with a call to a
*  JavaScript function that makes a request to another PHP file. */
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
	
	//get list of categories to use for dropdown
	$categories = $mysqli->query("SELECT cat_id, cat_name FROM Categories ORDER BY cat_name ASC");

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
		
		<title>New Company</title>
		<script src="add_company.js"></script>
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
				
					<h1> Add Company </h1>
					
					<div id="forErrors">
					</div>
					<form method="post" class="form-horizontal">	<!-- no action necessary -->
						<fieldset>
							<legend> Company Details </legend>
							<div class="form-group">
								<label for="name" class="col-sm-2 control-label"> Name*: </label>
								<div class="col-sm-10">
									<input type="text" id="name" name="name" class="form-control"/>
								</div>
							</div>
							<div class="form-group">
								<label for="addr1" class="col-sm-2 control-label"> Address 1: </label>
								<div class="col-sm-10">
									<input type="text" id="addr1" name="addr1" class="form-control"/>
								</div>
							</div>
							<div class="form-group">
								<label for="addr2" class="col-sm-2 control-label"> Address 2: </label>
								<div class="col-sm-10">
									<input type="text" id="addr2" name="addr2" class="form-control"/>
								</div>
							</div>
							<div class="form-group">
								<label for="city" class="col-sm-2 control-label"> City: </label>
								<div class="col-sm-10">
									<input type="text" id="city" name="city" class="form-control"/>
								</div>
							</div>
							<div class="form-group">
								<label for="state" class="col-sm-2 control-label"> State: </label>
								<div class="col-sm-10">
									<input type="text" id="state" name="state" class="form-control"/>
								</div>
							</div>
							<div class="form-group">
								<label for="zip" class="col-sm-2 control-label"> Zip: </label>
								<div class="col-sm-10">
									<input type="text" id="zip" name="zip" class="form-control"/>
								</div>
							</div>
							<div class="form-group">
								<label for="phone" class="col-sm-2 control-label"> Phone #: </label>
								<div class="col-sm-10">
									<input type="tel" id="phone" name="phone" class="form-control"/>
								</div>
							</div>
							<div class="form-group">
								<label for="email" class="col-sm-2 control-label"> Email: </label>
								<div class="col-sm-10">
									<input type="email" id="email" name="email" class="form-control"/>
								</div>
							</div>
							<div class="form-group">
								<label for="website" class="col-sm-2 control-label"> Website: </label>
								<div class="col-sm-10">
									<input type="url" id="website" name="website" class="form-control"/>
								</div>
							</div>
							<div class="form-group">
								<fieldset>
									<label class="col-sm-2 control-label"> Company Type*: </label>
										<div class="col-sm-10">
											<label for="reuseType"> Reuse </label>
											<input type="checkbox" name="reuseType" id="reuseType">
											<label for="repairType"> Repair </label>
											<input type="checkbox" name="repairType" id="repairType">
										</div>
								</fieldset>
							</div>
							<div class="form-group">
								<label for="categories" class="col-sm-2 control-label"> Categories*: </label>
								<div class="col-sm-10">
									<select id="categories" name="categories[]" class="form-control"multiple="multiple" size="5">
										<?php
										//populate multiselect dropdown with category options
										$categories->data_seek(0);		//start at beginning
										while ($category = $categories->fetch_assoc()) {
											echo '<option value="' . $category['cat_id'] . '">';
											echo "$category[cat_name]";
											echo "</option>";
										}
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for="notes" class="col-sm-2 control-label"> Notes: </label>
								<div class="col-sm-10">
									<input type="text" id="notes" name="notes" class="form-control"/>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-2 col-sm-10">
									<button type="button" name="create" onclick="newCompany()" class="btn btn-default"> Create </button>
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