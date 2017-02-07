// holds 'inv' or 'quote' for global reference.
var vt = "";

// Called to prime the document.
var docReady = function(qi) {
	vt = qi;
	resetRows();
	suggestInstall("name");
	updateQty();
}

// Used to determine whether or not addQuote should display the save result
// Set to false for PDF, email, or invoicing
var makeSure = true;
// Saves the quote/invoices and displays the server response
var addQuote = function(target, fn) {
	var form = document.getElementById("add_form");

	updateQty();
	if (verifyData()) {
		httpObj({
			url: target,
			complete: function(xhr, response, status) {
				var refNum = response.replace(/[^0-9]/gi, "");
				if (makeSure) {
					vtt = (vt == "inv") ? "invoice" : "quote";
					var mess = response.replace(/[0-9]/g, "");
					if (!confirm(mess+"\n\nClick 'Cancel' to clear form, 'OK' to retain."))
						window.location.href = "/"+vtt;
					else
						window.location.href = "/"+vtt+"/index/"+refNum;
				} else {
					makeSure = true;
					fn(refNum);
				}
			},
			data: formData(form)
		});

		// Make sure that the customer is up to date in the QB database.
		httpObj({
			url: "/customer/queueAddCustomer/"+form["cust_id"].value,
			complete: function(xhr, response, status) {},
			data: ""
		});
		// Make sure that the items are all up to date as well.
// 		for (var i = 0; i <= Number(form["currRow"].value); i++) {
// 			if (document.getElementById("itemRow"+i) && form["print"+i].value != "subitem" && form["modelnum"+i].value != "") {
// 				httpObj({
// 					url: "/item/queueAddItem/"+form["itemtype_id"+i].value,
// 					complete: function(xhr, response, status) {},
// 					data: ""
// 				});
// 			}
// 		}
	}
	return false;
}

// Remembers whether the user wanted the PDF printed or emailed.
var task = "print";
// Saves the quote/invoice and prints the PDF.
var printPDF = function(id, operation) {
	task = operation;
	makeSure = false;
	var vtt = (vt == "quote") ? "quote" : "invoice";
	addQuote('/'+vtt+'/add', PDF);
}
// Prints the quote/invoice to PDF and then either emails it or pops it up as a download.
var PDF = function(id) {
	var vtt = (vt == "quote") ? "quote" : "invoice";
	var form = document.getElementById("add_form");
	if (vt == "quote") {
		var exch = false;
		var repair = false;
		for (var i = 0; i <= Number(form["currRow"].value); i++) {
			if (form["type"+i] && form["itemtype_id"+i].value != "") {
				var type = form["type"+i].value;
				if (type == "exch")
					exch = true;
				if (type == "repair")
					repair = true;
			}
		}
		var message = "quotesale";
		if (repair) message = "quoterepair";
		if (exch) message = "quoteexch";
	} else {
		var message = form.elements["printType"].value;
	}
	var newWindow = window.open("/"+vtt+"/"+task+"PDF/"+id+"/"+message, "Print or Send!", POPUP_DIMENSIONS);
	window.location.href = "/"+vtt+"/index/"+id;
}

// Saves and creates an invoice from the current quote.
var makeInv = function() {
	makeSure = false;
	var vtt = (vt == "quote") ? "quote" : "invoice";
	addQuote('/'+vtt+'/add', invoice);
}
// Makes the call to create an invoice after quote is saved.
var invoice = function(id) {
	httpObj({
		url: "/invoice/create",
		complete: makeInvResult,
		data: "qid="+id
	});
}

var makeInvResult = function(xhr, response, status) {
	// alert(response);
	nd = eval("("+response+")");
	if (nd["ok"] == 0) {
		alert(nd["message"]);
	} else {
		if (confirm(nd["message"]+"\n\nContinue to the Invoice Screen?"))
			window.location.href = nd["target"];
	}
}

// Deletes the quote/invoice (with confirmation)
var deleteQuote = function() {
	form = document.getElementById("add_form");
	var vtt = (vt == "inv") ? "invoice" : "quote";
	var sure = confirm("Are you sure you want to delete this "+vtt+"?\nYou CANNOT undelete it.");
	if (sure) {
		id = form[vt+"_id"].value;
		httpObj({
			url: "/"+vtt+"/delete",
			complete: function(xhr, response, status) {
				alert(response);
				window.location.href = "/"+vtt;
			},
			data: "id="+id
		});
	}
}

// Resets the quote/invoiceitem rows.
var resetRows = function() {
	for (var i = 0; i <= Number(document.getElementById("currRow").value); i++)
		removeRow(i);
	document.getElementById("currRow").value = 0;
	document.getElementById("itemsDel").value = "";
	addRow();
}

var newwindow = null;

// Pops up a customer add/edit box.
var editCustomer = function() {
	var cust_name = document.getElementById("name").value;
	cust_name = encodeURIComponent(cust_name);
	newwindow = window.open("/customer/nomenu/"+cust_name, "Add/Edit Customer", "height=630, width=1050");
}


// Takes care of the popup's closing
var popupComplete = function(custName) {
// 	alert("boo");
	document.getElementById("name").value = custName;
	suggestBypass("name");
}

// Checks quote/invoice data prior to saving
var verifyData = function() {
	form = document.getElementById("add_form");
	var valid = true;
	for (var i = 0; i <= Number(document.getElementById("currRow").value); i++) {
		if (document.getElementById("itemRow"+i)) {
				valid = (validate("type"+i) && valid);

			// Format the item_id data from the barcode select
			if (form["type"+i].value != "repair") {
				form["item_id"+i].value = "";
				for (var j = 0; j < form["barcode"+i].options.length; j++) {
					if (form["barcode"+i].options[j].value)
						form["item_id"+i].value += form["barcode"+i].options[j].value+"|";
				}
			}
		}
	}
	valid = (validate("name") && valid);
	if (numRows() < 2) {
		alert("You must add at least one item");
		valid = false;
	}
	return valid;
}

// copies data from a form box to a pop up.
var cboard = function(which) {
	if (which != "email") {
		if (which == "phone")
			var sel = document.getElementById("phone_id");
		else if (which == "fax")
			var sel = document.getElementById("fax_id");

		var num = sel.options[sel.selectedIndex].text;
		num = num.substr( (num.indexOf(':') == -1) ? 0: num.indexOf(':') + 2 , 12);  // fax/phone text formating is different
		num = num.replace(/[^0-9]/gi, "");
	} else {
		var sel = document.getElementById("email_id");
		var num = sel.options[sel.selectedIndex].text;
		num = num.substring(num.indexOf("<") + 1, num.indexOf(">"));
	}
	window.prompt("Copy to clipboard: Ctrl+C, Enter", num);
}

// Counts the number of quote/invoiceitems
var numRows = function() {
	var cnt = 0;
	for (var i = 0; i < Number(document.getElementById("currRow").value); i++) {
		if (document.getElementById("itemRow"+i)) cnt++;
	}
	return cnt;
}

// Called when recalling a quote/invoice to populate the list with the quote/invoiceitems
var populateItems = function(items, subitem) {
	// Remember the row count before (where to start adding new items)
	var j = Number(document.getElementById("currRow").value) - 1;
	var form = document.getElementById("add_form");
	// Put the new rows in place before populating them with data
	for(var i = 0; i < items.length; i++) {addRow();}
	for(var i = j; i < items.length + j; i++) {
		// Add items from index 0 (current row - starting row)
		var k = i-j;
		form["modelnum"+i].value = items[k]["modelnum"];
		form["description"+i].value = items[k]["description"];
		form["quantity"+i].value = items[k]["quantity"];
		if (subitem) {
			form["itemtype_id"+i].value = items[k]["id"];
			form["print"+i].value = "subitem";
			form["assemqty"+i].value = items[k]["quantity"];
			form["rate"+i].value = 0.00;
		} else {
			form["assemqty"+i].value = items[k]["assemqty"];
			form["officenotes"+i].value = items[k]["officenotes"];
			form["itemtype_id"+i].value = items[k]["itemtype_id"];
			form["print"+i].value = items[k]["print"];
			form[vt+"item_id"+i].value = items[k]["id"];
			form["hts"+i].value = items[k]["hts"];
			form["madein"+i].value = items[k]["madein"];

			// Load the barcodes
			for (var b in items[k]["barcodes"]) {
				form["barcode"+i].add(new Option(items[k]["barcodes"][b]["barcode"], items[k]["barcodes"][b]["item_id"]), null);
				items[k]["barcodes"][b] = items[k]["barcodes"][b]["item_id"];
			}
			form["old_item_id"+i].value = items[k]["barcodes"].join("|");

			// Set the itemtype_id as the "id" for the setRates function
			items[k]["id"] = items[k]["itemtype_id"];
		}
		if (form["print"+i].value == "subitem") {
			form["rate"+i].style.visibility = "hidden";
			// form["quantity"+i].disabled = true;
			form["total"+i].style.visibility = "hidden";
			form["modelnum"+i].disabled = true;
			setRates(items[k], i, true);
		} else {
			setRates(items[k], i, false);
		}
		if (!subitem) form["rate"+i].value = items[k]["rate"];
		form["type"+i].value = items[k]["type"];
	}
	formatRows(form);
	updateQty();
}

// Formats the rows with different backgrounds based on their "print" value.
var formatRows = function(form) {
	for (var i = 0; i < Number(document.getElementById("currRow").value); i++) {
		var row = document.getElementById("itemRow"+i);
		if (row) {
			if (form["print"+i].value == "assembly" && row.className.indexOf("parentItem") < 0) {
				row.className += " parentItem";
			} else if (form["print"+i].value == "subitem" && row.className.indexOf("childItem") < 0)  {
				row.className += " childItem";
			} else { // regular item

			}
		}
	}
}

// Populates the phone/fax/email <select>s from customer information.
var populate = function(pORe, data) {
	if (pORe == "phone") var resetList = ["phone", "fax"];
	else var resetList = ["email"];
	for (var i in resetList) {
		var select = document.getElementById(resetList[i]+"_id");
		select.disabled = false;
		for (var j in select.options)
			select.options[j] = null;
	}

	var p = 0; //Counter for options list for phones
	var f = 0; //Counter for options list for faxes
	for (var i in data) {
		if (pORe == "phone") {
			if (data[i]["type"] != "fax") {
				sel = document.getElementById("phone_id");
				var contact = (data[i]["contact"] != "") ? " ("+data[i]["contact"]+")" : "";
				var typ = data[i]["type"].charAt(0).toUpperCase() + data[i]["type"].slice(1);
				sel.options[p++] = new Option(typ+": "+data[i]["num"]+contact, data[i]["id"]);
			} else {
				sel = document.getElementById("fax_id");
				var contact = (data[i]["contact"] != "") ? " ("+data[i]["contact"]+")" : "";
				sel.options[f++] = new Option(data[i]["num"]+contact, data[i]["id"]);
			}
		} else {
			var sel = document.getElementById("email_id");
			var emailAddress = (data[i]["email"] != "") ? " <"+data[i]["email"]+">" : "";
			sel.options[i] = new Option(data[i]["name"]+emailAddress, data[i]["id"]);
		}
	}
}

// Adds a new quote/invoiceitem row.
var addRow = function() {
	var i = Number(document.getElementById("currRow").value);
	var lastDel = document.getElementById("itemDel"+(i-1));
		if (lastDel)
			lastDel.style.display = "inline";
	var newRow = document.createElement("tr");
		newRow.id = "itemRow"+i;
	var typeSel = document.createElement("select");		// select element for service type
		typeSel.id = "type"+i;
		typeSel.name = "type"+i;
		typeSel.onchange = changeRate;
		typeSel.className = "typeInput";
		typeSel.options[0] = new Option("Exchange", "exch");
		typeSel.options[1] = new Option("Repair", "repair");
		typeSel.options[2] = new Option("Sale", "sale");
	var newCell = new Array(7);
	for (var j = 0; j < newCell.length; j++)
		newCell[j] = document.createElement("td");		// initiate td nodes for html item list rows
	var tarows = document.getElementById("tarows").value;
	newCell[0].innerHTML = '<input type="text" id="modelnum'+i+'" name="modelnum'+i+'" maxlength="63" class="modelInput" />'+
		'<div class="relPos"><div id="modelnum'+i+'Shadow" class="suggShadow"><div id="modelnum'+i+'Box" class="suggBox"></div></div></div>'+
		'<div id="assemChoose'+i+'" style="display: none;"><a href="javascript:barcodeSearch('+i+');">Find Assem Barcodes</a></div>'+
		'<div id="barcodeRow'+i+'" style="display: none;"><label for="barcode'+i+'">Barcodes</label> (<a href="javascript:barcodeSearch('+i+');">search</a>)'+
		'<br /><select id="barcode'+i+'" name="barcode'+i+'" size="4" class="quoteBarcodeSelect"></select>'+
		'<br /><input type="button" onclick="remBarcode('+i+');" value="Remove Barcode" />'+
		'<input type="hidden" id="item_id'+i+'" name="item_id'+i+'" /><input type="hidden" id="old_item_id'+i+'" name="old_item_id'+i+'" /></div>';
	newCell[1].appendChild(typeSel);
	newCell[2].innerHTML = '<textarea id="description'+i+'" name="description'+i+'" cols="57" rows="'+(Number(tarows)+1)+'" class="descInput"></textarea>'+
		'<br /><textarea id="details'+i+'" name="details'+i+'" cols="57" rows="'+tarows+'" disabled="disabled" class="descInput"></textarea>'+
		'<br /><textarea id="officenotes'+i+'" name="officenotes'+i+'" cols="57" rows="'+tarows+'" class="descInput"></textarea>';
	newCell[3].innerHTML = '<input type="text" id="quantity'+i+'" name="quantity'+i+'" value="0" size="2" onblur="updateQty();" class="qtyInput" />'+
		'<input type="hidden" id="assemqty'+i+'" name="assemqty'+i+'" value="1" />'+
		'<input type="hidden" id="hts'+i+'" name="hts'+i+'" />'+
		'<input type="hidden" id="madein'+i+'" name="madein'+i+'" />';
	newCell[4].innerHTML = '<input type="text" id="rate'+i+'" name="rate'+i+'" size="7" onblur="updateQty();" class="priceInput" />';
	newCell[5].innerHTML = '<input type="text" id="total'+i+'" name="total'+i+'" disabled="disabled" size="7" class="priceInput" />';
	newCell[6].innerHTML = '<input type="hidden" id="itemtype_id'+i+'" name="itemtype_id'+i+'" />'+
		'<input type="hidden" id="'+vt+'item_id'+i+'" name="'+vt+'item_id'+i+'" />'+
		'<input type="hidden" id="exchrate'+i+'" name="exchrate'+i+'" value="0.00" />'+
		'<input type="hidden" id="repairrate'+i+'" name="repairrate'+i+'" value="0.00" />'+
		'<input type="hidden" id="salerate'+i+'" name="salerate'+i+'" value="0.00" />'+
		'<input type="hidden" id="print'+i+'" name="print'+i+'" value="regular" />'+
		'<a id="itemDel'+i+'" href="javascript:itemRemove('+i+');" class="imglink" style="display:none;">'+
		'<img src="/pics/delsmall.png" alt="X" /></a>';
	for (j = 0; j < newCell.length; j++)
		newRow.appendChild(newCell[j]);
	var il = document.getElementById("itemList");
	il.insertBefore(newRow, document.getElementById("lastRow"));
	suggestInstall("modelnum"+i);
	document.getElementById("currRow").value = i+1;
}

// Confirms a quote/invoiceitem deletion
var itemRemove = function(row) {
	var ans = confirm("Delete this item?");
	if (ans) removeRow(row);
}
// Actually deletes the quote/invoiceitem
var removeRow = function(i) {
	i = Number(i);
	var form = document.getElementById("add_form");
	var c = document.getElementById("itemRow"+i);
	if (c) {
		if (form["print"+i].value == "assembly") {
			for (var j = i+1; j < Number(form["currRow"].value); j++) {
				if (typeof form["print"+j] == "undefined") continue;
				if (form["print"+j].value != "subitem") break;
				removeRow(j);
			}
		}
		if (form[vt+"item_id"+i].value != "")
			form["itemsDel"].value = form["itemsDel"].value+form[vt+"item_id"+i].value+"|";
		c.parentNode.removeChild(c);
	}
}

// Updates the quantities, rates, and totals to reflect any changes
// Called onblur of quantity and rate boxes.
var updateQty = function() {
	var subtotal = 0;
	var itemtotal = 0;
	var assemblyQnt = 1;
	var form = document.getElementById("add_form");
	for (var i = 0; i < Number(form["currRow"].value); i++) {
		if (form["quantity"+i]) {
			var print = form["print"+i].value;
			var qnt = Number(form["quantity"+i].value);

			// Store the assembly qnt for the next round
			if (print == "assembly") {
				assemblyQnt = qnt;
			// If it is a subitem, update the qnt from the changed assembly qnt
			} else if (print == "subitem") {
				qnt = assemblyQnt * Number(form["assemqty"+i].value);
				form["quantity"+i].value = qnt;
			}
			// Don't count the subitem qnts towards the total qnt
			if (print != "subitem") itemtotal += qnt;

			var tot = qnt * Number(form["rate"+i].value);
			form["total"+i].value = tot.toFixed(2);
			subtotal += tot;
		}
	}
	var tax = Number(form["taxrate"].value);
	var taxamount = subtotal * tax;
	var total = taxamount + subtotal;

	form["taxamnt"].value = taxamount.toFixed(2);
	form["subtotal"].value = subtotal.toFixed(2);
	form["itemtotal"].value = itemtotal.toFixed(0);
	form["total"].value = total.toFixed(2);
}

// Updates the rate if a different "service" is chosen.
// Also changes whether a barcode box is shown
var changeRate = function(row, fromSetRates) {
	if ((typeof row) == "object") row = this.id.replace(/[a-z]/gi, "");
	if ((typeof fromSetRates) == "undefined") fromSetRates = false;
	var form = document.getElementById("add_form");
	// If an exchange type is shown, pull up the barcode box
	if (form["type"+row].value != "repair") {
		if (form["print"+row].value != "assembly") {
			document.getElementById("barcodeRow"+row).style.display = "block";
		} else {
			document.getElementById("assemChoose"+row).style.display = "block";
		}
	} else {
		document.getElementById("barcodeRow"+row).style.display = "none";
		document.getElementById("assemChoose"+row).style.display = "none";
		for (var i in form["barcode"+row].options)
			form["barcode"+row].options[i] = null;
	}
	form["rate"+row].value = document.getElementById(form["type"+row].value+"rate"+row).value;
	updateQty();
	if (form["print"+row].value == "assembly" && !fromSetRates) {
		for (var i = Number(row)+1; form["print"+i].value == "subitem"; i++) {
			form["type"+i].value = form["type"+row].value;
			changeRate(i);
		}
	}
}

// Sets the rates of a particular "service"
var setRates = function(d, row, subitem) {
	var sel = document.getElementById("type"+row);
	var form = document.getElementById("add_form");
	sel.options.length = 0;
	var j = { "exch" : "Exchange", "repair" : "Repair", "sale" : "Sale" };

	// If this item already exists on the quote, don't allow the same service type
	var jnot = { "exch" : -1, "repair" : -1, "sale" : -1 };
	for (var i = 0; i < row; i++) {
		var currItem = form["itemtype_id"+i];
		if (currItem && currItem.value == d["id"]) {
			var typeSelect = form["type"+i];
			jnot[typeSelect.options[typeSelect.selectedIndex].value] = i;  // asign current service value to jnot
			typeSelect.disabled = true;
		}
	}
	if (subitem) {
		for (var l = (row-1); l >= 0; l--) {
			if (form["print"+l].value == "assembly") {
				for (var m = 0; m < form["type"+l].length; m++) {
					var k = form["type"+l][m].value;
					form[k+"rate"+row].value = "0.00";
					sel.add(new Option(j[k], k), null);
				}
				break;
			}
		}
	} else {
		for (var k in j) {		// asign service rates to their option values.  jnot services not included.
			if (d[k] == 1 && jnot[k] < 0) {
				form[k+"rate"+row].value = d[k+"rate"];
				sel.add(new Option(j[k], k), null);
			}
		}
	}
	// document.getElementById("quantity"+row).value = 1;  +++ not sure why this was here, but it overwrote saved quantities so its been commented +++
	changeRate(row, true);
}

// Pops up a view with the notes on a particular customer
// id is in format id_num/history or id_num/notes
var view = function(id) {
	var newwindow = window.open("/customer/view/"+id, "Customer Notes", POPUP_DIMENSIONS);
	if (window.focus) newwindow.focus();
	return false;
}

// Pops up a window with a barcode list of items on hand for a particular line-item
var barcodeSearch = function(row) {
	var id = document.getElementById("itemtype_id"+row).value;
	var newwindow = window.open("/item/quoteBarcodeSearch/"+id+"/"+row, "Barcode Search", POPUP_DIMENSIONS);
	if (window.focus) newwindow.focus();
}
// "Sets" a barcode as an option in the barcode list
var barcodeSet = function(row, info) {
	var form = document.getElementById("add_form");
	var sel = form["barcode"+row];
	// Check to see if the itemtype matches
	if (info["itemtype_id"] == form["itemtype_id"+row].value) {
		if (form["type"+row].value != "repair") {
			if (Number(form["quantity"+row].value) > sel.length) {
				var gogogo = true;
				for (var i = 0; i < sel.length; i++) {
					if (sel.options[i].value == info["id"]) gogogo = false;
				}
				if (gogogo) document.getElementById("barcode"+row).add(new Option(info["barcode"], info["id"]), null);
				else alert("That item is already in the barcode list.");
			} else {
				alert("There are already "+sel.length+" items selected for that row.");
			}
		}
	} else {
		alert("The barcode is for part "+info["modelnum"]+".");
	}
}
// Removes selected barcode from a particular barcode list
var remBarcode = function(row) {
	var sel = document.getElementById("barcode"+row);
	for (var i = 0; i < sel.length; i++) {
		if (sel.options[i].selected) {
			sel.remove(i);
			break;
		}
	}
}
// Called from the barcode select window
var barcodeSelect = function(info, row, assem) {
	if (assem == "false") {
		barcodeSet(row, info);
	} else { // Get an array of barcodes for that assembly
		for (var i = row; i >= 0; i--) {
			if (document.getElementById("print"+i).value == "assembly") {
				row = i;
				break;
			}
		}
		httpObj({
			url: "/item/quoteBarcodeList/"+info["id"]+"/"+row,
			complete: assemBarcodeFill,
			data: ""
		});
	}
}
// Puts a barcode in each of the boxes associated with the assembly (as is appropriate)
var assemBarcodeFill = function(xhr, response, status) {
	// alert(response);
	var form = document.getElementById("add_form");
	nd = eval("("+response+")");
	for (var i in nd["items"]) {
		var item = nd["items"][i];
		for (var row = nd["row"]; row < form["currRow"].value; row++) {
			if (item["itemtype_id"] == form["itemtype_id"+row].value) {
				barcodeSet(row, item);
				break;
			}
		}
	}
}

// Gets the quote/invoice to display.
var getQuote = function(id) {
	var qid = document.getElementById("refNumDisp");
	if (!id && qid.hasAttribute("disabled")) {
		qid.removeAttribute("disabled");
		return false;
	}
	var vtt = (vt == "inv") ? "invoice" : vt;
	if (!id) {
		id = qid.value;
		window.location.href = "/"+vtt+"/index/"+id;
		return false;
	}
	httpObj({
		url: "/"+vtt+"/get",
		complete: updateData,
		data: "id="+id
	});
	var ci = document.getElementById("createInv");
	if (ci) ci.removeAttribute("disabled");
	return false;
}

// Formats the billing address (as it is not in inputs)
var billAddress = function(nd) {
	var address = nd["address"]+"<br />";
	if (nd["city"] != "") {var comma = ", ";}
	else {var comma = "";}

	if (nd["address1"] != "") address += nd["address1"]+"<br />";
	address += nd["city"]+comma+nd["state"]+" "+nd["zip"]+"<br />"+nd["country"]+"<br />";
	if (nd["address1"] == "") address += "<br />";
	document.getElementById("addressBox").innerHTML = address;
}

// Get the assembly items
var assemblyItems = function(id) {
	httpObj({
		url: "/quote/assemblyItems/",
		complete: function(xhr, response, status) {
			// alert(response);
			var newData = eval("("+response+")");
			populateItems(newData, true);
		},
		data: "itemtype_id="+id
	});
}

// US Customs Invoice HTS codes
var HtsCheck = {
	check: function() {
		if ( document.getElementById("printType").selectedIndex == 4 ) {
			var rows = Number(document.getElementById("currRow").value) - 1;
			for (var i = 0; i < rows; i++ ) {
				if ( document.getElementById("print"+i).value != "subitem" ) {
					if ( !document.getElementById("hts"+i).value ) { // hts check
						alert(document.getElementById("modelnum"+i).value+" hts is not set!");
						document.getElementById("printType")[0].selected = true;
					}
					if ( !document.getElementById("madein"+i).value ) { // madin check
						alert(document.getElementById("modelnum"+i).value+" madein is not set!");
						document.getElementById("printType")[0].selected = true;
					}
				}
			}
		}
	}
}

// Checks for customer tax information
var checkTax = function(province, country, type) { // need to format appropiate conditions
	//alert(province);
	//alert(type);
	if ( country == "Canada" ) {
		httpObj({
			url: "/quote/getTax",
			complete: function (xhr, response, status) {
				var taxData = eval("("+response+")");
				if (taxData["defined"] == "false") {
					alert("The customer's state/province/tax is not in the database!");
					document.getElementById("taxtype").value = "";
					document.getElementById("taxname").innerHTML = "Tax <span id=\"taxpercent\">00.0</span>%):";
					document.getElementById("taxrate").value = "0.0";
				} else {
					document.getElementById("taxrate").value = taxData["tax"];
					document.getElementById("taxtype").value = taxData["name"];
					document.getElementById("taxname").innerHTML = taxData["name"]+"(<span id=\"taxpercent\">00.0</span>%):";
					document.getElementById("taxpercent").innerHTML = taxData["tax"];
				}
				updateQty();
			},
			data: "province="+province+"&type="+type
		});
	} else {
		document.getElementById("taxtype").value = "";
		document.getElementById("taxname").innerHTML = "Tax (<span id=\"taxpercent\">00.0</span>%):"
		document.getElementById("taxrate").value = "0.0";
		updateQty();
	}
}

// Formats and shows the creation/email notes at the top
var showCreator = function(newData) {
	if (newData[vt]["createdbyname"] != null) {
		document.getElementById("createdbyname").innerHTML = newData[vt]["createdbyname"];
		document.getElementById("datecreated").innerHTML = newData[vt]["date"];
	} else document.getElementById("cby").style.display = "none";

	if (newData[vt]["editedbyname"] != null) {
		document.getElementById("editedbyname").innerHTML = newData[vt]["editedbyname"];
		document.getElementById("datelastedited").innerHTML = newData[vt]["datelastedited"];
	} else document.getElementById("eby").style.display = "none";

	if (newData[vt]["emaildate"] != null) document.getElementById("emaildate").innerHTML = newData[vt]["emaildate"];
	else document.getElementById("edate").style.display = "none";

	document.getElementById("editSignature").style.visibility = "visible";
}

// Update the form data post-suggestion
var updateData = function(xhr, response, status) {
	var form = document.getElementById("add_form");
	// alert(response);
	var newData = eval("("+response+")");
	if (newData["modelnum"] && !newData["serial"]) { // Regular modelnum suggestion for items
		var rowNum = newData["row"];
		// Check to see if it is an assembly, and if it is, remove the current assembly first
		if (form["print"+rowNum].value == "assembly") {
			removeRow(rowNum);
			addRow();
			rowNum = Number(form["currRow"].value)-2;
		}
		if (rowNum == Number(form["currRow"].value)-1)
			addRow();
		updateForm(form, newData, rowNum);
		form["itemtype_id"+rowNum].value = newData["id"];
		form["type"+rowNum].removeAttribute("disabled");
		// Reset the item_id for that row
		form["barcode"+rowNum].value = "";
		form["item_id"+rowNum].value = "";
		// Grab the assembly data if the item added is an assembly
		if (newData["assembly"] == 1) {
			form["print"+rowNum].value = "assembly";
			assemblyItems(newData["id"]);
		}
		document.getElementById("quantity"+rowNum).value = 1;  // default quantity = 1
		setRates(newData, rowNum);
		form["modelnum"+rowNum].focus();
	} else if (newData["serial"]) { // Barcode suggestion (for specifically quoted items)
		barcodeSet(newData["row"], newData);
	} else if (newData["name"]) { // if new data is customer related
		// Flash customer notes only when not already there
		if (form.cust_id.value != newData["id"]) var vn = true;
		else var vn = false;
		form.cust_id.value = newData["id"];
		form.name.value = newData["name"];

		checkTax(newData["state"], newData["country"], newData["tax"]);
		// For invoice view only
		billAddress(newData);
		populate("phone", newData["phones"]);
		populate("email", newData["emails"]);
		updateForm(form, newData);
		if (vn) view(form.cust_id.value+"/notes");
	} else if (newData[vt]) { // if new data is a quote or invoice

		if (newData["customer"] == undefined) {
			var vtt = (vt == "inv") ? "invoice" : "quote";
			alert("Specified "+vtt+" does not exist");
			window.location.href = "/"+vtt;
		} else {
			form[vt+"_id"].value = newData[vt]["id"];
			form.name.value = newData["customer"]["name"];
			form.cust_id.value = newData["customer"]["id"];
			billAddress(newData["customer"]);
			checkTax(newData["customer"]["state"], newData["customer"]["country"], newData["customer"]["tax"]);
			populate("phone", newData["customer"]["phones"]);
			populate("email", newData["customer"]["emails"]);
			populateItems(newData["items"], false);
			form.phone_id.value = newData[vt]["phone_id"];
			form.email_id.value = newData[vt]["email_id"];
			showCreator(newData);
			updateForm(form, newData[vt]);
			form["name"].focus();
		}
	}
}