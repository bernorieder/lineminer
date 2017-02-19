<?php

// todo: put cwd() into the dir defintions
// is the $httproot really necessary?
// check stopword lists
// build a quick data error detection

// ----- analysis variables -----
$windowsize = 3;


// ----- directory variables -----
$datadir = getcwd() . "/data/";
$outdir = getcwd() . "/output/";
$stopwordsdir = getcwd() . "/stopwords/";


// ----- server stuff -----
ignore_user_abort(false);
set_time_limit(3600*1);
ini_set("memory_limit","3048M");
ini_set("error_reporting",1);
	
?>