"use strict";

var agentsPageURL = "?agents";

//Configuration
var agentsBaseUrl = "content/agents.php";
var agentsPrefix = "?ajax=";
var agentsTableUrl = agentsBaseUrl + agentsPrefix + "table";
var agentsPaggerUrl = agentsBaseUrl + agentsPrefix + "pagger";

//Draw table
//DIV
var agentsAjaxTableDivID = "#ajaxTableDiv";
//TABLE
var agentsAjaxTableID = "#agentsTable";

class Agents {

	//Load\reload table
	static loadTable() {
		$.get(agentsTableUrl, function (data) {
			Agents.drawTable(data);
			Agents.colorStatus();
		}, "json");
	}
	
	static colorStatus() {
		
	}
	
	static drawTable(data) {

		$(agentsAjaxTableDivID).html(

			'<div class="panel panel-default">' +
			'<table class="table table-striped table-bordered table-nonfluid " id="agentsTable">' +
			'<tbody>' +
			'<tr>' +
			'<th>#</th>' +
			'<th>Nick</th>' +
			'<th>OS</th>' +
			'<th>Perfomance</th>' +
			'<th>Status</th>' +
			'<th>Last seen</th>' +
			'</tr>'
			
		);

		var id = 1;
		data.forEach(function (element, index, array) {

			$(agentsAjaxTableID + " > tbody:last-child").append('<tr><td><strong>' + id + '</strong></td><td>' + element.nick + '</td><td>' + element.os + '</td><td>' + element.perf + '</td><td>' + element.status + '</td><td>' + element.ts + '</td>' + '</tr>');
			
			id++;
		});
	}
	
}

//After page fully loaded
$(function () {

	if (document.URL.indexOf(agentsPageURL) > -1) {

		//Load and draw table
		Agents.loadTable();

		/*//Draw pagger
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
		});*/

	}
});
