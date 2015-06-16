<?php
	ini_set('display_errors', 'On');
	
	include 'configuration.php';
	
	//check the ID is set as a GET parameter, if not redirect
	if (!isset($_GET['ID'])) {
		header("Location: professors.php");
	}
	
	//connect to database
	$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "percival-db", 
					$myPwd, "percival-db");
	if (!$mysqli || $mysqli->connect_errno) {
		echo "Connection to DB failed: (" . $mysqli->connect_errno . ")"
				. $mysqli->connect_error;
	}
	
	//check if it was POSTed to by update button & if so do update and redirect
	if (isset($_POST['update'])) {
		//add all necessary rows to professor_schools table (for each school selected)
		foreach ($_POST['schools'] as $aSchool) {
			if (!$stmnt = $mysqli->prepare("INSERT INTO professor_schools (professorID, schoolID) 
					VALUES (?, ?)")) {
				echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
			}
			if (!$stmnt->bind_param("ss", $_GET['ID'], $aSchool)) {
				echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
			}
			if (!$stmnt->execute()) {
				echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
			}
			$stmnt->close();
		}
		//go back to professors page
		header("Location: professors.php");
	}
	
	//get the name of the professor we're editing to show in title
	if (!$stmnt = $mysqli->prepare("SELECT firstName, lastName FROM professors
		WHERE id = ?")) {
		echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
	}
	if (!$stmnt->bind_param("i", $_GET['ID'])) {
		echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	if (!$stmnt->execute()) {
		echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	$dbFirstName = NULL;	//to hold results
	$dbLastName = NULL;
	if (!$stmnt->bind_result($dbFirstName, $dbLastName)) {
		echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	$stmnt->fetch(); 	//get results
	$stmnt->close();
?>

<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The page to update a professor AKA associate them with more schools -->

<html>
	<head>
		<meta charset="UTF-8">
		<title> Edit Professor's Schools </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
			<?php
			echo "<h1> ClassDoor: Edit Professor's Schools: $dbFirstName $dbLastName </h1>";
			?>
		</div>
		
		<div class="navigation">
			<a href="home.php"> Home </a>
			<a href="professors.php"> Back to Professors </a>
		</div>
		
		<div class="content">
			<?php
			//keep the GET parameter when load page again
			echo '<form method="post" action="update_professor.php?ID=' . $_GET['ID'] . '">';
			?>
				<fieldset>
					<legend> Update Details </legend>
					<div>
						<label for="schools"> Additional School(s): </label>
						<select id="schools" name="schools[]" multiple="multiple" required="required" size="2">
							<?php
							//populate the multi-select dropdown with schools,
							//but only those they aren't already associated with
							if (!$stmnt = $mysqli->prepare("SELECT s.id, s.name, 
								s.city, s.state FROM schools s
								WHERE s.id NOT IN
								(SELECT s2.id FROM schools s2
								INNER JOIN professor_schools ps ON s2.id = ps.schoolID
								INNER JOIN professors p ON ps.professorID = p.id
								WHERE p.id = ?)
								ORDER BY s.name ASC")) {
								echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
							}
							if (!$stmnt->bind_param("i", $_GET['ID'])) {
								echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
							}
							if (!$stmnt->execute()) {
								echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
							}
							$dbID = NULL;	//to hold results
							$dbName = NULL;
							$dbCity = NULL;
							$dbState = NULL;
							if (!$stmnt->bind_result($dbID, $dbName, $dbCity, $dbState)) {
								echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
							}
							while ($stmnt->fetch()) {	
								echo '<option value="' . $dbID . '">';
								echo "$dbName ($dbCity, $dbState)";
								echo "</option>";
							}
							$stmnt->close();
							?>
						</select>
					</div>
					<div>
						<button type="submit" name="update"> Save Changes </button>
					</div>				
				</fieldset>
			</form>
		</div>
	
	</body>
</html>