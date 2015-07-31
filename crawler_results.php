<?php
include_once 'includes/function.php';
include_once 'includes/db_connect.php';

sec_session_start();

if (check_login($mysqli, 'admin') == false) {
	header('Location: .');
}

if (isset($_GET["page"])) 
	$page  = $_GET["page"];
else 
	$page=1;

?>

<!DOCTYPE html>
<html>
<head>
	<title>Crawler Results</title>
</head>
<body>
	<?php
	$per_page = 30;
	display_crawler($mysqli_crawler, $page, $per_page);

	$num_results = count_rows($mysqli_crawler);
	$total_pages = ceil($num_results / $per_page);

	//links to next pages with SQL results
	if ($page != 1)
		echo "<a href='crawler_results.php?page=" . ($page - 1) . "'>Previous <a/>";

	for ($i=1; $i<=$total_pages; $i++) {
		echo "<a href='crawler_results.php?page=" . $i . "'>" . $i . " <a/>"; 
	}

	if ($page < $total_pages)
		echo "<a href='crawler_results.php?page=" . ($page + 1) . "'>Next<a/>";

	?>
</body>
</html>