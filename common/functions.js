var _interface = new Array;
_interface["col_date"] = new Array;
_interface["col_text"] = new Array;
_interface["col_score"] = new Array;

var _fileinfo;
var _selcounter = 0;

// process URL
var _httppath = window.location.href.split("?");
_httppath = _httppath[0];

var _params = false
if(window.location.search != "") {
		
	_params = new Object;
	var _tmpparams = window.location.search.substr(1, window.location.search.length - 1);
	
	_tmpparams = _tmpparams.split("&");
	for(var i = 0; i < _tmpparams.length; i++) {
		var _tmpcomp = _tmpparams[i].split("=");
		_params[_tmpcomp[0]] = _tmpcomp[1];
	}
}

function onloadTodo() {
	
	if(_params != false) {
		
		// reselect form selections
		$("[name='query']").val(decodeURIComponent(_params.query));
		$("[name='startdate']").val(_params.startdate);
		$("[name='enddate']").val(_params.enddate);
		
		loadFile(decodeURIComponent(_params.datafile));
		$("select[name='datafile'] option[value='" + decodeURIComponent(_params.datafile) + "']").prop("selected",true);
		$("select[name='language'] option[value='" + decodeURIComponent(_params.language) + "']").prop("selected",true);
		
		$("input:radio[name='timescale']").filter('[value="' + _params.timescale + '"]').attr("checked", true);
		
		if(_params.showfull == "true") { $("input:checkbox[name='showfull']").attr('checked', true); };
		if(_params.getcontext == "true") {
			$("input:checkbox[name='getcontext']").attr('checked', true);
			$("#if_panel_context").show();
		};
		if(_params.dosummary == "true") {
			$("input:checkbox[name='dosummary']").attr('checked', true);
			$("#if_panel_downloads").show();};
		if(_params.dooutput == "true") {
			$("input:checkbox[name='dooutput']").attr('checked', true);
			$("#if_panel_downloads").show();
		};
		
	} else {
		
		$("select[name='datafile']").prop("selectedIndex",0);
	}
}


function sendForm() {

	var _url = "";

	// basic form parameters
	_url += "?datafile=" + encodeURIComponent($("select[name='datafile']").val());
	_url += "&query=" + encodeURIComponent($("input[name='query']").val());
	_url += "&language=" + encodeURIComponent($("select[name='language']").val());
	_url += "&startdate=" + $("input[name='startdate']").val();
	_url += "&enddate=" + $("input[name='enddate']").val();
	_url += "&timescale=" + $("input[name='timescale']:checked").val();
	_url += "&showfull=" + $("input[name='showfull']").is(':checked');
	_url += "&getcontext=" + $("input[name='getcontext']").is(':checked');
	_url += "&dosummary=" + $("input[name='dosummary']").is(':checked');
	_url += "&dooutput=" + $("input[name='dooutput']").is(':checked');

	// column selectors
	_url += "&col_date=" + encodeURIComponent($("select[name='" + _interface["col_date"][0] + "']").val());
	
	var _colstext = new Array;
	for(var i = 0; i < _interface["col_text"].length; i++) {
		_colstext.push($("select[name='" + _interface["col_text"][i] + "']").val());
	}
	_url += "&cols_text=" + encodeURIComponent(_colstext.join(","));
	
	var _colsscore = new Array;
	for(var i = 0; i < _interface["col_score"].length; i++) {
		_colsscore.push($("select[name='" + _interface["col_score"][i] + "']").val() + "|" + $("input[name='" + _interface["col_score"][i] + "_below']").val());
	}
	_url += "&cols_score=" + encodeURIComponent(_colsscore.join(","));

	_url = _httppath + _url;
	
	window.location.href = _url;
}


function loadFile(_file) {
	
	// reset column lists
	_interface["col_date"] = new Array;
	_interface["col_text"] = new Array;
	_interface["col_score"] = new Array;
	$("[name='col_date']").html("");
	$("[name='col_text']").html("");
	$("[name='col_score']").html("");

	$.getJSON("examinefile.php", { file:_file }, function(_reply) {
		
		_fileinfo = _reply;
		
		$("#if_filedetected_text").html(_fileinfo.type + " file detected. Columns are automatically chosen, but you can modify them below.");
		
		createColselectors();
	});
}


function createColselectors() {
	
	if(_params != false) {
		
		createColelement("col_date",_params.col_date);
		
		var _tmp_cols = decodeURIComponent(_params.cols_text).split(",");
		for(var i = 0; i < _tmp_cols.length; i++) {
			createColelement("col_text",_tmp_cols[i]);
		}
		
		var _tmp_cols = decodeURIComponent(_params.cols_score).split(",");
		for(var i = 0; i < _tmp_cols.length; i++) {
			createColelement("col_score",_tmp_cols[i]);
		}
		
	} else {
		
		$("[name='col_date']").html("");
		createColelement("col_date",_fileinfo.col_date);
		
		$("[name='col_text']").html("");
		createColelement("col_text",_fileinfo.col_text);
		
		if(_fileinfo.col_text_post != false) {
			createColelement("col_text",_fileinfo.col_text_post);
		}
						
		$("[name='col_score']").html("");
		createColelement("col_score",_fileinfo.col_score);
	}

	$("#if_parameters").show();	
}


function createColelement(_type,_sel="false") {
	
	if(_type == "col_score") {
		var _selval = 0;
		if(_sel != "false" && _sel.match(/\|/)) {
			var _tmp = _sel.split("|");
			_sel = _tmp[0];
			_selval = _tmp[1];
		}
	}
	
	_name = _type + "_sel_" + _selcounter;
	_selcounter++;
	
	_interface[_type].push(_name);
	
	var _html = '<div id="' + _name + '">';
	_html += '<select name="' + _name + '">';
	for(var _colname in _fileinfo.columns) {
		var _selected  = (_fileinfo.columns[_colname] == _sel) ? "selected":"";
		_html += '<option value="'+_fileinfo.columns[_colname]+'" ' + _selected + '>'+_fileinfo.columns[_colname]+'</option>';
			
	}
	_html += '</select>';
	_html += (_type == "col_score") ? ' ignore lines below <input type="text" name="' + _name + '_below" style="width:30px;" value="' + _selval + '" /> score':'';
	_html += (_interface[_type].length > 1) ? ' <a onclick="removeColelement(\'' + _type + '\',\'' + _name + '\')">remove</a>':'';
	_html += '</div>';
	
	$("[name='" + _type + "']").append(_html);
}


function removeColelement(_type,_id) {
	$("#" + _id).remove();
	
	for(var i = 0; i < _interface[_type].length; i++) {
		if(_interface[_type][i] == _id) { _interface[_type].splice(i, 1); }
	}
	
	console.log(_interface[_type]);
	console.log(_interface[_type].length);
}


function loadLines(_date,_timescale) {
	$.getJSON( "getcomments.php", { date:_date,timescale:_timescale }, function(_reply) {
		$("#if_panel_lines_data").html(_reply.key);
	});
}