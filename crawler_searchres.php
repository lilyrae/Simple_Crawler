<?php
include_once 'includes/function.php';
include_once 'includes/db_connect.php';

sec_session_start();

if (check_login($mysqli, 'admin') == false) {
	header('Location: .');
}

if (isset($_GET["title"])) 
	$title  = $_GET["title"];
else
	$title = '';

if (isset($_GET["author"])) 
	$author  = $_GET["author"];
else 
	$author = '';

if (isset($_GET["date"])) 
	$date  = $_GET["date"];
else 
	$date = '';

$search_str = $title . $author . $date;

if ($search_str == '')
	header('Location: crawler_results.php');
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
		<tr> 
			<td> 
				<button type="button" onclick="location.href='crawler_results.php'">Return Home</button>
			</td>
		</tr>
	</table>
</form>

	<?php
		display_search($mysqli_crawler, $title, $author, $date);
	?>

</body>
</html>