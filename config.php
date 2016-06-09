<?php

// ----- configuration variables -----
$datadir = "./data";
$stopwordsdir = "./stopwords/";
$windowsize = 3;
$httproot = "https://tools.digitalmethods.net/netvizz/facebook/research/lineminer/";

// ----- server stuff -----
ignore_user_abort(false);
set_time_limit(3600*1);
ini_set("memory_limit","3048M");
ini_set("error_reporting",1);
	
?>