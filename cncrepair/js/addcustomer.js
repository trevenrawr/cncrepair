// Called in body's onload
var docReady = function(cust_id) {
	resetPorE("phone");
	resetPorE("email");
	suggestInstall("name");
	document.getElementById("number0").onblur = formatPhone;
	if (cust_id && cust_id != "") {
		goID(cust_id);
	}
}

// Takes an ID and loads the customer's information
var goID = function(cust_id) {
	httpObj({
		url: "/customer/customerInfo/"+cust_id,
		complete: updateData,
		data: "id="+cust_id
	});
}

// Saves the customer, after verifying data, and displays the response
var addCustomer = function(target, showResult) {
	form = document.getElementById("add_form");
	if (showResult) {
		var complete = function(xhr, response, status) {
			if (!confirm(response+"\n\nClick 'Cancel' to clear form, 'OK' to retain.")) {
				window.location.reload(true);
			} else {
				suggestSelect(window.nameSuggest);
			}
		}
	} else {
		var complete = function(xhr, response, status) {
			// alert(response);
			opener.popupComplete(document.getElementById("name").value);
			self.close();
		}
	}

	if (verifyData()) {
		// alert(formData(form));
		httpObj({
			url: target,
			complete: complete,
			data: formData(form)
		});
	}
	return false;
}

// Verifies the data before form submission
var verifyData = function() {
	var ecr = Number(document.getElementById("emailcurrRow").value);
	var pcr = Number(document.getElementById("phonecurrRow").value);
	var cntry = document.getElementById("country").value;  // used for country specific formats

	var valid = true;
	valid = (validate("name") && valid);

	// taxid format
	if ( cntry == "United States" ) {
		var taxId = document.getElementById("taxid");
		if (!taxId.value) {
			taxId.value = '00-0000000';
		}
		if ( taxId.value.indexOf("-") != 2 || taxId.value.length != 10) {
			taxId.style.border = "1px solid red";
			valid = false;
		}
	}
	// taxid format for canada PST NOT done yet.



	var r = (ecr > pcr) ? ecr : pcr;
	for (i = 0; i < r; i++) {
		curr = document.getElementById("email"+i);
		if (curr && curr.value != "") {
			apos = curr.value.indexOf("@");
			dotpos = curr.value.lastIndexOf(".");
			if (apos < 1 || dotpos - apos < 2) {
				curr.parentNode.style.border = "1px solid red";
				valid = false;
			} else curr.parentNode.style.border = "none";
		}
		curr = document.getElementById("number"+i);
		if (curr && curr.value != "") {
			var digits = curr.value.replace(/[^0-9]/g, "");
			var lowerbound = (curr.value.indexOf("x") > 0) ? 11 : 10;
			var upperbound = (curr.value.indexOf("x") > 0) ? 14 : 10;

			if ((cntry == "United States" || cntry == "Canada") && (digits.length < lowerbound || digits.length > upperbound )) {
				curr.parentNode.style.border = "1px solid red";
				valid = false;
			} else curr.parentNode.style.border = "none";
		}
	}
	return valid;
}
// Formats taxid in the US or Canada formats
/*
 * TaxCode object the deals with formating the taxcode box.
 * Keygrabber is anonoymous so it can not be removed with eventremovelistener: use named functions if such
 * behaviour is wanted.
*/
var TaxCode = {
	o: Object,
	taxcode: '',
	country: '',
	that: function() { return this; }
}
TaxCode.init = function() {
	that = this.that();
	this.country = document.getElementById("country").value;

	if (this.country == "United States" && typeof this.o === 'function') {
		this.o = document.getElementById("taxid");
		this.o.addEventListener("keyup", this.keygrabber(that), false);

	} else {
		return false;
	}
}
TaxCode.keygrabber = function(that) {

	// private preset members
	var count = 0;
// 	var keyevent = window.event;
	var extra = Array;
	return function() {
// 		alert(this.value.length);
		that.taxcode = this.value

		// US Formatting rules
		if (that.country == "United States") {

			// Alphabet Condition
			that.taxcode = that.taxcode.replace(/[^0-9]/g, "");

			// Hyphen Condition
			if ( that.taxcode.length > 2 && that.taxcode.indexOf("-") != 2 ) {
				that.taxcode = that.taxcode.replace(/(.{2})/, "$1-");
			}


			// Max character Condition
			if ( this.value.length >  10 ) {
				that.taxcode = extra["old"];
			} /*else {
				that.taxcode = that.taxcode.substr(0,10);
			}*/
		}
		//Canadian PST formatting rules
		// if ( that.country == canada....
		extra["old"] = that.taxcode;
		this.value = that.taxcode;
// 		alert(event.type);
// 		alert (this.value.indexOf("-"));
	}
}



// Formats phone numbers in US or Canada formats
var formatPhone = function() {
	var cntry = document.getElementById("country").value;
	if (cntry == "United States" || cntry == "Canada") {
		var str = this.value;
		str = str.replace(/[^0-9x]/g, "");
		str = str.replace(/(.{6})/, "$1-");
		str = str.replace(/(.{3})/, "$1-");
		this.value = str;
	}
}

// Populates the phones from a saved customer's data
var populatePhones = function(phones) {
	var newRows = false;
	for(var i = 0; i < phones.length; i++) {
		if (document.getElementById("phoneRow"+i)) {
			document.getElementById("phone_id"+i).value = phones[i]["id"];
			document.getElementById("phonecust_id"+i).value = phones[i]["cust_id"];
			document.getElementById("type"+i).value = phones[i]["type"];
			document.getElementById("number"+i).value = phones[i]["num"];
			document.getElementById("contact"+i).value = phones[i]["contact"];
		} else {
			phoneAdd();
			newRows = true;
		}
	}
	if (newRows)
		populatePhones(phones);
}

// Populates the emails from a saved customer's data
var populateEmails = function(emails) {
	var newRows = false;
	for(var i = 0; i < emails.length; i++) {
		if (document.getElementById("emailRow"+i)) {
			document.getElementById("email_id"+i).value = emails[i]["id"];
			document.getElementById("emailcust_id"+i).value = emails[i]["cust_id"];
			document.getElementById("email"+i).value = emails[i]["email"];
			document.getElementById("name"+i).value = emails[i]["name"];
		} else {
			emailAdd();
			newRows = true;
		}
	}
	if (newRows)
		populateEmails(emails);
}

// Adds a new row for phones
var phoneAdd = function() {
	addRow("phone");
}
// Adds a new row for emails
var emailAdd = function() {
	addRow("email");
}
// Adds a new row for either a phone or email
var addRow = function(pORe) {
	var i = Number(document.getElementById(pORe+"currRow").value);
	// Remove the addLink
	var lastAdd = document.getElementById(pORe+"Add"+(i-1));
		if (lastAdd) lastAdd.style.display = "none";
	var lastDel = document.getElementById(pORe+"Del"+(i-1));
		if (lastDel) {
			lastDel.style.display = "inline";
		}
	var newRow = document.createElement("tr");
		newRow.id = pORe+"Row"+i;
	if (pORe == "phone") {
		var typeSelect = document.createElement("select");
			typeSelect.name = "type"+i;
			typeSelect.id = "type"+i;
			typeSelect.options[0] = new Option("Fax", "fax");
			typeSelect.options[1] = new Option("Cell", "cell");
			typeSelect.options[2] = new Option("Technical", "technical");
			typeSelect.options[3] = new Option("Office", "office");
			typeSelect.options[4] = new Option("Primary", "primary");
			typeSelect.options[5] = new Option("Other", "other");
		var numberInput = '<input name="number'+i+'" id="number'+i+'" maxlength="31" size="18" />';
		var contactInput = '<input name="contact'+i+'" id="contact'+i+'" maxlength="63" size="19" />';
		var newCell = new Array(4);
		for (j = 0; j < newCell.length; j++)
			newCell[j] = document.createElement("td");
		newCell[0].appendChild(typeSelect);
		newCell[1].innerHTML = numberInput;
		newCell[2].innerHTML = contactInput;
	} else {
		var emailInput = '<input type="email" name="email'+i+'" id="email'+i+'" maxlength="63" size="33" />';
		var nameInput = '<input name="name'+i+'" id="name'+i+'" maxlength="63" size="19" />';
		var newCell = new Array(3);
		for (j = 0; j < 3; j++)
			newCell[j] = document.createElement("td");
		newCell[0].innerHTML = emailInput;
		newCell[1].innerHTML = nameInput;
	}
	var addLink = '<a href="javascript:'+pORe+'Add();" id="'+pORe+'Add'+i+'" class="imglink"><img src="/pics/addsmall.png" alt="+" /></a>';
	var delLink = '<a href="javascript:'+pORe+'Remove('+i+');" id="'+pORe+'Del'+i+'" class="imglink" style="display:none;"><img src="/pics/delsmall.png" alt="X" /></a>';
	var idInput = '<input type="hidden" name="'+pORe+'_id'+i+'" id="'+pORe+'_id'+i+'" /><input type="hidden" id="'+pORe+'cust_id'+i+'" name="'+pORe+'cust_id'+i+'" />';
	newCell[newCell.length - 1].innerHTML = delLink+addLink+idInput;
	for (j = 0; j < newCell.length; j++)
		newRow.appendChild(newCell[j]);
	document.getElementById(pORe+"List").appendChild(newRow);
	delLink = document.getElementById(pORe+"Del"+i);
	if (pORe == "phone") document.getElementById("number" + i).onblur = formatPhone;
	document.getElementById(pORe+"currRow").value = i+1;
}

// Confirm phone delete
var phoneRemove = function(row) {
	if (confirm("Delete this phone number?"))
		removeRow(row, "phone");
}
// Confirm email delete
var emailRemove = function(row) {
	if (confirm("Delete this email?"))
		removeRow(row, "email");
}
// Resets the email or phone rows
var resetPorE = function(pORe) {
	var list = document.getElementById(pORe+"List");
	while (list.firstChild) {
		list.removeChild(list.firstChild);
	}
	document.getElementById(pORe+"currRow").value = 0;
	addRow(pORe);
	if (pORe == "phone") {
		document.getElementById("type0").value = "primary";
	}
}
// Removes an email or phone row
var removeRow = function(i, pORe) {
	var currEntryID = document.getElementById(pORe+"_id"+i);
	if (currEntryID.value != '') {
		deleted = document.getElementById(pORe+"sDel");
		deleted.value += currEntryID.value+"|";
	}
	var c = document.getElementById(pORe+"Row"+i);
	c.parentNode.removeChild(c);
	var cr = Number(document.getElementById(pORe+"currRow").value);
	if (i == cr-1) {
		var r = null;
		for (var j = cr; j >= 0; j--) {
			r = document.getElementById(pORe+"Add"+j);
			if (r) {
				r.style.display = "inline";
				document.getElementById(pORe+"currRow").value = j+1;
				break;
			}
		}
	}
}

// Shows or hides the tax selection box (and sets currency defaults) based on country selection
// shows or hides the taxid input box.
var showBox = function(country) {
// 	alert(country);
	if ( !country || country == "United States" || country == "Canada") {
		document.getElementById("taxidrow").style.display = "table-row";
	} else {
		document.getElementById("taxidrow").style.display = "none";
		document.getElementById("taxid").value = "";
	}
	if (country == "Canada") {
		document.getElementById("taxRow").style.display = "table-row";
		document.getElementById("currency").value = "Canada Dollar";
	} else {
		document.getElementById("taxRow").style.display = "none";
		document.getElementById("currency").value = "United States of America Dollar";
		document.getElementById("tax").value = "";
	}
}

// Copies the address to the shipping address
var copyAddress = function() {
	if (confirm("Are you sure you want to copy addresses? \n This will overwrite the current shipping address.")) {
		var a = ["name", "address", "address1", "city", "state", "country", "zip"];
		form = document.getElementById("add_form");
		for (i in a)
			form["ship"+a[i]].value = form[a[i]].value;
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

// Update the form data post-suggestion
var updateData = function(xhr, response, status) {
//	alert(response);
	var form = document.getElementById("add_form");
	newData = eval('('+response+')'); // If JSON would work, could be JSON.parse(response);
	if (newData["id"]) {
		if (form["cust_id"].value != "" && form["cust_id"].value != newData["id"]) {
			if (!confirm("You entered a customer name which came with associated information.\nWould you like to load the form with that information?\nIf you choose not to, the previous customer name will get overwritten with the new one."))
				return;
		}
		form.reset();
		resetPorE("phone");
		resetPorE("email");
		form.cust_id.value = newData["id"];
		updateForm(form, newData);
		populatePhones(newData["phones"]);
		populateEmails(newData["emails"]);
		showBox(newData["country"]);
		showCreator(newData);
	}
}
// Bybasses the suggest feature when the form is first loaded
var CustName = function() {
// 	alert(document.getElementById("custname").value);
	if ( typeof CustName.count == 'undefined' ) {
		document.getElementById("name").value = document.getElementById("custname").value;
		suggestBypass("name");
		CustName.count = 0;
	}

	// 	document.getElementById("name").value = opener.savedname;
}