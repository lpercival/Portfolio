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
		//use prepared statement to add row to professors table
		if (!$stmnt = $mysqli->prepare("INSERT INTO professors (firstName, lastName) 
				VALUES (?, ?)")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("ss", $_POST['firstName'], $_POST['lastName'])) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$professorID = $mysqli->insert_id;	//gets ID inserted by previous query
		$stmnt->close();
		//then add all necessary rows to professor_schools table (for each school selected)
		//if they selected schools
		if (!empty($_POST['schools'])) {
			foreach ($_POST['schools'] as $aSchool) {
				if (!$stmnt = $mysqli->prepare("INSERT INTO professor_schools (professorID, schoolID) 
						VALUES (?, ?)")) {
					echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
				}
				if (!$stmnt->bind_param("ss", $professorID, $aSchool)) {
					echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
				}
				if (!$stmnt->execute()) {
					echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
				}
				$stmnt->close();
			}
		}
		//go back to professors page
		header("Location: professors.php");
	}
	
	//get list of schools to populate the dropdown
	$schools = $mysqli->query("SELECT id, name, city, state FROM schools ORDER BY name ASC");
?>

<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The page to add a new professor -->

<html>
	<head>
		<meta charset="UTF-8">
		<title> Add Professor </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
			<h1> ClassDoor: Add Professor </h1>
		</div>
		
		<div class="navigation">
			<a href="home.php"> Home </a>
			<a href="professors.php"> Back to Professors </a>
		</div>
		
		<div class="content">
			<form method="post" action="add_professor.php">
				<fieldset>
					<legend> Professor Details </legend>
					<div>
						<label for="firstName"> First Name*: </label>
						<input type="text" id="firstName" name="firstName" required="required"/>
					</div>
					<div>
						<label for="lastName"> Last Name*: </label>
						<input type="text" id="lastName" name="lastName" required="required"/>
					</div>
					<div>
						<label for="schools"> School(s): </label>
						<select id="schools" name="schools[]" multiple="multiple" size="2">
							<?php
							//populate the multi-select dropdown with schools
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
						<button type="submit" name="create"> Create </button>
					</div>				
				</fieldset>
			</form>
		</div>
	
	</body>
</html>