<?php
	session_start();

/* Author: Lisa Percival
*  Date Created: 5/22/15
*  Description: Perform the server-side work for changing a password. 
*  Validate that the field's filled out and done so correctly.
*  Once validated, update the user in the AuthorizedUsers table. */
	include 'configuration.php';
	require "lib/password.php";
	
	//verify came here from the right place with an ID set
	if (!isset($_POST['theID'])) {
		header("Location: users.php");
	}
	
	//first make sure the field's not empty
	if ($_POST['thePwd'] == '') {
		echo "A password is required. Please try again.";
	}
	//then make sure it's <= its max number of characters
	else if (strlen($_POST['thePwd']) > 64) {
		echo "The password must be less than or equal to 64 characters." .
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
		// make a hash with the password, so only the hash gets stored
		$hash = password_hash($_POST['thePwd'], PASSWORD_DEFAULT);
		//update validated user info in AuthorizedUsers table, using prepared statement
		if (!$stmnt = $mysqli->prepare("UPDATE AuthorizedUsers SET user_pwhash = ? WHERE user_id = ?")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("si", $hash, $_POST['theID'])) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$stmnt->close();
		echo "Success";
	}
?>