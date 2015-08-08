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
<form method="GET" action="crawler_searchres.php">
	<table border="1" style="width:100%"> 
		<tr> 
			<td>
				<input type="text" name="title" placeholder="Search Titles"> 
			</td>
		</tr>
		<tr> 
			<td>
				<input type="text" name="author" placeholder="Search Authors"> 
			</td>
		</tr>
		<tr> 
			<td>
				<input type="text" name="date" placeholder="Search Dates"> 
			</td>
		</tr>
		<tr> 
			<td> 
				<input type="submit" value="Go">
			</td>
		</tr>
	</table>
</form>



	<?php
	$per_page = 30;

	display_crawler($mysqli_crawler, $page, $per_page);

	$num_results = count_rows($mysqli_crawler);
	$total_pages = ceil($num_results / $per_page);

	//links to next pages showing results
	if ($page != 1) {
		echo "<a href='crawler_results.php?page=1'>First Page </a>";
		echo "<a href='crawler_results.php?page=" . ($page - 1) . "'>Previous </a>";
	}

	//display links for pages 3 before current page
	for ($i=3; $i > 0; $i--) {
		if ($page > $i)
			echo "<a href='crawler_results.php?page=" . ($page - $i) . "'>" . ($page - $i) . " </a>";
	}

	echo "<span> ... </span>";

	//display links for 3 pages after current page
	for ($i=1; $i<=3; $i++) {
		if (($page + $i) <= $total_pages)
			echo "<a href='crawler_results.php?page=" . ($page + $i) . "'>" . ($page + $i) . " </a>";
	}

	if ($page < $total_pages) {
		echo "<a href='crawler_results.php?page=" . ($page + 1) . "'>Next </a>";
		echo "<a href='crawler_results.php?page=" . $total_pages . "'>Last Page</a>";
	}

	?>
</body>
</html>