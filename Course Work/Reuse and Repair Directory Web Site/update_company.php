<?php
	session_start();

/* Author: Lisa Percival
*  Date Created: 5/15/15
*  Description: Perform the server-side work for updating a company. 
*  Validate that all required fields are filled out and done so correctly.
*  Once validated, update the company in the companies table. Also insert/ delete
*  appropriate records in the CompanyCategories relationship table. */
	include 'configuration.php';
	
	//verify came here from the right place with an ID set
	if (!isset($_POST['theID'])) {
		header("Location: companies.php");
	}
	
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
	// if everything's OK, update the companies table
	else {
		//connect to database
		$mysqli = new mysqli("mysql.corvallisrecycles.org", "osubenny_sql", 
						$myPwd, "corvallisrr_app");
		if (!$mysqli || $mysqli->connect_errno) {
			echo "Connection to DB failed: (" . $mysqli->connect_errno . ")"
					. $mysqli->connect_error;
		}
		//update validated company info in companies table, using prepared statement
		if (!$stmnt = $mysqli->prepare("UPDATE Companies SET cmp_name = ?, cmp_address1 = ?, 
					cmp_address2 = ?, cmp_city = ?, cmp_state = ?, cmp_zip = ?, 
					cmp_phone = ?, cmp_email = ?, cmp_website = ?, cmp_notes = ?,
					cmp_recycle_flag = ?, cmp_repair_flag = ?
					WHERE cmp_id = ?")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("ssssssssssiii", $_POST['theName'], $_POST['theAddr1'], 
			$_POST['theAddr2'], $_POST['theCity'], $_POST['theState'], $_POST['theZip'],
			$_POST['thePhone'], $_POST['theEmail'], $_POST['theWebsite'], $_POST['theNotes'],
			$_POST['isReuseType'], $_POST['isRepairType'], $_POST['theID'])) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$stmnt->close();
		// then update associations with categories in the CompanyCategories table
		// first get the set of currently-associated categories so can compare
		// and see what needs to be changed (added or deleted)
		$setCategories = array();
		if (!$stmnt = $mysqli->prepare("SELECT c.cat_id FROM Categories c
			INNER JOIN CompanyCategories cc ON c.cat_id = cc.cat_id
			WHERE cc.cmp_id = ?")) {
			echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
		}
		if (!$stmnt->bind_param("i", $_POST['theID'])) {
			echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		if (!$stmnt->execute()) {
			echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		$dbCategory = NULL;	//to hold results
		if (!$stmnt->bind_result($dbCategory)) {
			echo "Binding result failed: (" . $stmnt->errno . ")" . $stmnt->error;
		}
		while ($stmnt->fetch()) {	//get results into array	
			$setCategories[] = $dbCategory;
		}
		$stmnt->close();
		// it was coming from JS as just a comma-separated list, so use explode to make array
		$theCategories = explode(',', $_POST['theCategories']);
		// add any necessary associations - selected category not already set
		foreach ($theCategories as $category) {
			if (!in_array($category, $setCategories)) {
				if (!$stmnt = $mysqli->prepare("INSERT INTO CompanyCategories (cmp_id, cat_id) 
						VALUES (?, ?)")) {
					echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
				}
				if (!$stmnt->bind_param("ss", $_POST['theID'], $category)) {
					echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
				}
				if (!$stmnt->execute()) {
					echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
				}
				$stmnt->close();
			}
		}
		// delete any necessary association - a set category's not in selected
		foreach ($setCategories as $setCategory) {
			if (!in_array($setCategory, $theCategories)) {
				if (!$stmnt = $mysqli->prepare("DELETE FROM CompanyCategories 
					WHERE cmp_id = ? AND cat_id = ?")) {
					echo "Prepare failed: (" . $mysqli->errno . ")" . $mysqli->error;
				}
				if (!$stmnt->bind_param("ss", $_POST['theID'], $setCategory)) {
					echo "Binding failed: (" . $stmnt->errno . ")" . $stmnt->error;
				}
				if (!$stmnt->execute()) {
					echo "Execute failed: (" . $stmnt->errno . ")" . $stmnt->error;
				}
				$stmnt->close();
			}
		}
		echo "Success";
	}
?>