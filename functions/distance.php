<?php

function distance($lat1, $long1, $lat2, $long2, $miles = true)
{
    // Gets distance between two points

    $pi80 = M_PI / 180;
    $lat1 *= $pi80;
    $long1 *= $pi80;
    $lat2 *= $pi80;
    $long2 *= $pi80;

    $r = 6371.009; // mean radius of Earth in km
    $dlat = $lat2 - $lat1;
    $dlong = $long2 - $long1;
    $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlong / 2) * sin($dlong / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $km = $r * $c;
    
    return ($miles ? ($km * 0.621371192) : $km);
}

function speed($distance, $start, $end)
{
	// Returns speed of travel between two unix timestamps

	$time = $end-$start;
	$hours = $time / 3600;

	return ($distance / $hours);
}

function transport($speed, $human_readable = false, $miles = true)
{
	// Determines mode of transport used (very approximately)

	if ( !$miles ) $speed *= 0.621371192;	// km

	if ( $speed > 0 ) $mode = 0;	// walk
	if ( $speed > 5 )  $mode = 1;	// cycle
	if ( $speed > 8 ) $mode = 2;	// car/bus
	if ( $speed > 80 ) $mode = 3;	// fast train
	if ( $speed > 130 )$mode = 4;	// aircraft
	if ( $speed > 600 )$mode = 5;	// evacuated maglev

	if ( $human_readable ) 
	{
		switch($mode)
		{
			case 0: $mode="foot"; break;
			case 1: $mode="bicycle"; break;
			case 2: $mode="car/bus"; break;
			case 3: $mode="fast train"; break;
			case 4: $mode="air"; break;
			case 5: $mode="evaucated maglev"; break;
		}
	}

	return $mode;
}

