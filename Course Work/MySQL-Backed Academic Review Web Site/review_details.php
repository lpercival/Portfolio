<?php
	ini_set('display_errors', 'On');
	
	include 'configuration.php';
	
	//check the review ID and type are set as GET parameters, if not redirect
	if (!isset($_GET['ID']) || !isset($_GET['type'])) {
		header("Location: home.php");
	}
	
	//connect to database
	$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "percival-db", 
					$myPwd, "percival-db");
	if (!$mysqli || $mysqli->connect_errno) {
		echo "Connection to DB failed: (" . $mysqli->connect_errno . ")"
				. $mysqli->connect_error;
	}
	
	//check if it was POSTed to by delete button & if so do deletion and redirect
	if (isset($_POST['delete'])) {
		//which table delete from depends on review type in GET
		if ($_GET['type'] == 'schoolReview') {
			if (!$stmnt = $mysqli->prepare("DELETE FROM schoolReviews WHERE id = ?")) {
				echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
			}
			if (!$stmnt->bind_param("i", $_GET['ID'])) {
				echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
			}
			if (!$stmnt->execute()) {
				echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
			}
			$stmnt->close();
		}
		if ($_GET['type'] == 'programReview') {
			if (!$stmnt = $mysqli->prepare("DELETE FROM programReviews WHERE id = ?")) {
				echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
			}
			if (!$stmnt->bind_param("i", $_GET['ID'])) {
				echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
			}
			if (!$stmnt->execute()) {
				echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
			}
			$stmnt->close();
		}
		if ($_GET['type'] == 'professorReview') {
			if (!$stmnt = $mysqli->prepare("DELETE FROM professorReviews WHERE id = ?")) {
				echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
			}
			if (!$stmnt->bind_param("i", $_GET['ID'])) {
				echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
			}
			if (!$stmnt->execute()) {
				echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
			}
			$stmnt->close();
		}

		//go to home page
		header("Location: home.php");
	}
	
	//get info for the review, using prepared statements
	//which table select from depends on review type in GET
	if ($_GET['type'] == 'schoolReview') {
		if (!$stmnt = $mysqli->prepare("SELECT sr.title, sr.description, 
		sr.reviewDate, sr.ranking, r.username, s.name, s.city, s.state 
		FROM schoolReviews sr
		INNER JOIN reviewers r ON sr.reviewerID = r.id
		INNER JOIN schools s ON sr.schoolID = s.id
		WHERE sr.id = ?")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("i", $_GET['ID'])) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$dbTitle = NULL;	//to hold results
		$dbDescription = NULL;
		$dbReviewDate = NULL;
		$dbRanking = NULL;
		$dbReviewer = NULL;
		$dbSchool = NULL;
		$dbCity = NULL;
		$dbState = NULL;
		if (!$stmnt->bind_result($dbTitle, $dbDescription, $dbReviewDate, $dbRanking,
			$dbReviewer, $dbSchool, $dbCity, $dbState)) {
			echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$stmnt->fetch(); 	//get results
		$stmnt->close();
	}
	if ($_GET['type'] == 'programReview') {
		if (!$stmnt = $mysqli->prepare("SELECT pr.title, pr.description, pr.reviewDate, 
		pr.ranking, r.username, p.description, s.name 
		FROM programReviews pr
		INNER JOIN reviewers r ON pr.reviewerID = r.id
		INNER JOIN programs p ON pr.programID = p.id
		INNER JOIN schools s ON p.schoolID = s.id
		WHERE pr.id = ?")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("i", $_GET['ID'])) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$dbTitle = NULL;	//to hold results
		$dbDescription = NULL;
		$dbReviewDate = NULL;
		$dbRanking = NULL;
		$dbReviewer = NULL;
		$dbProgram = NULL;
		$dbSchool = NULL;
		if (!$stmnt->bind_result($dbTitle, $dbDescription, $dbReviewDate, $dbRanking,
			$dbReviewer, $dbProgram, $dbSchool)) {
			echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$stmnt->fetch(); 	//get results
		$stmnt->close();
	}
	if ($_GET['type'] == 'professorReview') {
		if (!$stmnt = $mysqli->prepare("SELECT pr.title, pr.description, pr.reviewDate, 
		pr.ranking, r.username, p.firstName, p.lastName 
		FROM professorReviews pr 
		INNER JOIN reviewers r ON pr.reviewerID = r.id
		INNER JOIN professors p ON pr.professorID = p.id
		WHERE pr.id = ?")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("i", $_GET['ID'])) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$dbTitle = NULL;	//to hold results
		$dbDescription = NULL;
		$dbReviewDate = NULL;
		$dbRanking = NULL;
		$dbReviewer = NULL;
		$dbFirstName = NULL;
		$dbLastName = NULL;
		if (!$stmnt->bind_result($dbTitle, $dbDescription, $dbReviewDate, $dbRanking,
			$dbReviewer, $dbFirstName, $dbLastName)) {
			echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$stmnt->fetch(); 	//get results
		$stmnt->close();
	}
?>

<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The details page for an individual review -->

<html>
	<head>
		<meta charset="UTF-8">
		<title> Review Details </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
		<?php
			echo "<h1> ClassDoor: Review Details: $dbTitle </h1>";
		?>
		</div>
		
		<div class="navigation">
			<a href="home.php"> Home </a>
		</div>
		
		<div class="content">
			<?php
			echo "<p> <strong> Description: </strong> $dbDescription </p>"; 
			echo "<p> <strong> Date: </strong> $dbReviewDate </p>";
			echo "<p> <strong> Ranking: </strong> $dbRanking </p>";
			echo "<p> <strong> Reviewer: </strong> $dbReviewer </p>";
			//last line depends on type of review
			if ($_GET['type'] == 'schoolReview') {
				echo "<p> <strong> School: </strong> $dbSchool ($dbCity, $dbState) </p>";
			}
			if ($_GET['type'] == 'programReview') {
				echo "<p> <strong> Program: </strong> $dbProgram ($dbSchool) </p>";
			}
			if ($_GET['type'] == 'professorReview') {
				echo "<p> <strong> Professor: </strong> $dbFirstName $dbLastName </p>";
			}
			
			//maintain GET parameters when reload page
			echo '<form method="post" action="review_details.php?ID=' . $_GET['ID'] .
				'&type=' . $_GET['type'] . '">';
			echo "<div>";
			echo '<button type="submit" name="delete"> Delete Review </button>';
			echo "</div>";
			echo "</form>";
			?>
		</div>
	
	</body>
</html>