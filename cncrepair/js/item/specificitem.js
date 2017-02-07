// Called in body's onload.
var docReady = function(barcode) {
	suggestInstall("name");
	suggestInstall("owner");
	suggestInstall("serial");
	suggestInstall("modelnum");
	suggestInstall("barcode");
	goBarcode(barcode);
}

// Collects information from the form and submits it to the server; displays response
var savePos = function() {
	form = document.getElementById("add_form");
	var stk = (document.getElementById("stockitem").checked) ? 1 : 0;
	var fd = formData(form)+"stock="+stk;
	if (verifyData(form)) {
		httpObj({
			url: "/item/specific/add/",
			complete: function(xhr, response, status) {
				if (!confirm(response+"\n\nClick 'Cancel' to clear form, 'OK' to retain."))
					window.location.href = "/item/specific";
			},
			data: fd
		});
	}
	return false;
}

// Checks data validity prior to submission
var verifyData = function(form, pos) {
	var valid = true;
	valid = (validate("rack") && valid);
	valid = (validate("shelf") && valid);
	valid = (validate("barcode") && valid);
	
	return valid;
}

// Pops up the notes or history of an item.
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

// Alters options available on the page based on whether the item is considered to be a "stock" item or not.
var cncstock = function(prompt) {
	var chk = document.getElementById("stockitem");
	if (prompt != "no" && chk.checked) {
		if (!confirm("This will overwrite 'owner' and 'at customer' fields.\n\nAre you SURE you want to continue?")) {
			chk.checked = false;
			return false;
		}
	}
	var nme = document.getElementById("name");
	var ownr = document.getElementById("owner");
	var rck = document.getElementById("rack");
	var shlf = document.getElementById("shelf");
	if (chk.checked) {
		ownr.value = "CNC Repair";
		document.getElementById("owner_id").value = 0;
		ownr.disabled = true;
		nme.value = "CNC Repair";
		document.getElementById("cust_id").value = 0;
		nme.disabled = true;
		rck.diabled = false;
		shlf.disabled = false;
	} else {
		nme.disabled = false;
		ownr.disabled = false;
	}
	return true;
}

// Used when hard-linked to the page by the item list to update page info
var goBarcode = function(barcode) {
	document.getElementById("barcode").value = decodeURIComponent(barcode);
	suggestBypass("barcode");
}

// Updates text status and priority when the sliders are moved.
var showStatus = function(val, id) {
	if (id == "status") {
		var valEnum = ["", "scrap", "salvageable", "needs work", "ready today", "ready NOW"];
		val = valEnum[val];
	} else {
		var valEnum = ["At Customer", "brown", "orange", "blue", "red"];
		val = valEnum[val];
		document.getElementById("txt"+id).style.backgroundColor = val;
	}
	document.getElementById("txt"+id).innerHTML = val;
}

// Alters page options when at a customer (CNC or otherwise)
var atCustomer = function(id) {
	var stts = document.getElementById("status");
	var rdyfr = document.getElementById("readyfor");
	var prrty = document.getElementById("priority");
	var stock = document.getElementById("stockitem");
	var rck = document.getElementById("rack");
	var shlf = document.getElementById("shelf");
	if (id != 0) { // It is not at CNC Repair
		stts.value = 5;
		stts.disabled = true;
		showStatus(5, "status");
		stock.checked = false;
		rdyfr.value = "";
		rdyfr.disabled = true;
		prrty.value = 0;
		prrty.disabled = true;
		showStatus(0, "priority");
		rck.value = "000";
		rck.disabled = true;
		shlf.value = "000";
		shlf.disabled = true;
	} else {
		stts.disabled = false;
		rdyfr.disabled = false;
		prrty.disabled = false;
		rck.disabled = false;
		shlf.disabled = false;
	}
}

// Called after a suggestion is selected.
var updateData = function(xhr, response, status) {
	// alert(response);
	if (response) {
		var form = document.getElementById("add_form");
		var newData = eval("("+response+")");
		if (newData["owner"] && newData["name"]) {
			form.owner_id.value = newData["id"];
		} else if (newData["name"]) {
			form.cust_id.value = newData["id"];
			atCustomer(newData["id"]);
		} else if (newData["modelnum"] && !newData["serial"]) {
			form.itemtype_id.value = newData["id"];
		} else if (newData["serial"]) { // Data comes in via barcode or serial response
			if (form.notes) form.notes.value = '';
			if (form.shipnotes) form.shipnotes.value = '';
			if (form.viewOldNotes) form.viewOldNotes.disabled = false;
			if (form.viewHist) form.viewHist.disabled = false;

			updateForm(form, newData);
			
			showStatus(newData["status"], "status");
			showStatus(newData["priority"], "priority");
			
			if (newData["stock"] == 1) document.getElementById("stockitem").checked = true;
			cncstock("no");	
	
			form.item_id.value = newData["id"];
			form.cust_id.value = newData["atcustomer"];
			document.getElementById("name").value = newData["atcust"];
			atCustomer(newData["atcustomer"]);
		}
	}
}