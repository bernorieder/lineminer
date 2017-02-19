<?php

// tool description
// Some stop word lists: http://www.semantikoz.com/blog/free-stop-word-lists-in-23-languages/
// and
// tab outputs
// language cleaning


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

	<script type="text/javascript" src="./common/jquery-3.1.1.min.js"></script>
	<script type="text/javascript" src="./common/functions.js"></script>

	<script type="text/javascript" src="https://www.google.com/jsapi"></script>

	<script type="text/javascript">

	google.load("visualization", "1", {packages:["corechart"]});

	</script>
	
	<link rel="stylesheet" type="text/css" href="main.css">
</head>

<body onload="onloadTodo();">

<div id="fullpage">

	<div id="if_header" class="if_structure">
		<div class="sectionTab">
			<h1>LineMiner</h1>
		</div>
	</div>
	
	
	<div id="if_description" class="if_structure">
		<div class="rowTab">
			<div class="fullTab">
				This tool allows for quick text searching through CSV/TSV files where each line is a timestamped unit of text.
				Source code <a href="https://github.com/bernorieder/lineminer" target="_blank">available on github.</a>	
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
				This tool works on files put into a data directory on the machine it runs. Since it is designed to run on very big files,
				there is no upload function - talk to your administrator for how to add files.
			</div>
		</div>
		
		<div class="rowTab">
			<div class="leftTab">Files in data directory:</div>
			
			<div class="rightTab">
				<select name="datafile" onchange="loadFile(this.value)">
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
			<div class="comTab" id="if_filedetected_text"></div>
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
				<input type="radio" name="timescale" value="day"  /> day
				<input type="radio" name="timescale" value="week"  /> week
			</div>
		</div>
		
		
		<div class="rowTab">
			<div class="sectionTab"><h3>Analysis options</h3></div>
		</div>

		<div class="rowTab">
			<div class="fullTab"><input type="checkbox" name="showfull" /> show full count on top linegraph </div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab"><input type="checkbox" name="getcontext" /> show word context</div>
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

// if there's no file, exit
if(!isset($_GET["datafile"])) {
	echo '</body></html>';
	exit;
}  

// ----- base variables -----
$datebins = array();
$datebins_full = array();
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


$col_date = urldecode($_GET["col_date"]);
$cols_text = explode(",", urldecode($_GET["cols_text"]));
$cols_score_tmp = explode(",", urldecode($_GET["cols_score"]));
for($i = 0; $i < count($cols_score_tmp); $i++) {
	$tmp = explode("|", $cols_score_tmp[$i]);
	$cols_score[$tmp[0]] = $tmp[1];
}

$datafile = urldecode($_GET["datafile"]);

$timescale = ($_GET["timescale"] == "") ? $_GET["timescale"]:"week";

$language = (isset($_GET["language"])) ? urldecode($_GET["language"]):"english";
$stopwords = getstopwords($language);

$showfull = ($_GET["showfull"] == "true") ? true:false;
$getcontext = ($_GET["getcontext"] == "true") ? true:false;
$dooutput = ($_GET["dooutput"] == "true") ? true:false;
$dosummary = ($_GET["dosummary"] == "true") ? true:false;

$_GET["startdate"] = ($_GET["startdate"] != "") ? $_GET["startdate"]:"1971-01-01";
if(preg_match("/ [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_GET["startdate"])) {
	$startdate = $_GET["startdate"];
} else if(preg_match("/ [0-9]{2}:[0-9]{2}$/", $_GET["startdate"])) {
	$startdate = $_GET["startdate"] . ":00";
} else {
	$startdate .= $_GET["startdate"] . " 00:00:00";
}

$_GET["enddate"] = ($_GET["enddate"]  != "") ? $_GET["enddate"]:date("Y-m-d");
if(preg_match("/ [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_GET["enddate"])) {
	$enddate = $_GET["enddate"];
} else if(preg_match("/ [0-9]{2}:[0-9]{2}$/", $_GET["enddate"])) {
	$enddate .= $_GET["enddate"] . ":59";
} else {
	$enddate .= $_GET["enddate"] . " 23:59:59";
}

//echo $startdate . " " . $enddate; exit;


// ----- make calculations for timescale -----
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
}


$filename_out = "filtered_" . md5($query) . "_" . $datafile;
$filename_summary = "summary_" . md5($query) . ".csv";

$extension = substr($filename, strlen($filename) - 3);
$delimiter = ($extension == "tab" || $extension == "tsv") ? "\t":",";


$fr = fopen($datadir . $datafile,"r");
if($dooutput) { $fw = fopen($outdir . $filename_out,"w"); }
$counter = 0;
$oldestdate = strtotime($enddate);
$newestdate = strtotime($startdate);

//exit;

// ----- main line loop -----
while(($rawbuffer = fgets($fr)) !== false) {

	if($counter == 0) {			// first line is different
		
		if($dooutput) { fwrite($fw, $rawbuffer); }
		
		$buffer = str_getcsv(preg_replace("/\xEF\xBB\xBF/","",$rawbuffer),$delimiter);
		
		for($i = 0; $i < count($buffer); $i++) {
			
			if($col_date == $buffer[$i]) { $colloc_date = $i; }
			if(in_array($buffer[$i],$cols_text)) { $collocs_text[] = $i; }
			if(isset($cols_score[$buffer[$i]])) { $collocs_score[] = array($i,$cols_score[$buffer[$i]]); }
		}
		
		$counter++;
		
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
	if(!isset($datebins_full[$date])) {
		$datebins_full[$date] = 0;
	}
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
	
	// the line has passed conitions, write it
	if($dooutput) { fwrite($fw, $rawbuffer); }
	
	//print_r($buffer);
	//echo $content;
	//print_r($queries); print_r($queries_found); exit;

	foreach($queries as $query) {

		if(isset($queries_found[$query])) {

			if(!isset($datebins[$query][$date])) { $datebins[$query][$date] = array(); }
			if(!isset($wordlists[$query][$date])) { $wordlists[$query][$date] = array(); }
			if(!isset($phrases[$query][$date])) { $phrases[$query][$date] = array(); }
			
			$datebins[$query][$date][] = true;
			
			if($getcontext) {

				$tmpcontent = strtolower($content);
				$tmpcontent = preg_replace("/[^a-z0-9\p{L}\p{N}\/]+/iu"," ", $tmpcontent);			// \p{} is unicode syntax
		
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

				$contextwords = preg_split('/\s+/', $tmpcontent);

				foreach($contextwords as $word) {
					
					if(isset($stopwords[$word]) || strlen($word) < 3) { continue; }

					if(!isset($wordlists[$query][$date][$word])) {
						$wordlists[$query][$date][$word] = 0;
						if(!isset($wordlist_full[$word])) { $wordlist_full[$word] = 0;}			// this is for tf*idf
						$wordlist_full[$word]++;
					}
					$wordlists[$query][$date][$word]++;
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

//echo $start . " " . $stop;
foreach($queries as $query) {

	for($i = $start; $i < $stop; $i += $seconds) {
		$date = date($dateformat,$i);
		$datelist[] = $date;
		if(!isset($datebins[$query][$date])) {
			$datebins[$query][$date] = array();
		}
		if(!isset($datebins_full[$date])) {
			$datebins_full[$date] = 0;
		}
	}

	ksort($datebins[$query]);
}


if($dosummary) {

	$counter = 0;	
	$outlist = "query / " . $timescale;
	
	foreach($datebins as $query => $bin) {
		
		if($counter == 0) {
			
			// write the timescale row
			foreach($datebins_full as $date => $val) {
				$outlist .= "," . $date;
			}
			$outlist .= ",total\n";
			
			// write full lines per timescale
			$total = 0;
			$outlist .= "total";
			foreach($datebins_full as $date => $val) {
				$outlist .= "," . $val;
				$total += $val;
			}
			$outlist .= ","  . $total . "\n";
			$counter++;
		}
		
		// write query numbers per timescale
		$total = 0;
		$outlist .= $query;		
		foreach($datebins_full as $date => $val) {
			$outlist .= "," . count($bin[$date]);
			$total += count($bin[$date]);
		}
		$outlist .= "," . $total . "\n";
		
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
	
	file_put_contents($outdir . $filename_summary, $outlist);
}



?>

	<div id="if_panel" class="if_structure">
		
		<div class="rowTab">
			<div class="sectionTab"><h2>Results</h2></div>
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
				<div id="if_panel_context_data">
<?php

if($getcontext) {
	
	echo '<table class="wordlist"><tr class="wordlist">';
	
	foreach($wordlists as $query => $wordlist) {

		ksort($wordlist);

		echo '<td class="wordlist_title" colspan="'.count($datelist).'"  <strong>' . preg_replace("/\|/"," OR ",$query) . '</strong></td></tr><tr class="wordlist">';

		foreach($datelist as $date) {
			
			echo '<th class="wordlist">'.$date.'</th>';
		}

		echo '</tr><tr class="wordlist">';
		
		
		foreach($datelist as $date) {
			if($filetype == "facebook" || $filetype == "facebook_topcomments") { $moreoptions = "&minfblikes=" . $minfblikes; }
			if($filetype == "youtube") { $moreoptions = "&minytlikes=" . $minytlikes; }
			if($filetype == "twitter") { $moreoptions = "&minfavs=" . $minfavs . "&minretweets=" . $minretweets; }
			echo '<td class="wordlist"><a href="getcomments.php?date='.$date.'&timescale='.$timescale.'&filename='.$filename.'&query='.$query.'&filetype='.$filetype.$moreoptions.'" target="_blank">lines</a></td>';
		}

		echo '</tr><tr class="wordlist">';
		
		//foreach($wordlist as $date => $list) {

		foreach($datelist as $date) {
			
			if(!isset($wordlist[$date])) { 
				echo '<td class="wordlist"></td>';
				continue;
			}

			$list = $wordlist[$date];
			
			arsort($list);

			echo '<td class="wordlist"><em>frequency:</em><br />';
			$speclist = array();

			$counter = 0;
			foreach($list as $word => $freq) {
				if($counter <= 15) {
					echo $word . "&nbsp;(" . $freq . "/" . $wordlist_full[$word] . ")<br />";	
				}
				$counter++;
				if($wordlist_full[$word] > 2) {
					$speclist[$word] = $freq / $wordlist_full[$word];
				}
			}

			echo '</td>';
		}
		
		echo '</tr><tr class="wordlist">';
		
		foreach($datelist as $date) {
			
			if(!isset($wordlist[$date])) { 
				echo '<td class="wordlist"></td>';
				continue;	
			}

			$speclist = $wordlist[$date];
			
			// calculate specificity
			foreach($speclist as $word => $freq) {
				$speclist[$word] = round($freq * log(count($datelist) / $wordlist_full[$word],2),2);  // tf*idf = tf * log(doccount / docwithterm)
			}

			arsort($speclist);

			echo '<td class="wordlist"><em>tf-idf:</em><br />';
			$counter = 0;
			foreach($speclist as $word => $freq) {
				if($counter == 15) {break;}
				$counter++;
				echo $word . "&nbsp;(" . $speclist[$word] . "," . $wordlist[$date][$word] . "," . $wordlist_full[$word] . ")<br />";
			}

			echo "</td>";
		}

		echo '</tr>';
	}
	
	echo '</table>';
}

?>
				</div>
			</div>
		</div>
	</div>
</div>


<script type="text/javascript">

	// todo: add full counts and per interval averages

    var data = new google.visualization.DataTable();

    data.addColumn('string', 'Date');

	<?php

	if($showfull) {
		echo "data.addColumn('number','full comments');";
	}
	foreach($queries as $query) {
		echo "data.addColumn('number', '".$query."');";
	}


	foreach($datebins[$queries[0]] as $key => $value) {
		echo "data.addRow(['".$key."'";
		if($showfull) {
			echo ",".$datebins_full[$key];
		}
		foreach($queries as $query) {
			echo ",".count($datebins[$query][$key]);
		}
		echo "]);";
	}

	?>

	var chart = new google.visualization.LineChart(document.getElementById('if_panel_linegraph_freq'));
	chart.draw(data, {width:1220, height:360, fontSize:9, hAxis:{slantedTextAngle:90, slantedText:true},  lineWidth:1, chartArea:{left:50,top:10,width:1080,height:300}});


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
	chart2.draw(data2, {width:1220, height:210, fontSize:9, hAxis:{slantedTextAngle:90, slantedText:true}, lineWidth:1, chartArea:{left:50,top:10,width:1000,height:150}});

</script>

</body>
</html>