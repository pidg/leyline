<?php

/* 
	Leyline
	This plots your locations onto an OSM SlippyMap and adds tracks between them. 

	by @tarasyoung

	Map code based heavily on example code from:
	http://wiki.openstreetmap.org/wiki/Openlayers_Track_example
	http://www.openlayers.org/dev/examples/osm-marker-popup.html
	http://harrywood.co.uk/maps/examples/openlayers/marker-popups.html

*/


// ----------------------------------------------------------- //

// Grab any start or end date from GET vars:
$sd = $_GET["startday"];
$sm = $_GET["startmonth"];
$sy = $_GET["startyear"];
$ed = $_GET["endday"];
$em = $_GET["endmonth"];
$ey = $_GET["endyear"];

// Convert start/end dates to unix timestamps:
$from = ( $sd || $sm || $sy ) ? mktime(0, 0, 1, $sm, $sd, $sy) : time() - (60*60*24*7);
$to = ( $ed || $em || $ey ) ? mktime(23, 59, 59, $em, $ed, $ey) : time();

$max = 5;	// Maximum number of places to highlight

// ----------------------------------------------------------- //

include_once("functions/sql.php");			// Connects to database
include_once("functions/places.php");		// Gets locations from database
include_once("functions/bases.php");		// Figures out most visited places
include_once("functions/usermgmt.php");		// User management
include_once("functions/dateform.php");		// Creates date forms
include_once("functions/revgeocode.php");		// Point-to-address (reverse geocoding)
include_once("functions/distance.php");		// Distance calculation

$is_installing=1; 	// This means we check whether setup has been run

$result = get_locations($from, $to);	// Grab all locations from datatbase
$bases = find_bases($from, $to);		// Order by the most common places visited

$location_home = $bases[0]['waypoints'][0];	// Home location (a guess)
$location_work = $bases[1]['waypoints'][0];	// Work location (a guess)

$home_address = reverse_geocode($location_home['latitude'], $location_home['longitude'] );
$work_address = reverse_geocode($location_work['latitude'], $location_work['longitude'] );

$is_installing=0;	// Turn this off because it's not needed after this point and breaks stuff

if ( count($result) < 10 ) { echo "(You've only checked in " . (count($result)) . " times in the period selected. This isn't likely to be enough to do anything useful. Try again when you've checked in for a day or so)"; }

?><html>
<head>
	<title>My Locations</title>

	<script src="http://www.openlayers.org/api/OpenLayers.js"></script>
	<script src="http://www.openstreetmap.org/openlayers/OpenStreetMap.js"></script>
 
	<script type="text/javascript">
<?php

	// Set some ridiculous initial values:
	$maxlat = -1000;
	$maxlong = -1000;
	$minlat = 1000;
	$minlong = 1000;

	// Determine minimum and maximum latitude and longitude visited:

	foreach ( $result as $location )
	{
		if ( $location['latitude'] > $maxlat ) $maxlat = $location['latitude'];
		if ( $location['latitude'] < $minlat ) $minlat = $location['latitude'];
		if ( $location['longitude'] > $maxlong ) $maxlong = $location['longitude'];
		if ( $location['longitude'] < $minlong ) $minlong = $location['longitude'];
	}
	
	$centerlat = $minlat + (( $maxlat - $minlat ) / 2 );		// Find centre point latitude
	$centerlong = $minlong + (( $maxlong - $minlong ) / 2 );	// Find centre point longitude
	
	// Set starting lat, long and zoom in Javascript:
	echo "	var lat=$centerlat;\n";
	echo "	var lon=$centerlong;\n";
	echo "	var zoom=7;\n\n";

?> 
		function init() {

		// Create new map object

		var map;

			map = new OpenLayers.Map ("map", {
				controls:[
					new OpenLayers.Control.Navigation(),
					new OpenLayers.Control.PanZoomBar(),
					new OpenLayers.Control.LayerSwitcher(),
					new OpenLayers.Control.Attribution()],
				maxExtent: new OpenLayers.Bounds(-20037508.34,-20037508.34,20037508.34,20037508.34),
				maxResolution: 156543.0399,
				numZoomLevels: 19,
				units: 'm',
				projection: new OpenLayers.Projection("EPSG:900913"),
				displayProjection: new OpenLayers.Projection("EPSG:4326")
			} );

			// Add Mapnik layer:

			layerMapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik");
			map.addLayer(layerMapnik);

			// Get projection info

			var epsg4326 = new OpenLayers.Projection("EPSG:4326");
			var projectTo = map.getProjectionObject(); // Map projection (Spherical Mercator)

 
			// Add GPX track layer (loads using tracksgpx.php):

			var lGPX = new OpenLayers.Layer.Vector("My Locations", {
				strategies: [new OpenLayers.Strategy.Fixed()],
				protocol: new OpenLayers.Protocol.HTTP({
					url: "functions/tracksgpx.php?from=<?php echo $from; ?>&to=<?php echo $to; ?>",
					format: new OpenLayers.Format.GPX()
				}),
				style: {strokeColor: "#ff0000", strokeWidth: 3, strokeOpacity: 0.5},
				projection: new OpenLayers.Projection("EPSG:4326")
			});

			map.addLayer(lGPX);


			var lonLat = new OpenLayers.LonLat(lon, lat).transform(epsg4326, projectTo);

			map.setCenter(lonLat, zoom);

			// Add 'Overlay' layer for our waypoint markers

			var vectorLayer = new OpenLayers.Layer.Vector("Overlay");

<?php 

	// Cycle through waypoints and add each one

	$total_distance=0;
	$flag=0;	// Flag whether this is the first point, in which case ignore.
	foreach ( $result as $location )
	{
	
		if ( $flag )
		{

			// This works out how fast you were travelling for each leg and stores in $distances[] by guessed mode of transport:

			$distance = 0;
			$distance = round(distance($lastone["latitude"], $lastone["longitude"], $location["latitude"], $location["longitude"]),3);
			$speed = round(speed($distance, $lastone["reqtime"]-$location["reqtime"]),2);
			$transport = transport($speed, 1);
			
			$distances[$transport] += $distance;
			$total_distance += $distance;
		}

		$flag=1;

		// Set bubble text:
		$when = "Location at " . date("d/m/Y H:i:s", $location['reqtime']) . " travelling at $speed mph by $transport.<br>Distance from last point: $distance miles";

		// Output waypoint code:
		echo	"var feature = new OpenLayers.Feature.Vector(
				new OpenLayers.Geometry.Point( " . $location['longitude'] . ", " . $location['latitude'] . " ).transform(epsg4326, projectTo),
				{description:'" . $when . "'} ,
				{externalGraphic: 'images/point.png', graphicHeight: 40, graphicWidth: 40, graphicXOffset:-20, graphicYOffset:-20  }
	       		);
			vectorLayer.addFeatures(feature);\n";

		$lastone = $location;

	}

	// It's very unlikely that someone travelled less than 30 miles by air, so it's probably a glitch:
	if ( $distances["air"] <= 30 )
	{
		$distances["car/bus"] += $distances["air"];
		$distances["air"] = 0;
	}

	// It's very unlikely that someone travelled less than 15 miles by fast train, so it's probably a glitch:
	if ( $distances["air"] <= 15 )
	{
		$distances["car/bus"] += $distances["fast train"];
		$distances["fast train"] = 0;
	}


	// Mark 5 most common locations with a special marker
	// we already did 	$bases = find_bases($from, $to);

	$max=$max-1;	// Adjust for 0 index

	for ( $n=0; $n <= $max; $n++ )
	{
		// Set bubble text:
		$when = "You were here for " . $bases[$n]['score'] . " check-ins (" . ($n+1) . "/" . ($max+1) . ")";

		$marker = ( $n < 1 ) ? "home" : "marker";

		$iconheight = ( $marker == "home" ) ? 37 : 41;
		$iconwidth = ( $marker == "home" ) ? 32 : 22;

		$iconYoffset = 0-$iconheight;
		$iconXoffset = (0-$iconwidth) / 2;

		// Output waypoint code:
		echo	"var feature = new OpenLayers.Feature.Vector(
				new OpenLayers.Geometry.Point( " . $bases[$n]['longitude'] . ", " . $bases[$n]['latitude'] . " ).transform(epsg4326, projectTo),
				{description:'" . $when . "'} ,
				{externalGraphic: 'images/$marker.png', graphicHeight: $iconheight, graphicWidth: $iconwidth, graphicYOffset:$iconYoffset, graphicXOffset:$iconXoffset }
	       		);
			vectorLayer.addFeatures(feature);\n";
	}


?>

	// Add all waypoints:
	map.addLayer(vectorLayer);

	// Add a selector control to the vectorLayer with popup functions

	var controls = { selector: new OpenLayers.Control.SelectFeature(vectorLayer, { onSelect: createPopup, onUnselect: destroyPopup }) };

	function createPopup(feature)
	{

		feature.popup = new OpenLayers.Popup.FramedCloud("pop",
			feature.geometry.getBounds().getCenterLonLat(),
			null,
			'<div class="markerContent">'+feature.attributes.description+'</div>',
			null,
			true,
			function() { controls['selector'].unselectAll(); }
			);
		// feature.popup.closeOnMove = true;

		map.addPopup(feature.popup);
	}

	function destroyPopup(feature)
	{
		feature.popup.destroy();
		feature.popup = null;
	}
    
	map.addControl(controls['selector']);
	controls['selector'].activate();

}
	</script>
 
</head>
<body onload="init();">

	<div id="date">
		<form action="index.php" method="get">
			From: <?php 


				if ( !$sd ) $sd = 1;
				if ( !$sm ) $sm = 1;
				if ( !$sy ) $sy = 2014;

				if ( !$ed ) $ed = 0;
				if ( !$em ) $em = 0;
				if ( !$ey ) $ey = 0;

				dateform("start", $sd, $sm, $sy); 

			?> // To: 
			<?php dateform("end", $ed, $em, $ey); ?> // 
			<input type="submit" value="View">
		</form>
	</div>

	<div>
	<?php 

	/* 
		// Display modes of transport.  Still glitchy so commented out.

		echo "You travelled $total_distance miles in this period using the following modes of transport:\n";

		echo "<table>\n";
		foreach ( $distances as $mode=>$distance )
		{
			echo "<tr><td>$mode</td><td>$distance</td></tr>\n";
		}
		echo "</table>\n";

	*/

 ?>
	</div>

	<div id="map" style="width:90%; height:90%"><!-- Map goes here --></div>
</body>
</html>