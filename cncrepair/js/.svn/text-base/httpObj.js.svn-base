// httpObj constructor can receive request settings:
// url - the destination url
// contentType - request content type
// type - request type (default is GET)
// data - optional request parameters
// async - whether the request is asynchronous (default is true)
// showErrors - display errors
// complete - the callback function to call when the request completes
var httpObj = function(settings) {
// 	settings = settings  // this references the global window object.  Not sure why
	this.settings = settings;
	var url = settings.url ? settings.url : location.href;
	var contentType = settings.contentType ? settings.contentType : "application/x-www-form-urlencoded";
	var type = settings.type ? settings.type : "POST";
	var data = settings.data ? settings.data : null;
	var async = settings.async ? settings.async : true;
	var showErrors = settings.showErrors ? settings.showErrors : true;
	// Set the url for type "GET" submissions
	if (type == "GET") url += "?" + data;

	function displayError(message) {
		if (showErrors)
			alert("Error encountered: \n" + message);
	}

	function readResponse() {
		try {
			var contentType = xhr.getResponseHeader("Content-Type");
			if (contentType == "application/json") {
				var response = json_parse(xhr.responseText);
			} else if (contentType == "text/xml")
				var response = xhr.responseXml;
			else {
				var response = xhr.responseText;

			}
			if (settings.complete)
				settings.complete(xhr, response, xhr.status);
		} catch (e) {
			displayError(e.toString());
		}
	}

	function onreadystatechange() {
		if (xhr.readyState == 4) {
			if (xhr.status == 200 || xhr.status == 0) {
				try {
					readResponse();
				} catch(e) {
					displayError("Can't read response: " + e.toString());
				}
			} else {
				displayError("HTML Status "+xhr.status+": " + xhr.statusText);
			}
		}
	}

	httpObj.create = function () {
		var xmlHttp
		try {
			xmlHttp = new XMLHttpRequest();
		} catch(e) {
			xmlHttp = new ActiveXObject("Microsoft.XMLHttp");
		}
		if (!xmlHttp) {
			alert("Error making the xmlhttp.");
			return false;
		} else {
			return xmlHttp;
		}
	}
	if ( xhr ) alert ("xhr is set");

	// Create the object
	var xhr = httpObj.create();
	xhr.open(type, url, async);
	xhr.onreadystatechange = onreadystatechange;
	xhr.setRequestHeader("Content-Type", contentType);
	xhr.send(data);
}

// Simple AJAX Call to load to the "main" div
var ajaxLoadMain = function(target, data) {
	data = (data) ? data : null;
	httpObj({
		url: target,
		complete: function(xhr, response, status) {
			document.getElementById("main").innerHTML = response;
		},
		data: data
	});
}