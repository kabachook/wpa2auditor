//AJAX bind on button for tasks.php
var isAutoReload = false;
var intervalID;

//Configuration
var ajaxUrl = "content/tasks.php?ajax=table";
var ajaxButtonLoadId = "#buttonTurnOnAutoRefresh";
var ajaxDivID = "#ajaxLoadTable";

function delTask(vard) {
	event.preventDefault();
	var id = vard.elements.deleteTaskID.value;
	jQuery.ajax({
		url: "content/tasks.php", //url страницы (action_ajax_form.php)
		type: "POST", //метод отправки
		data: JSON.stringify({"deleteTask" : true, "deleteTaskID" : id}),
		processData: false, // Не обрабатываем файлы (Don't process the files)
		contentType: false, // Так jQuery скажет серверу что это строковой запрос
		success: function (response) { //Данные отправлены успешно
			$.get("content/tasks.php?ajax=table", function (data) {
				$(ajaxDivID).html(data);
			});
		},
		error: function (response) { // Данные не отправлены
			console.log("Ошибка. Данные не отправленны.");
		}
	});
}

function sendHashAjax(vard) {
	event.preventDefault();
	data ={};
	data['buttonUploadHash'] = true;
	data['taskname'] = vard.elements.taskname.value;
	data['username'] = vard.elements.username.value;
	data['challenge'] = vard.elements.challenge.value;
	data['response'] = vard.elements.response.value;
	jQuery.ajax({
		url: "content/tasks.php?ajax=statusHash", //url страницы (action_ajax_form.php)
		type: "POST", //метод отправки
		data: JSON.stringify(data),
		//dataType: 'json',
		processData: false, // Не обрабатываем файлы (Don't process the files)
		contentType: false, // Так jQuery скажет серверу что это строковой запрос
		success: function (response) { //Данные отправлены успешно
			$("#tableUploadHash > tbody:last-child").append("<tr>" + response + "</tr>");
			//document.getElementById(result_form).innerHTML = "<td>" + response + "</td>";
			//$("#" + result_form_global).html(response);
			$.get("content/tasks.php?ajax=table", function (data) {
				$(ajaxDivID).html(data);
			});
		},
		error: function (response) { // Данные не отправлены
			console.log("Ошибка. Данные не отправленны.");
		}
	});
}

$(function () {
	$(ajaxButtonLoadId).click(function () {
		if (isAutoReload === true) {
			clearInterval(intervalID);
			$(ajaxButtonLoadId).val("Turn on auto-reload");
			isAutoReload = false;
		} else {
			intervalID = setInterval(
				function () {
					$.get(ajaxUrl, function (data) {
						$(ajaxDivID).html(data);
					});
				}, 1000);
			$(ajaxButtonLoadId).val("Turn off auto-reload");
			isAutoReload = true;
		}
	});

	//First, load all page
	//table
	$.get("content/tasks.php?ajax=table", function (data) {
		$(ajaxDivID).html(data);
	});
	//right bar
	$.get("content/tasks.php?ajax=right", function (data) {
		$("#ajaxLoadRightNavBar").html(data);
	});


	$("#buttonUploadFile").click(
		function () {
			sendAjaxForm('status_file_uploading', 'formUploadHandshake', 'content/tasks.php?ajax=statusHandshake');
		});
	$("#buttonUploadHash").click(
		function () {
			sendAjaxForm('status_hash_uploading', 'formUploadNTLMHash', 'content/tasks.php?ajax=statusHash');
		});

});

var file;
var result_form_global;

function sendAjaxForm(result_form, ajax_form, url,  vard) {
	event.preventDefault();
	var data = new FormData();
	result_form_global = result_form;
	data.append("upfile", file);
	data.append("buttonUploadFile", true);
	data.append("task_name",  vard.elements.task_name.value);
	jQuery.ajax({
		url: url, //url страницы (action_ajax_form.php)
		type: "POST", //метод отправки
		data: data,
		//dataType: 'json',
		processData: false, // Не обрабатываем файлы (Don't process the files)
		contentType: false, // Так jQuery скажет серверу что это строковой запрос
		success: function (response) { //Данные отправлены успешно
			$("#tableUploadHandshake > tbody:last-child").append("<tr>" + response + "</tr>");
			//document.getElementById(result_form).innerHTML = "<td>" + response + "</td>";
			//$("#" + result_form_global).html(response);
			console.log($("#" + result_form).html);
			$.get("content/tasks.php?ajax=table", function (data) {
				$(ajaxDivID).html(data);
			});
		},
		error: function (response) { // Данные не отправлены
			document.getElementById(result_form).innerHTML = "Ошибка. Данные не отправленны.";
		}
	});

}



function sendWpaKeysAJax() {
	//var data2 = {"wpaKeys" : $("#wpaKeysTable").val()};
	var array = {
		"sendWPAkey": 1
	};
	$(".wpaKeysTable").each(function () {
		var item = $(this).serializeArray()[0];
		//console.log($(this).serializeArray());
		array[item['name']] = item['value'];
		console.log(array);
	});
	/*$(".wpaKeysTable").each.serializeArray().forEach(function(item, arr){
		console.log(item);
		array[item['name']] = item['value'];
		console.log(array);
	});*/
	jQuery.ajax({
		url: "content/tasks.php", //url страницы (action_ajax_form.php)
		type: "POST", //метод отправки
		data: JSON.stringify(array),
		processData: false, // Не обрабатываем файлы (Don't process the files)
		contentType: false, // Так jQuery скажет серверу что это строковой запрос
		success: function (response) { //Данные отправлены успешно
			$.get("content/tasks.php?ajax=table", function (data) {
				$(ajaxDivID).html(data);
			});
		},
		error: function (response) { // Данные не отправлены
			console.log("Ошибка. Данные не отправленны.");
		}
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
