<?php
	ini_set('display_errors', 'On');
	
	include 'configuration.php';
	
	//check the ID is set as a GET parameter, if not redirect
	if (!isset($_GET['ID'])) {
		header("Location: reviewers.php");
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
		//use prepared statement to update row in reviewers table
		if (!$stmnt = $mysqli->prepare("UPDATE reviewers SET profile= ? 
			WHERE id= ?")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("si", $_POST['profile'], $_GET['ID'])) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$stmnt->close();
		//then add all necessary rows to reviewer_schools table (for each school selected)
		foreach ($_POST['schools'] as $aSchool) {
			if (!$stmnt = $mysqli->prepare("INSERT INTO reviewer_schools (reviewerID, schoolID) 
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
		//go back to reviewers page
		header("Location: reviewers.php");
	}
	
	//get the name of the reviewer we're editing to show in title
	if (!$stmnt = $mysqli->prepare("SELECT username FROM reviewers
		WHERE id = ?")) {
		echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
	}
	if (!$stmnt->bind_param("i", $_GET['ID'])) {
		echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	if (!$stmnt->execute()) {
		echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	$dbUsername = NULL;	//to hold results
	if (!$stmnt->bind_result($dbUsername)) {
		echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	$stmnt->fetch(); 	//get results
	$stmnt->close();
?>

<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The page to update a reviewer account -->

<html>
	<head>
		<meta charset="UTF-8">
		<title> Update Account </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
			<?php
			echo "<h1> ClassDoor: Edit Reviewer Account: $dbUsername </h1>";
			?>
		</div>
		
		<div class="navigation">
			<a href="home.php"> Home </a>
			<a href="reviewers.php"> Back to Reviewers </a>
		</div>
		
		<div class="content">
			<?php
			//keep the GET parameter when load page again
			echo '<form method="post" action="update_account.php?ID=' . $_GET['ID'] . '">';
			?>
				<fieldset>
					<legend> Account Details </legend>
					<div>
						<label for="profile"> Profile Info: </label>
						<textarea id="profile" name="profile"></textarea>
					</div>
					<div>
						<label for="schools"> Additional School(s): </label>
						<select id="schools" name="schools[]" multiple="multiple" required="required" size="2">
							<?php
							//populate the multi-select dropdown with schools,
							//but only those they aren't already associated with
							if (!$stmnt = $mysqli->prepare("SELECT s.id, s.name, s.city, s.state FROM schools s
								WHERE s.id NOT IN
								(SELECT s2.id FROM schools s2
								INNER JOIN reviewer_schools rs ON s2.id = rs.schoolID
								INNER JOIN reviewers r ON rs.reviewerID = r.id
								WHERE r.id = ?)
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