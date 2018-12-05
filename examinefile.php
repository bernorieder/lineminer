<?php

include("config.php");

header("Content-Type: application/json");

// detect delimiter from file type
$filename = $datadir . "/" . $_GET["file"];
$extension = substr($filename, strlen($filename) - 3);
$delimiter = ($extension == "tab" || $extension == "tsv") ? "\t":",";

// read first line of the file
$fr = fopen($filename,"r");
while(($buffer = fgets($fr)) !== false) { break; }
$buffer = str_getcsv(preg_replace("/\xEF\xBB\xBF/","",$buffer),$delimiter);

// create response
$reply = array("type" => "Unknown","columns" => $buffer);

if(in_array("publishedAtSQL",$buffer) && in_array("videoTitle",$buffer) && in_array("videoDescription",$buffer) && in_array("viewCount",$buffer)) {
	$reply["type"] = "YouTube Data Tools video list";
	$reply["col_date"] = "publishedAtSQL";
	$reply["col_text"] = "videoTitle";
	$reply["col_score"] = "viewCount";
	$reply["col_text_post"] = "videoDescription"; }

if($buffer[6] == "authorChannelId") {
		$reply["type"] = "YouTube Data Tools comment";
		$reply["col_date"] = $buffer[3];
		$reply["col_text"] = $buffer[5];
		$reply["col_score"] = $buffer[2];
		$reply["col_text_post"] = false; }
		
if($buffer[6] == "comment_like_count") {
		$reply["type"] = "Netvizz top comments";
		$reply["col_date"] = $buffer[3];
		$reply["col_text"] = $buffer[5];
		$reply["col_score"] = $buffer[2];
		$reply["col_text_post"] = $buffer[1]; }
		
if(in_array("post_published_sql",$buffer) && in_array("post_message",$buffer) && in_array("engagement_fb",$buffer)) {
		$reply["type"] = "Netvizz post stat file";
		$reply["col_date"] = "post_published_sql";
		$reply["col_text"] = "post_message";
		$reply["col_score"] = "engagement_fb";
		$reply["col_text_post"] = false; }
		
if(in_array("post_published",$buffer) && in_array("comment_message",$buffer) && in_array("comment_like_count",$buffer)  && in_array("post_text",$buffer)) {
		$reply["type"] = "Netvizz comments";
		$reply["col_date"] = "post_published";
		$reply["col_text"] = "comment_message";
		$reply["col_score"] = "comment_like_count"; 
		$reply["col_text_post"] = "post_text"; }
		
if($buffer[5] == "imageurl") {
		$reply["type"] = "Netvizz image file";
		$reply["col_date"] = $buffer[2];
		$reply["col_text"] = $buffer[1];
		$reply["col_score"] = $buffer[7]; 
		$reply["col_text_post"] = false; }
		
if(in_array("created_at",$buffer) && in_array("text",$buffer) && in_array("favorite_count",$buffer)) {
		$reply["type"] = "DMI-TCAT tweet export";
		$reply["col_date"] = "created_at";
		$reply["col_text"] = "text";
		$reply["col_score"] = "favorite_count";
		$reply["col_text_post"] = false; }
		
if($buffer[5] == "post_ups") {
		$reply["type"] = "Reddit Tools comment";
		$reply["col_date"] = $buffer[18];
		$reply["col_text"] = $buffer[12];
		$reply["col_score"] = $buffer[14];
		$reply["col_text_post"] = $buffer[2]; }

echo json_encode($reply);	
	
?>