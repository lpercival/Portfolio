<!DOCTYPE html>

<!-- Author: Lisa Percival -->
<!-- Description: The main page to view all reviews -->

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
	
	//get list of reviews to display
	$reviews = $mysqli->query("(SELECT sr.id, sr.title, sr.description, 
		sr.reviewDate, sr.ranking, s.name, 'schoolReview' AS type FROM schoolReviews sr
		INNER JOIN schools s ON sr.schoolID = s.id)
		UNION ALL
		(SELECT pr.id, pr.title, pr.description, pr.reviewDate, pr.ranking, 
		p.description AS name, 'programReview' AS type FROM programReviews pr
		INNER JOIN programs p ON pr.programID = p.id)
		UNION ALL
		(SELECT por.id, por.title, por.description, por.reviewDate, por.ranking, 
		CONCAT (po.firstName, ' ', po.lastName) AS name, 'professorReview' AS type 
		FROM professorReviews por
		INNER JOIN professors po ON por.professorID = po.id)
		ORDER BY reviewDate DESC");
?>

<html>
	<head>
		<meta charset="UTF-8">
		<title> Home: Reviews </title>
		<link href="myStyle.css" rel="stylesheet">
		
	</head>
	<body>
		<div class="heading">
			<h1> ClassDoor: Home Page </h1>
		</div>
		
		<div class="navigation">
			<a href="reviewers.php"> Reviewers </a>
			<a href="schools.php"> Schools </a>
			<a href="programs.php"> Programs </a>
			<a href="professors.php"> Professors </a>
		</div>
		
		<div class="content">
			<h2> Reviews </h2>
			<table>
				<tr>
					<th> Title </th>
					<th> Description </th>
					<th> Date </th>
					<th> Ranking </th>
					<th> About </th>
				</tr>
				<?php
				//build table of reviews
				$reviews->data_seek(0);		//start at beginning
				while ($review = $reviews->fetch_assoc()) {	
					echo "<tr>";
					echo "<td>";
					echo '<a href="review_details.php?ID=' . $review['id'] . '&type=' . 
						$review['type'] . '">' . $review['title'];
					echo "</td>";
					echo "<td> $review[description] </td>"; 
					echo "<td> $review[reviewDate] </td>";
					echo "<td> $review[ranking] </td>";
					echo "<td> $review[name] </td>";
					echo "</tr>";
				}
				?>
			</table>
		</div>
	
	</body>
</html>