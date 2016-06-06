<?php

include "stopwordsallforms.php";

$dir = "./data";

$windowsize = 3;

ignore_user_abort(false);
set_time_limit(3600*1);
ini_set("memory_limit","3048M");
ini_set("error_reporting",1);

$query = (isset($_GET["query"])) ? $_GET["query"]:"blabla";
$startdate = (isset($_GET["startdate"])) ? $_GET["startdate"]:"2011-01-01";
$enddate = (isset($_GET["enddate"])) ? $_GET["enddate"]:"2011-02-25";
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

if ($dh = opendir($dir)) {
	while (($file = readdir($dh)) !== false) {
		if(preg_match("/\.tab/", $file)) {
			$filenames[] = $file;
		}
	}
	closedir($dh);
} else {
	echo "error";
}

asort($filenames);

?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Comment Analytics</title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <script type="text/javascript" src="https://www.google.com/jsapi"></script>

    <script type="text/javascript">

        google.load("visualization", "1", {packages:["corechart"]});

    </script>

	<style type="text/css">

		body {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 11px;
		}

		table,tr,td {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 9px;
			border-collapse:collapse;
			border: 1px solid black;
		}

		table.if,tr.if,td.if {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 11px;
			border-collapse:collapse;
			border: 3px solid white;
		}

		td {
			padding:2px;
		}

	</style>
</head>

<body>

<form action="newsearch.php" method="get">
	<table class="if">
		<tr>
		<td class="if">filename:</td>
		<td class="if">
			<select name="file">
			<?php
			foreach($filenames as $filename) {
				$selectmarker = ($filename == $_GET["file"]) ? "selected":"";
				echo '<option value="'.$filename.'" '.$selectmarker.'>'.$filename.'</option>';
			}
			?>
			</select>
		</td>
		</tr>
		<tr>
			<td class="if">query:</td>
			<td class="if"><input type="text" name="query" value="<?php echo $query; ?>" /> (separate multiple keywords with comma, use a pipe for OR)</td>
		</tr>
		<tr>
			<td class="if">startdate:</td>
			<td class="if"><input type="text" name="startdate" value="<?php echo $startdate; ?>" /> (YYYY-MM-DD)</td>
		</tr>
		<tr>
			<td class="if">enddate:</td>
			<td class="if"><input type="text" name="enddate" value="<?php echo $enddate; ?>" /> (YYYY-MM-DD)</td>
		</tr>
		<tr>
			<td class="if">plot per:</td>
			<td class="if"><input type="radio" name="timescale" value="day" <?php if($_GET["timescale"] == "day") { echo 'checked="checked"'; } ?> /> day or
			<input type="radio" name="timescale" value="week" <?php if($_GET["timescale"] == "week") { echo 'checked="checked"'; } ?> /> week</td>
		</tr>
		<tr>
			<td class="if"></td>
			<td class="if"><input type="checkbox" name="showfull" <?php if($showfull != false) {  echo 'checked="checked"'; } ?> /> show full comment line</td>
		</tr>
		<tr>
			<td class="if"></td>
			<td class="if"><input type="checkbox" name="getcomments" <?php if($getcomments != false) {  echo 'checked="checked"'; } ?> /> show comments (with at least <input type="text" name="minlikes" style="width:25px;" value="<?php echo $minlikes; ?>" /> likes)</td>
		</tr>
		<tr>
			<td class="if"></td>
			<td class="if"><input type="checkbox" name="getcontext" <?php if($getcontext != false) {  echo 'checked="checked"'; } ?> /> show word context</td>
		</tr>
		<tr>
			<td class="if"></td>
			<td class="if"><input type="submit" /></td>
		</tr>
	</table>
</form>

<?php

if(isset($_GET["query"])) {
	$queries = explode(",",$_GET["query"]);
} else {
	echo "no query.";
	exit;
}

// مصر,الفتنه

$filename = $dir . "/" . $_GET["file"];

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

	if(preg_match("/full/", $filename)) {
		$unixdate = strtotime($buffer[3]);			// 4 and 3
		$content = $buffer[7];						// 5 and 7
	} else {
		$unixdate = strtotime($buffer[4]);
		$content = $buffer[5];
	}
	//print_r($buffer);

	if($unixdate < strtotime($startdate . " 00:00:00") || $unixdate > strtotime($enddate . " 23:59:59")) {
		continue;
	}

	// clean up content a little
	if($getcontext) {
		//$content = preg_replace("/[^\w\s]/","",$content);			// arabic is filtered out!
		$content = preg_replace("/[\n\r]/", " ", $content);
		$content = preg_replace("/\s+/", " ", $content);
		$content = strtolower($content);
	}
	//echo $content; exit;

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

		echo "<br /><br /><strong>" . $query . "</strong><br />";

		ksort($wordlist);

		foreach($wordlist as $date => $list) {

			arsort($list);

			echo $date . ":<br />frequency: ";
			$speclist = array();

			$counter = 0;
			foreach($list as $word => $freq) {
				if($counter <= 10) {
					echo $word . " (" . $freq . "/" . round($freq / $wordlist_full[$word],3) . "), ";
					//echo $word . " (".$freq . ") ";
				}
				$counter++;
				if($wordlist_full[$word] > 2) {
					$speclist[$word] = $freq / $wordlist_full[$word];
				}
			}

			arsort($speclist);

			//$tmplist = array_slice($tmplist,0,20);
			echo "<br />specificity: ";
			$counter = 0;
			foreach($speclist as $word => $freq) {
				if($counter == 10) {break;}
				$counter++;
				echo $word . " (" . $list[$word] . "/" . round($freq,3) . "), ";
			}

			echo "<br /><br />";

			/*
			foreach($phrases[$query][$date] as $phrase) {
				echo $phrase . "<br />";
			}

			echo "<br /><br />";
			 *
			 */
		}

		echo "<br /><br />";
	}

}



?>
</div>


</body>
</html>