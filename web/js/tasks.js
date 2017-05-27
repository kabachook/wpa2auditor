"use strict";

var tasksPageURL = "?tasks";
//Auto-reload button
//is clicked
var tasksIsAutoReload = false;

//Timer id for cleaning
var tasksIntervalID;
var tasksButtonAutoReloadID = "#buttonTurnOnAutoRefresh";

//SOMN = ShowOnlyMyNetworks
//Flag, if button was pressed
var tasksIsPressedSONM = false;

//Configuration
var tasksBaseUrl = "content/tasks.php";
var tasksPrefix = "?ajax=";
var tasksTableUrl = tasksBaseUrl + tasksPrefix + "table";
var tasksPaggerUrl = tasksBaseUrl + tasksPrefix + "pagger";
var tasksRightSideBarUrl = tasksBaseUrl + tasksPrefix + "right";
var tasksTableDivID = "#ajaxLoadTable";
var tasksRightSideBarDivID = "ajaxLoadRightNavBar";

//Status
var tasksStatusHandshakeUrl = tasksBaseUrl + tasksPrefix + "statusHandshakeUpload";
var tasksStatusHashUrl = tasksBaseUrl + tasksPrefix + "statusHashUpload";

//uniq ids
var tasksUniqHashStatusDivID = "statusHashUpload";
var tasksUniqHandshakeStatusDivID = "statusFileUpload";

//Table IDs
var tasksTableUploadHandshakeID = "tableUploadHandshake";
var tasksTableUploadHashID = "tableUploadHash";

//Forms IDs
var tasksFormUploadNTLMHashID = "formUploadNTLMHash";
var tasksFormUploadHandshakeID = "formUploadHandshake";

//Buttons IDs
var tasksButtonShowOnlyMyNetworksID = "buttonShowOnlyMyNetworks";

//Table
var tasksAjaxTableDivID = "#ajaxTableDiv";
var tasksAjaxTableID = "#taskTable";

var tasksAjaxPaggerDivID = "#ajaxPagger";

var tasksFile;

//ID that tr with status will have
var tasksResult_status;

//ID table with status
var tasksResult_table;

//ID Form with status
var tasksResult_form;

class Task {

	//Load\reload table
	static loadTable() {
		$.get(tasksTableUrl, {
			"somn": tasksIsPressedSONM
		}, function (data) {
			Task.drawTable(data);
			Task.colorStatus();
		}, "json");
	}

	static colorStatus() {

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

	static drawTable(data) {

		var admin = data.admin;

		$(tasksAjaxTableDivID).html(

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
			(admin ? "<th>Admin</th>" : "") +
			'</tr>'

		);

		var id = 1;
		data.table.forEach(function (element, index, array) {

			var net_key = element.net_key === "0" ? '<input type="text" class="form-control wpaKeysTable" placeholder="Enter wpa key" name="' + element.id + '">' : "<strong>" + element.net_key + "</strong>";

			$(tasksAjaxTableID + " > tbody:last-child").append('<tr><td><strong>' + id + '</strong></td><td>' + Task.getTypeByID(element.type) + '</td><td>' + element.station_mac + '</td><td>' + element.task_name + '</td><td>' + element.essid + '</td><td>' + net_key + '</td><td><a href="' + element.site_path + '"><i class="fa fa-download fa-lg  "></i></a><td class="status">' + Task.getStatusByID(element.status) + '</td>' +
				(admin ? '<td><form action="" method="get" onSubmit="Task.ajaxDeleteTask(this);"><input type="hidden" name="deleteTaskID" value="' + element.id + '"><button type="submit" class="btn btn-secondary" name="deleteTask"><i class="fa fa-trash-o"></i></button></form></td>' : '') + '</tr>');
			id++;
		});
	}

	static getTypeByID(id) {
		switch (id) {
			case "0":
				return "HANDSHAKE";
			case "1":
				return "NTLM";
		}
	}

	static getStatusByID(id) {
		switch (id) {
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

	static loadPagger() {
		$.get(tasksPaggerUrl, function (data) {
			Task.drawPagger(data);
		}, "json");
	}

	static ajaxGetPage(page) {

		//Cancel submit form to server via POST wtih page reload
		event.preventDefault();

		//Send via post showOnlyMyNetworks flag and get new table
		$.get(tasksTableUrl, {
			"page": page,
			"somn": tasksIsPressedSONM
		}, function (data) {
			Task.drawTable(data);
			Task.colorStatus();
			console.log(tasksIsPressedSONM);
		}, "json");
	}

	static drawPagger(data) {
		var result = '<nav aria-label="Page navigation"><ul class="pagination">';

		data.forEach(function (element, index, array) {
			var arrow;
			if (index !== 0) {
				arrow = "&raquo;";
			} else {
				arrow = "&laquo;";
			}

			if (element.arrow === true) {

				if (element.active === true) {
					result += '<li class="page-item"><a class="page-link disabled" onClick="ajaxGetPage(' + element.page + ');" aria-label="Previous"><span aria-hidden="true">' + arrow + '</span><span class="sr-only">Previous</span></a></li>';
				} else {
					result += '<li class="page-item"><a class="page-link" onClick="ajaxGetPage(' + element.page + ');" aria-label="Previous"><span aria-hidden="true">' + arrow + '</span><span class="sr-only">Previous</span></a></li>';
				}

			} else {

				if (element.active === true) {
					result += '<li class="page-item"><a class="page-link disabled" onClick="ajaxGetPage(' + element.page + ');">' + element.page + '</a></li>';
				} else {
					result += '<li class="page-item"><a class="page-link" onClick="ajaxGetPage(' + element.page + ');">' + element.page + '</a></li>';
				}
			}
		});

		result += '</ul></nav>';
		$(tasksAjaxPaggerDivID).html(result);
	}

	//Delete_task button
	static ajaxDeleteTask(vard) {

		//Cancel submit form to server via POST wtih page reload
		event.preventDefault();

		//Get id task for delete from form
		var id = vard.elements.deleteTaskID.value;

		//Data to send
		var data = new FormData();
		data.append("deleteTask", true);
		data.append("deleteTaskID", id);

		jQuery.ajax({
			url: tasksBaseUrl, //page url
			type: "POST",
			data: data,
			processData: false, // Dont process the tasksFiles
			contentType: false, // string request
			success: function () { //Data send success
				Task.loadTable();
			},
			error: function () { // Data send failed
				console.log("Delete task error while sending via POST");
			}
		});
	}

	static ajaxSendForm(vard, type) {

		//Cancel submit form to server via POST wtih page reload
		event.preventDefault();

		//Data to send
		var data = new FormData();
		var url;

		if (type === "handshake") {

			tasksResult_status = tasksUniqHandshakeStatusDivID;
			tasksFile = vard.elements.uptasksFile.tasksFiles[0];

			data.append("uptasksFile", tasksFile);
			data.append("buttonUploadFile", true);
			data.append("task_name", vard.elements.task_name.value);

			url = tasksStatusHandshakeUrl;
			tasksResult_table = tasksTableUploadHandshakeID;
			tasksResult_form = tasksFormUploadHandshakeID;

		} else if (type === "ntlm") {

			tasksResult_status = tasksUniqHashStatusDivID;

			data.append('buttonUploadHash', true);
			data.append('task_name', vard.elements.taskname.value);
			data.append('username', vard.elements.username.value);
			data.append('challenge', vard.elements.challenge.value);
			data.append('response', vard.elements.response.value);

			url = tasksStatusHashUrl;
			tasksResult_table = tasksTableUploadHashID;
			tasksResult_form = tasksFormUploadNTLMHashID;
		}

		jQuery.ajax({
			url: url, //page url
			type: "POST",
			data: data,
			processData: false, // Dont process the tasksFile
			contentType: false, // string requset

			//On success upload
			success: function (response) {

				//Reset all inputs
				$("#" + tasksResult_form).get(0).reset();

				//Reload table
				Task.loadTable();

				//generate notify
				var json = $.parseJSON(response);
				Task.genNotify(json.type, json.message);

			},
			//Failed to send data
			error: function (response) {
				console.log("Error while sending hash\handshake. " + response);
			}
		});

	}

	static genNotify(type, message) {
		$.notify({
			// options
			icon: 'fa fa-exclamation-triangle',
			message: message,
		}, {
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

	static ajaxSendWPAKeys() {

		//Cancel submit form to server via POST wtih page reload
		event.preventDefault();

		//Data to send
		var data = new FormData();

		data.append("sendWPAKey", true);

		//For all forms with class wpaKeysTable get id and key
		$(".wpaKeysTable").each(function () {
			var item = $(this).serializeArray()[0];
			if (item.value !== '') {
				data.append(item.name, item.value);
			}
		});

		jQuery.ajax({
			url: tasksBaseUrl, //page url
			type: "POST",
			data: data,
			processData: false, // Dont process the tasksFile
			contentType: false, // string requset

			//On success upload
			success: function () {
				Task.loadTable();
			},

			//On error upload
			error: function (response) {
				console.log("Error while sending hash\handshake. " + response);
			}
		});
	}

	static showOnlyMyNetworks(vard) {

		var button = vard.elements.buttonShowOnlyMyNetworks;

		//Cancel submit form to server via POST wtih page reload
		event.preventDefault();

		//Reverse somn
		tasksIsPressedSONM = !tasksIsPressedSONM;

		//Send via post showOnlyMyNetworks flag and get new table
		$.get(tasksTableUrl, {
			"somn": tasksIsPressedSONM
		}, function (data) {

			Task.drawTable(data);
			Task.colorStatus();

			//Change button value
			button.value = tasksIsPressedSONM === true ? "Show all networks" : "Show only my networks";
		});

	}
}

//After page fully loaded
$(function () {

	if (document.URL.indexOf(tasksPageURL) > -1) {
		//Load and draw table
		Task.loadTable();

		//Draw pagger
		Task.loadPagger();

		//Setup autoreload button
		$(tasksButtonAutoReloadID).click(function () {
			if (tasksIsAutoReload === true) {

				//If button was pressed, delete timer
				clearInterval(tasksIntervalID);

				//Change value
				$(tasksButtonAutoReloadID).val("Turn on auto-reload");
				tasksIsAutoReload = false;
			} else {

				//Time in ms
				var timer = 1000;

				//Set timer
				tasksIntervalID = setInterval(
					function () {
						Task.loadTable();
					}, timer);

				//Change value
				$(tasksButtonAutoReloadID).val("Turn off auto-reload");
				tasksIsAutoReload = true;
			}
		});
	}
});