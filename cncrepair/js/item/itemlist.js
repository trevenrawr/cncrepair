// Pops up the notes viewer.
var view = function(id) {
	newwindow = window.open("/item/view/"+id, "Notes Viewer", POPUP_DIMENSIONS);
	if (window.focus) newwindow.focus();
	return false;
}

// Pops up the editor for procedures on the Item Management page.
var edit = function(type) {
	newwindow = window.open("/item/edit/"+type, "Notes Editor", POPUP_DIMENSIONS);
	if (window.focus) newwindow.focus();
	return false;
}

// Primes document for use
var docReady = function(type) {
	// Write the notes from the parent's hidden input to the text area
	cnotes = window.opener.document.getElementById(type).value;
	if (cnotes != "")
		document.getElementById("editNotes").value = cnotes;
}

// Saves the procedures to the item management page
var save = function(type) {
	var notes = document.getElementById("editNotes").value;
	window.opener.document.getElementById(type).value = notes;
	window.close();
	return false;
}

// Clears the itemlist filters.
var clearForm = function() {
	var form = document.getElementById("add_form");
	var resetList = ["modelnum", "priority", "barcode", "status", "owner", "name", "inv_id"];
	for (var i in resetList) {
		if (form.elements[resetList[i]])
			form.elements[resetList[i]].value = '';
	}
	form.elements[resetList[0]].focus();
}