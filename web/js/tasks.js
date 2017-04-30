//Chnage class for status
$(".status").each(function(){
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