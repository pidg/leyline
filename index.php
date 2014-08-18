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

$from = time() - (3600*40);	// show up to the last 40 hours of locations... you can change this.
$to = time();
$max = 5;	// Maximum number of places to highlight

// ----------------------------------------------------------- //

include_once("functions/sql.php");
include_once("functions/places.php");
include_once("functions/bases.php");
include_once("functions/usermgmt.php");

$is_installing=1; 	// This means we check whether setup has been run

// Grab all locations from database:
$result = get_locations($from, $to);

$is_installing=0;	// Turn this off because it's not needed after this point and breaks stuff

if ( count($result) < 10 ) { echo "(You've only checked in " . (count($result)) . " times in the period selected. This isn't likely to be enough to do anything useful.)"; }

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

	foreach ( $result as $location )
	{

		// Set bubble text:
		$when = "Location at " . date("d/m/Y H:i:s", $location['reqtime']);

		// Output waypoint code:
		echo	"var feature = new OpenLayers.Feature.Vector(
				new OpenLayers.Geometry.Point( " . $location['longitude'] . ", " . $location['latitude'] . " ).transform(epsg4326, projectTo),
				{description:'" . $when . "'} ,
				{externalGraphic: 'images/point.png', graphicHeight: 40, graphicWidth: 40, graphicXOffset:-20, graphicYOffset:-20  }
	       		);
			vectorLayer.addFeatures(feature);\n";
	}


	// Mark 5 most common locations with a special marker

	$bases = find_bases($from, $to);
	$max=$max-1;	// Adjust for 0 index

	for ( $n=0; $n <= $max; $n++ )
	{
		// Set bubble text:
		$when = "You were here for " . $bases[$n]['score'] . " check-ins (" . ($n+1) . "/" . ($max+1) . ")";

		// Output waypoint code:
		echo	"var feature = new OpenLayers.Feature.Vector(
				new OpenLayers.Geometry.Point( " . $bases[$n]['longitude'] . ", " . $bases[$n]['latitude'] . " ).transform(epsg4326, projectTo),
				{description:'" . $when . "'} ,
				{externalGraphic: 'images/marker.png', graphicHeight: 41, graphicWidth: 22, graphicXOffset:-11, graphicYOffset:-41  }
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
	<div style="width:90%; height:90%" id="map"><!-- Map goes here --></div>
</body>
</html>