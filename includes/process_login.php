<?php

include_once 'db_connect.php';
include_once 'function.php';


//custom made function to start php (in functions.php)
sec_session_start();

//_POST contains HTTP POST variables
if (isset($_POST['email'], $_POST['p'])) {
	$email = $_POST['email'];
	$password = $_POST['p']; //the hashed password


	if (login($email, $password, 'admin', $mysqli)) {
		//login success
		header('Location: ../crawler_results.php');
	} else if (login($email, $password, 'user', $mysqli)) {
		header('Location: ../../Secure_Login/index.php?error=2');
	} else {
		//login failed
		header('Location: ../index.php?error=1');
	}
} else {
	//correct POST variables were not sent to this page
	echo 'Invalid Request';
}

?>