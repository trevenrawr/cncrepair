// This function is called when the page is loaded.
// It's not named docReady as the itemlist.js already one.
var primDocReady = function(itemtype_id) {
	suggestInstall("make");
	suggestInstall("modelnum");
	document.getElementById("add_form").reset();
	ratesCheck();
	if (itemtype_id != "") {
		goID(itemtype_id);
	}
}

// Takes an ID and loads the itemtype's information
var goID = function(itemtype_id) {
	httpObj({
		url: "/item/itemInfo/"+itemtype_id,
		complete: updateData,
		data: "id="+itemtype_id
	});
}

// This is called by the submit button to save the contents of the page and display the server's response
var addItem = function(target) {
	form = document.getElementById("add_form");
	quickbooks = document.getElementById("quickbooks").checked || false;
	if (verifyData(form)) {
		httpObj({
			url: target,
			complete: function (xhr, response, status) {
					response = eval('('+response+')');
					if (!confirm(response.message+"\n\nClick 'Cancel' to clear form, 'OK' to retain.")) {
						window.location.href = "/item";
					} else {
						goID(response.id);  // reload page with current item information (time etc).
					}
				},
			data: itemData(form)+"&quickbooks="+quickbooks
			});
	}
	return false;
}
// This opens up the HTS popup
var HtsCodes = function() {
	newwindow = window.open("/item/htscode", "HTS Editor", POPUP_DIMENSIONS);
}


// Verifies the data on the page before it gets submitted.
var verifyData = function(form) {
	var valid = true;
	var inputs = new Array("value", "onhand", "onhold", "weight");
	var curr = null;
	for (i = 0; i < inputs.length; i++) {
		validate(inputs[i], "num");
	}
	valid = (checkChecked("sale") && valid);
	valid = (checkChecked("repair") && valid);
	valid = (checkChecked("exch") && valid);
	valid = (validate("make") && valid);
	valid = (validate("modelnum") && valid);
	return valid;
}

// Called when the "Assembly" checkbox is either checked or unchecked.
var enableAssem = function() {
	var cb = document.getElementById("assembly");
	var eal = document.getElementById("editAssemLink");
	if (cb.checked && document.getElementById("itemtype_id").value != "")
		eal.removeAttribute("disabled");
	else
		eal.setAttribute("disabled", "disabled");
}

// Checks the prices, but only if the box enabling them is checked.
var checkChecked = function(id) {
	if (document.getElementById(id).checked == true) {
		return validate(id+"rate", "num");
	}
	return true;
}

// Puts information in an appropriate format for $_POST
var itemData = function(form) {
	var data = formData(form);
	data += "exch="+form.exch.checked+"&";
	data += "repair="+form.repair.checked+"&";
	data += "sale="+form.sale.checked+"&";
	data += "assembly="+form.assembly.checked+"&";
	data += "ajax=true";
	return data;
}

// When an itemtype has any items associated with it, they get listed at the bottom of the page by this function
var listItems = function(items) {
	if (items.length > 0)
		document.getElementById("itemsTable").style.display = "table";
		document.getElementById("itemList").innerHTML = "";
	for (var i = 0; i < items.length; i++) {
		var newRow = document.createElement("tr");
			newRow.id = "itemRow"+i;
			var cname = ""; var assembly = false;
			if (items[i]["parent"]) {
				if (items[i]["assembly"] == 1) {
					cname = " parentItem";
					assembly = true;
				} else {
					cname = " childItem";
				}
			}
			newRow.className = "r"+(i%2)+cname;
		var newCell = new Array(6);
		for (j = 0; j < newCell.length; j++)
			newCell[j] = document.createElement("td");
		if (!assembly) {
			newCell[0].innerHTML = items[i]["serial"];
			newCell[1].innerHTML = '<a href="/item/specific/'+items[i]["barcode"]+'">'+items[i]["barcode"]+'</a>';
			newCell[2].innerHTML = (items[i]['atcustomer'] == 0) ? items[i]["rack"]+" - "+items[i]["shelf"] : items[i]['atcust'];
			newCell[3].innerHTML = items[i]["lastseen"];
			newCell[4].innerHTML = items[i]["txtstatus"];
			newCell[4].className += " capitalize";
			var n = items[i]["id"] + "/notes";
			var h = items[i]["id"] + "/history";
			newCell[5].innerHTML = '<a href="#" onclick="view(\''+n+'\');">Notes</a> | <a href="#" onclick="view(\''+h+'\');">History</a>';
		}
		for (j = 0; j < newCell.length; j++)
			newRow.appendChild(newCell[j]);
		document.getElementById("itemList").appendChild(newRow);
	}
}

// This disables/enables the rate boxes based on whether their associated checkbox is checked.
var ratesCheck = function() {
	var types = ["exch", "repair", "sale"];
	for (var i in types) {
		var rateBox = document.getElementById(types[i]+"rate");
		if (!document.getElementById(types[i]).checked) {
			rateBox.setAttribute("disabled", "disabled");
		} else {
			rateBox.removeAttribute("disabled");
		}
	}
}

// Confirms that user wishes to delete the item.
var confirmDelete = function() {
	if(confirm("Are you SURE you want to delete this master item?\nThis will also delete any specific items!")) {
		if (confirm("This is your last chance to say 'no.'\n\nNote: If an error occurs, it is due to quotes or invoices containing this item.")) {
			httpObj({
				url: "/item/delete",
				complete: function(xhr, response, status) {alert(response);window.location.reload(true);},
				data: "itemtype_id="+document.getElementById("itemtype_id").value
			});
		}
	}
}

// Shows date information for item
var showCreator = function(newData) {
	if (newData["createdby"] != null) {
		document.getElementById("cby").style.display = "inline";
		document.getElementById("createdby").innerHTML = newData["createdbyname"];
		document.getElementById("created").innerHTML = newData["created"];
	} else document.getElementById("cby").style.display = "none";

	if (newData["editedby"] != null) {
		document.getElementById("eby").style.display = "inline";
		document.getElementById("editedby").innerHTML = newData["editedbyname"];
		document.getElementById("lastedited").innerHTML = newData["lastedited"];
	} else document.getElementById("eby").style.display = "none";

	document.getElementById("itemSignature").style.visibility = "visible";
}

// The suggest function (in suggest.js) calls this when a suggested item is picked
var updateData = function(xhr, response, status) {
	// alert(response);
	var newData = false;
	if (response) newData = eval('(' + response+ ')');
	if (newData["modelnum"]) {
		var form = document.getElementById("add_form");
		if (form["itemtype_id"].value != "" && form["itemtype_id"].value != newData["id"]) {
			if (!confirm("You entered a part number which came with associated information.\nWould you like to load the form with that information?\nIf you choose not to, the previous part number will get overwritten with the new one."))
				return;
		}
		form.reset();
		form.itemtype_id.value = newData["id"];
		updateForm(form, newData);
		form.exch.checked = (newData["exch"] == 1) ? true : false;
		form.repair.checked = (newData["repair"] == 1) ? true : false;
		form.sale.checked = (newData["sale"] == 1) ? true : false;
		form.assembly.checked = (newData["assembly"] == 1) ? true : false;
		enableAssem();
		listItems(newData["items"]);
		form.submit.value = "Update Item";
		form.deleteButton.style.visibility = "visible";
		showCreator(newData);
		ratesCheck();
	}
}