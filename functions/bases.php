<?php

/*
	Function for identifying most commonly-visited places

	$from and $to are unix timestamps and both optional (set 0 to ignore)
*/


include_once( __DIR__ . "/sql.php");
include_once( __DIR__ . "/places.php");

function intcmp($a, $b) { return $a['score'] - $b['score']; }	// For uasort comparison later

function find_bases($from, $to)
{
	$result = get_locations($from, $to);

	// Cycle through locations and create an array of locations based on 
	// rounding the latitude and longitude to three decimal places:

	foreach ( $result as $waypoint )
	{

		$lat = round( $waypoint['latitude'], 3 );
		$lon = round( $waypoint['longitude'], 3 );

		$locations[]['roundlat'] = $lat;							// Rounded
		$locations[count($locations)-1]['roundlon'] = $lon;				// Rounded
		$locations[count($locations)-1]['latitude'] = $waypoint['latitude'];		// Exact
		$locations[count($locations)-1]['longitude'] = $waypoint['longitude'];	// Exact

	}


	// Cycle through all locations, store new ones in $unique_locations, and add up 'scores' for each place:

	foreach ( $locations as $point )
	{

		$flag=0;

		for ( $n=0; $n < count($unique_locations); $n++ ) 
		{

			// Collision detection! (if lat/long rounded to three and a bit decimal
			// places is the same, then it's an existing place):

			$tolerance = 0.001;

			if (	$unique_locations[$n]['roundlat'] >= $point['roundlat'] - $tolerance 
			&&	$unique_locations[$n]['roundlat'] <= $point['roundlat'] + $tolerance
			&&	$unique_locations[$n]['roundlon'] >= $point['roundlon'] - $tolerance
			&&	$unique_locations[$n]['roundlon'] <= $point['roundlon'] + $tolerance
			   )
			{
				// Increase this location's score:
				$unique_locations[$n]['score']++;

				// Store the exact waypoint too, for later:
				$c = count($unique_locations[$n]['waypoints'])-1;
				$unique_locations[$n]['waypoints'][$c]['latitude'] = $point['latitude'];
				$unique_locations[$n]['waypoints'][$c]['longitude'] = $point['longitude'];

				$flag = 1;
			}

		}

		if ( !$flag )
		{
			// Store rounded coordinates:
			$unique_locations[]['roundlat'] = $point['roundlat'];
			$unique_locations[count($unique_locations)-1]['roundlon'] = $point['roundlon'];

			// Add this waypoint's exact coordinates for later:
			$unique_locations[count($unique_locations)-1]['waypoints'][0]['latitude'] = $point['latitude'];
			$unique_locations[count($unique_locations)-1]['waypoints'][0]['longitude'] = $point['longitude'];

			// Set score to 1
			$unique_locations[count($unique_locations)-1]['score'] = 1;
		}

	}


	// Because we're using a rounded lat/long for comparing places, we also need
	// to calculate a 'canonical' location for this place. This is the average of
	// all the waypoints which fall within the rounded area:

	for ( $n=0; $n < count($unique_locations); $n++ )
	{

		$totallat=0; $totallong=0;
		foreach ( $unique_locations[$n]['waypoints'] as $wp )
		{
			$totallat = $totallat + $wp['latitude'];
			$totallong = $totallong + $wp['longitude'];
		}

		$unique_locations[$n]['latitude'] = $totallat / count($unique_locations[$n]['waypoints']);
		$unique_locations[$n]['longitude'] = $totallong / count($unique_locations[$n]['waypoints']); 

	}

	// Sort by score and reverse (so most-visited are at the top):
	uasort($unique_locations, "intcmp");
	$unique_locations = array_reverse($unique_locations);

	return $unique_locations;

}