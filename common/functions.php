<?php

function getstopwords($language) {
	
	global $stopwordsdir;
	
	$filecontent = file_get_contents($stopwordsdir . "stopwords_" . $language .".txt");

	$list = explode("\n", $filecontent);
	
	$wordlist = array();
	foreach($list as $word) {
		$wordlist[$word] = $word;
	}
	
	return $wordlist;
}	
	
?>