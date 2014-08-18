<?php

/*
	Creates a GPX file showing tracks for a particular period (specified 
	as GET parameters from=unixtime & to=unixtime, both optional)
*/


include_once ( __DIR__  . "/sql.php"); 
include_once ( __DIR__  . "/places.php");

$result = get_locations($_GET["from"], $_GET["to"]);

?><?php echo "<?"; ?>xml version="1.0" encoding="UTF-8"<?php echo "?>\n"; ?>
<gpx version="1.0">
	<name>My Tracks</name>
	<trk><name>Track</name><number>1</number><trkseg>
<?php

foreach ( $result as $location )
{
	$lat = $location['latitude'];
	$long = $location['longitude'];
	$time = date("Y-m-d\Th:i:s\Z", $location['reqtime']);
	echo "		<trkpt lat=\"$lat\" lon=\"$long\"><ele>0</ele><time>$time</time></trkpt>\n";
}
?>
	</trkseg></trk>
</gpx>