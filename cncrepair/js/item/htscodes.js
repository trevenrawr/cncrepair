/*
 *  Javascript used for the htscodes.php view
 *
 */

// Selects hts information and sends it to item window
var HtsSelect = function(row) {
	var index = row*3;
	window.opener.document.getElementById("htsview").value = document.forms[0].elements[index].value;
	window.opener.document.getElementById("hts").value = document.getElementById("row"+row).firstElementChild.innerHTML;
	window.close();
}

// Removes a row
var RemoveRow = function(row) {
	var r = document.getElementById("row"+row);
	if (r)
		r.parentNode.removeChild(r);
}

// Edits a row
var EditRow = function(row) {
	document.getElementById("htsview"+row).removeAttribute("readonly");
	document.getElementById("htsdescription"+row).removeAttribute("readonly");
}

// Not Implemented yet
// var AddRow = function(row) {
// 	newrow = document.createElement("tr");
// 	newrow.id = "row"+row;
// }

// Saves changes and updates DB.  Removed rows become null; all child references point to nothing.
// var SaveChanges function () {
//
// }