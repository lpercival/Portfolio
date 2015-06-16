<?php
	ini_set('display_errors', 'On');
	
	include 'configuration.php';
	
	//check the reviewer ID is set as a GET parameter, if not redirect
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
	
	//check if it was POSTed to by create button & if so do insertion and redirect
	if (isset($_POST['create'])) {
		//use prepared statement to add row to schoolReviews table
		if (!$stmnt = $mysqli->prepare("INSERT INTO programReviews (title, description, 
			reviewDate, ranking, reviewerID, programID)
			VALUES (?, ?, ?, ?, ?, ?)")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("sssiii", $_POST['title'], $_POST['description'],
			$_POST['reviewDate'], $_POST['ranking'], $_GET['ID'], $_POST['program'])) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$stmnt->close();

		//go to home page
		header("Location: home.php");
	}
	
	//get the name of the reviewer writing the review to display
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
	
	//get list of programs to populate the dropdown
	$programs = $mysqli->query("SELECT p.id, p.description, s.name FROM programs p
		INNER JOIN schools s ON p.schoolID = s.id
		ORDER BY p.description ASC");
?>

<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The page to create a program review -->

<html>
	<head>
		<meta charset="UTF-8">
		<title> Make Program Review </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
			<h1> ClassDoor: Write Program Review </h1>
		</div>
		
		<div class="navigation">
			<a href="home.php"> Home </a>
			<a href="reviewers.php"> Back to Reviewers </a>
		</div>
		
		<div class="content">
			<?php
			//keep the GET parameter when load page again
			echo '<form method="post" action="make_programReview.php?ID=' . $_GET['ID'] . '">';
			?>
				<fieldset>
					<legend> Review Details </legend>
					<div>
						<label for="author"> Author (auto): </label>
						<?php
						echo $dbUsername;
						?>
					<div>
						<label for="title"> Title*: </label>
						<input type="text" id="title" name="title" required="required"/>
					</div>
					<div>
						<label for="description"> Description*: </label>
						<textarea id="description" name="description" required="required"></textarea>
					</div>
					<div>
						<label for="reviewDate"> Date for Review*: </label>
						<input type="date" id="reviewDate" name="reviewDate" required="required" />
					</div>
					<div>
						<label for="ranking"> Ranking*: </label>
						<select id="ranking" name="ranking" required="required">
							<?php
							//dropdown from 1-5
							for ($i = 1; $i <= 5; $i++) {
								echo "<option> $i </option>";
							}
							?>
						</select>
					</div>
					<div>
						<label for="program"> Program*: </label>
						<select id="program" name="program" required="required">
							<?php
							//populate the dropdown with programs
							$programs->data_seek(0);		//start at beginning
							while ($program = $programs->fetch_assoc()) {
								echo '<option value="' . $program['id'] . '">';
								echo "$program[description] ($program[name])";
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