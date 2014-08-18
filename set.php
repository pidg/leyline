<?php

/* 
	Receives location data from the Backitude app and stores it in the database.
*/

include_once ("functions/sql.php");
include_once ("functions/usermgmt.php");

$lat	= $_POST["latitude"];		// 
$long	= $_POST["longitude"];		// 
$locat	= $_POST["loc_timestamp"];		// Last time location was recorded?
$reqat	= $_POST["req_timestamp"];		// Time request was actually made
$acc	= $_POST["accuracy"];
$offset= $_POST["offset"];			// e.g. +01:00 for BST

$day 	= date("d", $reqat);
$month = date("m", $reqat);
$year 	= date("Y", $reqat);
$hour	= date("H", $reqat);
$minute= date("i", $reqat);
$second= date("s", $reqat);

$user	= $_POST["u"];			// Requires a valid user to update the database
$pass	= $_POST["p"];

if ( valid_user($user, $pass) )
{
	query("INSERT INTO `" . $dbprefix . "location` (`id`, `latitude`, `longitude`, `loctime`, `reqtime`, `year`, `month`, `day`, `hour`, `minute`, `second`, `accuracy`, `zone`) VALUES (NULL, '" . $lat . "', '" . $long . "', '" . $locat . "', '" . $reqat . "', '" . $year . "', '" . $month . "', '" . $day . "', '" . $hour . "', '" . $minute . "', '" . $second . "', '" . $acc . "', '" . $offset . "');", 0);
} else {
	header("Location: /");
}
