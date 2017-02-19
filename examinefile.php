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
$reply = array("type" => "unknown","columns" => $buffer);

if(preg_match("/authorChannelId/",$buffer)) {
		$reply["type"] = "YouTube Data Tools comment";
		$reply["col_date"] =$buffer[3];
		$reply["col_text"] = $buffer[5];
		$reply["col_score"] = $buffer[2];
		$reply["col_text_post"] = false; }
		
if(preg_match("/comment_like_count/",$buffer)) {
		$reply["type"] = "Netvizz top comments";
		$reply["col_date"] = $buffer[3];
		$reply["col_text"] = $buffer[5];
		$reply["col_score"] = $buffer[2];
		$reply["col_text_post"] = $buffer[1]; }
		
if(preg_match("/position/",$buffer)) {
		$reply["type"] = "Netvizz comments";
		$reply["col_date"] = $buffer[3];
		$reply["col_text"] = $buffer[5];
		$reply["col_score"] = $buffer[2]; 
		$reply["col_text_post"] = $buffer[3]; }
		
if(preg_match("/in_reply_to_status_id/",$buffer)) {
		$reply["type"] = "DMI-TCAT tweet export";
		$reply["col_date"] = $buffer[3];
		$reply["col_text"] = $buffer[5];
		$reply["col_score"] = $buffer[2];
		$reply["col_text_post"] = false; }
		
if($buffer[5] == "post_ups") {
		$reply["type"] = "Reddit Tools comment";
		$reply["col_date"] = $buffer[18];
		$reply["col_text"] = $buffer[12];
		$reply["col_score"] = $buffer[14];
		$reply["col_text_post"] = $buffer[2]; }

echo json_encode($reply);	
	
?>