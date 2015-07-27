<?php
include_once 'includes/function.php';
include_once 'includes/db_connect.php';

sec_session_start();

if (check_login($mysqli, 'admin')) {
	header('Location: crawler_results.php');
} else if (check_login($mysqli, 'user')) {
	header('Location: ../Secure_Login/');
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Crawler Login: Log In</title>
	<link rel="stylesheet" type="text/css" href="styles/main.css">
	<script type="text/JavaScript" src="js/sha512.js"></script>
	<script type="text/JavaScript" src="js/forms.js"></script>
</head>
<body>
	<form action="includes/process_login.php" method="post" name="login_form" />
		Email: <input type="text" name="email" placeholder="email@example.com" />
		Password: <input type="password" name="password" id="password" value="6ZaxN2Vzm9NUJT2y"/>
		<input type="button" value="login" onclick="checkform(this.form)" />
	</form>

</body>
</html>