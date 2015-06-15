<?php
	session_start();

/* Author: Lisa Percival
*  Date Created: 5/2/15
*  Description: Perform the server-side work for letting a user sign in. Validate their
*  username and password are in the database, and if so set $_SESSION['user'] */
	include 'configuration.php';
	require "lib/password.php";
	
	//connect to database
	$mysqli = new mysqli("mysql.corvallisrecycles.org", "osubenny_sql", 
					$myPwd, "corvallisrr_app");
	if (!$mysqli || $mysqli->connect_errno) {
		echo "Connection to DB failed: (" . $mysqli->connect_errno . ")"
				. $mysqli->connect_error;
	}

	if(isset($_POST['theName']) && isset($_POST['thePwd'])) {
		//check whether the username exists
		//run a select statement to get all the distinct usernames
		$validName = 0;		//start at false
		$users = $mysqli->query("SELECT DISTINCT user_login FROM AuthorizedUsers");
		$users->data_seek(0);		//start at beginning
		while ($user = $users->fetch_assoc()) {
			if ($user['user_login'] == $_POST['theName']) {
				$validName = 1;			//it's valid
			}
		}
		if ($validName == 0) {
			echo "Sorry, that username is invalid. Please try again.";
		}
		else {
			//check that user's password, using prepared statement
			if (!$stmnt = $mysqli->prepare("SELECT user_pwhash FROM AuthorizedUsers
							WHERE user_login = ?")) {
				echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
			}
			if (!$stmnt->bind_param("s", $_POST['theName'])) {
				echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
			}
			if (!$stmnt->execute()) {
				echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
			}
			$dbPwd = NULL;	//to hold results
			if (!$stmnt->bind_result($dbPwd)) {
				echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
			}
			$stmnt->fetch();	//get result
			// use password_verify to make sure it's correct
			if(!password_verify($_POST['thePwd'], $dbPwd)) {
				echo "Sorry, that password is invalid. Please try again.";
			}
			else {
				echo "Success";
				//set up the session to actually log in
				if (session_status() == PHP_SESSION_ACTIVE) {
					$_SESSION['user'] = $_POST['theName'];
				}				
			}
			$stmnt->close();
		}
	}
	
?>