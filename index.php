<?php

// tool description
// Some stop word lists: http://www.semantikoz.com/blog/free-stop-word-lists-in-23-languages/
// and/or
// tab outputs
// different file models




ignore_user_abort(false);
set_time_limit(3600*1);
ini_set("memory_limit","3048M");
ini_set("error_reporting",1);


// ----- link external files -----
include "functions.php";
include "config.php";


// ----- get URL parameters -----
$query = (isset($_GET["query"])) ? $_GET["query"]:"barcelona";
$language = (isset($_GET["language"])) ? $_GET["language"]:"english";
$startdate = (isset($_GET["startdate"])) ? $_GET["startdate"]:"2014-01-01";
$enddate = (isset($_GET["enddate"])) ? $_GET["enddate"]:"2015-01-01";
$showfull = (isset($_GET["showfull"]) == "on") ? true:false;
$getcontext = (isset($_GET["getcontext"]) == "on") ? true:false;
$getcomments = (isset($_GET["getcomments"]) == "on") ? true:false;
$minlikes = (isset($_GET["minlikes"])) ? $_GET["minlikes"]:0;


if($_GET["timescale"] == "day") {
	$dateformat = "Y-m-d";
	$seconds = 86400;
} else {
	$dateformat = "o-W";
	$seconds = 604800;
}

// check for and load list of data files
$filenames = array();
if ($dh = opendir($datadir)) {
	while (($file = readdir($dh)) !== false) {
		if(preg_match("/\.tab/", $file)) {
			$filenames[] = $file;
		}
	}
	closedir($dh);
} else {
	echo "Error: could not open files in data directory: " . $datadir;
}


// check for and load list of stopword files
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

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Comment Analytics</title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    
    <script type="text/javascript">

        google.load("visualization", "1", {packages:["corechart"]});

		function loadFile(_file) {
			$.getJSON( "examinefile.php", { file:_file }, function(_reply) {
				
				if(_reply.type == "facebook") {
					$("#if_parameters").show();
					$("#if_parameters_facebook").show();
					$("#if_filedetected").html("Netvizz file detected");
				}
				
				if(_reply.type == "youtube") {
					$("#if_parameters").show();
					$("#if_parameters_facebook").hide();
					$("#if_filedetected").html("YouTube Data Tools file detected");
				}
			});
		}
		
		function clearForm() {
			$("[name='file']").prop('selectedIndex',0);
		}

    </script>

	<link rel="stylesheet" type="text/css" href="main.css">
</head>

<body onload="clearForm();">

<h1>lineminer</h1>

<p>BlaBla, explanation, FAQ, links, credits. Source code <a href="https://github.com/bernorieder/lineminer" target="_blank">available on github.</a></p>

<div id="if_file">
	
	<form action="index.php" method="get">
	
	<div class="rowTab">
		<div class="leftTab">Choose a file:</div>
		
		<div class="rightTab">
			<select name="file" onchange="loadFile(this.value)">
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

<div class="clear"></div>

<div id="if_filedetected"></div>

<div id="if_parameters">
	
	<div id="if_parameters_common">
		<div class="rowTab">
			<div class="headTab">Common parameters</div>
		</div>
	
		<div class="rowTab">
			<div class="leftTab">Search query:</div>
			
			<div class="rightTab">
				<input type="text" name="query" value="<?php echo $query; ?>" /> (separate multiple keywords with comma, use a pipe for OR)
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
				<input type="text" name="startdate" value="<?php echo $startdate; ?>" /> (YYYY-MM-DD)
			</div>
		</div>
		
		<div class="rowTab">
			<div class="leftTab">Enddate:</div>
			
			<div class="rightTab">
				<input type="text" name="enddate" value="<?php echo $enddate; ?>" /> (YYYY-MM-DD)
			</div>
		</div>
		
		<div class="rowTab">
			<div class="leftTab">Time interval:</div>
			
			<div class="rightTab">
				<input type="radio" name="timescale" value="day" <?php if($_GET["timescale"] == "day") { echo 'checked="checked"'; } ?> /> day or
				<input type="radio" name="timescale" value="week" <?php if($_GET["timescale"] != "day") { echo 'checked="checked"'; } ?> /> week
			</div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab"><input type="checkbox" name="showfull" <?php if($showfull != false) {  echo 'checked="checked"'; } ?> /> show full comment line</div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab"><input type="checkbox" name="getcontext" <?php if($getcontext != false) {  echo 'checked="checked"'; } ?> /> show word context</div>
		</div>
	</div>
	
	<div id="if_parameters_facebook">
		<div class="rowTab">
			<div class="headTab">Facebook parameters</div>
		</div>
		
		<div class="rowTab">
			<div class="fullTab">
				<input type="checkbox" name="getcomments" <?php if($getcomments != false) {  echo 'checked="checked"'; } ?> /> show comments (with at least
				<input type="text" name="minlikes" style="width:25px;" value="<?php echo $minlikes; ?>" /> likes)
			</div>
		</div>
	</div>
	
	<div id="if_parameters_submit">
		<input type="submit" />
	</div>
		
	</form>
</div>

<?php

// check query
if(isset($_GET["query"])) {
	$queries = explode(",",$_GET["query"]);
} else {
	exit;
}

// get stopword list
$stopwords = getstopwords($language);

$filename = $datadir . "/" . $_GET["file"];

$datebins = array();
$datebins_full = array();
$wordlists = array();
$wordlist_full = array();
$phrases = array();

$fr = fopen($filename,'r');
$counter = 0;

while(($buffer = fgets($fr)) !== false) {

	if($counter == 0) {			// jump first line
		$counter++;
		continue;
	}

	$buffer = explode("\t", $buffer);

	//print_r($buffer); exit;

	$unixdate = strtotime($buffer[4]);			// 4 and 3
	$content = $buffer[8];						// 5 and 7
		//print_r($buffer);

	if($unixdate < strtotime($startdate . " 00:00:00") || $unixdate > strtotime($enddate . " 23:59:59")) {
		continue;
	}
	
	// get out significant objects
	preg_match("/http(.*?) /",$content, $matches);
	//print_r($matches[0]);
	
	//echo "<br />";
	
	preg_match("/ #(.*?) /",$content, $matches);
	//	print_r($matches[0]);
	

	// clean up content a little
	if($getcontext) {
		$content = preg_replace("/[\.\"\'\!\?\(\);,¿:]/", " ", $content); // currently also filters out URLs 
		$content = preg_replace("/[\n\r]/", " ", $content);
		$content = preg_replace("/\s+/", " ", $content);
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

		if(preg_match("/".$query."/i",$content)) {

			if(!isset($datebins[$query][$date])) { $datebins[$query][$date] = array(); }
			if(!isset($wordlists[$query][$date])) { $wordlists[$query][$date] = array(); }
			if(!isset($phrases[$query][$date])) { $phrases[$query][$date] = array(); }

			$datebins[$query][$date][] = ($getcomments) ? $buffer:"x";

			if($getcontext) {

				//echo $content;

				// spaghetti regex!
				//$kwic = preg_match_all("/(\w+\s\w+\s\w+\s\w+\s\w+\s\w+\s".$query."\s\w+\s\w+\s\w+\s\w+\s\w+\s\w+)/",$content,$matches);
				//$phrase = preg_replace("/ ".$query."/", "", $matches[0][0]);
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

				//print_r($words);print_r($contextwords); echo "<br /><br />";
				//exit;

				foreach($contextwords as $word) {

					if(isset($stopwords[$word])) { continue; }

					if(!isset($wordlists[$query][$date][$word])) { $wordlists[$query][$date][$word] = 0; }
					$wordlists[$query][$date][$word]++;

					if(!isset($wordlist_full[$word])) { $wordlist_full[$word] = 0;}
					$wordlist_full[$word]++;
				}
			}
		}
	}
}

fclose($fr);

// fill empty dates
$start = strtotime($startdate);
$stop = strtotime($enddate);

//echo $start . " " . $stop;
foreach($queries as $query) {

	// week = 604800
	// day = 86400

	for($i = $start; $i < $stop; $i += $seconds) {
		$date = date($dateformat,$i);
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

Line graph 1: number of comments the queries appear in:<br /><br />
<div id="if_panel_linegraph"></div><br /><br />

Line graph 2: percentage of comments the queries appear in:<br /><br />
<div id="if_panel_linegraph_norm"></div>

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

	var chart = new google.visualization.LineChart(document.getElementById('if_panel_linegraph'));
	chart.draw(data, {width:1200, height:480, fontSize:9, hAxis:{slantedTextAngle:90, slantedText:true},  lineWidth:1, chartArea:{left:50,top:10,width:1050,height:420}});


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
	chart2.draw(data2, {width:1200, height:220, fontSize:9, hAxis:{slantedTextAngle:90, slantedText:true}, lineWidth:1, chartArea:{left:50,top:10,width:1050,height:160}});

	//vAxis:{ticks: [10,20,30,40,50,60,70,80,90,100], maxValue:100},

</script>



<div id="comments">
<?php
//print_r($datebins);

// post_id		post_by		post_text		post_published		comment_id		comment_by		is_reply	comment_message		comment_published	comment_like_count
if($getcomments) {

	echo '<hr />';

	foreach ($datebins as $query => $weekdata) {
		echo '<h1>' . $query . '</h1>';
		echo '<table border="1" id="commenttable">';
		foreach($weekdata as $weekdate => $comments) {

			$tmpcomments = array();

			if(count($comments) > 0) {

				foreach($comments as $comment) {

					if ($comment[9] >= $minlikes) {

						if(!isset($tmpcomments[$comment[0]])) {
							$tmpcomments[$comment[0]] = array();
						}

						$tmpcomments[$comment[0]][] = $comment;
					}
				}

				if($exit == true) { continue; }

				echo '<tr><td colspan="7" style="background-color:#bdc3ff;font-size:10px;font-weight:bold;">week ' . $weekdate . '</td></tr>';
				echo '<tr>';
				echo '<td><i>post date</i></td>';
				echo '<td><i>com date</i></td>';
				echo '<td><i>com likes</i></td>';
				echo '<td><i>com by</i></td>';
				echo '<td><i>com message</i></td>';
				echo '<td><i>post id</i></td>';
				echo '<td><i>com id</i></td>';
				echo '</tr>';

				//print_r($comments);

				foreach($tmpcomments as $post => $comments) {

					$counter = 0;
					foreach($comments as $comment) {

						//print_r($comment);

						echo '<tr>';
						if($counter == 0) {
							echo '<td rowspan="'.count($comments).'">' . $comment[3] . '</td>';
						}
						echo '<td>' . $comment[8] . '</td>';
						echo '<td>' . $comment[9] . '</td>';
						echo '<td>' . $comment[5] . '</td>';
						echo '<td>' . $comment[7] . '</td>';
						echo '<td>' . $comment[0] . '</td>';
						echo '<td>' . $comment[4] . '</td>';
						$counter++;

						echo '</tr>';


					}
				}
			}
		}
		echo "</table>";
	}
}

?>
</div>

<div id="context">
<?php

if($getcontext) {

	echo '<hr />';
	
	foreach($wordlists as $query => $wordlist) {

		ksort($wordlist);

		echo "<br /><br /><strong>" . $query . "</strong><br />";

		echo '<table class="wordlist"><tr class="wordlist">';

		foreach($wordlist as $date => $list) {
			
			echo '<th class="wordlist">'.$date.'</th>';
		}

		echo '</tr><tr class="wordlist">';
		
		foreach($wordlist as $date => $list) {

			arsort($list);

			echo '<td class="wordlist">frequency:<br />';
			$speclist = array();

			$counter = 0;
			foreach($list as $word => $freq) {
				if($counter <= 10) {
					echo $word . " (" . $freq . "/" . round($freq / $wordlist_full[$word],3) . ")<br />";
					//echo $word . " (".$freq . ") ";
				}
				$counter++;
				if($wordlist_full[$word] > 2) {
					$speclist[$word] = $freq / $wordlist_full[$word];
				}
			}

			echo '</td>';

		}
		
		echo '</tr><tr class="wordlist">';
		
		foreach($wordlist as $date => $list) {
			
			arsort($speclist);

			//$tmplist = array_slice($tmplist,0,20);
			echo '<td class="wordlist">specificity:<br />';
			$counter = 0;
			foreach($speclist as $word => $freq) {
				if($counter == 10) {break;}
				$counter++;
				echo $word . " (" . $list[$word] . "/" . round($freq,3) . ")<br />";
			}

			echo "</td>";

			/*
			foreach($phrases[$query][$date] as $phrase) {
				echo $phrase . "<br />";
			}

			echo "<br /><br />";
			 *
			 */
		}

		echo '</tr></table>';

		echo "<br /><br />";
	}

	
}



?>
</div>


</body>
</html>