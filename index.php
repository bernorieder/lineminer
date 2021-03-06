<?php

// LineMiner is made by Bernhard Rieder, http://labs.polsys.net
// Documentation https://github.com/bernorieder/lineminer/wiki


// ----- link external files -----

include "config.php";
include "./common/functions.php";


// ----- check for and load list of data files -----

$filenames = array();
if ($dh = opendir($datadir)) {
	while (($file = readdir($dh)) !== false) {
		if(preg_match("/\.tab/", $file) || preg_match("/\.csv/", $file)) {
			$filenames[] = $file;
		}
	}
	closedir($dh);
} else {
	echo "Error: could not open files in data directory: " . $datadir;
}
asort($filenames);


// ----- check for and load list of stopword files -----

$stopfiles = array();
if ($dh = opendir($stopwordsdir)) {
	while (($file = readdir($dh)) !== false) {
		if(preg_match("/\.txt/", $file)) {
			preg_match("/_(.*?)\./",$file, $matches);
			$stopfiles[$matches[1]] = $file;
		}
	}
	closedir($dh);
} else {
	echo "Error: could not open files in stopword directory: " . $stopwordsdir;
}

?>

<!doctype html>

<html lang="en">
<head>
	<title>LineMiner</title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	
	<meta http-equiv="cache-control" content="no-cache"> 
	<meta http-equiv="expires" content="0"> 
	<meta http-equiv="pragma" content="no-cache">

	<script type="text/javascript" src="./common/jquery-3.1.1.min.js"></script>
	<script type="text/javascript" src="./common/d3.v4.min.js"></script>
	
	<script type="text/javascript" src="./common/functions.js"></script>

	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">

	google.load("visualization", "1", {packages:["corechart"]});

	</script>
	
	<link rel="stylesheet" type="text/css" href="main.css">
	<link href="https://fonts.googleapis.com/css?family=Droid+Sans|Muli:700" rel="stylesheet">
</head>

<body onload="onloadTodo();">

<div id="fullpage">

	<div id="if_header" class="if_structure">
		<div class="headTab">
			<div class="leftHead">LineMiner</div>
			<div class="rightHead">
				<a href="http://thepoliticsofsystems.net">blog</a>
				<a href="http://labs.polsys.net">software</a>
				<a href="http://thepoliticsofsystems.net/papers-and-talks/">research</a>
				<a href="https://www.digitalmethods.net">DMI</a>
				<a href="http://thepoliticsofsystems.net/about/">about</a>
			</div>
		</div>
	</div>
	
	
	<div id="if_description" class="if_structure">
		<div class="rowTab">
			<div class="fullTab">
				<p>This tool provides (reasonably) fast text searching through large CSV/TSV files where each line is a timestamped unit of text. The main search feature counts the number of lines a query - or queries - appear in.
				The tool adds a number of features for the exploration of query contexts.</p>
				
				<p>Source code is <a href="https://github.com/bernorieder/lineminer" target="_blank">available on github</a> and there is also a <a href="https://github.com/bernorieder/lineminer/wiki">documentation</a>.
				Written by <a href="http://labs.polsys.net" target="_blank">Bernhard Rieder</a>, with the support of <a href="http://www.uab.cat" target="_blank">Universitat Autònoma de Barcelona</a>.
				</p>
			</div>
		</div>
	</div>
		
	
	<div id="if_file" class="if_structure">
		
		<form action="index.php" method="get" onsubmit="sendForm(); return false;">
		
		<div class="rowTab">
			<div class="sectionTab"><h2>Choose a file to work with</h2></div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab">
				<p>This tool works on files read from a data directory on the machine it runs. Since it is designed to run on (very) big files,
				there is currently no upload function - talk to your administrator for how to add files.</p>
			</div>
		</div>
		
		<div class="rowTab">
			<div class="leftTab">Files in data directory:</div>
			
			<div class="rightTab">
				<select name="datafile" onchange="clearParams();loadFile(this.value)">
					<option value="none">select</option>
					<?php
					foreach($filenames as $filename) {
						echo '<option value="'.$filename.'">'.$filename.'</option>';
					}
					?>
				</select>
			</div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab"><p id="if_filedetected_text"></p></div>
		</div>
	</div>
	
	
	<div id="if_parameters" class="if_structure">
			
		<div class="rowTab">
			<div class="sectionTab"><h2>Define your analysis</h2></div>
		</div>
		
		<div class="rowTab">
			<div class="sectionTab"><h3>Choose the file columns to use</h3></div>
		</div>
		
		<div class="rowTab">
			<div class="leftTab">Timestamp:</div>
			<div class="rightTab" name="col_date">
				
			</div>
		</div>
		
		<div class="rowTab">
			<div class="leftTab">Text: <a onclick="createColelement('col_text')">add</a></div>
			<div class="rightTab" name="col_text">
				
			</div>
		</div>
		
		<div class="rowTab">
			<div class="leftTab">Score: <a onclick="createColelement('col_score')">add</a></div>
			<div class="rightTab" name="col_score">
				
			</div>
		</div>

		<div class="rowTab">
			<div class="sectionTab"><h3>Search parameters</h3></div>
		</div>
	
		<div class="rowTab">
			<div class="leftTab">Search query:</div>
			
			<div class="rightTab">
				<input type="text" name="query" /> (leave empty for no query, OR and AND, separate multiple queries with comma)
			</div>
		</div>
		
		<div class="rowTab">
			<div class="leftTab">File language:</div>
			
			<div class="rightTab">
				<select name="language">
					<?php
					foreach($stopfiles as $lang => $file) {
						echo '<option value="'.$lang.'">'.$lang.'</option>';
					}
					?>
				</select>
			</div>
		</div>
		
		<div class="rowTab">
			<div class="leftTab">Startdate:</div>
			
			<div class="rightTab">
				<input type="text" name="startdate" /> YYYY-MM-DD or YYYY-MM-DD HH:MM
			</div>
		</div>
		
		<div class="rowTab">
			<div class="leftTab">Enddate:</div>
			
			<div class="rightTab">
				<input type="text" name="enddate" /> YYYY-MM-DD or YYYY-MM-DD HH:MM
			</div>
		</div>
		
		<div class="rowTab">
			<div class="leftTab">Time interval:</div>
			
			<div class="rightTab">
				<input type="radio" name="timescale" value="minute" /> minute
				<input type="radio" name="timescale" value="hour" /> hour
				<input type="radio" name="timescale" value="day" /> day
				<input type="radio" name="timescale" value="week" checked="true" /> week
				<input type="radio" name="timescale" value="month" /> month
				<input type="radio" name="timescale" value="year" /> year
			</div>
		</div>
		
		
		<div class="rowTab">
			<div class="sectionTab"><h3>Analysis options</h3></div>
		</div>

		<div class="rowTab">
			<div class="fullTab"><input type="checkbox" name="showfull" /> show full count on top linegraph </div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab"><input type="checkbox" name="showscore" /> show <select name="scorecalc">
					<option value="sum">sum</option>
					<option value="avg">average</option>
				</select> of score column as extra line</div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab"><input type="checkbox" name="getcontext" /> show word context (with <input type="text" name="contextcutoff" style="width:25px;" value="15" /> words in lists and a window of <input type="text" name="contextwindow" style="width:25px;" value="0" /> words before and after; 0 = no limit) EPERIMENTAL: limit context to column: <input type="text" name="limittocol" style="width:50px;" /></div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab"><input type="checkbox" name="dowordtree" /> show word tree (experimental, use with a single query only; works well with queries like [we are]; can get very big for very common words; start with a small query; )</div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab"><input type="checkbox" name="dosummary" /> create a summary file for the query</div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab"><input type="checkbox" name="dooutput" /> write filtered lines to new file (use wisely)</div>
		</div>
		
		
		<div class="rowTab">
			<div class="fullTab">
				<input type="submit" />
			</div>
		</div>		
		
		</form>
	</div>

<?php


// ----- if there's no file, not point to continue -----

if(!isset($_GET["datafile"])) {
	echo '</body></html>';
	exit;
}  


// ----- base variables -----

$datebins = array();
$datebins_full = array();
$datebins_score = array();
$wordlists = array();
$wordlist_full = array();
$phrases = array();


// ----- get GET parameters and do some parsing -----

$query = (isset($_GET["query"])) ? urldecode($_GET["query"]):"";
$query = preg_replace("/ or /","|",strtolower($_GET["query"]));
$queries = explode(",",$query);
foreach($queries as $query) {
	$datebins[$query] = array();
	$wordlists[$query] = array();
	$phrases[$query] = array();
}

$timescale = ($_GET["timescale"] != "undefined") ? $_GET["timescale"]:"week";

$col_date = urldecode($_GET["col_date"]);
$cols_text = explode(",", urldecode($_GET["cols_text"]));
$cols_score_tmp = explode(",", urldecode($_GET["cols_score"]));
for($i = 0; $i < count($cols_score_tmp); $i++) {
	$tmp = explode("|", $cols_score_tmp[$i]);
	$cols_score[$tmp[0]] = $tmp[1];
}

$datafile = urldecode($_GET["datafile"]);

$language = (isset($_GET["language"])) ? urldecode($_GET["language"]):"english";
$stopwords = getstopwords($language);

$showfull = ($_GET["showfull"] == "true") ? true:false;
$showscore = ($_GET["showscore"] == "true") ? true:false;
$scorecalc = $_GET["scorecalc"];
$getcontext = ($_GET["getcontext"] == "true") ? true:false;
$contextcutoff = $_GET["contextcutoff"];
$contextwindow = $_GET["contextwindow"];
$limittocol = $_GET["limittocol"];
$dowordtree = ($_GET["dowordtree"] == "true" && $query != "") ? true:false;
$dooutput = ($_GET["dooutput"] == "true") ? true:false;
$dosummary = ($_GET["dosummary"] == "true") ? true:false;


// ----- date calculations -----

$_GET["startdate"] = ($_GET["startdate"] != "") ? $_GET["startdate"]:"1971-01-01";
if(preg_match("/ [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_GET["startdate"])) {
	$startdate = $_GET["startdate"];
} else if(preg_match("/ [0-9]{2}:[0-9]{2}$/", $_GET["startdate"])) {
	$startdate = $_GET["startdate"] . ":00";
} else {
	$startdate .= $_GET["startdate"] . " 00:00:00";
}

$_GET["enddate"] = ($_GET["enddate"] != "") ? $_GET["enddate"]:date("Y-m-d");
if(preg_match("/ [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_GET["enddate"])) {
	$enddate = $_GET["enddate"];
} else if(preg_match("/ [0-9]{2}:[0-9]{2}$/", $_GET["enddate"])) {
	$enddate .= $_GET["enddate"] . ":59";
} else {
	$enddate .= $_GET["enddate"] . " 23:59:59";
}


// ----- timescale calculations -----

switch ($timescale) {
	case "minute":
		$dateformat = "m-d H-i";
		$seconds = 60;
		break;
	case "hour":
		$dateformat = "m-d H";
		$seconds = 3600;
		break;
	case "day":
		$dateformat = "Y-m-d";
		$seconds = 86400;
		break;
	case "week":
		$dateformat = "o-W";
		$seconds = 604800;
		break;
	case "month":
		$dateformat = "Y-m";
		$seconds = 2419200;
		break;
	case "year":
		$dateformat = "Y";
		$seconds = 31449600;
		break;
}

$filename_out = "filtered_" . md5($query) . "_" . $datafile;
$filename_summary = "summary_" . md5($query) . ".csv";


$extension = substr($datafile, strlen($datafile) - 3);
$delimiter = ($extension == "tab" || $extension == "tsv") ? "\t":",";


$fr = fopen($datadir . $datafile,"r");
if($dooutput) { $fw = fopen($outdir . $filename_out,"w"); }
$counter = 0;
$oldestdate = 1000000000000;
$newestdate = 0;


// --------------------------
// ----- main line loop -----
// --------------------------

while(($rawbuffer = fgets($fr)) !== false) {

	$counter++;

	if($counter == 1) {			// first line is different
		
		if($dooutput) { fwrite($fw, $rawbuffer); }
		
		$buffer = str_getcsv(preg_replace("/\xEF\xBB\xBF/","",$rawbuffer),$delimiter);
		
		for($i = 0; $i < count($buffer); $i++) {
			
			if($col_date == $buffer[$i]) { $colloc_date = $i; }
			if(in_array($buffer[$i],$cols_text)) { $collocs_text[] = $i; }
			if(isset($cols_score[$buffer[$i]])) { $collocs_score[] = array($i,$cols_score[$buffer[$i]]); }
			if($limittocol != "") {
				if($buffer[$i] == $limittocol) { $limittocol = $i; }
			}
			//echo("limit " . $limittocol);
		}
		
		continue;
	}
	
	
	
	// separate line to work on
	$buffer = str_getcsv($rawbuffer,$delimiter,'"');
	
	
	// time filter
	$unixdate = strtotime($buffer[$colloc_date]);
	if($unixdate < strtotime($startdate) || $unixdate > strtotime($enddate)) { continue; }
	if($unixdate < $oldestdate) { $oldestdate = $unixdate; }
	if($unixdate > $newestdate) { $newestdate = $unixdate; }
	
	$date = date($dateformat,$unixdate);
	
	// count full lines
	if(!isset($datebins_full[$date])) { $datebins_full[$date] = 0; }
	$datebins_full[$date]++;
	
	// score filter
	foreach($collocs_score as $colloc_score) {
		if(intval($buffer[$colloc_score[0]]) < intval($colloc_score[1])) { continue 2; }
	}
	
	// content filter
	$content = "";
	foreach($collocs_text as $colloc_text) {
		$content .= $buffer[$colloc_text] . " ";
	}
	
	$found = false;
	$queries_found = array();			// we want to remeber found queries for later to reduce overhead
	foreach($queries as $query) {
		
		if(preg_match("/ and /i",$query)) {

			$andfound = true;
			$parts = explode(" and ", $query);

			foreach($parts as $part) {
				if(!preg_match("/".addslashes($part)."/i", $content)) { $andfound = false; }
			}
			
			if($andfound == true) {
				$found = true;
				$queries_found[$query] = true;
			}
			
		} else {
			
			if(preg_match("/".addslashes($query)."/i", $content)) {
				$found = true;
				$queries_found[$query] = true;
			}
		}
	}
	
	if($found == false) { continue; }
	
	// the line has passed conditions, write it
	if($dooutput) { fwrite($fw, $rawbuffer); }
	
	// count score of the surviving lines
	if(!isset($datebins_score[$date])) { $datebins_score[$date] = 0; }
	$datebins_score[$date] += intval($buffer[$collocs_score[0][0]]);
	
	//print_r($buffer);
	//echo $content;
	//print_r($queries); print_r($queries_found);

	foreach($queries as $query) {

		if(isset($queries_found[$query])) {

			if(!isset($datebins[$query][$date])) { $datebins[$query][$date] = array(); }
			if(!isset($wordlists[$query][$date])) { $wordlists[$query][$date] = array(); }
			//if(!isset($phrases[$query][$date])) { $phrases[$query][$date] = array(); }
			
			$datebins[$query][$date][] = true;
			
			if($getcontext || $dowordtree) {
				
				if($limittocol != "") {
					//echo "lmiting to text to: " . $limittocol;
					$content = $buffer[$limittocol];
				}
				
				$tmpcontent = strtolower($content);
				//$tmpcontent = preg_replace("/\s+/iu"," ", $tmpcontent);
				$tmpcontent = preg_replace("/[^a-z0-9\p{L}\p{N}#@:\/\.]+/iu"," ", $tmpcontent);			// \p{} is unicode syntax
				//$tmpcontent = preg_replace("/\./iu","%2E", $tmpcontent);
				//$tmpcontent = preg_replace( "/(?<![^a-zA-Z0-9])\.(?![^a-zA-Z0-9])/","%2E",$tmpcontent);
				$tmpcontent = preg_replace("/\s+/iu"," ", $tmpcontent);
				
				/*
				// clean up content
				if($getcontext) {
					//$content = preg_replace("/[\.\"\'\!\?\(\);,¿:]/", " ", $content); // currently also filters out URLs 
					//$content = preg_replace("/[\n\r]/", " ", $content);
					//$content = preg_replace("/\s+/", " ", $content);
					//$content = preg_replace("/\W+/", " ", $content);
					$content = preg_replace("/http.+?( |$)/i","", $content);
					$content = preg_replace("/[^a-z0-9\p{L}]+/iu"," ", $content);
					$content = trim($content);
					$content = strtolower($content);
				}
				*/
				
				if($contextwindow > 0) {

					$tmpcontext = preg_split("/".$query."/", $tmpcontent);
					
					$tmpcontent = "";
					for($i = 0; $i < count($tmpcontext); $i++) {
						
						$tmpwords = preg_split("/\s+/", trim($tmpcontext[$i]));
						$toget = (count($tmpwords) < $contextwindow) ? count($tmpwords):$contextwindow;
						
						if($i < count($tmpcontext) - 1) {
							$tmpcontent .= implode(" ",array_splice($tmpwords,count($tmpwords)-$toget,$toget)) . " ";
							
						}
						
						if($i > 0) {
							$tmpcontent .= implode(" ",array_splice($tmpwords,0,$toget)) . " ";
						}
						
					}
					
					//print($tmpcontent);
					
					$tmpafter = preg_split("/\s+/", $tmpcontext[1]);
					
					
					//print_r($tmpcontent);
					
				}
				
				$contextwords = preg_split("/\s+/", trim($tmpcontent));

				for($i = 0; $i < count($contextwords); $i++) {
					
					if(preg_match("/^https?:\//", $contextwords[$i])) {
						$contextwords[$i] = preg_replace("/\./iu","%2E", $contextwords[$i]);
					} else {
						$contextwords[$i] = preg_replace("/\./iu","", $contextwords[$i]);
						$contextwords[$i] = preg_replace("/:/iu","", $contextwords[$i]);
					}
					
					if($getcontext) {
					
						$word = $contextwords[$i];
					
						if(isset($stopwords[$word]) || strlen($word) < 3) { continue; }
	
						$word = preg_replace("/%2E/iu",".", $word);
	
						if(!isset($wordlists[$query][$date][$word])) {
							$wordlists[$query][$date][$word] = 0;
							if(!isset($wordlist_full[$word])) { $wordlist_full[$word] = array();}			// this is for tf*idf
							$wordlist_full[$word][$date] = 1;
						}
						
						$wordlists[$query][$date][$word]++;
					}
				}
				
			
					
				if($dowordtree) {
				
					$tmpcontent = trim(substr(implode(" ", $contextwords),strpos($tmpcontent, $query) + strlen($query)));
					
					//echo $tmpcontent . "\n";
					
					if(strlen($tmpcontent) > 0) {
						
						$contextwords = preg_split("/\s+/", $tmpcontent);
							
						$toget = (count($contextwords) > 20) ? 20:count($contextwords);
								
						if($toget > 0) {
							
							$tmptext = array_merge(array($query),array_splice($contextwords,0,$toget));

							$tmptext = preg_replace("/\//iu","%2F", $tmptext);
							
							$phrases[$query][] = $tmptext;						
						}
					}
				}
			}
		}
	}
}

fclose($fw);
fclose($fr);


// fill empty dates and create date list
$start = $oldestdate;
$stop = $newestdate;
$datelist = array();

foreach($queries as $query) {

	for($i = $start; $i < $stop + $seconds; $i += $seconds) {
		$date = date($dateformat,$i);
		if(!in_array($date, $datelist)) { $datelist[] = $date; }
		if(!isset($datebins[$query][$date])) {
			$datebins[$query][$date] = array();
		}
		if(!isset($datebins_full[$date])) {
			$datebins_full[$date] = 0;
		}
	}

	ksort($datebins[$query]);
}

// make overview calculations and write overview files

$counter = 0;	
$outlist = "query / " . $timescale;
$total = array();

foreach($datebins as $query => $bin) {
	
	if($counter == 0) {
		
		// write the timescale row
		foreach($datebins_full as $date => $val) {
			$outlist .= "," . $date;
		}
		$outlist .= ",total\n";
		
		// write full lines per timescale
		$totals["full"] = 0;
		$outlist .= "total";
		foreach($datebins_full as $date => $val) {
			$outlist .= "," . $val;
			$totals["full"] += $val;
		}
		$outlist .= ","  . $totals["full"] . "\n";
		$counter++;
	}
	
	// write query numbers per timescale
	$totals[$query] = 0;
	$outlist .= $query;		
	foreach($datebins_full as $date => $val) {
		$outlist .= "," . count($bin[$date]);
		$totals[$query] += count($bin[$date]);
	}
	$outlist .= "," . $totals[$query] . "\n";
	
	// write query percent per timescale
	$total = 0;
	$total_full = 0;
	$outlist .= $query . " (percent)";
	foreach($datebins_full as $date => $val) {
		$outlist .= "," . round((count($bin[$date]) / $val) * 100,2);
		$total += count($bin[$date]);
		$total_full += $val;
	}
	$outlist .= "," . round(($total / $total_full) * 100,2) . "\n";
}

if($dosummary) {
	/*
	if($getcontext) {
		echo "adding to output";
		
		print_r($wordlist_full);
		
		foreach($wordlists as $query => $wordlist) {

			ksort($wordlist);
			
			//print_r($wordlists); exit;
			
			$outlist .= "\nQuery: [" . preg_replace("/\|/"," OR ",$query) . "]\n";
			
			
		}
	}
	
	print_r($outlist);
	*/
	
	file_put_contents($outdir . $filename_summary, $outlist);
}

?>

	<div id="if_panel" class="if_structure">
		
		<div class="rowTab">
			<div class="sectionTab"><h2>Results</h2></div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab">
			<?php
			
			echo "full number of lines: " . $totals["full"];
			
			foreach($queries as $query) {
				if($query != "") {
					echo " / " . $query . ": " . $totals[$query];
				}
			}
				
			?>
			</div>
		</div>

		
		<div id="if_panel_downloads" class="if_structure">
			<div class="rowTab">
				<div class="sectionTab"><h3>Files written to disk</div>
			</div>
					
			<div class="rowTab">
				<div id="if_panel_downloads_data" class="fullTab">
					<?php
					
					if($dooutput) {
						
						echo 'Filtered file: <a href="./output/'.$filename_out.'" download>'.$filename_out.'</a>'; 
					}
						
					?>
				</div>
			</div>
			
			<div class="rowTab">
				<div id="if_panel_downloads_data" class="fullTab">
					<?php
					
					if($dosummary) {
						
						echo 'Filtered file: <a href="./output/'.$filename_summary.'" download>'.$filename_summary.'</a>'; 
					}
						
					?>
				</div>
			</div>
			
		</div>
		
				
		<div class="rowTab">
			<div class="sectionTab"><h3>Number of lines the queries appear in</h3></div>
		</div>
		
		<div id="if_panel_linegraph_freq" class="if_panel_linegraph"></div>
		
		<div class="rowTab">
			<div class="sectionTab"><h3>Percentage of lines the queries appear in</h3></div>
		</div>
		
		<div id="if_panel_linegraph_norm" class="if_panel_linegraph"></div>
		
		
		<div id="if_panel_context" class="if_structure">
		
			<div class="rowTab">
				<div class="sectionTab"><h3>Word context</h3></div>
			</div>
			
			<div class="rowTab">
				<div class="fullTab">tf: term frequency / df: document frequency / tf*idf: term frequency * inverse document frequency</div>
			</div>
			
			<div class="rowTab">
				<div id="if_panel_context_data">
<?php

if($getcontext) {
	
	echo '<table class="wordlist"><tr class="wordlist">';
	
	foreach($wordlists as $query => $wordlist) {

		$filename_rf = "rf_" . md5($query) . ".csv";

		// prepare structure for creating rankflow CSV
		$csv_rf = array();
		for($i = 0; $i <= $contextcutoff; $i++) {
			$csv_rf[] = array();
			for($j = 0; $j < count($datelist); $j++) {
				$csv_rf[count($csv_rf)-1][$j] = ","; 
			}
		}
		ksort($wordlist);

		echo '<td class="wordlist_title" colspan="'.count($datelist).'">Query: [<strong>' . preg_replace("/\|/"," OR ",$query) . '</strong>]</td></tr><tr class="wordlist">';
		
		
		
		
		$counter_rf = 0;
		foreach($datelist as $date) {
			
			$csv_rf[0][$counter_rf] = $date . ",count";
			$counter_rf++;
			
			echo '<th class="wordlist">'.$date.'</th>';
		}

		echo '</tr><tr class="wordlist">';
		
		foreach($datelist as $date) {
			
			//echo "\n" . $date . " = " . count($wordlist[$date]) . "\n";
			
			$collocs_tmp = array();
			if(count($wordlist[$date]) > 0) { 
				
				foreach($collocs_score as $colloc_score) {
					$collocs_tmp[] = implode("|", $colloc_score);
				}
				
				echo '<td class="wordlist"><a href="getcomments.php?date='.$date.'&timescale='.$timescale.'&datafile='.$datafile.'&query='.$query.'&colloc_date='.$colloc_date.'&collocs_text='.implode(",",$collocs_text).'&collocs_score='.implode(",",$collocs_tmp).'" target="_blank">lines</a></td>';
			} else {
				echo '<td class="wordlist"></td>';
			}
		}

		echo '</tr><tr class="wordlist">';
		
		$counter_rf = -1;
		foreach($datelist as $date) {
			
			$counter_rf++;
			
			if(count($wordlist[$date]) == 0) { 
				echo '<td class="wordlist"></td>';
				continue;
			}

			$list = $wordlist[$date];
			
			arsort($list);
			
			echo '<td class="wordlist"><strong>frequency (tf / df):</strong><br />';

			$counter = 0;
			foreach($list as $word => $freq) {
				if($counter < $contextcutoff) {
					echo $word . "&nbsp;(" . $freq . "&nbsp;/&nbsp;" . count($wordlist_full[$word]) . ")<br />";
					$csv_rf[$counter+1][$counter_rf] = $word.",".$freq;
				}
				$counter++;
			}
			
			
			
			echo '</td>';
		}
		
		echo '</tr><tr class="wordlist">';
		
		foreach($datelist as $date) {
			
			if(count($wordlist[$date]) == 0) { 
				echo '<td class="wordlist"></td>';
				continue;	
			}

			$speclist = $wordlist[$date];
			
			// calculate specificity tf*idf = tf * log(doccount / docwithterm)
			foreach($speclist as $word => $freq) {
				$speclist[$word] = round($freq * log(count($datelist) / count($wordlist_full[$word]),2),2);
			}

			arsort($speclist);

			echo '<td class="wordlist"><strong>specificity (tf*idf / tf / df):</strong><br />';
			$counter = 0;
			foreach($speclist as $word => $freq) {
				if($counter == $contextcutoff) { break; }
				$counter++;
				echo $word . "&nbsp;(" . $speclist[$word] . "&nbsp;/&nbsp;" . $wordlist[$date][$word] . "&nbsp;/&nbsp;" . count($wordlist_full[$word]) . ")<br />";
			}

			echo "</td>";
		}

		echo '</tr>';
		
		$fp = fopen($outdir . $filename_rf, 'w');

		foreach ($csv_rf as $fields) {
			fputs($fp, implode($fields, ',')."\n");
		}
		
		echo '<tr class="wordlist"><td class="wordlist" colspan="'.count($datelist).'">RankFlow compatible CSV file: <a href="./output/'.$filename_rf.'" download>'.$filename_rf.'</a></td></tr><tr class="wordlist">';
	}
	
	echo '</table>';

}

?>
				</div>
			</div>
		</div>
		
		
		<div id="if_panel_wordtree" class="if_structure">
		
			<div class="rowTab">
				<div class="sectionTab"><h3>Word Tree</h3></div>
			</div>
			
			<div class="rowTab">
				<div id="if_panel_wordtree_data">
					<svg width="1240" height="<?php echo count($phrases[$queries[0]]) * 12 ?>"></svg>
				</div>
			</div>
		</div>
	</div>
</div>


<?php


if($dowordtree && count($queries) > 0) {
	
	$lines = array();
	$tree = array();

	//print_r($phrases);

	foreach($queries as $query) {
		
		foreach($phrases[$query] as $phrase) {
			 
			if(!isset($lines[$phrase[0]])) { $lines[$phrase[0]] = 0; }
			$lines[$phrase[0]]++;
			
			if(isset($phrase[1])) {
				
				if(!isset($lines[$phrase[0] . "." . $phrase[1]])) { $lines[$phrase[0] . "." . $phrase[1]] = 0; }
				$lines[$phrase[0] . "." . $phrase[1]]++;
				
				if(isset($phrase[2])) {
					
					if(!isset($lines[$phrase[0] . "." . $phrase[1] . "." . $phrase[2]])) { $lines[$phrase[0] . "." . $phrase[1] . "." . $phrase[2]]  = 0; }
					$lines[$phrase[0] . "." . $phrase[1] . "." . $phrase[2]]++;
					
					if(isset($phrase[3])) {
						
						if(!isset($lines[$phrase[0] . "." . $phrase[1] . "." . $phrase[2] . "." . implode(" ", array_slice($phrase, 3))])) { 
							$lines[$phrase[0] . "." . $phrase[1] . "." . $phrase[2] . "." . implode(" ", array_slice($phrase, 3))] == 0;
						}
						$lines[$phrase[0] . "." . $phrase[1] . "." . $phrase[2] . "." . implode(" ", array_slice($phrase, 3))]++;
					}
					
				}
			}
		}
	}
	
	//ksort($lines);
	//print_r($lines);
	$replacements = array();
	
	
	foreach($lines as $line => $freq) {
		
		//echo $line . " " . $freq . "\n";
		
		$toreplace = "";
		foreach($lines as $line2 => $freq2) {
			
			if(preg_match("/^".preg_quote($line)."\./", $line2)) {
										
				if($freq == $freq2)  {
					
					//echo "ln2:" . $line2 . " " . $freq2 . "\n";
					
					unset($lines[$line2]);
					$substr = substr($line2,strlen($line));
					if(strlen($substr) > strlen($toreplace)) { $toreplace = $substr; }
				}
			}
		}
		
		//echo "tr: " . $toreplace . "\n\n"; 
		
		if($toreplace != "") {
			$replacements[$line.$toreplace] = $line.preg_replace("/\./", " ", $toreplace);
			$lines[$replacements[$line.$toreplace]] = $freq;
			unset($lines[$line]);
		}			
	}
	
	//print_r($replacements);
	
	// apply the replacements
	$newlines = array();
	foreach($lines as $line => $freq) {
		foreach($replacements as $from => $to) {
			if(preg_match("/^".preg_quote($from)."/", $line)) {
				$line = preg_replace("/^".preg_quote($from)."/", $to, $line);
			}
		}
				
		$newlines[$line] = $freq;
	}

	//print_r($newlines);

	$csv = "id,value\n";

	foreach($newlines as $id => $value) {
		$csv .= $id . "," . $value . "\n";
	}
	
	$treefn = "lines_" . md5($csv) . ".csv";
	
	file_put_contents($outdir . $treefn,substr($csv,0,strlen($csv)-1));	
}

?>

<script>

if(_params.dowordtree == "false" || _params.query == "") {
	throw "no d3 at this time";
}

function revchars(_string) {
	console.log(_string)
	_string = _string.replace(/%2F/gi,"/")
	_string = _string.replace(/%2E/gi,".")
	return _string
}

var _nodesep = 200;

var svg = d3.select("svg"),
	width = +svg.attr("width"),
	height = +svg.attr("height"),
	g = svg.append("g").attr("transform", "translate(40,0)");

var tree = d3.cluster()
	.size([height, width - 160]);

var stratify = d3.stratify()
	.parentId(function(d) { return d.id.substring(0, d.id.lastIndexOf(".")); });

d3.csv("./output/<?php echo $treefn; ?>", function(error, data) {
	if (error) throw error;
  

	// do some precalculations
	var _smallest = 10000000000;
	var _biggest = 0;

	for(var _el in data) {
		data[_el].value = parseInt(data[_el].value);
		if(data[_el].value > _biggest) { _biggest = data[_el].value; }
		if(data[_el].value < _smallest) { _smallest = data[_el].value; }
	}

	for(var _el in data) {
		if(_el != "columns") {
			data[_el].size =  10 + Math.round((data[_el].value - _smallest) / _biggest * 10);
		}
	}

	// create a word tree, based on the standard d3 tree implementation
	var root = stratify(data)
		.sort(function(a, b) { return (a.height - b.height) || a.id.localeCompare(b.id); });

	tree(root);

	var link = g.selectAll(".link")
		.data(root.descendants().slice(1))
		.enter().append("path")
		.attr("class", "link")
		.attr("d", function(d) {
			return "M" + (d.depth * _nodesep) + "," + d.x
			+ "C" + ((d.parent.depth * _nodesep) + (_nodesep / 2)) + "," + d.x
			+ " " + ((d.parent.depth * _nodesep) + (_nodesep / 2)) + "," + d.parent.x
			+ " " + (d.parent.depth * _nodesep) + "," + d.parent.x;
		});

	var node = g.selectAll(".node")
		.data(root.descendants())
		.enter().append("g")
		.attr("class", function(d) { return "node" + (d.children ? " node--internal" : " node--leaf"); })
		.attr("transform", function(d) { return "translate(" + (d.depth * _nodesep) + "," + d.x + ")"; })

	//node.append("circle")
		//.attr("r", 5);

	node.append("text")
		.attr("dy", function(d) { return (d.data.size / 3); })			// text y anchor
		.attr("x", function(d) { return (d.depth > 0) ? 5 : -5; })
		.style("text-anchor", function(d) { return (typeof(d.children) == "object" && d.depth > 0) ? "middle":"start"; })
		.style("font-size", function(d) { return d.data.size + "px"; })
		.text(function(d) { return revchars(d.id.substring(d.id.lastIndexOf(".") + 1) + " (" + d.data.value + ")"); });
});



</script>


<script type="text/javascript">

	// todo: add full counts and per interval averages

    var data = new google.visualization.DataTable();

    data.addColumn('string', 'Date');

	<?php

	if($showfull) {
		echo "data.addColumn('number','full comments');";
	}
	if($showscore) {
		echo "data.addColumn('number','".$scorecalc." of score');";
	}
	foreach($queries as $query) {
		echo "data.addColumn('number', '".$query."');";
	}


	foreach($datebins[$queries[0]] as $key => $value) {
		echo "data.addRow(['".$key."'";
		if($showfull) {
			echo ",".$datebins_full[$key];
		}
		if($showscore) {
			switch($scorecalc) {
				case "sum":
					$tmpval = $datebins_score[$key];
					break;
				case "avg":
					$tmpval = ($datebins_full[$key] > 0) ? $datebins_score[$key] / $datebins_full[$key]:0;
					break;
			}
			echo ",".$tmpval;
		}
		foreach($queries as $query) {
			echo ",".count($datebins[$query][$key]);
		}
		echo "]);";
	}

	?>

	var chart = new google.visualization.LineChart(document.getElementById('if_panel_linegraph_freq'));
	chart.draw(data, {width:1220, height:360, fontSize:9, hAxis:{slantedTextAngle:90, slantedText:true}, vAxis:{logScale:false}, lineWidth:1, chartArea:{left:50,top:10,width:1080,height:300}});


	var data2 = new google.visualization.DataTable();

    data2.addColumn('string', 'Date');

	<?php

	foreach($queries as $query) {
		echo "data2.addColumn('number', '".$query."');";
	}

	foreach($datebins[$queries[0]] as $key => $value) {
		echo "data2.addRow(['".$key."'";
		foreach($queries as $query) {
			$val = (count($datebins[$query][$key]) == 0) ? 0:round((count($datebins[$query][$key]) / $datebins_full[$key] * 100),2);
			//$val = $datebins_full[$key];
			echo ",".$val;
		}
		echo "]);";
	}

	?>

	var chart2 = new google.visualization.AreaChart(document.getElementById('if_panel_linegraph_norm'));
	chart2.draw(data2, {width:1220, height:210, fontSize:9, hAxis:{slantedTextAngle:90, slantedText:true}, lineWidth:1, chartArea:{left:50,top:10,width:1080,height:150}});

</script>

</body>
</html>