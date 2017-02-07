// Keeps track of the currently <selected> search value.
var oldval = "name";

// Called in the body's onload
var onLoad = function() {
	suggestInstall("name");
	suggestInstall("modelnum");
	var selected = document.getElementById("searchTerm").value;
	criteriaChange(selected);
}

// used to change the search criteria when the <select> changes
var criteriaChange = function(val) {
	var searchButton = document.getElementById("searchHistory");
	searchButton.setAttribute("onclick", "showHistory('"+val+"');");
	var oldbox = document.getElementById(oldval);
	oldbox.style.display = "none";
	var newbox = document.getElementById(val);
	newbox.value = "";
	newbox.style.display = "inline";
	oldval = val;
}

// replaces the old search results with the new ones.
var populateHistory = function(xhr, response, status) {
	var histList = document.getElementById("searchList");
	histList.innerHTML = response;
}

// Opens a new window with the selected quote or invoice.
// id comes in as quote## or invoice##
var openQuote = function(id) {
	var type = id.replace(/[0-9]/g, "");
	id = id.replace(/[a-z_]/gi, "");
	var newWindow = window.open();
	newWindow.location.href = "/"+type+"/index/"+id;
}

// sends the filter parameters and calls populateHistory
var showHistory = function(type) {
	var data = encodeURIComponent(document.getElementById(type).value);
	
	httpObj({
		url: "/quote/custHistory",
		complete: populateHistory,
		data: "id="+data+"&type="+type
	});
}