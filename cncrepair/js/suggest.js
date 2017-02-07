// Used to create deep copies of the suggest state variables so that multiple suggests
// can exist on one page.
function deepCopy(obj) {
	if (typeof obj === 'object') {
		var out = {}, i;
		for ( i in obj ) {
			out[i] = arguments.callee(obj[i]);
		}
		return out;
	} else return false;
}

// Removes the <span> tags from modelnum and serial suggests
var strip = function(str) {
	return str.replace(/ \<s.*n\>/g, "");
}

// holds the suggest timer.
var t = null;

// Provides an initial state variable collection
var suggestState = function() {
	this.lS = ""; // The last input recieved from the user
	this.iE = null; // Holds the element to listen to, set on Install()
	this.idb = ""; // Hold the base element id (without row nums)
	this.rn = ""; // Holds the row number for suggests in lists.
	this.hld = 0; // Holds the index of highlighted suggestion
}
var ss = new suggestState();

// Install the suggest functionality via EventListeners
var suggestInstall = function(inputID) {
	var s = deepCopy(ss);
	window[inputID+"Suggest"] = s;
	s.iE = document.getElementById(inputID);
	s.iE.setAttribute("autocomplete", "off");
	if (typeof window.opera  === "undefined") {
		s.iE.addEventListener("keydown", inputKeyPress, true); // keypress bugs out in opera? control-7 and control-9 act like up/down arrow keys.  some kind of keycode mixup
	} else {
		s.iE.addEventListener("keydown", inputKeyPress, true);
	}
	s.rn = s.iE.id.replace(/[a-z]/gi, "");
	s.idb = s.iE.id.replace(/[0-9]/g, "");
	s.iE.addEventListener("blur", waitHide, false);
	s.iE.focus();
}

// Hide the suggestShadow after search field blurred for 1s
var waitHide = function(e) {
	if (document.getElementById(this.id+"Shadow").style.display != "none")
		window.setTimeout("suggestHide('"+this.id+"')", 1000);
}
var suggestHide = function(id) {
	try {
		document.getElementById(id+"Shadow").style.display = "none";
	} catch(e) {}
}

// Highlights the suggestion, unHL the last highlighted
// Also called onmouseover
var suggestHL = function(sugg, s) {
	if (!s.iE)
		s = window[s+"Suggest"];
	try {
		document.getElementById(s.iE.id+"sugg"+s.hld).removeAttribute("class");
	} catch(e) {}
	try {
		var hl = document.getElementById(s.iE.id+"sugg"+sugg);
		hl.setAttribute("class", "highlight");
		window.clearTimeout(t);
		s.hld = sugg;
	} catch(e) {
		s.hld = 0;
	}
}

// On down key when nothing is highlighted
var findLastSugg = function(i, s) {
	if (document.getElementById(s.iE.id+"sugg"+i))
		suggestHL(i, s);
	else if (i > -1)
		findLastSugg(i - 1, s);
}

// Called by keypress in the input box
var inputKeyPress = function(e) {
	var s = window[this.id+"Suggest"];
	if (e.keyCode == 40 ) { // On down key
		dropBox = document.getElementById(s.iE.id+"Shadow");
		if (dropBox.style.display == "none") {
			dropBox.style.display = "block";
			s.hld = 0;
		} else {
			suggestHL(s.hld+1, s);
			e.preventDefault();
		}
	} else if (e.keyCode == 38 ) { // On up key
		if (s.hld == 0)
			findLastSugg(15, s);
		else
			suggestHL(s.hld - 1, s);
		e.preventDefault();
	} else if (e.keyCode == 39) { // Right arrow key
		if (s.hld < 1)
			s.hld = 1;
		s.iE.value = strip(document.getElementById(s.iE.id+"sugg"+s.hld).firstChild.data);
	} else if (e.keyCode == 27) { // Esc hides box
		s.hld = 0;
		suggestHide(s.iE.id);
	} else if (e.keyCode == 9) { // Tab
		if (s.hld > 0)
			suggestSelect(s);
		suggestHide(s.iE.id);
	} else if (e.keyCode == 13) { // Enter is pressed and something is HL
		e.preventDefault();
		suggestSelect(s);
	} else { // Something else is pressed, suggest in 100ms
		t = window.setTimeout("suggest('"+s.iE.id+"')", 100);
	}
}

// Populate the suggestions box and make it show up!
var suggest = function(id) {
	var s = window[id+"Suggest"];
	// If something has changed since last suggest()ion.
//	if (s.lS != s.iE.value) { // This would limit the suggestions sent
		if (s.iE.value == "") {
			suggestHide(id);
			s.lS = "";
			return false; // Leave the function
		}
		httpObj({
			url: "/suggest/"+s.idb+"/"+s.rn,
			complete: function(xhr, response, status) {
// 				alert(typeof window.modelnumsugg1);
				document.getElementById(s.iE.id+"Box").innerHTML = response;
				s.hld = 0;
				if (document.activeElement == s.iE)
					document.getElementById(s.iE.id+"Shadow").style.display = "block";

			},
			data: "q="+encodeURIComponent(s.iE.value),
		});
		s.lS = s.iE.value;
		return true;
//	}
}

// Called by 'enter' or 'tab' key press or mouseclick
var suggestSelect = function(s) {
	window.clearTimeout(t);
	if (!s.iE)
		s = window[s+"Suggest"];
	try {
		document.getElementById(s.iE.id+"sugg"+s.hld).removeAttribute("class");
		s.iE.value = strip(document.getElementById(s.iE.id+"sugg"+s.hld).innerHTML).replace(/amp;/g, "");
	} catch(e) {}
	if (s.iE.value != "" && document.getElementById(s.iE.id+"sugg"+s.hld)) {
		httpObj({
			url: "/suggest/filldata/"+s.iE.id,
			complete: updateData, // Defined in page-specific .js file.
			data: s.idb+"="+encodeURIComponent(document.getElementById(s.iE.id+"sugg"+s.hld).innerHTML.replace(/amp;/g, ""))
		});
		s.iE.focus();
	}
	suggestHide(s.iE.id);
}

// Used when bypassing actual suggest selection
var suggestBypass = function(s, data) {
	s = window[s+"Suggest"];
	if (!data)
		data = s.iE.value;
	if (s.iE.value != "") {
		httpObj({
			url: "/suggest/filldata/"+s.iE.id,
			complete: updateData,
			data: s.idb+"="+encodeURIComponent(data)
		});
		s.iE.focus();
	}
}