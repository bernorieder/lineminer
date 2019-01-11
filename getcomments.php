<?php

// ----- link external files -----
include "config.php";
include "functions.php";

//print_r($_GET);

// ----- parse parameters -----
$date = $_GET["date"];
$timescale = $_GET["timescale"];
$datafile = $_GET["datafile"];
$query = (isset($_GET["query"])) ? urldecode($_GET["query"]):"";
$query = preg_replace("/ or /","|",strtolower($_GET["query"]));

$colloc_date = urldecode($_GET["colloc_date"]);
$collocs_text = explode(",", urldecode($_GET["collocs_text"]));
$collocs_score_tmp = explode(",", urldecode($_GET["collocs_score"]));
for($i = 0; $i < count($collocs_score_tmp); $i++) {
	$tmp = explode("|", $collocs_score_tmp[$i]);
	$collocs_score[$tmp[0]] = $tmp[1];
}

if($timescale == "minute") {
	$startdate = $date . " 00:00:00";
	$enddate = $date . " 23:59:59";
}

if($timescale == "hour") {
	$startdate = $date . " 00:00:00";
	$enddate = $date . " 23:59:59";
}


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

if($timescale == "month") {
	$startdate = $date . "-01 00:00:00";
	$enddate = date("Y-m-t", strtotime($startdate)) . " 23:59:59";
}

if($timescale == "year") {
	$startdate = $date . "-01-01 00:00:00";
	$enddate = $date . "-12-31 23:59:59";
}

?>

<html lang="en">
<head>
	<title>lineminer - show lines</title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<link rel="stylesheet" type="text/css" href="main.css">
</head>

<body>
	
<div class="rowTab">
	<div class="sectionTab"><h3>
		
<?php

echo "Lines from " . $startdate . " to " . $enddate . " with query [" . $query . "]";

?>

	</h3></div>
</div>

<div class="rowTab">
	<div class="fullTab">
		<table class="lines">

<?php

//exit;

$delimiter = (preg_match("/\.tab/",$datafile) || preg_match("/\.tsv/",$datafile)) ? "\t":",";
$fr = fopen($datadir . $datafile,"r");
$counter = 0;

// ----- main file loop -----
while(($buffer = fgets($fr)) !== false) {

	echo '<tr class="lines">';

	$buffer = str_getcsv($buffer,$delimiter,'"');

	if($counter == 0) {			// jump first line
		$buffer[0] = preg_replace("/\xEF\xBB\xBF/","",$buffer[0]);
		echo '<th class="lines">';
		echo implode('</th><th class="lines">', $buffer);
		echo '</th></tr>';
		$counter++;
		continue;
	}
	
	
	// time filter
	$unixdate = strtotime($buffer[$colloc_date]);
	//print_r($unixdate . " ");
	if($unixdate < strtotime($startdate) || $unixdate > strtotime($enddate)) { continue; }	
	//print_r($buffer);
	//exit;
	
	
	// content filter
	$content = "";
	foreach($collocs_text as $colloc_text) {
		$content .= $buffer[$colloc_text] . " ";
	}
	
	// score filter
	foreach($collocs_score as $col => $score) {
		if(intval($buffer[$col]) < intval($score)) { continue 2; }
	}
	
	// content filter
	if(preg_match("/ and /i",$query)) {
				
		$parts = explode(" and ", $query);
		foreach($parts as $part) {
			if(!preg_match("/".addslashes($part)."/i", $content)) { continue 2; }
		}
				
	} else {
		
		if(!preg_match("/".addslashes($query)."/i",$content) && $query != "all") { continue; }
	}
		
	echo '<td class="lines">';
	echo implode('</td><td class="lines">', $buffer);
	echo '</td>';
	
	echo '</tr>';
}
	
?>

		</table>
	</div>
</div>

</body>
</html>