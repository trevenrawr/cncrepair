// Saves the assembling position.
var savePos = function(pos) {
	form = document.getElementById("add_form");
	
	var rte = "";
	for (var i=0; i < form.route.length; i++) {
		if (form.route[i].checked) {
			rte = encodeURIComponent(form.route[i].value);
		}
	}
	var stck = "0";
	if (form.stock && form.stock.checked) {
		stck = "1";
	}
	var fd = formData(form)+"route="+rte+"&stock="+stck;
	// alert(fd);
	
	if (verifyData(form, pos)) {
		httpObj({
			url: "/positions/save/"+pos,
			complete: saveResult,
			data: fd
		});
	}
	return false;
}
// Displays the save response from the server.
var saveResult = function(xhr, response, status) {
	if (confirm(response+"\n\nContinue with another item?")) {
		document.getElementById("add_form").reset();
		var pos = window.location.href.split("/")[4];
		window.location.href = "/positions/"+pos;
	}
}

// Verifies the data prior to save submission.
var verifyData = function(form, pos) {
	var valid = true;

	// make sure a routing decision was made
	rt = Boolean(form.stay && form.stay.checked);
	rt0 = Boolean(form.route0 && form.route0.checked);
	rt1 = Boolean(form.route1 && form.route1.checked);
	if (!(rt || rt0 || rt1)) {
		document.getElementById("routing").style.border = "1px solid red";
		valid = false;
	} else {
		document.getElementById("routing").style.border = "1px dotted black";
	}
	
	if (pos != "shipping" ) {
		valid = (validate("shelf") && valid);
		valid = (validate("rack") && valid);
	}
	
	return valid;
}

var showStatus = function(val, id) {
	if (id == "status") {
		var valEnum = ["", "scrap", "salvageable", "needs work", "ready today", "ready NOW"];
		val = valEnum[val];
	} else {
		var valEnum = ["", "brown", "orange", "blue", "red"];
		val = valEnum[val];
		document.getElementById("txt"+id).style.backgroundColor = val;
	}
	document.getElementById("txt"+id).innerHTML = val;
}

// Removes an item from the assembly.
var itemRemove = function(row) {
	var form = document.getElementById("add_form");
	form["item_id"+row].value = "";
	form["barcode"+row].value = "";
	form["barcode"+row].disabled = false;
	form["barcode"+row].focus();
	document.getElementById("itemDel"+row).style.display = "none";
}

// Creates the appropriate rows in the assembly table based on assembly type
var listLines = function(items) {
	resetRows();
	var k = 0;
	for (var i = 0; i < items.length; i++) {
		var qty = (typeof items[i]["quantity"] != "undefined") ? Number(items[i]["quantity"]) : 1;
		for (var j = 0; j < qty; j++) {
			var barcode = (typeof items[i]["barcode"] != "undefined") ? items[i]["barcode"] : "";
			var newRow = document.createElement("tr");
				newRow.id = "editRow"+k;
			var newCell = Array(2);
			for (b = 0; b < newCell.length; b++)
				newCell[b] = document.createElement("td");
			newCell[0].innerHTML = '<label for="barcode'+k+'" id="modelnum'+k+'">'+items[i]["modelnum"]+'</label>'+
				'<input type="hidden" id="itemtype_id'+k+'" name="itemtype_id'+k+'" value="'+items[i]["id"]+'" />';
			newCell[1].innerHTML = '<input type="text" id="barcode'+k+'" name="barcode'+k+'" maxlength="63" size="31" value="'+barcode+'" />'+
				'<div class="relPos"><div id="barcode'+k+'Shadow" class="suggShadow"><div id="barcode'+k+'Box" class="suggBox"></div></div></div>'+
				'<input type="hidden" id="item_id'+k+'" name="item_id'+k+'" />'+
				'<a id="itemDel'+k+'" href="javascript:itemRemove('+k+');" class="imglink" style="display: none;">'+
				'<img src="/pics/delsmall.png" alt="X" /></a>';
			for (a = 0; a < newCell.length; a++)
				newRow.appendChild(newCell[a]);
			var il = document.getElementById("barcodeListing");
			il.appendChild(newRow);
			suggestInstall("barcode"+k);
			k++;
		}
	}
	if (k == 0) {
		document.getElementById("assembly").value = "";
		var newRow = document.createElement("tr");
			newRow.id = "editRow0";
		var newCell = document.createElement("td");
			newCell.setAttribute("colspan", "2");
			newCell.innerHTML = "Item is not registered in an assembly.";
		newRow.appendChild(newCell);
		document.getElementById("barcodeListing").appendChild(newRow);
		k = 1;
	} else {
		document.getElementById("barcode0").focus();
	}
	document.getElementById("currRow").value = k;
}

// Resets the rows of the assembly table.
var resetRows = function() {
	for (var i = 0; i <= Number(document.getElementById("currRow").value); i++)
		removeRow(i);
	document.getElementById("currRow").value = 0;
}
var removeRow = function(i) {
	var c = document.getElementById("editRow"+i);
	if (c)
		c.parentNode.removeChild(c);
}

// This fills the assembly rows with current barcode information
var fillAssem = function(xhr, response, status) {
	// alert("fillAssem\n"+response);
	if (response) {
		var form = document.getElementById("add_form");
		newData = eval("("+response+")");
		if (!newData["parent"]) {
			listLines("");
			return;
		}
		form["assembly"].value = newData["itemList"]["parent"]["modelnum"];
		form["parent_id"].value = newData["parent"];
		form["itemtype_id"].value = newData["itemList"]["parent"]["id"];
		form["barcode"].value = "";
		listLines(newData["itemList"]["items"]);
		for (var i = 0; i < newData["items"].length; i++) {
			for (var j = 0; j < Number(form["currRow"].value); j++) {
				var barInput = form["barcode"+j];
				if (newData["items"][i]["itemtype_id"] == form["itemtype_id"+j].value && barInput.value == "") {
					barInput.value = newData["items"][i]["barcode"];
					setRow(form, j, newData["items"][i]["id"]);
					break;
				}
			}
			
		}
	}
}

// Used to disable a barcode input after an item is chosen to "lock it in"
var setRow = function(form, row, id, itemtype) {
	for (var i = 0; i < Number(document.getElementById("currRow").value); i++) {
		if (id == form["item_id"+i].value) {
			alert("That item already exists in this assembly.");
			form["barcode"+row].value = "";
			form["barcode"+row].focus();
			return;
		}
	}
	form["item_id"+row].value = id;
	form["barcode"+row].disabled = true;
	document.getElementById("itemDel"+row).style.display = "inline";
}

// After the selection of a particular assembly type or barcode (for existing assemblies)
var updateData = function(xhr, response, status) {
	// alert(response);
	if (response) {
		var form = document.getElementById("add_form");
		var newData = eval("("+response+")");
		if (newData["items"]) {
			form["barcode"].value = "";
			form["itemtype_id"].value = newData["parent"]["id"];
			listLines(newData["items"]);
		} else if (newData["serial"] && !newData["row"]) { // A barcode was scanned instead of a modelnum inserted
			httpObj({
				url: "/item/get/specificassem",
				complete: fillAssem,
				data: "item_id="+newData["id"]
			});
		} else if (newData["barcode"]) { // A sub item was scanned
			if (form["itemtype_id"+newData["row"]].value != newData["itemtype_id"]) {
				alert("Scanned item is not on record as a "+document.getElementById("modelnum"+newData["row"]).innerHTML+".");
				form["barcode"+newData["row"]].value = "";
			} else {
				setRow(form, newData["row"], newData["id"]);
			}
		}
	}
}