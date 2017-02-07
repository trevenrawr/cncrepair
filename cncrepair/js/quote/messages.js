// Saves the messages on the pages and displays the response.
var saveMessages = function() {
	var form = document.getElementById("add_form");
	
	tinyMCE.triggerSave();
	
	httpObj({
		url: "/quote/savemessages",
		complete: function(xhr, response, status) {
			alert(response);
			window.location.reload(true);
		},
		data: formData(form)
	});
	
	return false;
}