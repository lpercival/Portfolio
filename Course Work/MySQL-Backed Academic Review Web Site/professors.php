<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The main page to view all professors -->

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
		<title> Professors </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
			<h1> ClassDoor: Professors </h1>
		</div>
		
		<div class="navigation">
			<a href="home.php"> Home </a>
			<a href="add_professor.php"> Add Professor </a>
		</div>
		
		<div class="content">
			<table>
				<tr>
					<th> First Name </th>
					<th> Last Name </th>
					<th> School(s) </th>
				</tr>
				<?php
				//get list of professors
				$professors = $mysqli->query("SELECT id, firstName, lastName 
					FROM professors ORDER BY lastName ASC");
				while ($professor = $professors->fetch_assoc()) {
					//get list of schools for that professor
					//query is OK because variable doesn't come from the user
					$schools = $mysqli->query("SELECT s.name FROM schools s
						INNER JOIN professor_schools ps ON s.id = ps.schoolID
						WHERE ps.professorID = " . $professor['id']);

					echo "<tr>";
					echo "<td>";
					echo '<a href="professor_profile.php?ID=' . $professor['id'] .
						'">' . $professor['firstName'];
					echo "</td>";
					echo "<td> $professor[lastName] </td>";
					echo "<td>";
					$schoolString = "";		//reset for professor, allow to append
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