<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The main page to view all schools -->

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
	
	//get list of schools to display
	$schools = $mysqli->query("SELECT id, name, city, state FROM schools");
?>

<html>
	<head>
		<meta charset="UTF-8">
		<title> Schools </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
			<h1> ClassDoor: Schools </h1>
		</div>
		
		<div class="navigation">
			<a href="home.php"> Home </a>
			<a href="add_school.php"> Add School </a>
		</div>
		
		<div class="content">
			<table>
				<tr>
					<th> Name </th>
					<th> City </th>
					<th> State </th>
				</tr>
				<?php
				//populate table with schools
				while ($school = $schools->fetch_assoc()) {
					echo "<tr>";
					echo "<td>";
					echo '<a href="school_profile.php?ID=' . $school['id'] .
						'">' . $school['name'];
					echo "</td>";
					echo "<td> $school[city] </td>";
					echo "<td> $school[state] </td>";
					echo "</tr>";
				}
				?>
			</table>
		</div>
	
	</body>
</html>