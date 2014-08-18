<?php

/*

	Functions for logging users in and out

*/

include_once ( __DIR__ . "/sql.php" );

function valid_user($user, $pass)
{

	// Checks if a username and password pair are valid

	global $dbprefix;

	$query = "SELECT * FROM `" . $dbprefix . "users` WHERE username='" . $user . "'";
	$result = query($query, 1);

	if ( !$result )
	{
		// No such username
		return 0;

	} else {

		// Username found, validate password

		if ( password_verify($pass, $result[0]['password']) )
		{		
			return 1;
		} else {
			return 0;
		}	
	
	}
}

function login($user, $pass)
{
	// Logs user in

	if ( valid_user($user, $pass) )
	{
		setcookie("locuser", $user, 0, "/");
		setcookie("locpass", $pass, 0, "/");
		return 1;
	} else {
		logout();
		return 0;
	}

}

function logout()
{
	// Logs user out

	setcookie("locuser", NULL, -1, "/");
	setcookie("locpass", NULL, -1, "/");
}

function is_logged_in()
{

	// Checks whether user is logged in (using cookies)

	global $dbprefix;

	if ( $_COOKIE["locuser"] && $_COOKIE["locpass"] )
	{
		$result = ( valid_user($_COOKIE["locuser"], $_COOKIE["locpass"]) ) ? 1 : 0;
		return $result;

	} else {

		return 0;

	}
}


function create_user($username, $password, $role)
{

	// Creates a new user

	global $dbprefix;

	$pass = password_hash($password, PASSWORD_BCRYPT);
	$query = "INSERT INTO `" . $dbprefix . "users` (`username`, `password`, `role`) VALUES ('" . $username . "', '" . $pass . "', " . $role . ");";
	query($query, 0);

}


?>
