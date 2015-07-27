<?php
include_once 'includes/function.php';
include_once 'includes/db_connect.php';

sec_session_start();

if (check_login($mysqli, 'admin') == false) {
	header('Location: .');
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Crawler Results</title>
</head>
<body>
	<?php
	display_crawler($mysqli_crawler);
	?>
</body>
</html>