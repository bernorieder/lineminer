function onloadTodo() {
	
	// clear the browsers selection memory
	
	var _fileselect = getURLParameter("fileselect");
	if(typeof(_fileselect) != "undefined") {
		$("select option[value='" + _fileselect + "']").prop("selected",true);
		loadFile(_fileselect);
	} else {
		$("[name='fileselect']").prop('selectedIndex',0);
	}
	
	var _getcontext = getURLParameter("getcontext");
	if(_getcontext == "on") {
		$("#if_panel_context").show();
	}
	
	var _dooutput = getURLParameter("dooutput");
	var _dosummary = getURLParameter("dosummary");
	if(_dooutput == "on" || _dosummary == "on") {
		$("#if_panel_downloads").show();
	}
}


function loadFile(_file) {

	$.getJSON( "examinefile.php", { file:_file }, function(_reply) {
		
		if(_reply.type == "facebook" || _reply.type == "facebook_topcomments") {
			$("[name='filetype']").val(_reply.type);
			$("#if_parameters").show();
			$("#if_parameters_facebook").show();
			$("#if_parameters_twitter").hide();
			$("#if_parameters_youtube").hide();
			$("#if_parameters_reddit").hide();
			$("#if_parameters_unknown").hide();
			$("#if_filedetected_text").html("Netvizz file detected.");
		}
				
		if(_reply.type == "twitter") {
			$("[name='filetype']").val("twitter");
			$("#if_parameters").show();
			$("#if_parameters_facebook").hide();
			$("#if_parameters_twitter").show();
			$("#if_parameters_youtube").hide();
			$("#if_parameters_reddit").hide();
			$("#if_parameters_unknown").hide();
			$("#if_filedetected_text").html("DMI-TCAT file detected.");
		}
		
		if(_reply.type == "youtube") {
			$("[name='filetype']").val("youtube");
			$("#if_parameters").show();
			$("#if_parameters_facebook").hide();
			$("#if_parameters_twitter").hide();
			$("#if_parameters_youtube").show();
			$("#if_parameters_reddit").hide();
			$("#if_parameters_unknown").hide();
			$("#if_filedetected_text").html("YouTube Data Tools file detected.");
		}
		
		if(_reply.type == "reddit_comments") {
			$("[name='filetype']").val(_reply.type);
			$("#if_parameters").show();
			$("#if_parameters_facebook").hide();
			$("#if_parameters_twitter").hide();
			$("#if_parameters_youtube").hide();
			$("#if_parameters_reddit").show();
			$("#if_parameters_unknown").hide();
			$("#if_filedetected_text").html("Reddit comment file detected.");
		}
		
		if(_reply.type == "unknown") {
			$("[name='filetype']").val("unknown");
			$("#if_parameters").show();
			$("#if_parameters_facebook").hide();
			$("#if_parameters_twitter").hide();
			$("#if_parameters_youtube").hide();
			$("#if_parameters_reddit").hide();
			$("#if_parameters_unknown").show();
			$("#if_filedetected_text").html("Unknown file detected. Select column definitions below.");
		}

	});
}


function loadLines(_date,_timescale) {

	$.getJSON( "getcomments.php", { date:_date,timescale:_timescale }, function(_reply) {
		$("#if_panel_lines_data").html(_reply.key);
	});
}


function getURLParameter(sParam) {
	var sPageURL = window.location.search.substring(1);
	var sURLVariables = sPageURL.split('&');
	
	for (var i = 0; i < sURLVariables.length; i++) {
		var sParameterName = sURLVariables[i].split('=');
		if (sParameterName[0] == sParam) {
			return sParameterName[1];
		}
	}
}