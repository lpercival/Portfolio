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
		//use prepared statement to add row to reviewers table
		if (!$stmnt = $mysqli->prepare("INSERT INTO reviewers (username, profile) 
				VALUES (?, ?)")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("ss", $_POST['username'], $_POST['profile'])) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$reviewerID = $mysqli->insert_id;	//gets ID inserted by previous query
		$stmnt->close();
		//then add all necessary rows to reviewer_schools table (for each school selected)
		foreach ($_POST['schools'] as $aSchool) {
			if (!$stmnt = $mysqli->prepare("INSERT INTO reviewer_schools (reviewerID, schoolID) 
					VALUES (?, ?)")) {
				echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
			}
			if (!$stmnt->bind_param("ss", $reviewerID, $aSchool)) {
				echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
			}
			if (!$stmnt->execute()) {
				echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
			}
			$stmnt->close();
		}
		//go back to reviewers page
		header("Location: reviewers.php");
	}
	
	//get list of schools to populate the dropdown
	$schools = $mysqli->query("SELECT id, name, city, state FROM schools ORDER BY name ASC");
?>

<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The page to create a reviewer account -->

<html>
	<head>
		<meta charset="UTF-8">
		<title> Make Account </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
			<h1> ClassDoor: Make Reviewer Account </h1>
		</div>
		
		<div class="navigation">
			<a href="home.php"> Home </a>
			<a href="reviewers.php"> Back to Reviewers </a>
		</div>
		
		<div class="content">
			<form method="post" action="make_account.php">
				<fieldset>
					<legend> Account Details </legend>
					<div>
						<label for="username"> Username*: </label>
						<input type="text" id="username" name="username" required="required"/>
					</div>
					<div>
						<label for="profile"> Profile Info: </label>
						<textarea id="profile" name="profile"></textarea>
					</div>
					<div>
						<label for="schools"> School(s)*: </label>
						<select id="schools" name="schools[]" multiple="multiple" required="required" size="2">
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