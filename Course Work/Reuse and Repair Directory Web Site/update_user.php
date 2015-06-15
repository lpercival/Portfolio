<?php
	session_start();

/* Author: Lisa Percival
*  Date Created: 5/15/15
*  Description: Perform the server-side work for updating a user. 
*  Validate that all required fields are filled out and done so correctly.
*  Once validated, update the user in the AuthorizedUsers table. */
	include 'configuration.php';
	
	//verify came here from the right place with an ID set
	if (!isset($_POST['theID'])) {
		header("Location: users.php");
	}
	
	//first make sure the fields (all required) are not empty
	if ($_POST['theLogin'] == '') {
		echo "A username is required. Please try again.";
	}
	else if ($_POST['theFname'] == '') {
		echo "A first name is required. Please try again.";
	}
	else if ($_POST['theLname'] == '') {
		echo "A last name is required. Please try again.";
	}
	//then make sure the text fields are <= their max number of characters
	else if (strlen($_POST['theLogin']) > 50) {
		echo "The username must be less than or equal to 50 characters." .
			"Please try again.";
	}
	else if (strlen($_POST['theFname']) > 50) {
		echo "The first name must be less than or equal to 50 characters." .
			"Please try again.";
	}
	else if (strlen($_POST['theLname']) > 50) {
		echo "The last name must be less than or equal to 50 characters." .
			"Please try again.";
	}
	// if everything's OK, update the AuthorizedUsers table
	else {
		//connect to database
		$mysqli = new mysqli("mysql.corvallisrecycles.org", "osubenny_sql", 
						$myPwd, "corvallisrr_app");
		if (!$mysqli || $mysqli->connect_errno) {
			echo "Connection to DB failed: (" . $mysqli->connect_errno . ")"
					. $mysqli->connect_error;
		}
		//update validated user info in AuthorizedUsers table, using prepared statement
		if (!$stmnt = $mysqli->prepare("UPDATE AuthorizedUsers SET user_login = ?, 
				user_fname = ?, user_lname = ? WHERE user_id = ?")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("sssi", $_POST['theLogin'], $_POST['theFname'], 
			$_POST['theLname'], $_POST['theID'])) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$stmnt->close();
		echo "Success";
	}
?>