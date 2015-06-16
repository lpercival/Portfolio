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
	
	//get info for the professor, using prepared statements
	//first from the professors table + their average ranking
	if (!$stmnt = $mysqli->prepare("SELECT p.firstName, p.lastName, AVG(pr.ranking) 
		FROM professors p 
		LEFT JOIN professorReviews pr ON p.id = pr.professorID
		GROUP BY p.id
		HAVING p.id = ?")) {
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
	$dbRanking = NULL;
	if (!$stmnt->bind_result($dbFirstName, $dbLastName, $dbRanking)) {
		echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	$stmnt->fetch(); 	//get results
	$stmnt->close();
	
	//then get the set of schools they have worked at
	$schools = array();
	if (!$stmnt = $mysqli->prepare("SELECT s.name FROM schools s
		INNER JOIN professor_schools ps ON s.id = ps.schoolID
		WHERE ps.professorID = ?")) {
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
	
	//then get the set of reviews written about them later where use fetch
	
?>

<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The profile page for an individual professor-->

<html>
	<head>
		<meta charset="UTF-8">
		<title> Professor Profile </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
		<?php
			echo "<h1> ClassDoor: Professor Profile: $dbFirstName $dbLastName </h1>";
		?>
		</div>
		
		<div class="navigation">
			<a href="home.php"> Home </a>
			<?php
			echo '<a href="update_professor.php?ID=' . $_GET['ID'] . '"> Edit Professor\'s Schools </a>';
			?>
		</div>
		
		<div class="content">
			<?php
			$schoolString = "";		//allow to append
			foreach ($schools as $school) {
				$schoolString .= $school;
				$schoolString .= ", ";
			}
			$schoolString = substr($schoolString, 0, -2);	//get rid of last , 
			echo "<p> <strong> School(s) Worked At: </strong> $schoolString </p>";
			echo "<p> <strong> Average Ranking: </strong> $dbRanking </p>";
			?>
			<h2> Reviews: </h2>
			<table>
				<tr>
					<th> Title </th>
					<th> Description </th>
					<th> Date </th>
					<th> Ranking </th>
				</tr>
				<?php
				//get set of reviews about the school
				if (!$stmnt = $mysqli->prepare("SELECT id, title, description, 
					reviewDate, ranking, 'professorReview' AS type FROM professorReviews
					WHERE professorID = ?")) {
					echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
				}
				if (!$stmnt->bind_param("i", $_GET['ID'])) {
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
				$dbType = NULL;
				if (!$stmnt->bind_result($dbID, $dbTitle, $dbDescription,
					$dbReviewDate, $dbRanking, $dbType)) {
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
					echo "</tr>";
				}
				$stmnt->close();
				?>
			</table>
		</div>
	
	</body>
</html>