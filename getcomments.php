<?php

// ----- link external files -----
include "config.php";
include "functions.php";

?>

<html lang="en">
<head>
	<title>lineminer - show lines</title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<link rel="stylesheet" type="text/css" href="main.css">
</head>

<body>

<table class="lines">
<?php

$date = $_GET["date"];
$timescale = $_GET["timescale"];
$filename = $_GET["filename"];
$filetype = $_GET["filetype"];
$query = $_GET["query"];

$minfblikes = (isset($_GET["minfblikes"])) ? $_GET["minfblikes"]:0;
$minytlikes = (isset($_GET["minytlikes"])) ? $_GET["minytlikes"]:0;
$minretweets = (isset($_GET["minretweets"])) ? $_GET["minretweets"]:0;
$minfavs = (isset($_GET["minfavs"])) ? $_GET["minfavs"]:0;

print_r($_GET);

if($timescale == "day") {
	$startdate = $date . " 00:00:00";
	$enddate = $date . " 23:59:59";
}

if($timescale == "week") {
	$year = substr($date,0,4);
	$week_number = substr($date,5,2);
	
	$startdate = date('Y-m-d', strtotime($year."W".$week_number."1")) . " 00:00:00";
	$enddate = date('Y-m-d', strtotime($year."W".$week_number."7")) . " 23:59:59";
}

echo "Tweets from " . $startdate . " to " . $enddate;

//exit;

$separator = (preg_match("/\.tab/",$filename)) ? "\t":",";
$fr = fopen($filename,'r');
$counter = 0;

// ----- main file loop -----
while(($buffer = fgets($fr)) !== false) {

	echo '<tr class="lines">';

	$buffer = str_getcsv($buffer,$separator,'"');

	if($counter == 0) {			// jump first line
		echo '<th class="lines">';
		echo implode('</th><th class="lines">', $buffer);
		echo '</th></tr>';
		$counter++;
		continue;
	}
	

	// select appropriate colums
	if($filetype == "facebook") {
		$unixdate = strtotime($buffer[4]);
		$content = $buffer[8];
	} else if($filetype == "youtube") {
		$unixdate = strtotime($buffer[3]);
		$content = $buffer[5];	
	} else if($filetype == "twitter") {
		$unixdate = strtotime($buffer[2]);
		$content = $buffer[4];	
	}
	
	//echo strtotime($startdate) . " " . strtotime($enddate) . " " . $unixdate . "<br />";
	
	// time filter
	if($unixdate < strtotime($startdate) || $unixdate > strtotime($enddate)) { continue; }
	if(!preg_match("/".$query."/i",$content) && $query != "all") { continue; }
	
	// filter lines below specified threshold
	if($filetype == "facebook") {
		if($buffer[10] < $minfblikes) { continue; }
	} else if($filetype == "youtube") {
		if($buffer[2] < $minytlikes) { continue; }
	} else if($filetype == "twitter") {
		if($buffer[10] < $minretweets || $buffer[11] < $minfavs) { continue; }
	}

	
	echo '<td class="lines">';
	echo implode('</td><td class="lines">', $buffer);
	echo '</td>';
	
	echo '</tr>';
}
	
?>

</table>

</body>
</html>