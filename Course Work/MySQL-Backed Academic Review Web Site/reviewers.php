<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The main page to view all reviewers -->

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
?>

<html>
	<head>
		<meta charset="UTF-8">
		<title> Reviewers </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
			<h1> ClassDoor: Reviewers </h1>
		</div>
		
		<div class="navigation">
			<a href="home.php"> Home </a>
			<a href="make_account.php"> Make Account </a>
		</div>
		
		<div class="content">
			<table>
				<tr>
					<th> Username </th>
					<th> School(s) </th>
				</tr>
				<?php
				//get list of reviewers
				$reviewers = $mysqli->query("SELECT id, username FROM reviewers
					ORDER BY username ASC");
				while ($reviewer = $reviewers->fetch_assoc()) {
					//get list of schools for that reviewer
					$schools = $mysqli->query("SELECT s.name FROM schools s
						INNER JOIN reviewer_schools rs ON s.id = rs.schoolID
						WHERE rs.reviewerID = " . $reviewer['id']);

					echo "<tr>";
					echo "<td>";
					echo '<a href="reviewer_profile.php?ID=' . $reviewer['id'] .
						'">' . $reviewer['username'];
					echo "</td>";
					echo "<td>";
					$schoolString = "";		//reset for reviewer, allow to append
					while ($school = $schools->fetch_assoc()) {
						$schoolString .= $school['name'];
						$schoolString .= ", ";
					}
					echo substr($schoolString, 0, -2);	//get rid of last , 
					echo "</td>";
					echo "</tr>";
				}
				?>
			</table>
		</div>
	
	</body>
</html>