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
	if(_dooutput == "on") {
		$("#if_panel_downloads").show();
	}
}


function loadFile(_file) {

	$.getJSON( "examinefile.php", { file:_file }, function(_reply) {
		
		if(_reply.type == "facebook") {
			$("[name='filetype']").val("facebook");
			$("#if_parameters").show();
			$("#if_parameters_facebook").show();
			$("#if_parameters_twitter").hide();
			$("#if_parameters_youtube").hide();
			$("#if_filedetected_text").html("Netvizz file detected");
		}
				
		if(_reply.type == "twitter") {
			$("[name='filetype']").val("twitter");
			$("#if_parameters").show();
			$("#if_parameters_facebook").hide();
			$("#if_parameters_twitter").show();
			$("#if_parameters_youtube").hide();
			$("#if_filedetected_text").html("DMI-TCAT file detected");
		}
		
		if(_reply.type == "youtube") {
			$("[name='filetype']").val("youtube");
			$("#if_parameters").show();
			$("#if_parameters_facebook").hide();
			$("#if_parameters_twitter").hide();
			$("#if_parameters_youtube").show();
			$("#if_filedetected_text").html("YouTube Data Tools file detected");
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