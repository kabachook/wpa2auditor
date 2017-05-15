"use strict";

//Auto-reload button
//is clicked
var isAutoReload = false;
//Timer id for cleaning
var intervalID;
var buttonAutoReloadID = "#buttonTurnOnAutoRefresh";

//Configuration
var baseUrl = "content/tasks.php";
var prefix = "?ajax=";
var tableUrl = baseUrl + prefix + "table";
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

//Reload table
function loadTable() {
	$.get(tableUrl, function (data) {
		$(tableDivID).html(data);
	});
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
			if ($("tr").is("#" + result_status)) {

				//Change status
				$("#" + result_status).html(response);
			} else {

				//if doesn't exists add it to table
				$("#" + result_table + " > tbody:last-child").append("<tr id='" + result_status + "'>" + response + "</tr>");
			}
			
			//Reset all inputs
			$("#" + result_form).get(0).reset();
			
			//Reload table
			loadTable();
		},
		
		//Failed to send data
		error: function (response) {
			console.log("Error while sending hash\handshake. " + response);
		}
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
		error: function (response) { // Данные не отправлены
			console.log("Error while sending hash\handshake. " + response);
		}
	});
}

function showOnlyMyNetworks() {
	
	//Cancel submit form to server via POST wtih page reload
	event.preventDefault();
	
	//Send via post showOnlyMyNetworks flag and get new table
	$.post(tableUrl, {"showOnlyMyNetworks": true}, function (data) {
				$(tableDivID).html(data);
	});

}

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

//After page fully loaded
$(function () {

	//Load table
	loadTable();

	//load right upload bar
	$.get(rightSideBarUrl, function (data) {
		$("#" + rightSideBarDivID).html(data);
	});

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
