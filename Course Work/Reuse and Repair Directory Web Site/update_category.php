<?php
	session_start();

/* Author: Lisa Percival
*  Date Created: 5/15/15
*  Description: Perform the server-side work for updating a category. 
*  Validate that all required fields are filled out and done so correctly.
*  Once validated, update the category in the categories table. */
	include 'configuration.php';
	
	//verify came here from the right place with an ID set
	if (!isset($_POST['theID'])) {
		header("Location: companies.php");
	}
	
	//first make sure the required name field's not empty
	if ($_POST['theName'] == '') {
		echo "A name is required. Please try again.";
	}
	//then make sure the text fields are <= their max number of characters
	else if (strlen($_POST['theName']) > 50) {
		echo "The name must be less than or equal to 50 characters." .
			"Please try again.";
	}
	else if (strlen($_POST['theDescription']) > 255) {
		echo "The description must be less than or equal to 255 characters." .
			"Please try again.";
	}
	else if (strlen($_POST['theNotes']) > 255) {
		echo "The notes must be less than or equal to 255 characters." .
			"Please try again.";
	}
	// if everything's OK, update the categories table
	else {
		//connect to database
		$mysqli = new mysqli("mysql.corvallisrecycles.org", "osubenny_sql", 
						$myPwd, "corvallisrr_app");
		if (!$mysqli || $mysqli->connect_errno) {
			echo "Connection to DB failed: (" . $mysqli->connect_errno . ")"
					. $mysqli->connect_error;
		}
		//update validated category info in categories table, using prepared statement
		if (!$stmnt = $mysqli->prepare("UPDATE Categories SET cat_name = ?, 
				cat_descr = ?, cat_notes = ? WHERE cat_id = ?")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("sssi", $_POST['theName'], $_POST['theDescription'], 
			$_POST['theNotes'], $_POST['theID'])) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$stmnt->close();
		echo "Success";
	}
?>