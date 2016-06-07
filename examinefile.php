<?php

include("config.php");

$filename = $datadir . "/" . $_GET["file"];

$fr = fopen($filename,'r');

while(($buffer = fgets($fr)) !== false) {

	break;

}

$type = "unable";

if(preg_match("/authorChannelId/",$buffer)) { $type = "youtube"; }
if(preg_match("/comment_like_count/",$buffer)) { $type = "facebook"; }

$test = array("type" => $type);

echo json_encode($test);	
	
?>