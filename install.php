<?php

/*
	Interactive, browser-based installation script.
*/

$is_installing = 1;

if ( !$_POST["install"] )
{  

// Form hasn't been submitted - check the state of installation and proceed

?><html>
<head>
	<title>Install</title>
	<style>
		body
		{
			font-family: 'Arial', sans-serif;
			margin: 1.5em;
		}

		.bordered
		{
			border: 1px solid #aaa;
			border-collapse: collapse;
			box-shadow: 0 0 8px #ccc;
		}

		.bordered td
		{
			border: 1px solid #aaa;
			padding: 5px;
		}

	</style>
	<script>
		function validate()
		{
			if
			(
				   document.getElementById('user').value 
				&& document.getElementById('pass').value 
				&& document.getElementById('dbhost').value
				&& document.getElementById('dbuser').value
				&& document.getElementById('dbpass').value
				&& document.getElementById('dbname').value
				&& document.getElementById('dbprefix').value
			)
			{ 
				document.getElementById('submit').disabled = false; 
			} else {	
				document.getElementById('submit').disabled = true;
			}

			document.getElementById('myprefix1').innerHTML = document.getElementById('dbprefix').value;
			document.getElementById('myprefix2').innerHTML = document.getElementById('dbprefix').value;

			document.getElementById('mydbname1').innerHTML = document.getElementById('dbname').value;
			document.getElementById('mydbname2').innerHTML = document.getElementById('dbname').value;

		}
	</script>
</head>
<body onload="document.getElementById('submit').disabled = true; validate();">
<?php

// Check whether everything's already set up:

if ( file_exists( __DIR__ . "/database.ini") )
{ 

	include_once("functions/sql.php");	
	$installed = ( query("SELECT * FROM `" . $dbprefix . "users` WHERE role='1'", 1) ) ? 1 : 0;

	if ( $installed )
	{
		// database.ini exists and there is an admin user

		?>
		<h1>Installed</h1>
		<p>Congratulations! Everything's set up and ready to go. <a href="index.php">Get started now.</a></p>
		<?php
	

			if ( $_GET["permissions"] )
			{
				echo "<p style=\"margin-bottom: 5px;\">You should now set the permissions for this folder back to what they were.<br> You can do this from the command line by entering:</p>\n";
				echo "<code style=\"background-color: #ddd; font-size: 1.2em;\">chmod " . $_GET["permissions"] . " " . __DIR__ . "</code>\n";
			}

			if ( !file_exists( __DIR__ . "/.htaccess") )
			{
				echo "<p>Optionally, you may also want to set up an .htaccess file to password protect this folder.</p>\n";
			}

	} else {

		// database.ini exists but there's no admin user. Usually because we could write the file but
		// the database settings were entered incorrectly.

		// Need to fix this to allow user to change database details in case they entered them wrongly.

		echo "you broke it!";

	}

} else {
?>

<div>
<h1>Leyline installer</h1>

<p>This browser-based installer will help you set up Leyline on your server.</p>
<form action="install.php" method="post">
<?php 

	// Check permissions of the folder are writeable to me. If not 0777 then ask user to change them temporarily:

	$p = substr(sprintf('%o', fileperms('.')), -4);
	if ( $p != "0777" )
	{
		echo "<p style=\"margin-bottom: 5px;\">First, make this folder writeable. You can do this from the command line by entering:</p>\n";
		echo "<code style=\"background-color: #ddd; font-size: 1.2em;\">chmod 0777 " . __DIR__ . "</code>\n";
		if ( $_GET["nochmod"] ) { echo "<p style=\"color: red;\">Installation won't complete without you doing this.</p>\n"; }
		echo "<p style=\"margin-top: 5px;\">This installer will remind you to set it back to " . $p . " once it's finished.</p>\n";
		echo "<input type=\"hidden\" name=\"permissions\" value=\"$p\">\n";
		$next=1;
	}

?>
<div style="max-width: 30em;">
<p><?php if ( $next ) { echo "Next,"; } else { echo "First,"; } ?> enter your MySQL database settings below. These are usually provided by whoever hosts your website.</p>
<table border="0" class="bordered">
<tr><td>Database host</td> <td><input id="dbhost" type="text" size="40" value="localhost" name="dbhost" onkeydown="validate();" onkeyup="validate();" onchange="validate();"></td></tr>
<tr><td>Database username</td> <td><input id="dbuser" type="text" size="40" value="" name="dbuser" onkeydown="validate();" onkeyup="validate();" onchange="validate();"></td></tr>
<tr><td>Database password</td> <td><input id="dbpass" type="password" size="40" value="" name="dbpass" onkeydown="validate();" onkeyup="validate();" onchange="validate();"></td></tr>
<tr><td>Database name</td> <td><input id="dbname" type="text" size="40" value="" name="dbname" onkeydown="validate();" onkeyup="validate();" onchange="validate();"><div style="font-size: 0.7em;">This database should already exist.</div></td></tr>
<tr><td>Prefix for tables</td> <td><input id="dbprefix" type="text" size="40" value="location_" name="dbprefix" onkeydown="validate();" onkeyup="validate();" onchange="validate();"><div style="font-size: 0.7em;">You don't need to change this unless you want to.</div></td></tr>
</table>

<p>You also need to provide a new username and password to create an administrative account:</p>
<table>
	<tr><td>Username:</td><td><input id="user" type="text" size="30" maxlength="64" value="admin" name="user" onkeydown="validate();" onkeyup="validate();" onchange="validate();"></td></tr>
	<tr><td>Password:</td><td><input id="pass" type="password" size="30" name="pass" onkeydown="validate();" onkeyup="validate();" onchange="validate();"></td></tr>
</table>

<p>Clicking 'Install now' will create the following tables, or empty them if they already exist:</p>
<ul>
	<li><span id="mydbname1"></span>.<span id="myprefix1"></span>location</li>
	<li><span id="mydbname2"></span>.<span id="myprefix2"></span>users</li>
</ul>
<div align="right">
	<input type="submit" value="Install now" id="submit">
</div>
<input type="hidden" name="install" value="1">
</form>

</div>
</div>

<?php
}

?>
</body>
</html><?php

} else {

/*  Form submitted - ready to install! */

$tester = new mysqli($_POST["dbhost"], $_POST["dbuser"], $_POST["dbpass"], $_POST["dbname"]);
	if($tester->connect_errno)
	{
		echo "Sorry, couldn't connect to the database with those details.<br><br>The error returned was: " . $tester->connect_error . "<br><br><a href=\"install.php\">Go back to the installation form</a>";
		exit();
	} else {
		$tester->close();
	}


// Create database.ini
$out = "<?php
\$dbhost = '" . $_POST["dbhost"] . "';
\$dbuser = '" . $_POST["dbuser"] . "';
\$dbpass = '" . $_POST["dbpass"] . "';
\$dbname = '" . $_POST["dbname"] . "';
\$dbprefix = '" . $_POST["dbprefix"] . "';
";

echo $out;

$f = fopen( __DIR__ . "/database.ini", "w");
	fputs($f, $out);
fclose($f);

// Include our MySQLi functions:
if ( file_exists("database.ini") ) 
{ 
	include_once("functions/sql.php");
} else {
	die("?><meta http-equiv='refresh' content='0;url=install.php?nochmod=1'>");
}


// Create tables (if they don't already exist):

$query = "CREATE TABLE IF NOT EXISTS `" . $dbprefix . "location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `loctime` varchar(11) NOT NULL,
  `reqtime` varchar(11) NOT NULL,
  `year` smallint(6) NOT NULL,
  `month` tinyint(4) NOT NULL,
  `day` tinyint(4) NOT NULL,
  `hour` tinyint(4) NOT NULL,
  `minute` tinyint(4) NOT NULL,
  `second` tinyint(4) NOT NULL,
  `accuracy` float NOT NULL,
  `zone` varchar(7) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Location tracking' AUTO_INCREMENT=1 ;";

query($query, 0);

$query = "CREATE TABLE IF NOT EXISTS `" . $dbprefix . "users` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL,
  `role` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

query($query, 0);

// Empty the tables (in case they do):

$query = "TRUNCATE TABLE `" . $dbprefix . "users`";
query($query, 0);

$query = "TRUNCATE TABLE `" . $dbprefix . "location`";
query($query, 0);

include_once("functions/usermgmt.php");			// Load user management functions
create_user($_POST["user"], $_POST["pass"], 1);	// Create admin user
login($_POST["user"], $_POST["pass"]);		// Log user in

if ( $_POST["permissions"] ) { $permissions = "?permissions=" . $_POST["permissions"]; }

header("Location: install.php" . $permissions);  // Reload this page and it should show the 'Installed' content.

}

