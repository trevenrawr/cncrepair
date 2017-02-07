// Sends login credentials
var login = function() {
	send("/cnc/userlogin", loginRes);
	return false;
}
// Sends account changes
var account = function()  {
	if (validateData(false)) send("/cnc/addaccount", addRes);
	return false;
}
// Sends account management stuff
var manage = function() {
	if (validateData(true)) send("/cnc/updateaccount", updateRes);
	return false;
}
// Sets up the privileges string for parsing on the server side
var acctmanage = function() {
	var form = document.getElementById("add_form");
	var data = "";
	for (var i in form.elements) {
		if (form.elements[i].type == "checkbox")
			data += form.elements[i].id+"="+form.elements[i].checked+"&";
	}
	httpObj({
		url: "/cnc/acctmanagesave",
		complete: saveRes,
		data: data
	});
	return false;
}
// Prepares password (MD5) and other data to send, and sends it.
var send = function(url, complete) {
	var user = encodeURIComponent(document.getElementById("user").value);
	var password = encodeURIComponent(hex_md5(document.getElementById("password").value));
	var name = "";
	if (document.getElementById("name"))
		name = encodeURIComponent(document.getElementById("name").value);
	var oldpass = "";
	if (document.getElementById("oldpassword"))
		oldpass = encodeURIComponent(hex_md5(document.getElementById("oldpassword").value));
	httpObj({
		url: url,
		complete: complete,
		data: "user="+user+"&password="+password+"&name="+name+"&oldpassword="+oldpass
	});
}

// Displays the result of a login attempt
var loginRes = function(xhr, response, status) {
	var data = eval("("+response+")");
	if (data["locked"] && data["locked"] == 1) {
		alert("Your account has been locked.  Contact an administrator for assistance.");
	} else if (data["name"]) {
		window.location.href = document.getElementById("referrer").value;
	} else {
		alert("Incorrect username or password.");
		document.forms.add_form.password.value = "";
		document.forms.add_form.password.focus();
	}
}

// Redirects after creating an account.
var addRes = function(xhr, response, status) {
	window.location.href = "/cnc/account";
}

// Redirects after account is updated
var updateRes = function(xhr, response, status) {
	window.location.href = "/cnc/manage";
}

// Displays the result of a privilege save
var saveRes = function(xhr, response, status) {
	if (response == "ok")
		alert("User privileges updated sucessfully.");
	else
		alert("User privileges not updated.");
}

// Validates the form information prior to submission
var validateData = function(manage) {
	var valid = true;
	form = document.getElementById("add_form");
	if (manage) {
		valid = (validate("oldpassword") && valid);
		if (form.oldpassword.value !== "" && form.password.value !== "") {
			// Cause the password matching to run if the new password is trying to be set.
			manage = false;
		}
	}
	if (form.password.value !== form.password1.value && !manage) {
		alert("Your new passwords do not match");
		form.password.value = "";
		form.password1.value = "";
		form.password.focus();
		return false;
	}
	if (!manage) valid = (validate("password") && valid);
	valid = (validate("name") && valid);
	
	var user = form["user"];
	var userchars = user.value.replace(/[0-9a-z]/gi, "");
	if (userchars.length > 0) {
		user.parentNode.style.border = "1px solid red";
		user.focus();
		valid = false;
	} else {
		user.parentNode.style.border = "none";
	}
	
	return valid;
}