// Collects and sends info from page; displays server response.
var savePos = function(pos) {
	form = document.getElementById("add_form");
	
	var rte = "";
	for (var i = 0; i < form.route.length; i++) {
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

// Displays server response after a save request.
var saveResult = function(xhr, response, status) {
	if (confirm(response+"\n\nContinue with another item?")) {
		document.getElementById("add_form").reset();
		var pos = window.location.href.split("/")[4];
		window.location.href = "/positions/"+pos;
	}
}

// Pops up the queue list for a particular position
var viewQueued = function(pos) {
	newwindow = window.open("/positions/viewQueued/"+pos, "", POPUP_DIMENSIONS);
	if (window.focus) newwindow.focus();
}

// Verifies data on the page before saving.
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
	
	// Position specific checks:
	
	if (pos != "shipping") {
		valid = (validate("shelf") && valid);
		valid = (validate("rack") && valid);
	}
	
	if (pos == "receiving") {
		if (!validate("invoiceitem_id")) {
			alert("You cannot save without an invoice chosen.");
			valid = false;
		}
	} else if (pos == "shipping") {
		// if (!validate("ship_inv_id")) {
			// alert("You cannot save without an invoice chosen.");
			// valid = false;
		// }
	} else if (pos == "unpacking") {
		valid = (validate("serial") && valid);
		valid = (validate("name") && valid);
		var mn = document.getElementById("incomingTypes").value.split("|");
		var itid = document.getElementById("itemtype_id").value;
		if (itid != "0" && itid != mn[0]) {
			alert("That serial number is on record for another part number.");
			valid = false;
		}
	}
	
	if (form["barcode"]) { // For a normal barcode input
		valid = (validate("barcode") && valid);
	} else { // For assembly barcode inputs
		for (var i = Number(document.getElementById("currRow").value)-1; i >=0; i--)
			valid = (validate("barcode"+i) && valid);
	}
	
	return valid;
}

// Pops up history or notes or procedures for a partiular item
var view = function(pType) {
	if (pType == "history" || pType == "oldnotes") {
		var id = document.getElementById("item_id").value;
		var url = "/item/view/"+id+"/"+pType;
	} else {
		var id = document.getElementById("itemtype_id").value;
		var url = "/item/view/"+id+"/"+pType+"/itemtypes";
	}
	newwindow = window.open(url, "", POPUP_DIMENSIONS);
	if (window.focus) newwindow.focus();
	return false;
}

// Updates text status and priority based on slider change.
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

// Used for unpacking position; gets the expected incoming parts based on customer selection
var getIncoming = function() {
	if (validate("name")) {
		httpObj({
			url: "/positions/unpacking/incoming",
			complete: fillIncoming,
			data: "name="+encodeURIComponent(trim(document.getElementById("name").value))
		});
	}
}

// Used for unpacking; global var for holding the descriptions of the items in the itemtype select
var descriptions = new Array();

// Used for unpacking; Updates description and other fields when select is changed.
var updateModel = function() {
	sel = document.getElementById("incomingTypes");
	var stats = sel.value.split("|");
	document.getElementById("itemtype_id").value = stats[0];
	document.getElementById("quoteitem_id").value = stats[1];
	document.getElementById("description").value = descriptions[stats[0]];
}

// Used for unpacking; updates modelnum select
var fillIncoming = function(xhr, response, status) {
	// alert(response);
	form = document.getElementById("add_form");
	newData = eval("("+response+")");
	var sel = document.getElementById("incomingTypes");
	for (var i in sel.options)
		sel.options[i] = null;
	for (var i in newData) {
		var nbsp = (newData[i]["print"] == "subitem") ? "\u00A0\u00A0\u00A0" : "";
		sel.options[i] = new Option(nbsp+newData[i]["modelnum"], newData[i]["itemtype_id"]+"|"+newData[i]["quoteitem_id"]);
		if (newData[i]["print"] == "assembly")
			sel.options[i].disabled = true;
		descriptions[newData[i]["itemtype_id"]] = newData[i]["description"];
	}
	sel.removeAttribute("disabled");
	updateModel();
}

// Used in receiving/shipping; lists the invoices for a particular item barcode scanned.
var invList = function(xhr, response, status) {
	// alert(response);
	newData = eval("("+response+")");
	document.getElementById(newData["pos"]+"List").innerHTML = "";
	if (newData["invoices"].length == 0) {
		var tr = document.createElement("tr");
		var td = document.createElement("td");
		td.setAttribute("colspan", "6");
		td.innerHTML = "There are no open invoices for this particular part number.";
		tr.appendChild(td);
		document.getElementById(newData["pos"]+"List").appendChild(tr);
	}
	var k = 0;
	// Grab the current (shipping)invoice# to display currently selected
	var flavor = (newData["pos"] == "receiving") ? "invoiceitem_id" : "ship_inv_id";
	var iid = document.getElementById(flavor).value;
	flavor = (newData["pos"] == "receiving") ? "invoiceitem_id" : "inv_id";
	for (var r in newData["invoices"]) {
		var tr = document.createElement("tr");
		if (iid != "" && iid == newData["invoices"][r][flavor]) {
			tr.id = "selected";
		}
		tr.className = "r"+(k++%2);
		tr.onclick = invNum;
		var newCell = new Array(6);
		for (var j = 0; j < newCell.length; j++)
			newCell[j] = document.createElement("td");
		newCell[0].innerHTML = '<input type="hidden" id="invoiceitem_id'+r+'" name="invoiceitem_id'+r+'" value="'+newData["invoices"][r]["invoiceitem_id"]+'" />'+
			'<span id="invoiceNumber'+r+'">'+newData["invoices"][r]["inv_id"]+"</span>";
		newCell[1].innerHTML = newData["invoices"][r]["type"];
		newCell[2].id = "qtyremaining"+r;
		newCell[2].innerHTML = newData["invoices"][r]["qtyremaining"];
		newCell[3].innerHTML = newData["invoices"][r]["billto"];
		newCell[4].innerHTML = newData["invoices"][r]["created"];
		newCell[5].innerHTML = newData["invoices"][r]["shipname"];
		for (j = 0; j < newCell.length; j++)
			tr.appendChild(newCell[j]);
		document.getElementById(newData["pos"]+"List").appendChild(tr);
	}
}

// Creates the appropriate rows in the assembly table based on assembly type
var fillAssem = function(xhr, response, status) {
	// alert(response);
	resetRows();
	if (response != "") {
		var newData = eval("("+response+")");
		var items = newData["items"];
		var k = 1;
		var pos = document.getElementById("pos").value;
		if (newData["items"]) {
			var barcode0 = document.getElementById("barcode0").value;
			var ship_inv_id = document.getElementById("ship_inv_id").value;
			var message = "";
			for (var i = 0; i < items.length; i++) {
				var qty = (typeof items[i]["quantity"] != "undefined") ? Number(items[i]["quantity"]) : 1;
				for (var j = 0; j < qty; j++) {
					var barcode = items[i]["barcode"];
					if (barcode != barcode0) {
						if (pos == "shipping") {
							// Check that all the ship_inv_ids match for each item
							if (items[i]["ship_inv_id"] != ship_inv_id){
								if (items[i]["ship_inv_id"] != null)
									message += barcode+" is promised to Invoice #"+items[i]["ship_inv_id"]+" not Invoice #"+ship_inv_id+".\n";
								else
									message += barcode+" is not promised to an invoice.\n";
							}
						}
						var newRow = document.createElement("tr");
							newRow.id = "editRow"+k;
						var newCell = Array(2);
						for (b = 0; b < newCell.length; b++)
							newCell[b] = document.createElement("td");
						newCell[0].innerHTML = ((k == 1) ? '<span class="term">Also with:<span class="note">Other items registered as being in an assembly with the above barcode.</span></span>' : "");
						var ship_inv = ((pos == "shipping") ? '<input type="hidden" id="ship_inv_id'+k+'" name="ship_inv_id'+k+'" value="'+items[i]["ship_inv_id"]+'" />' : '');
						newCell[1].innerHTML = '<input type="text" id="barcode'+k+'" name="barcode'+k+'" maxlength="63" size="31" value="'+barcode+'" disabled="disabled" />'+
							'<input type="hidden" id="item_id'+k+'" name="item_id'+k+'" value="'+items[i]["id"]+'" />'+
							'<input type="hidden" id="itemtype_id'+k+'" name="itemtype_id'+k+'" value="'+items[i]["itemtype_id"]+'" />'+
							ship_inv;
						for (a = 0; a < newCell.length; a++)
							newRow.appendChild(newCell[a]);
						var il = document.getElementById("barcodeList");
						il.appendChild(newRow);
						k++;
					}
				}
			}
			if (message != "") {
				alert(message+"Please resolve this issue before continuing.");
				document.getElementById("save").disabled = true;
			} else {
				document.getElementById("save").disabled = false;
			}
		}
		document.getElementById("currRow").value = k;
	}
}

// Resets the rows of the assembly barcode input list.
var resetRows = function() {
	for (var i = 1; i <= Number(document.getElementById("currRow").value); i++)
		removeRow(i);
	document.getElementById("currRow").value = 1;
}
var removeRow = function(i) {
	var c = document.getElementById("editRow"+i);
	if (c)
		c.parentNode.removeChild(c);
}

// Alters the chosen invoice number based on selection.
var invNum = function() {
	if (document.getElementById("selected"))
		document.getElementById("selected").removeAttribute("id");
	this.id = "selected";
	var pos = document.getElementById("pos").value;
	if (pos == "shipping") {
		// document.getElementById("ship_invoiceitem_id").value = this.firstChild.firstChild.value;
		// document.getElementById("ship_inv_id").value = this.firstChild.childNodes[1].firstChild.data;
	} else {
		document.getElementById("invoiceitem_id").value = this.firstChild.firstChild.value;
		document.getElementById("inv_id").value = this.firstChild.childNodes[1].firstChild.data;
	}
}

// Used to jump straight to a particular barcode (from a hard-link).
var goBarcode = function(barcode) {
	barcode = decodeURIComponent(barcode);
	var barIn = document.getElementById("barcode");
	if (barIn) {
		barIn.value = barcode;
		suggestBypass("barcode");
	} else {
		document.getElementById("barcode0").value = barcode;
		suggestBypass("barcode0");
	}
}

// Called after a suggestion for both customers and for items
var updateData = function(xhr, response, status) {
	// alert(response);
	if (response) {
		var form = document.getElementById("add_form");
		var newData = eval("("+response+")");
		if (newData["name"]) {
			form.cust_id.value = newData["id"];
			getIncoming();
		} else if (newData["serial"]) { // Data comes in via barcode or serial response
			// if it's the receiving page, update the list of possible invoice choices.
			var pos = document.getElementById("pos").value;
			if (pos == "receiving" || pos == "shipping") {
				if (pos == "receiving" && newData["invoiceitem_id"] > 0) {
					var go = confirm("This item has already been received!\n\nAre you SURE you want to continue?");
					if (!go) { window.location.reload(true); return; }
					form["invoiceitem_id"] = newData["invoiceitem_id"];
				}
				if (pos == "shipping" && newData["shipped"]) {
					var go = confirm("This item has already been shipped!\n\nAre you SURE you want to continue?");
					if (!go) { window.location.reload(true); return; }
					form["ship_inv_id"] = newData["ship_inv_id"];
				} else if (pos == "shipping" && !newData["ship_inv_id"]) {
					alert("This item is not promised to an invoice.\nPlease use the Invoice tool to do that.");
					return;
				}
				httpObj({
					url: "/positions/"+pos+"/invlist",
					complete: invList,
					data: "id="+newData["id"]
				});
			}
			
			if (form.notes) form.notes.value = '';
			if (form.history) form.history.value = '';
			if (form.shipnotes) form.shipnotes.value = '';
			if (form.viewProcs) form.viewProcs.disabled = false;
			if (form.viewOldNotes) form.viewOldNotes.disabled = false;
			if (form.viewHist) form.viewHist.disabled = false;
			
			form.item_id.value = newData["id"];
			form.itemtype_id.value = newData["itemtype_id"];
			if (form.stock) {
				form.stock.checked = (newData["stock"] == 1) ? true : false;
			}
			
			// barcode0 (instead of regular barcode) means the page is set up to handle assemblies.
			if (form["barcode0"]) {
				form.item_id0.value = newData["id"];
				form.itemtype_id0.value = newData["itemtype_id"];
				
				// Grab any assembly information
				httpObj({
					url: "/item/get/specificassem",
					complete: fillAssem,
					data: "item_id="+newData["id"]
				});
			}
			
			if (document.getElementById("status"))
				showStatus(newData["status"], "status");
			if (document.getElementById("priority"))
				showStatus(newData["priority"], "priority");
			updateForm(form, newData);
		}
	}
}