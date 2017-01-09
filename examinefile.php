<?php

include("config.php");

$filename = $datadir . "/" . $_GET["file"];

$fr = fopen($filename,'r');

while(($buffer = fgets($fr)) !== false) {
	break;
}

$type = "unknown";

if(preg_match("/authorChannelId/",$buffer)) { $type = "youtube"; }
if(preg_match("/comment_like_count/",$buffer)) { $type = "facebook_topcomments"; }
if(preg_match("/position/",$buffer)) { $type = "facebook"; }
if(preg_match("/in_reply_to_status_id/",$buffer)) { $type = "twitter"; }
if(preg_match("/post_ups/",$buffer)) { $type = "reddit_comments"; }


$test = array("type" => $type);

echo json_encode($test);	
	
?>