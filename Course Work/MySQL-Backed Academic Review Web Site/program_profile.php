<?php
	ini_set('display_errors', 'On');
	
	include 'configuration.php';
	
	//check the ID is set as a GET parameter, if not redirect
	if (!isset($_GET['ID'])) {
		header("Location: programs.php");
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
		if (!$stmnt = $mysqli->prepare("DELETE FROM programs WHERE id = ?")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("i", $_GET['ID'])) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$stmnt->close();

		//go to programs page
		header("Location: programs.php");
	}
	
	//get info for the program, using prepared statements
	//first from the programs table + its average ranking
	if (!$stmnt = $mysqli->prepare("SELECT p.description, s.name, AVG(pr.ranking) 
		FROM programs p 
		LEFT JOIN programReviews pr ON p.id = pr.programID
		INNER JOIN schools s ON p.schoolID = s.id
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
	$dbDescription = NULL;	//to hold results
	$dbSchool = NULL;
	$dbRanking = NULL;
	if (!$stmnt->bind_result($dbDescription, $dbSchool, $dbRanking)) {
		echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
	}
	$stmnt->fetch(); 	//get results
	$stmnt->close();
	
	//then get the set of reviews written about it later where use fetch
	
?>

<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The profile page for an individual program -->

<html>
	<head>
		<meta charset="UTF-8">
		<title> Program Profile </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
		<?php
			echo "<h1> ClassDoor: Program Profile: $dbDescription </h1>";
		?>
		</div>
		
		<div class="navigation">
			<a href="home.php"> Home </a>
		</div>
		
		<div class="content">
			<?php
			echo "<p> <strong> School At: </strong> $dbSchool </p>";
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
				//get set of reviews about the program
				if (!$stmnt = $mysqli->prepare("SELECT id, title, description, 
					reviewDate, ranking, 'programReview' AS type FROM programReviews
					WHERE programID = ?")) {
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
			
			<?php
			//small form for delete button- maintain GET parameter when reload page
			echo '<form method="post" action="program_profile.php?ID=' . $_GET['ID'] . '">';
			echo "<div>";
			echo '<button type="submit" name="delete"> Delete Program </button>';
			echo "</div>";
			echo "</form>";
			?>
		</div>
	
	</body>
</html>