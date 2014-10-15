<?php

/*
	MySQLi stuff
*/

$conn=0;
include( __DIR__ . "/../database.ini");	// Load MySQL settings

function opendb()
{
	global $conn, $dbhost, $dbuser, $dbpass, $dbname, $is_installing;

	if ( $is_installing )
	{
		if ( !file_exists("database.ini") )
		{
			if ( !$dbhost || !$dbuser || $dbpass || $dbname ) die("<meta http-equiv='refresh' content='0;url=install.php'>");
		}
	}

	$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
	if($conn->connect_errno > 0) die('Couldn\'t connect to the database. [' . $conn->connect_error . ']. If you haven\'t installed the software yet, please read README.txt.');
}

function closedb()
{
	global $conn;
	$conn->close();
}


function query($query, $wantresult)
{
	global $conn, $is_installing;

	opendb();

		if( !$result = $conn->query($query) ) 
			{
				if ( !$wantresult ) die('Couldn\'t run the query. [' . $conn->error . ']');
				$wantresult = 0;
				$errored = 1;
			}
		
		if ( $wantresult ) while($row = $result->fetch_assoc()) $output[] = $row;
		if ( $errored ) return 0;

	closedb();

	return $output;
}

