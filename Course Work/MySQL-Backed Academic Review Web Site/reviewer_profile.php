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
	
	//get info for the reviewer, using prepared statements
	//first from the reviewers table
	if (!$stmnt = $mysqli->prepare("SELECT username, profile FROM reviewers
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
	$dbProfile = NULL;
	if (!$stmnt->bind_result($dbUsername, $dbProfile)) {
		echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	$stmnt->fetch(); 	//get results
	$stmnt->close();
	
	//then get the set of schools they have attended
	$schools = array();
	if (!$stmnt = $mysqli->prepare("SELECT s.name FROM schools s
		INNER JOIN reviewer_schools rs ON s.id = rs.schoolID
		WHERE rs.reviewerID = ?")) {
		echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
	}
	if (!$stmnt->bind_param("i", $_GET['ID'])) {
		echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	if (!$stmnt->execute()) {
		echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	$dbSchool = NULL;	//to hold results
	if (!$stmnt->bind_result($dbSchool)) {
		echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	while ($stmnt->fetch()) {	//get results into array	
		$schools[] = $dbSchool;
	}
	$stmnt->close();
	
	//get the set of reviews they have written later so can just use the fetch there
	
?>

<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The profile page for an individual reviewer -->

<html>
	<head>
		<meta charset="UTF-8">
		<title> Reviewer Profile </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
		<?php
			echo "<h1> ClassDoor: Reviewer Profile: $dbUsername </h1>";
		?>
		</div>
		
		<div class="navigation">
			<a href="home.php"> Home </a>
			<?php
			echo '<a href="make_schoolReview.php?ID=' . $_GET['ID'] . '"> Review a School </a>';
			echo '<a href="make_programReview.php?ID=' . $_GET['ID'] . '"> Review a Program </a>';
			echo '<a href="make_professorReview.php?ID=' . $_GET['ID'] . '"> Review a Professor </a>';
			echo '<a href="update_account.php?ID=' . $_GET['ID'] . '"> Edit Account </a>';
			?>
		</div>
		
		<div class="content">
			<?php
			echo "<p> <strong> Profile info: </strong> $dbProfile </p>";
			$schoolString = "";		//allow to append
			foreach ($schools as $school) {
				$schoolString .= $school;
				$schoolString .= ", ";
			}
			$schoolString = substr($schoolString, 0, -2);	//get rid of last , 
			echo "<p> <strong> School(s): </strong> $schoolString </p>";
			?>
			<h2> Reviews: </h2>
			<table>
				<tr>
					<th> Title </th>
					<th> Description </th>
					<th> Date </th>
					<th> Ranking </th>
					<th> About </th>
				</tr>
				<?php
				//get set of reviews they've written
				if (!$stmnt = $mysqli->prepare("(SELECT sr.id, sr.title, sr.description, 
					sr.reviewDate, sr.ranking, s.name, 'schoolReview' AS type FROM schoolReviews sr
					INNER JOIN schools s ON sr.schoolID = s.id
					WHERE reviewerId = ?)
					UNION ALL
					(SELECT pr.id, pr.title, pr.description, pr.reviewDate, pr.ranking, 
					p.description, 'programReview' AS type FROM programReviews pr
					INNER JOIN programs p ON pr.programID = p.id
					WHERE reviewerId = ?)
					UNION ALL
					(SELECT por.id, por.title, por.description, por.reviewDate, por.ranking, 
					CONCAT (po.firstName, ' ', po.lastName) AS name, 'professorReview' AS type FROM professorReviews por
					INNER JOIN professors po ON por.professorID = po.id
					WHERE reviewerId = ?)
					ORDER BY reviewDate DESC")) {
					echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
				}
				if (!$stmnt->bind_param("iii", $_GET['ID'], $_GET['ID'], $_GET['ID'])) {
					echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
				}
				if (!$stmnt->execute()) {
					echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
				}
				$dbID = NULL;	//to hold results
				$dbTitle = NULL;
				$dbDescription = NULL;
				$dbReviewDate = NULL;
				$dbRanking = NULL;
				$dbAbout = NULL;
				$dbType = NULL;
				if (!$stmnt->bind_result($dbID, $dbTitle, $dbDescription,
					$dbReviewDate, $dbRanking, $dbAbout, $dbType)) {
					echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
				}
				while ($stmnt->fetch()) {	
					echo "<tr>";
					echo "<td>";
					echo '<a href="review_details.php?ID=' . $dbID . '&type=' . 
						$dbType . '">' . $dbTitle . '</a>';
					echo "</td>";
					echo "<td> $dbDescription </td>"; 
					echo "<td> $dbReviewDate </td>";
					echo "<td> $dbRanking </td>";
					echo "<td> $dbAbout </td>";
					echo "</tr>";
				}
				$stmnt->close();
				?>
			</table>
		</div>
	
	</body>
</html>