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
		if (!$stmnt = $mysqli->prepare("INSERT INTO schoolReviews (title, description, 
			reviewDate, ranking, reviewerID, schoolID)
			VALUES (?, ?, ?, ?, ?, ?)")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("sssiii", $_POST['title'], $_POST['description'],
			$_POST['reviewDate'], $_POST['ranking'], $_GET['ID'], $_POST['school'])) {
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
	
	//get list of schools to populate the dropdown
	$schools = $mysqli->query("SELECT id, name, city, state FROM schools ORDER BY name ASC");
?>

<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The page to create a school review -->

<html>
	<head>
		<meta charset="UTF-8">
		<title> Make School Review </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
			<h1> ClassDoor: Write School Review </h1>
		</div>
		
		<div class="navigation">
			<a href="home.php"> Home </a>
			<a href="reviewers.php"> Back to Reviewers </a>
		</div>
		
		<div class="content">
			<?php
			//keep the GET parameter when load page again
			echo '<form method="post" action="make_schoolReview.php?ID=' . $_GET['ID'] . '">';
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
						<button type="submit" name="create"> Create </button>
					</div>				
				</fieldset>
			</form>
		</div>
	
	</body>
</html>