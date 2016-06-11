<?php

// tool description
// Some stop word lists: http://www.semantikoz.com/blog/free-stop-word-lists-in-23-languages/
// and
// tab outputs
// language cleaning


// ----- link external files -----
include "config.php";
include "functions.php";


// ----- get URL parameters and do some parsing -----
$query = (isset($_GET["query"])) ? $_GET["query"]:"barcelona";
$language = (isset($_GET["language"])) ? $_GET["language"]:"english";
$showfull = (isset($_GET["showfull"]) == "on") ? true:false;
$getcontext = (isset($_GET["getcontext"]) == "on") ? true:false;
$dooutput = (isset($_GET["dooutput"]) == "on") ? true:false;
$incfbpost = (isset($_GET["incfbpost"]) == "on") ? true:false;
$minfblikes = (isset($_GET["minfblikes"])) ? $_GET["minfblikes"]:0;
$minytlikes = (isset($_GET["minytlikes"])) ? $_GET["minytlikes"]:0;
$minretweets = (isset($_GET["minretweets"])) ? $_GET["minretweets"]:0;
$minfavs = (isset($_GET["minfavs"])) ? $_GET["minfavs"]:0;
$timescale = (isset($_GET["timescale"])) ? $_GET["timescale"]:"week";



$_GET["startdate"] = (isset($_GET["startdate"])) ? $_GET["startdate"]:"2014-01-01";
if(preg_match("/ [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_GET["startdate"])) {
	$startdate = $_GET["startdate"];
} else if(preg_match("/ [0-9]{2}:[0-9]{2}$/", $_GET["startdate"])) {
	$startdate = $_GET["startdate"] . ":00";
} else {
	$startdate .= $_GET["startdate"] . " 00:00:00";
}

$_GET["enddate"] = (isset($_GET["enddate"])) ? $_GET["enddate"]:"2015-01-01";
if(preg_match("/ [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_GET["enddate"])) {
	$enddate = $_GET["enddate"];
} else if(preg_match("/ [0-9]{2}:[0-9]{2}$/", $_GET["enddate"])) {
	$enddate .= $_GET["enddate"] . ":59";
} else {
	$enddate .= $_GET["enddate"] . " 23:59:59";
}

//echo $startdate . "|" . $enddate;


// ----- make calculations for interval -----
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
	<title>lineminer</title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
	<script type="text/javascript" src="functions.js"></script>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>

	<script type="text/javascript">

	google.load("visualization", "1", {packages:["corechart"]});

	</script>

	<link rel="stylesheet" type="text/css" href="main.css">
</head>

<body onload="onloadTodo();">


<div id="if_header" class="if_structure">
	<div class="rowTab">
		<div class="fullTab">
			<a href="<?php echo $httproot; ?>"><h1>lineminer</h1></a>
		</div>
	</div>
</div>


<div id="if_description" class="if_structure">
	<div class="rowTab">
		<div class="fullTab">
			BlaBla, explanation, FAQ, links, credits. Source code <a href="https://github.com/bernorieder/lineminer" target="_blank">available on github.</a>	
		</div>
	</div>
</div>



<div id="if_file" class="if_structure">
	<form action="index.php" method="get">
	<input type="hidden" name="filetype" value="<?php echo $_GET["filetype"]; ?>" />
	
	<div class="rowTab">
		<div class="leftTab">Choose a file:</div>
		
		<div class="rightTab">
			<select name="fileselect" onchange="loadFile(this.value)">
				<option value="none">select</option>
				<?php
				foreach($filenames as $filename) {
					$selectmarker = ($filename == $_GET["file"]) ? "selected":"";
					echo '<option value="'.$filename.'" '.$selectmarker.'>'.$filename.'</option>';
				}
				?>
			</select>
		</div>
	</div>
</div>

<div id="if_filedetected" class="if_structure">
	<div class="rowTab">
		<div class="comTab" id="if_filedetected_text"></div>
	</div>
</div>

<div id="if_parameters" class="if_structure">
	<div id="if_parameters_common" class="if_structure">
		<div class="rowTab">
			<div class="headTab">Common parameters</div>
		</div>
	
		<div class="rowTab">
			<div class="leftTab">Search query:</div>
			
			<div class="rightTab">
				<input type="text" name="query" value="<?php echo $query; ?>" /> (use [all] for no query, OR and AND, separate multiple queries with comma)
			</div>
		</div>
		
		<div class="rowTab">
			<div class="leftTab">File language:</div>
			
			<div class="rightTab">
				<select name="language">
					<?php
					foreach($stopfiles as $lang => $file) {
						if($lang == $language) {
							echo '<option value="'.$lang.'" selected>'.$lang.'</option>';
						} else {
							echo '<option value="'.$lang.'">'.$lang.'</option>';
						}
					}
					?>
				</select>
			</div>
		</div>
		
		<div class="rowTab">
			<div class="leftTab">Startdate:</div>
			
			<div class="rightTab">
				<input type="text" name="startdate" value="<?php echo $_GET["startdate"]; ?>" /> YYYY-MM-DD or YYYY-MM-DD HH:MM
			</div>
		</div>
		
		<div class="rowTab">
			<div class="leftTab">Enddate:</div>
			
			<div class="rightTab">
				<input type="text" name="enddate" value="<?php echo $_GET["enddate"]; ?>" /> YYYY-MM-DD or YYYY-MM-DD HH:MM
			</div>
		</div>
		
		<div class="rowTab">
			<div class="leftTab">Time interval:</div>
			
			<div class="rightTab">
				<input type="radio" name="timescale" value="minute" <?php if($_GET["timescale"] == "minute") { echo 'checked="checked"'; } ?> /> minute
				<input type="radio" name="timescale" value="hour" <?php if($_GET["timescale"] == "hour") { echo 'checked="checked"'; } ?> /> hour
				<input type="radio" name="timescale" value="day" <?php if($_GET["timescale"] == "day") { echo 'checked="checked"'; } ?> /> day
				<input type="radio" name="timescale" value="week" <?php if($_GET["timescale"] != "minute" && $_GET["timescale"] != "hour" && $_GET["timescale"] != "day") { echo 'checked="checked"'; } ?> /> week
			</div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab"><input type="checkbox" name="showfull" <?php if($showfull != false) {  echo 'checked="checked"'; } ?> /> show full count on top linegraph </div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab"><input type="checkbox" name="getcontext" <?php if($getcontext != false) {  echo 'checked="checked"'; } ?> /> show word context</div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab"><input type="checkbox" name="dooutput" <?php if($dooutput != false) {  echo 'checked="checked"'; } ?> /> write filtered lines to new file (use wisely)</div>
		</div>
	</div>
	
	<div id="if_parameters_facebook" class="if_structure">
		<div class="rowTab">
			<div class="headTab">Facebook parameters</div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab">
				<input type="checkbox" name="incfbpost" <?php if($incfbpost != false) {  echo 'checked="checked"'; } ?> /> include lines where post text matches query</div>
			</div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab">
				filter comments below <input type="text" name="minfblikes" style="width:20px;" value="<?php echo $minfblikes; ?>" /> comment likes
			</div>
		</div>
	</div>
	
	<div id="if_parameters_twitter" class="if_structure">
		<div class="rowTab">
			<div class="headTab">Twitter parameters</div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab">
				filter tweets below
				<input type="text" name="minretweets" style="width:20px;" value="<?php echo $minretweets; ?>" /> retweets and
				<input type="text" name="minfavs" style="width:20px;" value="<?php echo $minfavs; ?>" /> favorites 
			</div>
		</div>
	</div>
	
	<div id="if_parameters_youtube" class="if_structure">
		<div class="rowTab">
			<div class="headTab">YouTube parameters</div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab">
				filter comments below <input type="text" name="minytlikes" style="width:20px;" value="<?php echo $minytlikes; ?>" /> comment likes
			</div>
		</div>
	</div>
	
	<div id="if_parameters_submit" class="if_structure">
		<div class="rowTab">
			<div class="fullTab">
				<input type="submit" />
			</div>
		</div>		
	</div>
	</form>
</div>

<?php

// check query
if(isset($_GET["query"])) {
	$query = preg_replace("/ OR /","|",$_GET["query"]);
	$queries = explode(",",$query);
} else {
	exit;
}

// get stopword list
$stopwords = getstopwords($language);

$filename = $datadir . "/" . $_GET["fileselect"];
$filename_out = $outdir . "/filtered_" . $query . "_" . $_GET["fileselect"];
$filetype = $_GET["filetype"];
$separator = (preg_match("/\.tab/",$_GET["fileselect"])) ? "\t":",";

$datebins = array();
$datebins_full = array();
$wordlists = array();
$wordlist_full = array();
$phrases = array();


$fr = fopen($filename,'r');
if($dooutput) { $fw = fopen($filename_out,'w'); }
$counter = 0;

// ----- main file loop -----
while(($rawbuffer = fgets($fr)) !== false) {

	if($counter == 0) {			// jump first line
		if($dooutput) { fwrite($fw, $rawbuffer); }
		$counter++;
		continue;
	}

	$buffer = str_getcsv($rawbuffer,$separator,'"');

	// select appropriate colums
	if($filetype == "facebook") {
		$unixdate = strtotime($buffer[4]);
		$content = $buffer[8];
		$postcontent = $buffer[3];
	} else if($filetype == "youtube") {
		$unixdate = strtotime($buffer[3]);
		$content = $buffer[5];	
	} else if($filetype == "twitter") {
		$unixdate = strtotime($buffer[2]);
		$content = $buffer[4];	
	}
	
	// time filter
	if($unixdate < strtotime($startdate) || $unixdate > strtotime($enddate)) { continue; }
	
	// filter lines below specified threshold
	if($filetype == "facebook") {
		if($buffer[10] < $minfblikes) { continue; }
	} else if($filetype == "youtube") {
		if($buffer[2] < $minytlikes) { continue; }
	} else if($filetype == "twitter") {
		if($buffer[10] < $minretweets || $buffer[11] < $minfavs) { continue; }
	}
	
	// stream filtered lines to file
	if($dooutput) { fwrite($fw, $rawbuffer); }
	
	// get out significant objects
	//preg_match("/http(.*?) /",$content, $matches);
	//print_r($matches[0]);
	
	//echo "<br />";
	
	//preg_match("/ #(.*?) /",$content, $matches);
	//	print_r($matches[0]);
	

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
	//echo $content;

	$date = date($dateformat,$unixdate);
	if(!isset($datebins_full[$date])) {
		$datebins_full[$date] = 0;
	}

	$datebins_full[$date]++;

	foreach($queries as $query) {

		if(!isset($datebins[$query])) { $datebins[$query] = array(); }
		if(!isset($wordlists[$query])) { $wordlists[$query] = array(); }
		if(!isset($phrases[$query])) { $phrases[$query] = array(); }

		$incpost = ($incfbpost == true && preg_match("/".$query."/i",$postcontent)) ? true:false;

		if(preg_match("/".$query."/i",$content) || $query == "all" || $incpost == true) {

			if(!isset($datebins[$query][$date])) { $datebins[$query][$date] = array(); }
			if(!isset($wordlists[$query][$date])) { $wordlists[$query][$date] = array(); }
			if(!isset($phrases[$query][$date])) { $phrases[$query][$date] = array(); }

			$datebins[$query][$date][] = ($getcomments) ? $buffer:"x";

			if($getcontext) {

				$contextwords = explode(" ", $content);
				
				
				/* code for previous sliding window
				$words = explode(" ", $content);
				$contextwords = array();

				for($i = 0; $i < count($words); $i++) {
					//echo $words[$i];
					if(preg_match("/".$query."/i",$words[$i])) {
						$lengthhead = ($i - $windowsize < 0) ? $i:$windowsize;
						$lengthtail = ($i + $windowsize > count($words)) ? count($words) - $i:$windowsize;
						$headarray = array_slice($words, $i - $lengthhead,$lengthhead);
						$tailarray = array_slice($words, $i + 1,$lengthtail);
						$contextwords = array_merge($contextwords,$headarray,$tailarray);
					}
				}
				*/

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
$start = strtotime($startdate);
$stop = strtotime($enddate);
$datelist = array();

//echo $start . " " . $stop;
foreach($queries as $query) {

	// week = 604800
	// day = 86400

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

?>

<div id="if_panel" class="if_structure">
	
	<div id="if_panel_downloads" class="if_structure">
		<div class="rowTab">
			<div class="headTab">
				Files written to disk:
			</div>
		</div>
				
		<div class="rowTab">
			<div id="if_panel_downloads_data" class="fullTab">
				<?php
				
				if($dooutput) {
					
					echo 'Filtered file: <a href="'.$filename_out.'" download>'.$filename_out.'</a>'; 
				}
					
				?>
			</div>
		</div>
	</div>
	
	<div class="rowTab">
		<div class="headTab">
			Number of lines the queries appear in:
		</div>
	</div>
	
	<div id="if_panel_linegraph_freq"></div>
	
	<div class="rowTab">
		<div class="headTab">
			Percentage of lines the queries appear in:
		</div>
	</div>
	
	<div id="if_panel_linegraph_norm"></div>
	
	
	<div id="if_panel_context" class="if_structure">
	
		<div class="rowTab">
			<div class="headTab">
				Word context:
			</div>
		</div>
		
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
			if($filetype == "facebook") { $moreoptions = "&minfblikes=" . $minfblikes; }
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


<script type="text/javascript">

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
	chart.draw(data, {width:1200, height:360, fontSize:9, hAxis:{slantedTextAngle:90, slantedText:true},  lineWidth:1, chartArea:{left:50,top:10,width:1050,height:300}});


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
	chart2.draw(data2, {width:1200, height:210, fontSize:9, hAxis:{slantedTextAngle:90, slantedText:true}, lineWidth:1, chartArea:{left:50,top:10,width:1050,height:150}});

	//vAxis:{ticks: [10,20,30,40,50,60,70,80,90,100], maxValue:100},

</script>

</body>
</html>