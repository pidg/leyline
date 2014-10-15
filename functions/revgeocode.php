<?php

// Reverse geocoding functions

function reverse_geocode($lat, $long)
{
	// Reverse geocode using Nominatim service

	/*
	Returns array containing e.g.
		[full] => 999 Letsbe Avenue, etc..
		[house_number] => 999
		[road] => Letsbe Avenue
		[suburb] => West Chesterton Ward
		[city] => Cambridge
		[county] => Cambridgeshire
		[state_district] => East of England
		[state] => England
		[postcode] => CB1 1ZE
		[country] => United Kingdom
		[country_code] => gb
	*/

	$data = file_get_contents("http://nominatim.openstreetmap.org/reverse?format=xml&lat=" . $lat . "&lon=" . $long . "&zoom=18&addressdetails=1");
	$data = json_decode(json_encode(simplexml_load_string($data)),true);

	$results = $data['addressparts'];
	$results['full'] = $data['result'];

	return $results;

}

?>