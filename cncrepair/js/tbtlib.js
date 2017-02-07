// GLOBAL popup dimensions to maintain continunity.
var POPUP_DIMENSIONS = "height=325, width=650";

// Implemented a trim function
var trim = function(stt) {
	if (!stt)
		stt = '';
	return stt.replace(/^\s+|\s+$/g,"");
}

// Validates form data or borders the box with red when invalid
var validate = function(id, type) {
	var f = document.getElementById("add_form");
	var valid = true;
	if (type && type == "num") {
		curr = f[id];
		if (((curr.value.length - curr.value.indexOf(".")) > 3)
			|| (curr.value.indexOf(".") != curr.value.lastIndexOf("."))
			|| (curr.value.replace(/[0-9\.]/g, "") != '')){
			curr.parentNode.style.border = "1px solid red";
			valid = false;
		} else {
			curr.parentNode.style.border = "none";
		}
	} else {
		if (trim(f[id].value) == "") {
			f[id].parentNode.style.border = "1px solid red";
			f[id].focus();
			valid = false;
		} else {
			f[id].parentNode.style.border = "none";
		}
	}
	return valid;
}

// Updates the form with data from an AJAX query
// Takes form, newData as nd, and Special Additions
var updateForm = function(form, newData, sa) {
	if (!sa) sa = "";
	for (var i in newData) {
		var j = form[i+sa];
		if (j)
			j.value = newData[i];
	}
}

// Collects data from the form and combines it into a POST string
var formData = function(f) {
	var d = '';
	for (var i = 0; i < f.elements.length; i++) {
		d += f.elements[i].id+"="+encodeURIComponent(trim(f.elements[i].value))+"&";
	}
	if (typeof window.opera !== "undefined") {
		return d.replace(/\%0D/g, "");
	} else {
		return d;
	}
}