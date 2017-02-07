// Unconventionally-named "docReady" function
var onReady = function() {
	resetRows();
	itemtype_id = window.opener.document.getElementById("itemtype_id").value
	document.getElementById("itemtype_id").value = itemtype_id;
	httpObj({
		url: "/item/get/assem",
		complete: updateData,
		data: "id="+itemtype_id
	});
}

// Saves the assembly, whether or not the itemtype is saved.
var editAssem = function(target) {
	form = document.getElementById("add_form");
	if (verifyData(form)) {
		httpObj({
			url: "/item/save/assem",
			complete: function(xhr, response, status) {
				alert(response);
				if (response == "Assembly saved successfully.")
					window.close();
			},
			data: formData(form)
		});
	}
	return false;
}

// Verifies the form data prior to submission.
var verifyData = function(form) {
	var valid = true;
	if (!validate("itemtype_id")) {
		alert("You must save the master item before you can edit assembly items.");
		valid = false;
	}
	if (valid && numRows() < 2) {
		alert("You must add at least one item");
		valid = false;
	}
	return valid;
}

// Counts the number of items listed.
var numRows = function() {
	var cnt = 0;
	for (i = 0; i < Number(document.getElementById("currRow").value); i++)
		if (document.getElementById("editRow"+i)) cnt++;
	return cnt;
}

// Adds a row to the page for further subitem insertion.
var addRow = function() {
	var i = Number(document.getElementById("currRow").value);
	var lastDel = document.getElementById("itemDel"+(i-1));
		if (lastDel)
			lastDel.style.display = "inline";
	var newRow = document.createElement("tr");
		newRow.id = "editRow"+i;
	var newCell = new Array(4);
	for (j = 0; j < newCell.length; j++)
		newCell[j] = document.createElement("td");
	tarows = document.getElementById("tarows").value;
	newCell[0].innerHTML = '<input type="text" id="modelnum'+i+'" name="modelnum'+i+'" maxlength="63" size="25" />'+
		'<div class="relPos"><div id="modelnum'+i+'Shadow" class="suggShadow"><div id="modelnum'+i+'Box" class="suggBox"></div></div></div>';
	newCell[1].innerHTML = '<textarea type="text" id="description'+i+'" name="description'+i+'" cols="40" rows="'+tarows+'"></textarea>';
	newCell[2].innerHTML = '<input type="text" id="quantity'+i+'" name="quantity'+i+'" size="2" value="1" />';
	newCell[3].innerHTML = '<input type="hidden" id="itemtype_id'+i+'" name="itemtype_id'+i+'" />'+
		'<a id="itemDel'+i+'" href="#" onclick="removeRow('+i+');" class="imglink" style="display:none;">'+
		'<img src="/pics/delsmall.png" alt="X" /></a>';
	for (j = 0; j < newCell.length; j++)
		newRow.appendChild(newCell[j]);
	var il = document.getElementById("editAssemBody");
	il.appendChild(newRow);
	suggestInstall("modelnum"+i);
	document.getElementById("currRow").value = i+1;
}

// Resets the rows to their default state.
var resetRows = function() {
	for (i = 0; i <= Number(document.getElementById("currRow").value); i++)
		removeRow(i);
	document.getElementById("currRow").value = 0;
	addRow();
}
// Removes a row, if it exists.
var removeRow = function(i) {
	var c = document.getElementById("editRow"+i);
	if (c)
		c.parentNode.removeChild(c);
}

// Doesn't do anything special.
var gracefulClose = function() {
	if (false) {
		var form = window.opener.document.forms.add_form.assembly.checked = false;
	}
	window.close();
}

// Updates the form after a modelnum suggestion is selected.
var updateData = function(xhr, response, status) {
	form = document.getElementById("add_form");
//	alert(response);
	newData = eval("("+response+")");
	if (newData["modelnum"]) {
		rowNum = newData["row"];
		for (var i = 0; i < Number(document.getElementById("currRow").value); i++) {
			if (document.getElementById("itemtype_id"+i) && document.getElementById("itemtype_id"+i).value == newData["id"]) {
				alert("An item of this type already exists.  Please use quantity column.");
				document.getElementById("modelnum"+rowNum).value = "";
				return false;
			}
		}
		if (rowNum == Number(document.getElementById("currRow").value)-1) addRow();
		updateForm(form, newData, rowNum);
		document.getElementById("itemtype_id"+rowNum).value = newData["id"];
	} else if (newData["items"]) {
		for (i = 0; i < newData["items"].length; i++)
			addRow();
		for (i = 0; i < newData["items"].length; i++) {
			form["modelnum"+i].value = newData["items"][i]["modelnum"];
			form["description"+i].value = newData["items"][i]["description"];
			form["itemtype_id"+i].value = newData["items"][i]["id"];
			form["quantity"+i].value = newData["items"][i]["quantity"];
		}
	}
}