<?php
	session_start();

/* Author: Lisa Percival
*  Date Created: 5/9/15
*  Description: Perform the server-side work for creating a new company. 
*  Validate that all required fields are filled out and done so correctly.
*  Once validated, insert a new company in the companies table. Also insert
*  appropriate records in the CompanyCategories relationship table. */
	include 'configuration.php';
	
	//first make sure the required name field's not empty
	if ($_POST['theName'] == '') {
		echo "A name is required. Please try again.";
	}
	// then make sure at least one of the company type checkboxes are selected
	else if (!$_POST['isReuseType'] && !$_POST['isRepairType']) {
		echo "A company type must be selected. Please try again.";
	}
	// and check there was at least one category selected
	else if (empty($_POST['theCategories'])) {
		echo "At least one category must be selected. Please try again.";
	}
	//then make sure the text fields are <= their max number of characters
	else if (strlen($_POST['theName']) > 50) {
		echo "The name must be less than or equal to 50 characters." .
			"Please try again.";
	}
	else if (strlen($_POST['theAddr1']) > 50) {
		echo "The first address line must be less than or equal to 50 characters." .
			"Please try again.";
	}
	else if (strlen($_POST['theAddr2']) > 50) {
		echo "The second address line must be less than or equal to 50 characters." .
			"Please try again.";
	}
	else if (strlen($_POST['theCity']) > 35) {
		echo "The city must be less than or equal to 35 characters." .
			"Please try again.";
	}
	else if (strlen($_POST['theState']) > 2) {
		echo "The state must be less than or equal to 2 characters." .
			"Please try again.";
	}
	else if (strlen($_POST['theZip']) > 10) {
		echo "The zip code must be less than or equal to 10 characters." .
			"Please try again.";
	}
	else if (strlen($_POST['thePhone']) > 20) {
		echo "The phone number must be less than or equal to 20 characters." .
			"Please try again.";
	}
	else if (strlen($_POST['theEmail']) > 100) {
		echo "The email must be less than or equal to 100 characters." .
			"Please try again.";
	}
	else if (strlen($_POST['theWebsite']) > 255) {
		echo "The website must be less than or equal to 255 characters." .
			"Please try again.";
	}
	else if (strlen($_POST['theNotes']) > 255) {
		echo "The notes must be less than or equal to 255 characters." .
			"Please try again.";
	}
	// if everything's OK, add it to the companies table
	else {
		//connect to database
		$mysqli = new mysqli("mysql.corvallisrecycles.org", "osubenny_sql", 
						$myPwd, "corvallisrr_app");
		if (!$mysqli || $mysqli->connect_errno) {
			echo "Connection to DB failed: (" . $mysqli->connect_errno . ")"
					. $mysqli->connect_error;
		}
		//add validated company to companies table, using prepared statement
		if (!$stmnt = $mysqli->prepare("INSERT INTO Companies (cmp_name, cmp_address1, cmp_address2,
					cmp_city, cmp_state, cmp_zip, cmp_phone, cmp_email, cmp_website, cmp_notes,
					cmp_recycle_flag, cmp_repair_flag)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("ssssssssssii", $_POST['theName'], $_POST['theAddr1'], 
			$_POST['theAddr2'], $_POST['theCity'], $_POST['theState'], $_POST['theZip'],
			$_POST['thePhone'], $_POST['theEmail'], $_POST['theWebsite'], $_POST['theNotes'],
			$_POST['isReuseType'], $_POST['isRepairType'])) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$companyID = $mysqli->insert_id;	//gets ID inserted by previous query
		$stmnt->close();
		// then add associations with categories to the CompanyCategories table
		// it was coming from JS as just a comma-separated list, so use explode to make array
		$theCategories = explode(',', $_POST['theCategories']);
		foreach ($theCategories as $category) {
			if (!$stmnt = $mysqli->prepare("INSERT INTO CompanyCategories (cmp_id, cat_id) 
					VALUES (?, ?)")) {
				echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
			}
			if (!$stmnt->bind_param("ss", $companyID, $category)) {
				echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
			}
			if (!$stmnt->execute()) {
				echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
			}
			$stmnt->close();
		}
		echo "Success";
	}
?>