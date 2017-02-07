// Used to clear the form
var clearForm = function() {
	var form = document.getElementById("add_form");
	var resetList = ["modelnum", "make"];
	for (var i in resetList) {
		if (form.elements[resetList[i]])
			form.elements[resetList[i]].value = '';
	}
	form.elements[resetList[0]].focus();
}