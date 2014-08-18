<?php

/*
	Gets locations from database for a given period (unix timestamps).
	get_locations(0,0) gets the whole dataset.
*/

include_once ( __DIR__ . "/sql.php");

function get_locations($from, $to)
{
	global $dbprefix;

	if ( $from && !$to ) $to = time();
	if ( $to && !$from ) $from = 1;
	
	$where = ( $from && $to ) ? "WHERE reqtime >= $from AND reqtime <= $to " : "";
	
	$result = query("SELECT * FROM `" . $dbprefix . "location` $where ORDER BY `id` ASC", 1);
	return $result;
}
