<?php
	session_start();
/* Author: Lisa Percival
*  Date Created: 5/2/15
*  Description: Simply log a user out and redirect them back to the login 
*  page */
	$_SESSION = array();
	session_destroy();
	header("Location: signIn.html");
?>