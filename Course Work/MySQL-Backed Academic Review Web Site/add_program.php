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
		//use prepared statement to add row to program table
		if (!$stmnt = $mysqli->prepare("INSERT INTO programs (description, schoolID) 
				VALUES (?, ?)")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("si", $_POST['description'], $_POST['school'])) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$stmnt->close();
		//go back to programs page
		header("Location: programs.php");
	}
	
	//get list of schools to populate the dropdown
	$schools = $mysqli->query("SELECT id, name, city, state FROM schools ORDER BY name ASC");
	
?>

<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The page to add a new program -->

<html>
	<head>
		<meta charset="UTF-8">
		<title> Add Program </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
			<h1> ClassDoor: Add Program </h1>
		</div>
		
		<div class="navigation">
			<a href="home.php"> Home </a>
			<a href="programs.php"> Back to Programs </a>
		</div>
		
		<div class="content">
			<form method="post" action="add_program.php">
				<fieldset>
					<legend> Program Details </legend>
					<div>
						<label for="description"> Description*: </label>
						<input type="text" id="description" name="description" required="required"/>
					</div>
					<div>
						<label for="school"> School*: </label>
						<select id="school" name="school" required="required">
							<?php
							//populate the dropdown with schools
							$schools->data_seek(0);		//start at beginning
							while ($school = $schools->fetch_assoc()) {
								echo '<option value="' . $school['id'] . '">';
								echo "$school[name] ($school[city], $school[state])";
								echo "</option>";
							}
							?>
						</select>
					</div>
					<div>
						<button type="submit" name="create"> Add </button>
					</div>				
				</fieldset>
			</form>
		</div>
	
	</body>
</html>