<?php
	ini_set('display_errors', 'On');
	
	include 'configuration.php';
	
	//check the ID is set as a GET parameter, if not redirect
	if (!isset($_GET['ID'])) {
		header("Location: schools.php");
	}
	
	//connect to database
	$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "percival-db", 
					$myPwd, "percival-db");
	if (!$mysqli || $mysqli->connect_errno) {
		echo "Connection to DB failed: (" . $mysqli->connect_errno . ")"
				. $mysqli->connect_error;
	}
	
	//get info for the school, using prepared statements
	//first from the schools table + its average ranking
	if (!$stmnt = $mysqli->prepare("SELECT s.name, s.city, s.state, AVG(sr.ranking) 
		FROM schools s 
		LEFT JOIN schoolReviews sr ON s.id = sr.schoolID
		GROUP BY s.id
		HAVING s.id = ?")) {
		echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
	}
	if (!$stmnt->bind_param("i", $_GET['ID'])) {
		echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	if (!$stmnt->execute()) {
		echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	$dbName = NULL;	//to hold results
	$dbCity = NULL;
	$dbState = NULL;
	$dbRanking = NULL;
	if (!$stmnt->bind_result($dbName, $dbCity, $dbState, $dbRanking)) {
		echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	$stmnt->fetch(); 	//get results
	$stmnt->close();
	
	//then get the set of reviews written about it, set of programs at it,
	// and set of professors who have worked there later where can use fetch
	
?>

<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The profile page for an individual school -->

<html>
	<head>
		<meta charset="UTF-8">
		<title> School Profile </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
		<?php
			echo "<h1> ClassDoor: School Profile: $dbName </h1>";
		?>
		</div>
		
		<div class="navigation">
			<a href="home.php"> Home </a>
		</div>
		
		<div class="content">
			<?php
			echo "<p> <strong> City: </strong> $dbCity </p>";
			echo "<p> <strong> State: </strong> $dbState </p>";
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
					reviewDate, ranking, 'schoolReview' AS type FROM schoolReviews
					WHERE schoolID = ?")) {
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
			
			<h2> Programs: </h2>
			<ul>
				<?php
				//get list of programs at the school
				if (!$stmnt = $mysqli->prepare("SELECT id, description FROM programs 
					WHERE schoolID = ?")) {
					echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
				}
				if (!$stmnt->bind_param("i", $_GET['ID'])) {
					echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
				}
				if (!$stmnt->execute()) {
					echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
				}
				$dbID = NULL;	//to hold results
				$dbDescription = NULL;
				if (!$stmnt->bind_result($dbID, $dbDescription)) {
					echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
				}
				while ($stmnt->fetch()) {
					echo "<li>";
					echo '<a href="program_profile.php?ID=' . $dbID . '">' . 
						$dbDescription . '</a>';
					echo "</li>";
				}
				?>	
			</ul>
			
			<h2> Professors (past and present): </h2>
			<ul>
				<?php
				//get list of professors who have worked at the school
				if (!$stmnt = $mysqli->prepare("SELECT p.id, p.firstName, p.lastName 
					FROM professors p
					INNER JOIN professor_schools ps ON p.id = ps.professorID
					WHERE ps.schoolID = ?")) {
					echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
				}
				if (!$stmnt->bind_param("i", $_GET['ID'])) {
					echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
				}
				if (!$stmnt->execute()) {
					echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
				}
				$dbID = NULL;	//to hold results
				$dbFirstName = NULL;
				$dbLastName = NULL;
				if (!$stmnt->bind_result($dbID, $dbFirstName, $dbLastName)) {
					echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
				}
				while ($stmnt->fetch()) {
					echo "<li>";
					echo '<a href="professor_profile.php?ID=' . $dbID . '">' . 
						$dbFirstName . ' ' . $dbLastName . '</a>';
					echo "</li>";
				}
				?>	
			</ul>
		</div>
	
	</body>
</html>