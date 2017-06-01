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

if($buffer[6] == "authorChannelId") {
		$reply["type"] = "YouTube Data Tools comment";
		$reply["col_date"] =$buffer[3];
		$reply["col_text"] = $buffer[5];
		$reply["col_score"] = $buffer[2];
		$reply["col_text_post"] = false; }
		
if($buffer[6] == "comment_like_count") {
		$reply["type"] = "Netvizz top comments";
		$reply["col_date"] = $buffer[3];
		$reply["col_text"] = $buffer[5];
		$reply["col_score"] = $buffer[2];
		$reply["col_text_post"] = $buffer[1]; }
		
if($buffer[0] == "position") {
		$reply["type"] = "Netvizz comments";
		$reply["col_date"] = $buffer[4];
		$reply["col_text"] = $buffer[8];
		$reply["col_score"] = $buffer[2]; 
		$reply["col_text_post"] = $buffer[3]; }
		
if($buffer[5] == "imageurl") {
		$reply["type"] = "Netvizz image file";
		$reply["col_date"] = $buffer[2];
		$reply["col_text"] = $buffer[1];
		$reply["col_score"] = $buffer[7]; 
		$reply["col_text_post"] = false; }
		
if($buffer[14] == "in_reply_to_status_id") {
		$reply["type"] = "DMI-TCAT tweet export";
		$reply["col_date"] = $buffer[3];
		$reply["col_text"] = $buffer[5];
		$reply["col_score"] = $buffer[6];
		$reply["col_text_post"] = false; }
		
if($buffer[5] == "post_ups") {
		$reply["type"] = "Reddit Tools comment";
		$reply["col_date"] = $buffer[18];
		$reply["col_text"] = $buffer[12];
		$reply["col_score"] = $buffer[14];
		$reply["col_text_post"] = $buffer[2]; }

echo json_encode($reply);	
	
?>