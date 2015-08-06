<?php
include_once 'psl-config.php';


//necessary to start a session to access "global" variables (found in array $_SESSION)
function sec_session_start() {
	$session_name = 'sec_session_id';
	$secure = SECURE; // stops javascript accessing session ID
	$httponly = true; 

	// forces session to only use cookies
	// '===' true if a == b and they're the same type
	if(ini_set('session.use_only_cookies', 1) === FALSE) {
		header("Location: ../error.php?err=Could not initiate a safe session (ini_set)");
		exit();
	}

	// gets current cookies parameters
	$cookieParams = session_get_cookie_params();
	session_set_cookie_params($cookieParams["lifetime"],
		$cookieParams["path"],
		$cookieParams["domain"],
		$secure,
		$httponly);

	session_name($session_name);
	session_start(); // starts php session
	session_regenerate_id(true);
}

function login($email, $password, $type, $mysqli) {

	//prepared statment (SQL query) ($stmt)
	if ($stmt = $mysqli->prepare("SELECT m.ID, m.Name, m.Password, m.Salt
										FROM Members AS m
										INNER JOIN Members_Type AS mt
										ON m.ID = mt.Member_ID
										WHERE mt.Member_Type = ? AND m.Email = ? LIMIT 1")) {

		$stmt->bind_param('ss', $type, $email); // binds email variable to prepared statement (bind_param forces email to be a string, indicated by 's')
		$stmt->execute(); //execute prepared SQL query
		$stmt->store_result();

		// get result from query
		$stmt->bind_result($user_id, $username, $db_password, $salt); // binds empty variables to result from prepared statement
		$stmt->fetch(); //fetches results of prepared statement

		// hash the password with the unique salt
		// sha512 is a secure hash algorithm
		$password = hash('sha512', $password . $salt);

		if ($stmt->num_rows == 1) {

			//if user is not an admin, stop them from logging in by returning true at this point
			if ($type == 'user') {
				return true;
			}

			// if user account exists, check if account is locked from too many login attempts
			if (checkbrute($user_id, $mysqli) == true) {
				// account is locked -> send email to user
				return false;

			}	else {
				//check if password matches password in db
				if ($db_password == $password) {
					//correct password

					// get user-agent string (i.e. the name of the software acting on behalf of the user)
					$user_browser = $_SERVER['HTTP_USER_AGENT'];

					//protect against cross site scripting attact (XXS) as value might get print
					$user_id = preg_replace("/[^0-9]+/", "", $user_id);
					$_SESSION['user_id'] = $user_id;
					$username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $username);
					$_SESSION['username'] = $username;
					$_SESSION['login_string'] = hash('sha512', $password . $user_browser);
					$_SESSION['email'] = $email;

					return true; // successful login

				} else {
					//incorrect password
					// record number of login attemps (to check for brute force attacks)
					$now = time();
					$mysqli ->query("INSERT INTO login_attempts(user_ID, Time)
									VALUES ('$user_id', '$now')");

					return false;

				}
			}
		} else {
			// no user exists
			return false;
		}
	}

}


function checkbrute($user_id, $mysqli) {
	//get current time
	$now = time();

	//count login attemps during the past 2 hours
	$valid_attempts = $now - (2 * 60 * 60);

	if ($stmt = $mysqli ->prepare("SELECT Time
								FROM login_attempts
								WHERE user_ID = ?
								AND Time > '$valid_attempts'")) {

		//execute query
		$stmt->bind_param('i', $user_id);
		$stmt->execute();
		$stmt->store_result();

		//more than 5 failed logins
		if ($stmt->num_rows > 5) {
			return true;
		} else {
			return false;
		}
	}
}


//check if user logged in
function check_login($mysqli, $type) {

	// check if all session variables are set
	if (isset($_SESSION['user_id'],
				$_SESSION['username'],
				$_SESSION['login_string'])) {

		$user_id = $_SESSION['user_id'];
		$username = $_SESSION['username'];
		$login_string = $_SESSION['login_string'];

		$user_browser = $_SERVER['HTTP_USER_AGENT'];

		if ($stmt = $mysqli ->prepare("SELECT m.Password
										FROM Members AS m
										INNER JOIN Members_Type AS mt
										ON m.ID = mt.Member_ID
										WHERE mt.Member_Type = ? AND m.ID = ? LIMIT 1")) {
			
			//bind parameter and execute query
			$stmt->bind_param('si', $type, $user_id);
			$stmt->execute();
			$stmt->store_result();

			if ($stmt->num_rows == 1) {
				//if user exists, get users details
				$stmt->bind_result($password);
				$stmt->fetch();

				//compare login string from session with generated login string (using hash)
				$login_check = hash('sha512', $password . $user_browser);
				if ($login_check == $login_string) {
					//logged in
					return true;
				} else {
					//not logged in
					return false;
				}
			} else {
				//not logged in
				return false;
			}
		} else {
			//not logged in
			return false;
		}
	} else {
		//not logged in
		return false;
	}

}

function display_crawler($mysqli_crawler, $page, $per_page) {

 	// set offset for displayings results from sql query
	$offset = ($page - 1) * $per_page;

	if ($stmt = $mysqli_crawler->prepare("SELECT * FROM Article_Summary LIMIT $offset, $per_page")) {
		$stmt->execute();
		$stmt->bind_result($ID, $title, $date, $author, $tease, $source, $updated);

		echo '<table border="1" style="width:100%">';

		while($stmt->fetch()){
			echo '<tr><td>' . $ID . '</td><td>' . $title . '</td><td>' . $date . '</td><td>' . $author . '</td><td>' . $tease . '</td><td>' . $source . '</td><td>' . $updated . '.</td><td><input type="button" value="View" onclick="location.href=\'crawler_article.php?id=' . $ID . '&page=' . $page . '\';"/></td></tr>';
		}
		echo '</table>';

		$stmt->close();
	}

}

function count_rows($mysqli_crawler) {

	if ($count = $mysqli_crawler->query("SELECT COUNT(*) FROM Article_Summary")) {

		// determine number of results in query
	 	$row_count = $count->fetch_row();
	 	$count->close();
	 	return $row_count[0];
	 }
}

function display_article($mysqli_crawler, $ID){

	if ($stmt = $mysqli_crawler->prepare("SELECT a.Content, a.References, a_s.Title, a_s.Author, a_s.Article_Date
											FROM Article AS a
											INNER JOIN Article_Summary AS a_s
											WHERE a.Summary_ID = ? AND a_s.ID = ? LIMIT 1")) {

		$stmt->bind_param('ii', $ID, $ID); // binds email variable to prepared statement (bind_param forces email to be a string, indicated by 's')
		$stmt->execute(); //execute prepared SQL query
		$stmt->store_result();

		// get result from query
		$stmt->bind_result($content, $ref, $title, $author, $date); // binds empty variables to result from prepared statement
		$stmt->fetch(); //fetches results of prepared statement

		echo '<input type="button" value="Go Back" onclick="location.href=\'crawler_results.php?page=' . $_GET["page"] . '\';"/>';
		echo "<h1>" . $title . "</h1><hr>";
		echo "<p><b>Date:</b> " . $date . ", <b>Author:</b> " . $author . "</p><hr>";
		echo "<span class='content'>" . $content . "</span><hr>";
		echo "<p><b>References:</b> <a href='" . $ref . "'>" . $ref . "</a></p><br>";
		echo '<input type="button" value="Go Back" onclick="location.href=\'crawler_results.php?page=' . $_GET["page"] . '\';"/>';
	}
}