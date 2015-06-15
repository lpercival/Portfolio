<?php
	session_start();

/* Author: Lisa Percival
*  Date Created: 5/10/15
*  Description: Perform the server-side work for creating a new user. 
*  Validate that all required fields are filled out and done so correctly.
*  Once validated, insert a new user in the AuthorizedUsers table. */
	include 'configuration.php';
	require "lib/password.php";
	// http://www.sitepoint.com/hashing-passwords-php-5-5-password-hashing-api/
	// https://github.com/ircmaxell/password_compat
	
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
	else if ($_POST['thePwd'] == '') {
		echo "A password is required. Please try again.";
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
	else if (strlen($_POST['thePwd']) > 64) {
		echo "The password must be less than or equal to 64 characters." .
			"Please try again.";
	}
	// if everything's OK, add it to the AuthorizedUsers table
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
		//add validated user to AuthorizedUsers table, using prepared statement
		if (!$stmnt = $mysqli->prepare("INSERT INTO AuthorizedUsers (user_login, 
				user_fname, user_lname, user_pwhash)
				VALUES (?, ?, ?, ?)")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("ssss", $_POST['theLogin'], $_POST['theFname'], 
			$_POST['theLname'], $hash)) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$stmnt->close();
		echo "Success";
	}
?>