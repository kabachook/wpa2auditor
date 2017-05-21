"use strict";

//Auto-reload button
//is clicked
var isAutoReload = false;

//Timer id for cleaning
var intervalID;
var buttonAutoReloadID = "#buttonTurnOnAutoRefresh";

//SOMN = ShowOnlyMyNetworks
//Flag, if button was pressed
var isPressedSONM = false;

//Configuration
var baseUrl = "content/tasks.php";
var prefix = "?ajax=";
var tableUrl = baseUrl + prefix + "table";
var paggerUrl = baseUrl + prefix + "pagger";
var rightSideBarUrl = baseUrl + prefix + "right";
var tableDivID = "#ajaxLoadTable";
var rightSideBarDivID = "ajaxLoadRightNavBar";

//Status
var statusHandshakeUrl = baseUrl + prefix + "statusHandshakeUpload";
var statusHashUrl = baseUrl + prefix + "statusHashUpload";

//uniq ids
var uniqHashStatusDivID = "statusHashUpload";
var uniqHandshakeStatusDivID = "statusFileUpload";

//Table IDs
var tableUploadHandshakeID = "tableUploadHandshake";
var tableUploadHashID = "tableUploadHash";

//Forms IDs
var formUploadNTLMHashID = "formUploadNTLMHash";
var formUploadHandshakeID = "formUploadHandshake";

//Buttons IDs
var buttonShowOnlyMyNetworksID = "buttonShowOnlyMyNetworks";

//Table
var ajaxTableDivID = "#ajaxTableDiv";
var ajaxTableID = "#taskTable";

var ajaxPaggerDivID = "#ajaxPagger";













//Load\reload table
function loadTable() {
	$.get(tableUrl, {"somn" : isPressedSONM}, function (data) {
		drawTable(data);
		colorStatus();
	}, "json" );
}

function loadPagger() {
	$.get(paggerUrl, function (data) {
		drawPagger(data);
	}, "json" );
}

function drawPagger(data) {
	var result = '<nav aria-label="Page navigation"><ul class="pagination">';
	//
	data.forEach(function(element, index, array){
		if(element.arrow === true) {
			
			if (element.active === true) {
				result += '<li class="page-item"><a class="page-link disabled" onClick="ajaxGetPage(' + element.page + ');" aria-label="Previous"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>';
			}
			else {
				result += '<li class="page-item"><a class="page-link" onClick="ajaxGetPage(' + element.page + ');" aria-label="Previous"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>';
			}
			
		} else {
			
			if (element.active === true) {
				result += '<li class="page-item"><a class="page-link disabled" onClick="ajaxGetPage(' + element.page + ');">' + element.page + '</a></li>';
			}
			else {
				result += '<li class="page-item"><a class="page-link" onClick="ajaxGetPage(' + element.page + ');">' + element.page + '</a></li>';
			}
		}
	});
	result += '</ul></nav>';
	$(ajaxPaggerDivID).html(result);
}



//Delete task button
function ajaxDeleteTask(vard) {

	//Cancel submit form to server via POST wtih page reload
	event.preventDefault();

	//Get id task for delete from form
	var id = vard.elements.deleteTaskID.value;
	
	//Data to send
	var data = new FormData();
	data.append("deleteTask", true);
	data.append("deleteTaskID", id);
	
	jQuery.ajax({
		url: baseUrl, //page url
		type: "POST",
		data: data,
		processData: false, // Dont process the files
		contentType: false, // string request
		success: function () { //Data send success
			loadTable();
		},
		error: function () { // Data send failed
			console.log("Delete task error while sending via POST");
		}
	});
}

var file;

//ID that tr with status will have
var result_status;

//ID table with status
var result_table;

//ID Form with status
var result_form;

function ajaxSendForm(vard, type) {

	//Cancel submit form to server via POST wtih page reload
	event.preventDefault();

	//Data to send
	var data = new FormData();
	var url;

	if (type === "handshake") {
		
		result_status = uniqHandshakeStatusDivID;
		
		data.append("upfile", file);
		data.append("buttonUploadFile", true);
		data.append("task_name", vard.elements.task_name.value);
		
		url = statusHandshakeUrl;
		result_table = tableUploadHandshakeID;
		result_form = formUploadHandshakeID;
		
	} else if (type === "ntlm") {
		
		result_status = uniqHashStatusDivID;
		
		data.append('buttonUploadHash', true);
		data.append('taskname', vard.elements.taskname.value);
		data.append('username', vard.elements.username.value);
		data.append('challenge', vard.elements.challenge.value);
		data.append('response', vard.elements.response.value);
		
		url = statusHashUrl;
		result_table = tableUploadHashID;
		result_form = formUploadNTLMHashID;
	}

	jQuery.ajax({
		url: url, //page url
		type: "POST",
		data: data,
		processData: false, // Dont process the file
		contentType: false, // string requset

		//On success upload
		success: function (response) {
			
			//Check if status exists in table
			/*if ($("tr").is("#" + result_status)) {

				//Change status
				$("#" + result_status).html(response);
			} else {

				//if doesn't exists add it to table
				$("#" + result_table + " > tbody:last-child").append("<tr id='" + result_status + "'>" + response + "</tr>");
			}*/
			
			//Reset all inputs
			$("#" + result_form).get(0).reset();
			
			//Reload table
			loadTable();
			
			//generate notify
			var json = $.parseJSON(response);
			genNotify(json.type, json.message);
			
		},
		//Failed to send data
		error: function (response) {
			console.log("Error while sending hash\handshake. " + response);
		}
	});

}

function genNotify(type, message) {
	$.notify({
	// options
	icon: 'glyphicon glyphicon-warning-sign',
	message: message,
},{
	// settings
	type: type,
	newest_on_top: false,
	placement: {
		from: "bottom",
		align: "right"
	},
	mouse_over: "pause",
});
}

function ajaxSendWPAKeys() {
	
	//Cancel submit form to server via POST wtih page reload
	event.preventDefault();
	
	//Data to send
	var data = new FormData();
	
	data.append("sendWPAKey", true);
	
	//For all forms with class wpaKeysTable get id and key
	$(".wpaKeysTable").each(function () {
		var item = $(this).serializeArray()[0];
		data.append(item.name, item.value);
	});

	jQuery.ajax({
		url: baseUrl, //page url
		type: "POST",
		data: data,
		processData: false, // Dont process the file
		contentType: false, // string requset
		
		//On success upload
		success: function () { 
			loadTable();
		},
		
		//On error upload
		error: function (response) {
			console.log("Error while sending hash\handshake. " + response);
		}
	});
}

function showOnlyMyNetworks(vard) {
	
	var button = vard.elements.buttonShowOnlyMyNetworks;
	
	//Cancel submit form to server via POST wtih page reload
	event.preventDefault();
	
	//Reverse somn
	isPressedSONM = !isPressedSONM;
	
	//Send via post showOnlyMyNetworks flag and get new table
	$.get(tableUrl, {"somn" : isPressedSONM}, function (data) {
		
		//Reload table
		$(tableDivID).html(data);
		
		//Color status
		colorStatus();
		
		//Change button value
		button.value = isPressedSONM === true ? "Show all networks" : "Show only my networks";
	});
	
}

function ajaxGetPage(page) {
	
	//Cancel submit form to server via POST wtih page reload
	event.preventDefault();
	
	//Send via post showOnlyMyNetworks flag and get new table
	$.get(tableUrl, {"page": page, "somn" : isPressedSONM}, function (data) {
			drawTable(data);
		colorStatus();
		console.log(isPressedSONM);
	}, "json");

}

function colorStatus() {
	
	//Change class for status
	$(".status").each(function () {
	
	if ($(this).text() === "SUCCESS") {
		$(this).addClass("alert");
		$(this).addClass("alert-success");
	}

	if ($(this).text() === "IN QUEUE") {
		$(this).addClass("alert");
		$(this).addClass("alert-info");
	}

	if ($(this).text() === "FAILED") {
		$(this).addClass("alert");
		$(this).addClass("alert-danger");
	}

	if ($(this).text() === "IN PROGRESS") {
		$(this).addClass("alert");
		$(this).addClass("alert-warning");
	}
});
}

function drawTable(data) {
	$(ajaxTableDivID).html(
						 
						 '<div class="panel panel-default">' +
							'<table class="table table-striped table-bordered table-nonfluid " id="taskTable">' +
							'<tbody>' +
								'<tr>' +
									'<th>#</th>' +
									'<th>Type</th>' +
									'<th>MAC</th>' +
									'<th>Task name</th>' +
									'<th>Net name</th>' +
									'<th>Key</th>' +
									'<th>Files</th>' +
									'<!-- <th>Agents</th> for better days -->' +
									'<th>Status</th>' +
								'</tr>'
		
						 );
	
	var id = 1;
	data.forEach(function(element, index, array) {
		$(ajaxTableID + " > tbody:last-child").append('<tr><td><strong>' + id + '</strong></td><td>' + getTypeByID(element.type) + '</td><td>' + element.station_mac + '</td><td>' + element.task_name + '</td><td>' + element.essid + '</td><td>net_key</td><td><a href="' + element.site_path + '" class="btn btn-default"><span class="glyphicon glyphicon-download"></span></a><td class="status">' + getStatusByID(element.status) + '</td></tr>');
		id++;
	});
}

function getTypeByID(id) {
	switch(id) {
		case "0":
			return "HANDSHAKE";
		case "1":
			return "NTLM";
	}
}

function getStatusByID(id) {
	switch(id) {
		case "0":
			return "IN QUEUE";
		case "1":
			return "IN PROGRESS";
		case "2":
			return "SUCCESS";
		case "3":
			return "FAILED";
	}
}

//After page fully loaded
$(function () {
	
	//Load and draw table
	loadTable();
	
	//Draw pagger
	loadPagger();

	//Setup autoreload button
	$(buttonAutoReloadID).click(function () {
		if (isAutoReload === true) {

			//If button was pressed, delete timer
			clearInterval(intervalID);

			//Change value
			$(buttonAutoReloadID).val("Turn on auto-reload");
			isAutoReload = false;
		} else {

			//Time in ms
			var timer = 1000;

			//Set timer
			intervalID = setInterval(
				function () {
					loadTable();
				}, timer);

			//Change value
			$(buttonAutoReloadID).val("Turn off auto-reload");
			isAutoReload = true;
		}
	});
});
