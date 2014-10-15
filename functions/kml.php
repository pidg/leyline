<?php

/*
	Creates a KML file of all points for a particular period (specified 
	as GET parameters from=unixtime & to=unixtime, both optional)
*/


include_once ( __DIR__  . "/sql.php"); 
include_once ( __DIR__  . "/places.php");

$result = get_locations($_GET["from"], $_GET["to"]);

?><?php echo "<?"; ?>xml version="1.0" encoding="UTF-8"<?php echo "?>\n"; ?>
<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">
<Document>
<name>Location history</name>
<open>1</open>
<description/>
<StyleMap id="multiTrack">
<Pair>
<key>normal</key>
<styleUrl>#multiTrack_n</styleUrl>
</Pair>
<Pair>
<key>highlight</key>
<styleUrl>#multiTrack_h</styleUrl>
</Pair>
</StyleMap>
<Style id="multiTrack_n">
<IconStyle>
<Icon>
<href>http://earth.google.com/images/kml-icons/track-directional/track-0.png</href>
</Icon>
</IconStyle>
<LineStyle>
<color>99ffac59</color>
<width>6</width>
</LineStyle>
</Style>
<Style id="multiTrack_h">
<IconStyle>
<scale>1.2</scale>
<Icon>
<href>http://earth.google.com/images/kml-icons/track-directional/track-0.png</href>
</Icon>
</IconStyle>
<LineStyle>
<color>99ffac59</color>
<width>8</width>
</LineStyle>
</Style>
<Placemark>
<name>Latitude User</name>
<description>Location history</description>
<styleUrl>#multiTrack</styleUrl>
<gx:Track>
<altitudeMode>clampToGround</altitudeMode>
<?php

foreach ( $result as $location )
{
	$lat = $location['latitude'];
	$long = $location['longitude'];
	$time = date("Y-m-d\Th:i:s\.000\+00:00", $location['reqtime']);
	echo "<when>$time</when>\n";
	echo "<gx:coord>$long $lat 0</gx:coord>\n";

}
?></gx:Track>
</Placemark>
</Document>
</kml>
