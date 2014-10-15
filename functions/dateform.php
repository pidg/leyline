<?php

// Some functions for building a date picker

function numerical_select($fieldname, $start, $end, $padding, $selected)
{
	// e.g.  numerical_select("year", 2010, 2020, 4, 2014)

	echo "<select class=\"datefield\" name=\"$fieldname\">\n";

	for ( $n=$start; $n <= $end; $n++ )
	{
			$pad = sprintf("%0" . $padding . "d", $n);
			$is_selected = ($pad == $selected) ? " selected=\"selected\"" : "";
			echo "	<option" . $is_selected . " value=\"$pad\">$pad</option>\n";
	}

	echo "</select>\n";
}

function month_select($fieldname, $selected)
{
	echo "<select class=\"datefield\" name=\"$fieldname\">\n";
	$months = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

	$n=0;
	foreach ( $months as $month )
	{
		$n++;
		$is_selected = ($n == $selected) ? " selected=\"selected\"" : "";
		echo "	<option" . $is_selected . " value=\"$n\">$month</option>\n";
	}

	echo "</select>\n";
}


function dateform($prefix, $d, $m, $y)
{

	// dateform("start", 27, 8, 2014);
	// set 0,0,0 to use today's date.

	if ( !$d ) $d = date("d");
	if ( !$m ) $m = date("n");
	if ( !$y ) $y = date("Y");

	numerical_select($prefix."day", 1, 31, 2, $d);
	month_select($prefix."month", $m);
	numerical_select($prefix."year", date("Y")-5, date("Y")+5, 4, $y);
}

