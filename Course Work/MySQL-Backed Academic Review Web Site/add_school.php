<?php
	ini_set('display_errors', 'On');
	
	include 'configuration.php';
	
	//connect to database
	$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "percival-db", 
					$myPwd, "percival-db");
	if (!$mysqli || $mysqli->connect_errno) {
		echo "Connection to DB failed: (" . $mysqli->connect_errno . ")"
				. $mysqli->connect_error;
	}
	
	//check if it was POSTed to by create button & if so do insertion and redirect
	if (isset($_POST['create'])) {
		//use prepared statement to add row to schools table
		if (!$stmnt = $mysqli->prepare("INSERT INTO schools (name, city, state) 
			VALUES (?, ?, ?)")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("sss", $_POST['name'], $_POST['city'], $_POST['state'])) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$stmnt->close();
		//go back to schools page
		header("Location: schools.php");
	}
	
?>

<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The page to add a new school -->

<html>
	<head>
		<meta charset="UTF-8">
		<title> Add School </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
			<h1> ClassDoor: Add School </h1>
		</div>
		
		<div class="navigation">
			<a href="home.php"> Home </a>
			<a href="schools.php"> Back to Schools </a>
		</div>
		
		<div class="content">
			<form method="post" action="add_school.php">
				<fieldset>
					<legend> School Details </legend>
					<div>
						<label for="name"> Name*: </label>
						<input type="text" id="name" name="name" required="required"/>
					</div>
					<div>
						<label for="city"> City: </label>
						<input type="text" id="city" name="city" />
					</div>
					<div>
						<label for="state"> State: </label>
						<input type="text" id="state" name="state" />
					</div>
					<div>
						<button type="submit" name="create"> Add </button>
					</div>				
				</fieldset>
			</form>
		</div>
	
	</body>
</html>