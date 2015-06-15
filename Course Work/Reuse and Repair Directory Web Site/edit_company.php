<?php
	session_start();

/* Author: Lisa Percival
*  Date Created: 5/15/14
*  Description: If someone is logged in, let them fill out a form that will
*  be used to update the given company. Form submittal is done with a call to a
*  JavaScript function that makes a request to another PHP file. Form fields are
*  pre-populated with the existing values of the given company. */
	include 'configuration.php';
	
	//ensure someone's logged in
	if (!isset($_SESSION['user'])) {
		header("Location: signIn.html");
	}
	
	//check the ID is set as a GET parameter, otherwise redirect
	if (!isset($_GET['ID'])) {
		header("Location: companies.php");
	}
	
	//connect to database
	$mysqli = new mysqli("mysql.corvallisrecycles.org", "osubenny_sql", 
					$myPwd, "corvallisrr_app");
	if (!$mysqli || $mysqli->connect_errno) {
		echo "Connection to DB failed: (" . $mysqli->connect_errno . ")"
				. $mysqli->connect_error;
	}
	
	//get list of all possible categories to use for dropdown
	$categories = $mysqli->query("SELECT cat_id, cat_name FROM Categories ORDER BY cat_name ASC");
	
	//get info for the requested company, using prepared statement
	if (!$stmnt = $mysqli->prepare("SELECT cmp_name, cmp_address1, cmp_address2,
					cmp_city, cmp_state, cmp_zip, cmp_phone, cmp_email, cmp_website,
					cmp_notes, cmp_recycle_flag, cmp_repair_flag FROM Companies
					WHERE cmp_id = ?")) {
		echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
	}
	if (!$stmnt->bind_param("i", $_GET['ID'])) {
		echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	if (!$stmnt->execute()) {
		echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	$dbName = NULL;	//to hold results
	$dbAddr1 = NULL;
	$dbAddr2 = NULL;
	$dbCity = NULL;
	$dbState = NULL;
	$dbZip = NULL;
	$dbPhone = NULL;
	$dbEmail = NULL;
	$dbWebsite = NULL;
	$dbNotes = NULL;
	$dbIsReuse = NULL;
	$dbIsRepair = NULL;
	if (!$stmnt->bind_result($dbName, $dbAddr1, $dbAddr2, $dbCity, $dbState,
			$dbZip, $dbPhone, $dbEmail, $dbWebsite, $dbNotes, $dbIsReuse, $dbIsRepair)) {
		echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	$stmnt->fetch(); 	//get results
	$stmnt->close();
	
	// get list of categories associated with company from CompanyCategories
	$setCategories = array();
	if (!$stmnt = $mysqli->prepare("SELECT c.cat_id FROM Categories c
		INNER JOIN CompanyCategories cc ON c.cat_id = cc.cat_id
		WHERE cc.cmp_id = ?")) {
		echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
	}
	if (!$stmnt->bind_param("i", $_GET['ID'])) {
		echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	if (!$stmnt->execute()) {
		echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	$dbCategory = NULL;	//to hold results
	if (!$stmnt->bind_result($dbCategory)) {
		echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	while ($stmnt->fetch()) {	//get results into array	
		$setCategories[] = $dbCategory;
	}
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
		
		<title>Edit Company</title>
		<script src="edit_company.js"></script>
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
				
					<h1> Update Company </h1>
					
					<div id="forErrors">
					</div>
					<form method="post" class="form-horizontal">	<!-- no action necessary -->
						<fieldset>
							<legend> Company Details </legend>
							<div class="form-group">
								<label for="name" class="col-sm-2 control-label"> Name*: </label>
								<div class="col-sm-10">
									<?php
									echo '<input type="text" id="name" name="name" value="' . $dbName . '" class="form-control"/>';
									?>
								</div>
							</div>
							<div class="form-group">
								<label for="addr1" class="col-sm-2 control-label"> Address 1: </label>
								<div class="col-sm-10">
									<?php
									echo '<input type="text" id="addr1" name="addr1" value="' . $dbAddr1 . '" class="form-control"/>';
									?>
								</div>
							</div>
							<div class="form-group">
								<label for="addr2" class="col-sm-2 control-label"> Address 2: </label>
								<div class="col-sm-10">
									<?php
									echo '<input type="text" id="addr2" name="addr2" value="' . $dbAddr2 . '" class="form-control"/>';
									?>
								</div>
							</div>
							<div class="form-group">
								<label for="city" class="col-sm-2 control-label"> City: </label>
								<div class="col-sm-10">
									<?php
									echo '<input type="text" id="city" name="city" value="' . $dbCity . '" class="form-control"/>';
									?>
								</div>
							</div>
							<div class="form-group">
								<label for="state" class="col-sm-2 control-label"> State: </label>
								<div class="col-sm-10">
									<?php
									echo '<input type="text" id="state" name="state" value="' . $dbState . '" class="form-control"/>';
									?>
								</div>
							</div>
							<div class="form-group">
								<label for="zip" class="col-sm-2 control-label"> Zip: </label>
								<div class="col-sm-10">
									<?php
									echo '<input type="text" id="zip" name="zip" value="' . $dbZip . '" class="form-control"/>';
									?>
								</div>
							</div>
							<div class="form-group">
								<label for="phone" class="col-sm-2 control-label"> Phone #: </label>
								<div class="col-sm-10">
									<?php
									echo '<input type="tel" id="phone" name="phone" value="' . $dbPhone . '" class="form-control"/>';
									?>
								</div>
							</div>
							<div class="form-group">
								<label for="email" class="col-sm-2 control-label"> Email: </label>
								<div class="col-sm-10">
									<?php
									echo '<input type="email" id="email" name="email" value="' . $dbEmail . '" class="form-control"/>';
									?>
								</div>
							</div>
							<div class="form-group">
								<label for="website" class="col-sm-2 control-label"> Website: </label>
								<div class="col-sm-10">
									<?php
									echo '<input type="url" id="website" name="website" value="' . $dbWebsite . '" class="form-control"/>';
									?>
								</div>
							</div>
							<div class="form-group">
								<fieldset>
									<label class="col-sm-2 control-label"> Company Type*: </label>
										<div class="col-sm-10">
											<label for="reuseType"> Reuse </label>
											<?php
											if ($dbIsReuse) {
												echo '<input type="checkbox" name="reuseType" id="reuseType" checked="true">';
											}
											else {
												echo '<input type="checkbox" name="reuseType" id="reuseType">';
											}
											?>
											<label for="repairType"> Repair </label>
											<?php
											if ($dbIsRepair) {
												echo '<input type="checkbox" name="repairType" id="repairType" checked="true">';
											}
											else {
												echo '<input type="checkbox" name="repairType" id="repairType">';
											}
											?>
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
											// select those in setCategories
											if (in_array($category['cat_id'], $setCategories)) {
												echo '<option value="' . $category['cat_id'] . '" selected="true">';
											}
											else {
												echo '<option value="' . $category['cat_id'] . '">';
											}
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
									<?php
									echo '<input type="text" id="notes" name="notes" value="' . $dbNotes . '" class="form-control"/>';
									?>
								</div>
							</div>
							<!-- hidden ID field so gets passed to the JS and other PHP -->
							<?php
							echo '<input type="hidden" name="ID" id="ID" value="' . $_GET['ID'] . '">';
							?>
							<div class="form-group">
								<div class="col-sm-offset-2 col-sm-10">
									<button type="button" name="update" onclick="editCompany()" class="btn btn-default"> Save Changes </button>
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