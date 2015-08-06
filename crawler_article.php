<?php
include_once 'includes/function.php';
include_once 'includes/db_connect.php';

sec_session_start();

if (check_login($mysqli, 'admin') == false) {
	header('Location: .');
}

if (isset($_GET["id"])) 
	$ID  = $_GET["id"];
else 
	#### error page
	echo "error";
?>

<!DOCTYPE html>
<html>
<head>
	<title>Crawler Article Content</title>
</head>
<body>
	<?php
		display_article($mysqli_crawler, $ID);
	?>
</body>
</html>
