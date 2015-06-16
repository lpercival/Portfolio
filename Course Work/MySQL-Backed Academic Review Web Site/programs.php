<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The main page to view all programs -->

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
	
	//get list of programs to display
	$programs = $mysqli->query("SELECT p.id, p.description, s.name FROM programs p
		INNER JOIN schools s ON p.schoolID = s.id");
?>

<html>
	<head>
		<meta charset="UTF-8">
		<title> Programs </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
			<h1> ClassDoor: Programs </h1>
		</div>
		
		<div class="navigation">
			<a href="home.php"> Home </a>
			<a href="add_program.php"> Add Program </a>
		</div>
		
		<div class="content">
			<table>
				<tr>
					<th> Description </th>
					<th> School At </th>
				</tr>
				<?php
				//populate table with programs
				while ($program = $programs->fetch_assoc()) {
					echo "<tr>";
					echo "<td>";
					echo '<a href="program_profile.php?ID=' . $program['id'] .
						'">' . $program['description'];
					echo "</td>";
					echo "<td> $program[name] </td>";
					echo "</tr>";
				}
				?>
			</table>
		</div>
	
	</body>
</html>