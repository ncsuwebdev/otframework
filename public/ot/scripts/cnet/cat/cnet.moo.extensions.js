/*	Script: string.cnet.js
		These are mootools authored extensions designed to allow prototype.lite libraries run in this environment.

Dependancies:
	 mootools - <Moo.js>, <String.js>, <Array.js>

Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>
	

		Class: String
		This extends the <String> prototype.
	*/
String.extend({
/*	Property: stripTags
		Remove all html tags from a string.	*/
	stripTags: function() {
		return this.replace(/<\/?[^>]+>/gi, '');
  },
/*	Property: stripScripts
		Removes all script tags from an HTML string.
	*/
	stripScripts: function() {
		return this.replace(/<script[^>]*?>.*?<\/script>/img, '');
	},
/*	Property: evalScripts
		Executes scripts included in an HTML string.
	*/
	evalScripts: function() {
		var scripts = this.match(/<script[^>]*?>.*?<\/script>/g);
		if(scripts) scripts.each(function(script){
				eval(script.replace(/^<script[^>]*?>/, '').replace(/<\/script>$/, ''));
			});
	},
/*	Property: replaceAll
		Replaces all instances of a string with the specified value.
		
		Arguments:
		searchValue - the string you want to replace
		replaceValue - the string you want to insert in the searchValue's place
		regExOptions - defaults to "ig" but you can pass in your preference
		
		Example:
		>"I like cheese".replaceAll("cheese", "cookies");
		> > I like cookies
	*/
	replaceAll: function(searchValue, replaceValue, regExOptions) {
		return this.replace(new RegExp(searchValue, $pick(regExOptions,'gi')), replaceValue);
	},
/*	Property: urlEncode
		urlEncodes a string (if it is not already).
		
		Example:
		> "Mondays aren't that fun".urlEncode()
		> > Mondays%20aren%27t%20that%20fun
	*/
	urlEncode: function() {
		return (this.test('%'))?this:escape(this);
	},
/*	Property: parseQuery
		Turns a query string into an associative array of key/value pairs.
		
		Example:
(start code)
"this=that&what=something".parseQuery()
> { this: "that", what: "something" }

var values = "this=that&what=something".parseQuery();
> values.this > "that"
(end)
	*/

	parseQuery: function() {
		var vars = this.split(/[&;]/);
		var rs = {};
		if (vars.length) vars.each(function(val) {
			var keys = val.split('=');
			if (keys.length && keys.length == 2) rs[encodeURIComponent(keys[0])] = encodeURIComponent(keys[1]);
		});
		return rs;
	},
/*	Property: tidy
		Replaces common special characters with their ASCII counterparts (smart quotes, elipse characters, stuff from MS Word, etc.).
	*/
	tidy: function() {
		var txt = this.toString();
		$each({
			"[\xa0\u2002\u2003\u2009]": " ",
			"\xb7": "*",
			"[\u2018\u2019]": "'",
			"[\u201c\u201d]": '"',
			"\u2026": "...",
			"\u2013": "-",
			"\u2014": "--"
		}, function(value, key){
			txt = txt.replace(new RegExp(key, 'g'), value);
		});
		return txt;
	}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Native/string.cnet.js,v $
$Log: string.cnet.js,v $
Revision 1.3  2007/11/19 23:23:07  newtona
CNETAPI: added method "getMany" to all the CNETAPI.Utils.* classes so that you can get numerous items in one request.
ObjectBrowser: improved exclusion handling for child elements
jlogger.js, element.position.js: docs update
Fx.Sort: cleaning up tabbing
string.cnet: just reformatting the logic a little.

Revision 1.2  2007/07/17 20:38:44  newtona
Fx.SmoothShow - refactored the exploration of the element dimensions when hidden so that it isn't visible to the user
element.position - refactored to allow for more than just the previous 5 positions, now supports nine: all corners, all mid-points between those corners, and the center
string.cnet.js - fixed up the query string logic to decode values

Revision 1.1  2007/05/29 21:25:31  newtona
splitting up element.cnet.js (which had grown to be too unweildy); moving things into sub-directories

Revision 1.8  2007/03/16 00:23:24  newtona
added string.tidy and element.tidy

Revision 1.7  2007/03/09 20:14:47  newtona
strict javascript warnings cleaned up

Revision 1.6  2007/03/08 23:32:14  newtona
strict javascript warnings cleaned up

Revision 1.5  2007/02/06 18:14:01  newtona
re-implemented replaceAll because String.replace(new, old, "ig") doesn't work in IE. Ungh. IE.

Revision 1.4  2007/01/26 06:08:27  newtona
updated docs
refactored .replaceAll
removed dependency on Prototype.compatibility.js

Revision 1.3  2006/11/15 01:19:19  newtona
added String.parseQuery

Revision 1.2  2006/11/02 21:34:00  newtona
Added cvs footer


*/
/*	Script: window.cnet.js
Extends the Mootools <Window> class.

Class: window
		This extends the <window> class from the <http://mootools.net> library.
		
Dependencies:
 mootools = <Moo.js>, <Utility.js>, <Common.js>, <Window.Base.js>
 cnet - <string.cnet.js>
	*/
window.extend({
/*	property: isLoaded (deprecated)
		Use <window.loaded>; true if the dom is ready.
	*/
	isLoaded: window.loaded,
/*	Property: getHost	
		Returns the domain of the window or the passed in url.
		
		Arguments:
		url (optional) - the url you wish to get the host for (otherwise window.getHost
							returns the host of the current window location).
*/
	getHost:function(url){
		url = $pick(url, window.location.href);
		var host = url;
		if(url.test('http://')){
			url = url.substring(url.indexOf('http://')+7,url.length);
			if(url.test(':')) url = url.substring(0, url.indexOf(":"));
			if(url.test('/')) return url.substring(0,url.indexOf('/'));
			return url;
		}
		return false;
	},
/*	Property: getQueryStringValue
		Returns a specific query string value from the window location.
		
		Arguments:
		key - the key to search for in the query string
		url - (optional) url with a query string to parse (defaults to window.location)
		
		Example:
(start code)
//window.location is http://www.example.com/?red=apple&yellow=lemon
var something = window.getQueryStringValue("red");
> something = "apple"
(end)
	*/
	getQueryStringValue: function(key, url) {
		try { 
			return window.getQueryStringValues(url)[key];
		}catch(e){return null;}
	},

/* Property: getQueryStringValues 
		An object with name/value pairs of the values in the query string of the window.
		
		Arguments:
		url - (optional) url with a query string to parse (defaults to window.location)
		
		Example:
		If you were on the page http://www.example.com?red=apple&yellow=lemon
		
		then window.getQueryStringValues() would return:
(start code)
{
	red: 'apple',
	yellow: 'lemon'
}
(end)
*/
		getQueryStringValues: function(url){
			var qs = $pick(url, $pick(window.location.search, '')).split('?')[1]; //get the query string
			if(qs) return qs.parseQuery();
			return {}; //if there isn't one, return null
		},

	
/*	Property: getPort
		Returns the port number of the window location.
		
		Arguments
		url - (optional) the url to test for a port; defaults to the window location.
		
		Example:
		(start code)
//window.location.href is http://www.example.com:8001/blah.html
window.getPort()
> 8001
		(end)
	*/
	getPort: function(url) {
		url = $pick(url, window.location.href);
		var re = new RegExp(':([0-9]{4})');
		var m = re.exec(url);
	  if (m == null) return false;
	  else {
			var port = false;
			m.each(function(val){
				if($chk(parseInt(val))) port = val;
			});
	  }
		return port;
	},
/*	Property:	qs
		An object with name/value pairs of the values in the query string of the window.
		
		Example:
		If you were on the page http://www.example.com?red=apple&yellow=lemon
		
		then window.qs would be:
(start code)
{
	red: 'apple',
	yellow: 'lemon'
}
(end)
	*/
	qs: {}
});
window.qs = window.getQueryStringValues();

/*	Class: window.popup
This class opens a popup window with the passed in values.
		
Arguments
	url - the destination for the popup
	options - an object containing key/value options
	
Options:
	width - (integer) the width of the window; defaults to 500
	height - (integer) the height of the window; defaults to 300
	x - (integer) the offest from the left of the screen; defaults to 50
	y - (integer) the offset from the top of the screen; defaults to 50
	toolbar - (integer) show the browser toolbar in the window; 
			0 (zero) does not show it, 1 (one) does; defaults to 0 (zero)
	location - (integer) show the location in the browser;
			0 does not show it; defautls to 0
	directories - (integer) show the directories in the browser;
			0 does not show it; defautls to 0
	status - (integer) show the status bar in teh browser;
			0 does not show it; defautls to 0
	scrollbars - (string) 'auto' shows the scroll bars if they are required,
			'no' shows none, 'yes' shows them all the time
	resizable - (integer) lets the user resize the window;
			1 allows resizeing; defaults to 1
	name - (string) the name of the popup; defaults to "popup"
	
	Examples:
	(start code)
var myPopup = new window.popup('http://www.example.com'); //opens with default parameters

var myPopup = new window.popup('http://www.example.com', {
	width: 300,
	height: 800,
	x: 500,
	toolbar: 1
}); //launch a window with custom properties
	(end)

	Property: popupWindow
	The window object itself (the popup). The class window.popup opens a new browser window. The pointer to this
	window can be reached like so:
	(start code)
	var myPopup = new window.popup('http://www.example.com');
	myPopup.popupWindow // this is the reference to the popup itself.
	(end)
	
	Note that if you call this class with the same name (the default name is 'popup') as an already open window
	you won't open a new popup window, but instead will send your url to the existing window. You should probably
	give it something unique so you can have more than one if you need. 

	Example:
	(start code)
	var myPopup = new window.popup('http://www.example.com'); //default name for the popup is "popup"
	var anotherPopup = new window.popup('http://www.example2.com'); //you just refreshed the "popup" window with this new url
	(end)
	
	This actually represents a way to keep refering to the same window that's already open. So long as the window
	calling it is the same window that opened the popup to begin with (even if the user goes to another page), the
	above code will always re-acquire the already open popup.
	
	Example:
	(start code)
	//page loads
	var myPopup = new window.popup('http://www.example.com'); //default name for the popup is "popup"
	
	//user goes to another page, and, when that page loads, this happens again
	var myPopup = new window.popup('http://www.example.com'); //default name for the popup is "popup"
	(end)
	
	The result is you just refreshed the already open window with the same url. There are ways to do this
	without refreshing, but not with this class (yet).
	*/
window.popup = new Class({
	options: {
			width: 500,
			height: 300,
			x: 50,
			y: 50,
			toolbar: 0,
			location: 0,
			directories: 0,
			status: 0,
			scrollbars: 'auto',
			resizable: 1,
			name: 'popup',
			onBlock: Class.empty
	},
	initialize: function(url, options){
		this.url = url || false;
		this.setOptions(options);
		if(this.url) this.openWin();
		return this;
	},
	openWin: function(url){
		url = url || this.url;
		var options = 'toolbar='+this.options.toolbar+
			',location='+this.options.location+
			',directories='+this.options.directories+
			',status='+this.options.status+
			',scrollbars='+this.options.scrollbars+
			',resizable='+this.options.resizable+
			',width='+this.options.width+
			',height='+this.options.height+
			',top='+this.options.y+
			',left='+this.options.x;
		this.popupWindow = window.open(url,
			this.options.name, options);
		this.focus.delay(100, this);
		return this.popupWindow;
	},
/*	Property: focus
		Focus the window related to the window.popup object.
		
		Example:
		(start code)
var myPopup = new window.popup('http://www.example.com'); //opens with default parameters
myPopup.focus(); //bring it to the front
		(end)
		
		Note:
		When you create a new popup it calls .focus() on itself immediately by default.
	*/
	focus: function(){
		if (this.popupWindow) this.popupWindow.focus();
		else if (this.focusTries<10) this.focus.delay(100, this); //try again
		else {
			this.blocked = true;
			this.fireEvent('onBlock');
		}
		return this;
	},
	focusTries: 0,
	blocked: null,
/*	Property: close
		Closes the popup window related to the window.popup object.

		Example:
		(start code)
var myPopup = new window.popup('http://www.example.com'); //opens with default parameters
myPopup.close(); //close the window
		(end)

	*/
	close: function(){
		this.popupWindow.close();
	}
});
window.popup.implement(new Options);
window.popup.implement(new Events);

/*	Class: legacyPopup
		A legacy instance of <window.popup> that defaults to a specific width and height; not intended for use.	*/
var legacyPopup = window.popup.extend({
	setOptions: function(){
		this.parent();
		this.options = Object.extend({
			width: 516, 
			height: 350
		}, this.options);
	}
});

/*	Function: openPop
		An instance of <legacyPopup>; not intended for actual use.
	*/
function openPop(url){
	return new legacyPopup(url);
}

/*	Function: GetValue
		Legacy syntax for window.getQueryStringValue; deprecated.
	*/
var GetValue = window.getQueryStringValue;
	
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Native/window.cnet.js,v $
$Log: window.cnet.js,v $
Revision 1.5  2007/09/13 22:19:46  newtona
modalizer now fixes it's overlay if hte window is resized
mooscroller refactors and faster; no more dependencies on Drag or Scroller
window.cnet - updating the query string method and returning a consistent value (an object)

Revision 1.4  2007/08/15 01:03:34  newtona
Added more event info for Autocompleter.js
Slimbox no longer adds css to the page if there aren't any images found for the instance
Iframeshim now exits quietly if you try and position it before the dom is ready
jsonp now handles having more than one request open at a time
removed a console.log statement from window.cnet.js (shame on me for leaving it there)

Revision 1.3  2007/07/20 00:06:02  newtona
fixed an error with window.popup arguments

Revision 1.2  2007/06/25 17:24:22  newtona
fixed a typo in window.popup

Revision 1.1  2007/05/29 21:25:31  newtona
splitting up element.cnet.js (which had grown to be too unweildy); moving things into sub-directories

Revision 1.18  2007/04/11 20:52:23  newtona
removing function.cnet.js dependency

Revision 1.17  2007/03/10 01:24:47  newtona
getQueryString returns an empty array instead of null

Revision 1.16  2007/03/09 20:14:47  newtona
strict javascript warnings cleaned up

Revision 1.15  2007/02/21 00:22:21  newtona
fixed some syntax problems
implemented Events

Revision 1.14  2007/02/08 01:30:37  newtona
renamed the popup window in the class window.popup "popupWindow" instead of "window" - a reserved name in IE

Revision 1.13  2007/01/26 06:13:34  newtona
now .isLoaded = .loaded
syntax update for mootools 1.0
getHost takes a url now (defaults to window.location)
added getQueryStringValues
added .qs - an object of the window query string values

Revision 1.12  2007/01/22 22:02:02  newtona
removed ie background cache fixed; it's in mootools 1.0

Revision 1.11  2007/01/19 01:23:09  newtona
fixed a bug in window.getHost

Revision 1.10  2007/01/09 01:29:24  newtona
added returns of the Window.popup class when calling functions on Window.popups

Revision 1.9  2006/11/27 19:34:32  newtona
changed the line about firefox bugs; this comment was misleading. no functional changes.

Revision 1.8  2006/11/26 00:28:19  newtona
forgot about ports in getHost... fixed

Revision 1.7  2006/11/26 00:26:35  newtona
ok. actually *fixed* the bug with getHost

Revision 1.6  2006/11/26 00:16:48  newtona
fixed conditional bug in Window.getHost

Revision 1.5  2006/11/16 18:50:40  newtona
fixed a syntax error in getQueryStringValue

Revision 1.4  2006/11/15 01:19:49  newtona
added Window.getQueryStringValue

Revision 1.3  2006/11/04 00:54:35  newtona
added Widnow.getPort()

Revision 1.2  2006/11/02 21:34:00  newtona
Added cvs footer


*/
/*	Script: element.shortcuts.js
Extends the <Element> object with some basic shortcuts (like .hide and .show).

Dependancies:
	 mootools - <Moo.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>

Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>
	
Class: Element
		This extends the <Element> prototype.
	*/
Element.extend({

/*	Property: isVisible
		Returns a boolean; true = visible, false = not visible.
		
		Example:
		>$(id).isVisible()
		> > true | false	*/
	isVisible: function() {
		return this.getStyle('display') != 'none';
	},
/*	Property: toggle
		Toggles the state of an element from hidden (display = none) to 
		visible (display = what it was previously or else display = block)
		
		Example:
		> $(id).toggle()
	*/
	toggle: function() {
		return this[this.isVisible() ? 'hide' : 'show']();
	},
/*	Property: hide
		Hides an element (display = none)
		
		Example:
		> $(id).hide()
		*/
	hide: function() {
		this.originalDisplay = this.getStyle('display'); 
		this.setStyle('display','none');
		return this;
	},
/*	Property: show
		Shows an element (display = what it was previously or else display = block)
		
		Example:
		>$(id).show() */
	show: function(display) {
		this.originalDisplay = (this.originalDisplay=="none")?'block':this.originalDisplay;
		this.setStyle('display',(display || this.originalDisplay || 'block'));
		return this;
	},
/*	Property: tidy
		Uses <String.tidy> to clean up common special characters with their ASCII counterparts (smart quotes, elipse characters, stuff from MS Word, etc.).
	*/
	tidy: function(){
		try {	
			if(this.getValue().tidy())this.value = this.getValue().tidy();
		}catch(e){dbug.log('element.tidy error: %o', e);}
	},
/*	Property: findParent
		Returns the first element that contains	this element within a collection.
		
		Arguments:
		collection - (string or array) Either a css selector ("div.className") or a collection ($$(elements)) to inspect; the first element in the collection that contains this element is returned.
		*/
	findParent: function(collection){
		return $$(collection).filter(function(el){
			return el.hasChild(this);
		}, this)[0];
	},
	//DO NOT USE THIS METHOD
	//it is temporary, as Mootools 1.1 will negate its requirement
	fxOpacityOk: function(){
		if (!window.ie6)return true;
		var isColor = false;
		try {
			if (new Color(this.getStyle('backgroundColor'))) isColor = true;
		}catch(e){}
		return isColor;
	}
});
//legacy namespace
Element.visible = Element.isVisible;


if(!Element.empty) {
	Element.extend({
		/*
		Property: empty
			Empties an element of all its children (overridden by Mootools 1.1 <Element.empty> if present).
	
		Example:
			>$('myDiv').empty() // empties the Div and returns it
	
		Returns:
			The element.
		*/
		empty: function(){
			//Garbage.trash(this.getElementsByTagName('*'));
			return this.setHTML('');
		}
	});
}
/*	legacy support for $S	*/
var $S = $$;
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Native/element.shortcuts.js,v $
$Log: element.shortcuts.js,v $
Revision 1.5  2007/10/15 18:21:56  newtona
element.findParent was buggy; fixed.

Revision 1.4  2007/09/17 22:06:08  newtona
added Element.getParent(selector) method.

Revision 1.3  2007/05/30 20:32:33  newtona
doc updates

Revision 1.2  2007/05/29 22:01:53  newtona
Split element.cnet.js into seperate files; updated docs in files to note this
Changed element.visible to element.isVisible (left old namespace for legacy support)
Fixed Element.empty in prototype.compatibility.js
Removed as many dependencies in common code to element.*.js as possible (espeically element.shortcuts.js)

Revision 1.1  2007/05/29 21:25:31  newtona
splitting up element.cnet.js (which had grown to be too unweildy); moving things into sub-directories

Revision 1.29  2007/05/17 19:45:57  newtona
updated element.empty for mootools 1.1

Revision 1.28  2007/05/16 20:09:42  newtona
adding new js files to redball.common.full
product.picker.js now has no picklets; these are in the implementations/picklets directory
ProductPicker now detects if there is no doctyp and, if not, sets the position of the picker to be fixed (no IE6 support)
small docs update in element.cnet.js
added new picklet: CNETProductPicker_PricePath
added new picklet: NewsStoryPicker_Path
new file: clipboard.js (allows you to insert text into the OS clipboard)
new file: html.table.js (automates building html tables)
new file: element.forms.js (for managing text inputs - get selected text information, insert content around selection, etc.)

Revision 1.27  2007/05/05 01:01:26  newtona
stickywinHTML: tweaked the options for buttons
element.cnet: tweaked smoothshow/hide css handling

Revision 1.26  2007/04/09 21:35:22  newtona
removing garbage collection from Element.empty (will have to wait for Mootools 1.1)

Revision 1.25  2007/04/02 18:04:48  newtona
syntax fix

Revision 1.24  2007/03/30 21:42:43  newtona
adding Element.isEmpty

Revision 1.23  2007/03/30 19:27:58  newtona
moved .empty to .flush

Revision 1.22  2007/03/29 22:36:42  newtona
Element.fxOpacityOk now only checks bgcolor for ie6
Added Element.flush

Revision 1.21  2007/03/28 23:22:54  newtona
Element.smoothShow/smoothHide: added Element.fxOpacityOk to deal with the IE bug where text gets blurry when you fade an element in and out without a bgcolor set

Revision 1.20  2007/03/28 18:07:21  newtona
added Element.fxOpacityOk to deal with the IE bug where text gets blurry when you fade an element in and out without a bgcolor set

Revision 1.19  2007/03/26 18:30:12  newtona
iframeShim: fixed reference to options (should be this.options)
element.cnet: removed some dbug lines

Revision 1.18  2007/03/23 20:13:38  newtona
getDimensions: added support for getComputedSize
getComputedSize: added
setPosition: added edge option, uses getComputedSize
smoothHide: uses getComputedSize
smoothShow: uses getComputedSize
sumObj: removed function; no longer needed

Revision 1.17  2007/03/16 00:23:24  newtona
added string.tidy and element.tidy

Revision 1.16  2007/03/08 23:32:14  newtona
strict javascript warnings cleaned up

Revision 1.15  2007/03/01 00:50:35  newtona
type.isNumber now returns false for NaN
element.smoothshow/hide now works (in IE specifically) when there are no values for border

Revision 1.14  2007/02/27 19:37:56  newtona
element.show now enforces that the original display was not 'none'

Revision 1.13  2007/02/22 21:05:35  newtona
smoothHide now checks that the element is not already hidden

Revision 1.12  2007/02/21 00:21:22  newtona
added legacy support for $S

Revision 1.11  2007/02/08 22:14:04  newtona
added border widths to smoothshow/hide

Revision 1.10  2007/02/08 01:30:58  newtona
tweaking element.setPosition, now can use effects

Revision 1.9  2007/02/07 20:52:34  newtona
added Element.position

Revision 1.8  2007/02/06 18:13:13  newtona
added element.smoothShow and smoothHide; depends on latest svn of mootools

Revision 1.7  2007/02/03 01:40:05  newtona
fixed a typo bug

Revision 1.6  2007/01/26 06:06:13  newtona
element.replace now takes a 2nd argument to eval scripts or not
element.getDimensions now returns w & h for hidden elements

Revision 1.5  2007/01/05 19:45:48  newtona
made getDimensions capable of discovering dimensions of hidden elements

Revision 1.4  2006/12/06 20:14:59  newtona
carousel - improved performance, changed some syntax, actually deployed into usage and tested
cnet.nav.accordion - improved css selectors for time
multiple accordion - fixed a typo
dbug.js - added load timers
element.cnet.js - changed syntax to utilize mootools more effectively
function.cnet.js - equated $set to $pick in preparation for mootools v1

Revision 1.3  2006/11/27 17:59:32  newtona
small change to replace and the way it uses timeouts

Revision 1.2  2006/11/02 21:34:00  newtona
Added cvs footer


*/
/*	Script: element.dimensions.js
Extends the <Element> object.

Dependancies:
	 mootools - <Moo.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>

Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>
	
Class: Element
		This extends the <Element> prototype.
	*/
Element.extend({
/*	Property: getDimensions
		Returns width and height for element; if element is not visible the element is
		cloned off screen, shown, measured, and then removed.
		
		Arguments:
		options - a key/value set of options
		
		Options:
		computeSize - (boolean; optional) use <Element.getComputedSize> or not; defaults to false
		styles - (array; optional) see <Element.getComputedSize>
		plains - (array; optional) see <Element.getComputedSize>
		
		Returns:
		An object with .width and .height defined as integers. If options.computeSize is true, returns
		all the values that <Element.getComputedSize> returns.
		
		Example:
		>$(id).getDimensions()
		> > {width: #, height: #}
	*/
	getDimensions: function(options) {
		options = $merge({computeSize: false},options);
		var dim = {};
		function getSize(el, options){
			if(options.computeSize) dim = el.getComputedSize(options);
			else {
				dim.width = el.getSize().size.x;
				dim.height = el.getSize().size.y;
			}
			return dim;
		}
		try { //safari sometimes crashes here, so catch it
			dim = getSize(this, options);
		}catch(e){}
		if(this.getStyle('display') == 'none'){
			var before = {};
			//use this method instead of getStyles 
			['visibility', 'display', 'position'].each(function(style){
				before[style] = this.style[style]||'';
			}, this);
			//this.getStyles('visibility', 'display', 'position');
			this.setStyles({
				visibility: 'hidden',
				display: 'block',
				position:'absolute'
			});
			dim = getSize(this, options); //works now, because the display isn't none
			this.setStyles(before); //put it back where it was
		}
		return $merge(dim, {x: dim.width, y: dim.height});
	},
/*	Property: getComputedSize
		Calculates the size of an element including the width, border, padding, etc.
		
		Arguments:
		options - an object with key/value options
		
		Options:
		styles - (array) the styles to include in the calculation; defaults to ['padding','border']	
		plains - (object) an object with height and width properties, each of which is an 
							array including the edges to include in that plain. 
							defaults to {height: ['top','bottom'], width: ['left','right']}
		mode - (string; optional) limit the plain to 'vertical' or 'horizontal'; defaults to 'both'
		
		Returns:
		size - an object that contans dimension values (integers); see list below
		
		
		Dimension Values Returned:
		width - the actual width of the object (not including borders or padding)
		height - the actual height of the object (not including borders or padding)
		border-*-width - (where * is top, right, bottom, and left) the width of the border on that edge
		padding-* - (where * is top, right, bottom, and left) the width of the padding on that edge
		computed* - (where * is Top, Right, Bottom, and Left; e.g. computedRight) the width of all the 
			styles on that edge computed (so if options.styles is left to the default padding and border,
			computedRight is the sum of border-right-width and padding-right)
		totalHeight - the total sum of the height plus all the computed styles on the top or bottom. by
			default this is just padding and border, but if you were to specify in the styles option
			margin, for instance, the totalHeight calculated would include the margin.
		totalWidth - same as totalHeight, only using width, left, and right

		Example:
(start code)
$(el).getComputedSize();
returns:
{
	padding-top:0,
	border-top-width:1,
	padding-bottom:0,
	border-bottom-width:1,
	padding-left:0,
	border-left-width:1,
	padding-right:0,
	border-right-width:1,
	width:100,
	height:100,
	totalHeight:102,
	computedTop:1,
	computedBottom:1,
	totalWidth:102,
	computedLeft:1,
	computedRight:1
}
(end)		
	*/
	getComputedSize: function(options){
		options = $merge({
			styles: ['padding','border'],
			plains: {height: ['top','bottom'], width: ['left','right']},
			mode: 'both'
		}, options);
		var size = {width: 0,height: 0};
		switch (options.mode){
			case 'vertical':
				delete size.width;
				delete options.plains.width;
				break;
			case 'horizontal':
				delete size.height;
				delete options.plains.height;
				break;
		}
		var getStyles = [];
		//this function might be useful in other places; perhaps it should be outside this function?
		$each(options.plains, function(plain, key){
			plain.each(function(edge){
				options.styles.each(function(style){
					getStyles.push((style=="border")?style+'-'+edge+'-'+'width':style+'-'+edge);
				});
			});
		});
		var styles = this.getStyles.apply(this, getStyles);
		var subtracted = [];
		$each(options.plains, function(plain, key){ //keys: width, height, plains: ['left','right'], ['top','bottom']
			size['total'+key.capitalize()] = 0;
			size['computed'+key.capitalize()] = 0;
			plain.each(function(edge){ //top, left, right, bottom
				size['computed'+edge.capitalize()] = 0;
				getStyles.each(function(style,i){ //padding, border, etc.
					//'padding-left'.test('left') size['totalWidth'] = size['width']+[padding-left]
					if(style.test(edge)) {
						styles[style] = styles[style].toInt(); //styles['padding-left'] = 5;
						if(isNaN(styles[style]))styles[style]=0;
						size['total'+key.capitalize()] = size['total'+key.capitalize()]+styles[style];
						size['computed'+edge.capitalize()] = size['computed'+edge.capitalize()]+styles[style];
					}
					//if width != width (so, padding-left, for instance), then subtract that from the total
					if(style.test(edge) && key!=style && 
						(style.test('border') || style.test('padding')) && !subtracted.test(style)) {
						subtracted.push(style);
						size['computed'+key.capitalize()] = size['computed'+key.capitalize()]-styles[style];
					}
				});
			});
		});
		if($chk(size.width)) {
			size.width = size.width+this.offsetWidth+size.computedWidth;
			size.totalWidth = size.width + size.totalWidth;
			delete size.computedWidth;
		}
		if($chk(size.height)) {
			size.height = size.height+this.offsetHeight+size.computedHeight;
			size.totalHeight = size.height + size.totalHeight;
			delete size.computedHeight;
		}
		return $merge(styles, size);
	}
});
/* do not edit below this line */	 
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Native/element.dimensions.js,v $
$Log: element.dimensions.js,v $
Revision 1.8  2007/09/07 22:19:30  newtona
popupdetails: updating options handling methodology
stickyWinFx: fixed a bug where, if you were fast enough, you could introduce a flicker bug - this is hard to produce so most people probably hadn't seen it

Revision 1.7	2007/08/27 23:09:02	newtona
MooScroller: removed periodical for scrollbar resizing; the user can implement this if it's needed for each instance; also, renamed refactor to update
dbug: added support for dbug.dir, profile, stackTrace, etc.
element.dimensions: when getting the size of hidden elements the method now restores the previous inline styles to their original state
element.pin: fixed positioning bug

Revision 1.6	2007/08/20 19:53:33	newtona
fixing a typo

Revision 1.5	2007/08/20 18:11:51	newtona
Iframeshim: better handling for methods when the shim isn't ready
stickyWin: fixed a bug for the cssClass option in stickyWinHTML
html.table: push now returns the dom elements it creates
jsonp: fixed a bug; request no longer requires a url argument (my bad)
Fx.SmoothMove: just tidying up some syntax.
element.dimensions: updated getDimensions method for computing size of elements that are hidden; no longer clones element

Revision 1.4	2007/08/08 18:22:07	newtona
fixed a bug with Element.getDimensions (which affected Fx.SmoothMove, Fx.SmoothShow, Element.setPosition, and the bazillion other things that use it). would only show up under certain CSS layout situations.

Revision 1.3	2007/05/30 20:32:33	newtona
doc updates

Revision 1.2	2007/05/29 22:01:53	newtona
Split element.cnet.js into seperate files; updated docs in files to note this
Changed element.visible to element.isVisible (left old namespace for legacy support)
Fixed Element.empty in prototype.compatibility.js
Removed as many dependencies in common code to element.*.js as possible (espeically element.shortcuts.js)

Revision 1.1	2007/05/29 21:25:31	newtona
splitting up element.cnet.js (which had grown to be too unweildy); moving things into sub-directories


*/
/*	Script: element.forms.js
		Handles numerous element functions for editing text.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Mootools - <Moo.js>, <Utilities.js>, <Common.js>, <Array.js>, <String.js>, <Element.js>, <Function.js>
	*/

Element.extend({

/*	Property: getTextInRange
		Returns the text of an input within a range.
		
		Arguments:
		start - beginning select position
		end - end position
	*/

	getTextInRange: function(start, end) {
		return this.getValue().substring(start, end);
	},

/*	Property: getSelectedText
		Get the text selected in an input, returns a range (see <Element.getTextInRange>).
	*/
	getSelectedText: function() {		
		if(window.ie) return document.selection.createRange().text;
		return this.getValue().substring(this.getSelectionStart(), this.getSelectionEnd());
	},

/*	Property: getSelectionStart
		Returns the index of start of the selected text.
	*/

	getSelectionStart: function() {
		if(window.ie) {
			this.focus();
			var range = document.selection.createRange();
			var tmp = range.duplicate();
			tmp.moveToElementText(this);
			tmp.setEndPoint('EndToEnd', range);
			return tmp.text.length - range.text.length;
		}
		return this.selectionStart;
	},

/*	Property: getSelectionEnd
		Returns the index of end of the selected text.
	*/

	getSelectionEnd: function() {
		if(window.ie) {
			this.focus();
			var range = document.selection.createRange();
			var tmp = range.duplicate();
			tmp.moveToElementText(this);
			tmp.setEndPoint('EndToEnd', range);
			return this.getSelectionStart() + range.text.length;
		}
		return this.selectionEnd;
	},


/*	Property: getSelectedRange
		Gets the range of what is selected within the element.
		
		Returns:
		Object with start and end properties.
		
		Example:
		>{start: 2, end: 12} */

	getSelectedRange: function() {
		return {
			start: this.getSelectionStart(),
			end: this.getSelectionEnd()
		}
	},
	
/*	Property: setCaretPosition
		Sets the caret at the given position.
		
		Arguments:
		pos - (integer) the location to place the caret OR "end" to place it at the end.
	*/

	setCaretPosition: function(pos) {
		if(pos == 'end') pos = this.getValue().length;
		this.selectRange(pos, pos);
		return this;
	},

/*	Property: getCaretPosition
		Returns the caret position (integer). */

	getCaretPosition: function() {
		return this.getSelectedRange().start;
	},
	
/*	Property: selectRange
		Selects text within a given range.
		
		Arguments:
		start - (integer) starting integer
		end - (integer) ending integer
		
		Examples:
(start code)
<input id="test" value="012345">
<script>
$('test').selectRange(2,4); //selects "23"
</script>
(end)
	*/

	selectRange: function(start, end) {
		this.focus();
		if(window.ie) {
			var range = this.createTextRange();
			range.collapse(true);
			range.moveStart('character', start);
			range.moveEnd('character', end - start);
			range.select();
			return this;
		}
		this.setSelectionRange(start, end);
		return this;
	},

/*	Property: insertAtCursor
		Inserts a value at the cursor location; if text is selected, it replaces this text.
		
		Arguments:
		value - (string) value to insert.
		selectText - (boolean) selects the text after it's been inserted
	*/

	insertAtCursor: function(value, select) {
		var start = this.getSelectionStart();
		var end = this.getSelectionEnd();
		this.value = this.getValue().substring(0, start) + value + this.getValue().substring(end, this.getValue().length);
 		if($pick(select, true)) this.selectRange(start, start + value.length);
		else this.setCaretPosition(start + value.length);
		return this;
	},
 
/*	Property: insertAroundCursor
		Inserts values around selected text (think HTML).
		
		Arguments:
		options - (object) key/value set of options.
		
		Options:
		before - (string) the prefix to insert before the selected text
		after - (string) the suffix to insert after the selected text
		defaultMiddle - (string) value to insert between the prefix and the suffix if no text was selected (defaults to "SOMETHING HERE")
	*/

	insertAroundCursor: function(options, select) {
		options = $merge({
			before: '',
			defaultMiddle: 'SOMETHING HERE',
			after: ''
		}, options);
		value = this.getSelectedText() || options.defaultMiddle;
		var start = this.getSelectionStart();
		var end = this.getSelectionEnd();
		if(start == end) {
			var text = this.getValue();
			this.value = text.substring(0, start) + options.before + value + options.after + text.substring(end, text.length);
			this.selectRange(start + options.before.length, end + options.before.length + value.length);
			text = null;
		} else {
			text = this.getValue().substring(start, end);
			this.value = this.getValue().substring(0, start) + options.before + text + options.after + this.getValue().substring(end, this.getValue().length);
			var selStart = start + options.before.length;
			if($pick(select, true)) this.selectRange(selStart, selStart + text.length);
			else this.setCaretPosition(selStart + text.length);
		}	
		return this;
	}
});

/* do not edit below this line */	 
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Native/element.forms.js,v $
$Log: element.forms.js,v $
Revision 1.6  2007/08/28 23:16:09  newtona
IconMenu now handles deleteing items off screen more effectively
reverted some logic in element.forms; the new stuff was a little buggy

Revision 1.5  2007/08/02 18:38:30  newtona
fixed a bug in element.forms for IE

Revision 1.4  2007/05/30 20:32:33  newtona
doc updates

Revision 1.3  2007/05/29 23:06:47  newtona
fixed a few returns in element.form.js

Revision 1.2  2007/05/29 22:58:29  newtona
fixed a bug in reference to range as an array (which is no longer the case)

Revision 1.1  2007/05/29 21:25:31  newtona
splitting up element.cnet.js (which had grown to be too unweildy); moving things into sub-directories

Revision 1.3  2007/05/16 22:20:18  newtona
fixded a bug with insertAround

Revision 1.2  2007/05/16 21:39:41  newtona
added missing ;

Revision 1.1  2007/05/16 20:09:42  newtona
adding new js files to redball.common.full
product.picker.js now has no picklets; these are in the implementations/picklets directory
ProductPicker now detects if there is no doctyp and, if not, sets the position of the picker to be fixed (no IE6 support)
small docs update in element.cnet.js
added new picklet: CNETProductPicker_PricePath
added new picklet: NewsStoryPicker_Path
new file: clipboard.js (allows you to insert text into the OS clipboard)
new file: html.table.js (automates building html tables)
new file: element.forms.js (for managing text inputs - get selected text information, insert content around selection, etc.)


*//*	Script: element.pin.js
		Extends the Mootools <Window> and <Element> classes to allow fixed positioning for an element.

		Dependencies:
		mootools = <Element.js>, <Window.js> and all their dependencies
	
		Class: window
		This extends the <window> class from the <http://mootools.net> library.
	*/
window.extend({
/*	Property: supportsPositionFixed
		Returns true if the browser supports fixed positioning; must be called after DomReady (or it returns null);
	*/
	supportsPositionFixed: function(){
		if(!window.loaded) return null;
		var test = new Element('div').setStyles({
			position: 'fixed',
			top: '0px',
			right: '0px'
		}).injectInside(document.body);
		var supported = (test.offsetTop === 0);
		test.remove();
		return supported;
	}
});

/*	Class: Element
		Extends the <Element> class from the <http://mootools.net> library.
	*/
Element.extend({
/*	Property: pin
		Affixes an element at its current position, even if the window is scrolled.
		
		Arguments: 
		pin - (boolean) true: pin, false: release pin. See also <Element.unpin>.
	*/
	pin: function(enable){
		var p = this.getPosition();
		if(enable!==false) {
			if(!this.pinned) {
				var pos = {
					top: (p.y - window.getScrollTop())+'px',
					left: (p.x - window.getScrollLeft())+'px'
				};
				if(window.supportsPositionFixed()) {
					this.setStyle('position','fixed').setStyles(pos);
				} else {
					this.setStyles({
						position: 'absolute',
						top: p.y+'px',
						left: p.x+'px'
					});
					window.addEvent('scroll', function(){
						if(this.pinned) {
							var to = {
								top: (pos.top.toInt() + window.getScrollTop())+'px',
								left: (pos.left.toInt() + window.getScrollLeft())+'px'
							};
							this.setStyles(to);
						}
					}.bind(this));
				}
				this.pinned = true;
			}
		} else {
			this.pinned = false;
			var reposition = (window.supportsPositionFixed())?
				{
					top: (p.y + window.getScrollTop())+'px',
					left: (p.x + window.getScrollLeft())+'px'
				}:
				{
					top: (p.y)+'px',
					left: (p.x)+'px'
				};
			this.setStyles($merge(reposition, {position: 'absolute'}));
		}
		return this;
	},
/*	Property: unpin
		Un-pins an element at its current position (see <Element.pin>).
	*/
	unpin: function(){
		return this.pin(false);
	},
/*	Property: togglepin
		Toggles the pin state of the element.
	*/
	togglepin: function(){
		this.pin(!this.pinned);
	}
});

/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Native/element.pin.js,v $
$Log: element.pin.js,v $
Revision 1.5  2007/09/04 19:43:16  newtona
fixed issues with unpin in non IE6 browsers

Revision 1.4  2007/08/30 17:52:14  newtona
removing some errant dbug lines; added one to jsonp

Revision 1.3  2007/08/27 23:09:02  newtona
MooScroller: removed periodical for scrollbar resizing; the user can implement this if it's needed for each instance; also, renamed refactor to update
dbug: added support for dbug.dir, profile, stackTrace, etc.
element.dimensions: when getting the size of hidden elements the method now restores the previous inline styles to their original state
element.pin: fixed positioning bug

Revision 1.2  2007/05/30 20:32:33  newtona
doc updates

Revision 1.1  2007/05/29 21:25:31  newtona
splitting up element.cnet.js (which had grown to be too unweildy); moving things into sub-directories

Revision 1.7  2007/05/04 16:40:50  newtona
fixed a trailing comma typo

Revision 1.6  2007/05/04 01:22:38  newtona
added togglepin

Revision 1.5  2007/05/04 01:06:54  newtona
*sigh* ok, last typo

Revision 1.4  2007/05/04 01:04:35  newtona
woops, missing a "+"

Revision 1.3  2007/05/04 01:03:01  newtona
fixed a bug with unpin

Revision 1.2  2007/05/04 01:01:45  newtona
.pin only pins if the element isn't already pinned.

Revision 1.1  2007/05/04 00:36:19  newtona
*** empty log message ***


*/
/*	Script: element.position.js
Extends the <Element> object with Element.setPosition; Sets the location of an element relative to another (defaults to the document body).

Dependancies:
	 mootools - <Moo.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>
	 cnet - <element.dimensions.js>

Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>
	
Class: Element
		This extends the <Element> prototype.
	*/
Element.extend({
/*	Property: setPosition
		Sets the location of an element relative to another (defaults to the document body).
		
		Note:
		The element must be absolutely positioned (if it isn't, this method will set it to be);
		
		Arguments:
		options - a key/value object with options
		
		Options:
		relativeTo - (element) the element relative to which to position this one; defaults to document.body.
		position - (string OR object) the aspect of the relativeTo element that this element should be positioned. See position section below.
		edge - (string OR object; optional) the edge of the element to set relative to the relative elements corner; this way you can specify to position this element's upper right corner to the bottom left corner of the relative element. this is optional; the default behavior positions the element's upper left corner to the relative element unless position == center, in which case it positions the center of the element to the center of the relative element. See position section below.
		offset - (object) x/y coordinates for the offset (i.e. {x: 10, y:100} will move it down 100 and to the right 10). Negative values are allowed.
		returnPos - (boolean) don't move the element, but instead just return the position object ({top: '#', left: '#'}); defaults to false
		overflown - (collection) optional, an array of nested scrolling containers for scroll offset calculation, use this if your element is inside any element containing scrollbars
		relFixedPosition - (boolean) if true, adds the scroll position of the window to the location to account for a fixed position relativeTo item; defaults to false
		ignoreMargins - (boolean) you can have the position calculate the offsets added margins if you like; defaults to false. If true, the corner of the element will be used EXCLUDING the margin.

		Position & Edge Options:
		There are two ways to specify the position: strings and objects. The strings are combinations of "left", "right", and "center" with "top" (or "upper"), "bottom", and "center". These are case insensitive. These translate to:

    - upperLeft, topLeft (same thing) - or upperleft, leftupper, LEFTUPPER whatever.
    - bottomLeft
    - centerLeft
    - upperRight, topRight (same thing)
    - bottomRight
    - centerRight
    - centerTop
    - centerBottom
    - center

		Alternatively, you can be a little more expicit by using an object with x and y values. Acceptable values for the x axis are "left", "right", and "center", and for y you can use "top", "bottom" and "center".

    - {x: 'left', y: 'top'} � same as "upperLeft" or "topLeft"
    - {x: 'left', y: 'bottom'} � same as "bottomLeft"
    - etc.

		Using these options you can specify a position for each corner of the relativeTo object as well as the points between those corners (center left, top, right, bottom and the center of the entire object). 

	*/
	setPosition: function(options){
		options = $merge({
			relativeTo: document.body,
			position: {
				x: 'center', //left, center, right
				y: 'center' //top, center, bottom
			},
			edge: false,
			offset: {x:0,y:0},
			returnPos: false,
			relFixedPosition: false,
			ignoreMargins: false,
			overflown: [] //dom elements
		}, options);
		//compute the offset of the parent positioned element if this element is in one
		var parentOffset = {x: 0, y: 0};
		var parentPositioned = false;
		if(this.getParent() != document.body) {
			var parent = this.getParent();
			while(parent != document.body && parent.getStyle('position') == "static") {
				parent = parent.getParent();
			}
			if(parent != document.body) {
				parentOffset = parent.getPosition();
				parentPositioned = true;
			}
			options.offset.x = options.offset.x - parentOffset.x;
			options.offset.y = options.offset.y - parentOffset.y;
		}
		//upperRight, bottomRight, centerRight, upperLeft, bottomLeft, centerLeft
		//topRight, topLeft, centerTop, centerBottom, center
		function fixValue(option) {
			if($type(option) != "string") return option;
			option = option.toLowerCase();
			var val = {};
			if(option.test('left')) val.x = 'left';
			else if(option.test('right')) val.x = 'right';
			else val.x = 'center';

			if(option.test('upper')||option.test('top')) val.y = 'top';
			else if (option.test('bottom')) val.y = 'bottom';
			else val.y = 'center';
			return val;
		};
		options.edge = fixValue(options.edge);
		options.position = fixValue(options.position);
		if(!options.edge) {
			if(options.position.x == 'center' && options.position.y == 'center') options.edge = {x:'center',y:'center'};
			else options.edge = {x:'left',y:'top'};
		}
		
		this.setStyle('position', 'absolute');
		var rel = $(options.relativeTo) || document.body;
		if (window.opera) {
      var top = (rel == document.body)?window.getScrollTop():rel.getTop();
      var left = (rel == document.body)?window.getScrollLeft():rel.getLeft();
    } else {
      var top = (rel == document.body)?window.getScrollTop():rel.getTop(options.overflown);
      var left = (rel == document.body)?window.getScrollLeft():rel.getLeft(options.overflown);
    }
		
		if (top < 0) top = 0;
    if (left < 0) left = 0;
		var dim = this.getDimensions({computeSize: true, styles:['padding', 'border','margin']});
		if (options.ignoreMargins) {
			options.offset.x += ((options.edge && options.edge.x == "right")?dim['margin-right']:-dim['margin-left']);
			options.offset.y += ((options.edge && options.edge.y == "bottom")?dim['margin-bottom']:-dim['margin-top']);
		}
		var pos = {};
		var prefY = options.offset.y.toInt();
		var prefX = options.offset.x.toInt();
		switch(options.position.x) {
			case 'left':
				pos.x = left + prefX;
				break;
			case 'right':
				pos.x = left + prefX + rel.offsetWidth;
				break;
			default: //center
				pos.x = left + (((rel == document.body)?window.getWidth():rel.offsetWidth)/2) + prefX;
				break;
		};		
		switch(options.position.y) {
			case 'top':
				pos.y = top + prefY;
				break;
			case 'bottom':
				pos.y = top + prefY + rel.offsetHeight;
				break;
			default: //center
				pos.y = top + (((rel == document.body)?window.getHeight():rel.offsetHeight)/2) + prefY;
				break;
		};
		
		if(options.edge){
			var edgeOffset = {};
			
			switch(options.edge.x) {
				case 'left':
					edgeOffset.x = 0;
					break;
				case 'right':
					edgeOffset.x = -dim.x-dim.computedRight-dim.computedLeft;
					break;
				default: //center
					edgeOffset.x = -(dim.x/2);
					break;
			};
			switch(options.edge.y) {
				case 'top':
					edgeOffset.y = 0;
					break;
				case 'bottom':
					edgeOffset.y = -dim.y-dim.computedTop-dim.computedBottom;
					break;
				default: //center
					edgeOffset.y = -(dim.y/2);
					break;
			};
			pos.x = pos.x+edgeOffset.x;
			pos.y = pos.y+edgeOffset.y;
		}
		pos = {
			left: ((pos.x >= 0 || parentPositioned)?pos.x:0).toInt()+'px',
			top: ((pos.y >= 0 || parentPositioned)?pos.y:0).toInt()+'px'
		};
		if(rel.getStyle('position') == "fixed"||options.relFixedPosition) {
			pos.top = pos.top.toInt() + window.getScrollTop()+'px';
			pos.left = pos.left.toInt() + window.getScrollLeft()+'px';
		}

		if(options.returnPos) return pos;
		if(options.smoothMove) new Fx.SmoothMove(this, options).start(); //deprecated; use Fx.SmoothMove instead
		else this.setStyles(pos);
		return this;
	}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Native/element.position.js,v $
$Log: element.position.js,v $
Revision 1.18  2007/11/19 23:23:07  newtona
CNETAPI: added method "getMany" to all the CNETAPI.Utils.* classes so that you can get numerous items in one request.
ObjectBrowser: improved exclusion handling for child elements
jlogger.js, element.position.js: docs update
Fx.Sort: cleaning up tabbing
string.cnet: just reformatting the logic a little.

Revision 1.17  2007/11/17 00:21:23  andyl
Fixed edge setting in element.position.js

Revision 1.16  2007/11/02 20:23:38  newtona
added the ability to ignore margins with setPosition

Revision 1.15  2007/10/15 17:36:30  newtona
fixing a bug with element.position.js from the last commit

Revision 1.14  2007/10/05 20:59:09  newtona
hey, new files!
CNETAPI - Hunter's first work on an API handler
CNETAPI.Category.Browser.js - this is still very rough and not ready for primetime.
ObjectBrowser.js - also might have a few quirks; this is a tree browser for objects (kinda like in firebug)
element.position.js - fixed an issue with positioning.

Revision 1.13  2007/08/28 23:26:59  newtona
fixing syntax errors - damned semi-colons.

Revision 1.12  2007/08/28 21:09:23  newtona
rather than deduce fixed positioning for setPosition (because we'd have to check the relativeTo item and all it's parents), making it an option.

Revision 1.11  2007/08/28 20:58:40  newtona
still tweaking element.setPosition for fixed elements; should work now.

Revision 1.10  2007/08/28 20:38:54  newtona
doc update in jsonp
element.setPosition now accounts for fixed position relativeTo elements

Revision 1.9  2007/08/27 21:22:56  newtona
elements inside of positioned elements can now be safely positioned; the offset is accounted for

Revision 1.8  2007/08/23 18:20:14  newtona
updated cat files: added assets back to rb.global; added stickywin to commerce.global (for now; used for the moment in history bar)
added docs to element.position
removed .reverse in RTSS.History

Revision 1.7  2007/07/28 00:03:29  newtona
removed a dbug line

Revision 1.6  2007/07/17 20:38:44  newtona
Fx.SmoothShow - refactored the exploration of the element dimensions when hidden so that it isn't visible to the user
element.position - refactored to allow for more than just the previous 5 positions, now supports nine: all corners, all mid-points between those corners, and the center
string.cnet.js - fixed up the query string logic to decode values

Revision 1.5  2007/06/27 18:58:15  newtona
checking in built versions of cat libraries; i have not compressed these or anything to prevent accidental publishing
element.position.js: fixed an error with overflown items in Element.setPosition; this fix depends on Mootools 1.11, see: http://forum.mootools.net/viewtopic.php?pid=20391#p20391
default.accordion.nav.js: changed window.onDomReady (which is deprecated) to window.addEvent('domready'...
checking in the build file for redball.global.framework.dev.js - this will build a copy of redball.global using the copy of Mootools in the dev directory (currently, 1.11)

Revision 1.4  2007/06/27 18:30:33  newtona
debugger.footer.js: added a delay for IE to avoid the dreaded "operation aborted" bug
element.position.js: deprecated the smoothMove option in Element.setPosition
Fx.SmoothMove: moving this effect out of Element.setPosition and making it a stand alone Fx class
bat file (build file) changes: adding Fx.SmoothMove

Revision 1.3  2007/05/30 20:32:33  newtona
doc updates

Revision 1.2  2007/05/29 22:01:53  newtona
Split element.cnet.js into seperate files; updated docs in files to note this
Changed element.visible to element.isVisible (left old namespace for legacy support)
Fixed Element.empty in prototype.compatibility.js
Removed as many dependencies in common code to element.*.js as possible (espeically element.shortcuts.js)

Revision 1.1  2007/05/29 21:25:31  newtona
splitting up element.cnet.js (which had grown to be too unweildy); moving things into sub-directories


*//*	Script: Fx.Marquee.js
		A simple marquee effect for fading in and out messages.
		
		Author:
		Aaron Newton
		
		Dependencies:
		Mootools 1.11 - <Fx.Styles.js> and all its dependencies
		
		Class: Fx.Marquee
		A simple marquee effect for fading in and out messages.
		
		Arguments:
		container - (DOM element or id) the item that contains the message
		options - (object) key/value set of options
		
		Note:
		All options specified can be specified at initialization and also at
		invocation (so the same effect can be used for numerous messages).
		
		Options:
		mode - (string) "horizontal" or "vertical" - which way the marquee goes
		message - (string) the message to display; can also be specified at run time
		revert - (boolean) revert back to the initial message after a delay; defaults to true
		delay - (integer) duration (in milliseconds) to wait before reverting
		cssClass - (string) the css class name to add to the message
		showEffect - (object) an object passed to Fx.Styles for the transition in; defaults to {opcaity: 1}
		hideEffect - (object) an object passed to Fx.Styles for the transition out; defaults to {opcaity: 0}
		revertEffect - (object) an object passed to Fx.Styles for the transition on revert; defaults to {opcaity: [0,1]}
		currentMessage - (dom element or id) the container of the currently displayed message; defaults to the first
					child of the container
	*/
Fx.Marquee = Fx.Styles.extend({
	options: {
		mode: 'horizontal', //or vertical
		message: '', //the message to display
		revert: true, //revert back to the previous message after a specified time
		delay: 5000, //how long to wait before reverting
		cssClass: 'msg', //the css class to apply to that message
		showEffect: {
			opacity: 1
		},
		hideEffect: {opacity: 0},
		revertEffect: {
			opacity: [0,1]
		},
		currentMessage: null,
		onRevert: Class.empty,
		onMessage: Class.empty
	},
	initialize: function(container, options){
		container = $(container); //make sure the container is an extended DOM element
		//get the message from the options
		var msg = this.options.currentMessage || (container.getChildren().length == 1)?container.getFirst():''; 
		//create a wrapper to hold the messages
		var wrapper = new Element('div', {	
				styles: { position: 'relative' },
				'class':'fxMarqueeWrapper'
			}).injectInside(container); //inject it in the container
		//set up the Fx.Styles effect
		this.parent(wrapper, options);
		//store the current message
		this.current = this.wrapMessage(msg);
	},
/*	Property: wrapMessage
		Internal; wraps the message in a span element.
		
		Arguments:
		msg - (string or DOM element) the message element
 */
	//internal; wraps a message in a span element
	wrapMessage: function(msg){
		if($(msg) && $(msg).hasClass('fxMarquee')) { //already set up
			var wrapper = $(msg);
		} else {
			//create the wrapper
			var wrapper = new Element('span', {
				'class':'fxMarquee',
				styles: {
					position: 'relative'
				}
			});
			if($(msg)) wrapper.adopt($(msg)); //if the message is a dom element, inject it inside the wrapper
			else if ($type(msg) == "string") wrapper.setHTML(msg); //else set it's value as the inner html
		}
		return wrapper.injectInside(this.element); //insert it into the container
	},
/*	Property: announce
		Shows the message, hiding the old one.

		Arguments:
		options - (object) a key/value set of options
		
		Options:
		These are identical to the optoins for the class. This way you can use the instance for numerous messages.
	*/
	announce: function(options) {
		this.setOptions(options).showMessage();
		return this;
	},
/*	Property: showMessage
		Internal; shows the message, hiding the old one; reverts if it's supposed to based on the options passed in
		
		Arguments:
		reverting - (boolean) true if this method has called itself to revert to previous state.
 */
	showMessage: function(reverting){
		//delay the fuction if we're reverting
		(function(){
			//store a copy of the current chained functions
			var chain = this.chains?this.chains.copy():[];
			//clear teh chain
			this.clearChain();
			this.element = $(this.element);
			this.current = $(this.current);
			this.message = $(this.message);
			//execute the hide effect
			this.start(this.options.hideEffect).chain(function(){
				//if we're reverting, hide the message and show the original
				if(reverting) {
					this.message.hide();
					if(this.current) this.current.show();
				} else {
					//else we're showing; remove the current message
					if(this.message) this.message.remove();
					//create a new one with the message supplied
					this.message = this.wrapMessage(this.options.message);
					//hide the current message
					if(this.current) this.current.hide();
				}
				//if we're reverting, execute the revert effect, else the show effect
				this.start((reverting)?this.options.revertEffect:this.options.showEffect).chain(function(){
					//merge the chains we set aside back into this.chains
					this.chains.merge(chain);
					this.fireEvent((reverting)?'onRevert':'onMessage');
					//then, if we're reverting, show the original message
					if(!reverting && this.options.revert) this.showMessage(true);
					//if we're done, call the chain stack
					else this.callChain.delay(this.options.delay, this);
				}.bind(this));
			}.bind(this));
		}).delay((reverting)?this.options.delay:10, this);
		return this;
	}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Effects/Fx.Marquee.js,v $
$Log: Fx.Marquee.js,v $
Revision 1.3  2007/08/31 00:26:53  newtona
a little more tweaking for chaining in Fx.Marquee

Revision 1.2  2007/08/30 23:59:33  newtona
fixed chaining in Fx.Marquee; added to redball.common.full
tweaked docs in IconMenu

Revision 1.1  2007/08/20 21:20:46  newtona
first big check in for RTSS History


*/
/*	Script: Fx.Smoothshow.js
Extends the <Element> object.

Dependancies:
	 mootools - <Moo.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>

Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>
	
		Class: Fx.SmoothShow
		Transitions the height, opacity, padding, and margin (but not border) from and to their current height from and to zero, then set's display to none or block and resets the height, opacity, etc. back to their original values.

		Arguments:
		options - a key/value object of options
		
		Options:
		all the options passed along to <Fx.Base> (transition, duration, etc.); (optional); PLUS
		
		styles - (array; optional) css properties to transition in addition to width/height; 
							defaults to ['padding','border','margin']
		mode - (string; optional) 'vertical','horizontal', or 'both' to describe how the element should slide in.
							defaults to 'vertical'
		heightOverride - (integer; optional) height to open to; overrides the default offsetheight
		widthOverride -  (integer; optional) width to open to; overrides the default offsetwidth
	*/
Fx.SmoothShow = Fx.Styles.extend({
	options: {
		styles: ['padding','border','margin'],
		transitionOpacity: true,
		mode:'vertical',
		heightOverride: null,
		widthOverride: null
	},
	//Mootools 1.0 compatability; CNET needs this for now
	//just adds "px" to integer values
	fixStyle: function(style, name){
		if(!$type(style)=="number") return style;
		var fix = ['margin', 'padding', 'width', 'height'].some(function(st){return name.test(st, 'i')});
		return (fix)?style+'px':style;
	},
/*	Property: hide
		Transitions the height, opacity, padding, and margin (but not border) from their current height to zero, then set's display to none and resets the height, opacity, etc. back to their original values.	
		*/
	hide: function(){
		try {
			if(!this.hiding && !this.showing) {
				if(this.element.getStyle('display') != 'none'){
					this.hiding = true;
					this.showing = false;
					this.hidden = true;
					var startStyles = this.element.getComputedSize({
						styles: this.options.styles,
						mode: this.options.mode
					});
					if (this.element.fxOpacityOk() && this.options.transitionOpacity) startStyles.opacity = 1;
					var zero = {};
					$each(startStyles, function(style, name){
						zero[name] = this.fixStyle(0, name); 
					}, this);
					this.chain(function(){
						if(this.hidden) {
							this.hiding = false;
							$each(startStyles, function(style, name) {
								startStyles[name] = this.fixStyle(style, name);
							}, this);
							this.element.setStyles(startStyles).setStyle('display','none');
						}
						this.callChain();
					}.bind(this));
					this.start(zero);
				} else {
					this.callChain.delay(10, this);
					this.fireEvent('onComplete', this.element);
				}
			}
		} catch(e) {
			this.element.hide();
			this.callChain.delay(10, this);
			this.fireEvent('onComplete', this.element);
		}
		return this;
	},
/*	Property: show
		Sets the display of the element to opacity: 0 and display: block, then transitions the height, opacity, padding, and margin (but not border) from zero to their proper height.
	*/
	show: function(){
		try {
			if(!this.showing && !this.hiding) {
				//if(arguments[1]) options.heightOverride = arguments[1];
				if(this.element.getStyle('display') == "none" || 
					 this.element.getStyle('visiblity') == "hidden" || 
					 this.element.getStyle('opacity')==0){
					this.showing = true;
					this.hiding = false;
					this.hidden = false;
					//toggle display, but hide it
					var before = this.element.getStyles('visibility', 'display', 'position');
					this.element.setStyles({
						visibility: 'hidden',
						display: 'block',
						position:'absolute'
					});
					//enable opacity effects
					if(this.element.fxOpacityOk() && this.options.transitionOpacity) this.element.setStyle('opacity',0);
					//create the styles for the opened/visible state
					var startStyles = this.element.getComputedSize({
						styles: this.options.styles,
						mode: this.options.mode
					});
					//reset the styles back to hidden now
					this.element.setStyles(before);
					$each(startStyles, function(style, name) {
						startStyles[name] = this.fixStyle(style, name);
					}, this);
					//if we're overridding height/width
					if($chk(this.options.heightOverride)) startStyles['height'] = this.options.heightOverride.toInt()+'px';
					if($chk(this.options.widthOverride)) startStyles['width'] = this.options.widthOverride.toInt()+'px';
					if(this.element.fxOpacityOk() && this.options.transitionOpacity) startStyles.opacity = 1;
					//create the zero state for the beginning of the transition
					var zero = { 
						height: '0px',
						display: 'block'
					};
					$each(startStyles, function(style, name){ zero[name] = this.fixStyle(0, name); }, this);
					//set to zero
					this.element.setStyles(zero);
					//start the effect
					this.start(startStyles);
					this.chain(function(){
						if(!this.hidden) this.showing = false;
						this.callChain();
					}.bind(this));
				} else {
					this.callChain();
					this.fireEvent('onComplete', this.element);
				}
			}
		} catch(e) {
			this.element.setStyles({
				display: 'block',
				visiblity: 'visible',
				opacity: 1
			});
			this.callChain.delay(10, this);
			this.fireEvent('onComplete', this.element);
		}
		return this;
	},
/*	Property: toggle
		Toggles the element from shown to hidden.
	*/
	toggle: function(){
		try {
			if(this.element.getStyle('display') == "none" || 
				 this.element.getStyle('visiblity') == "hidden" || 
				 this.element.getStyle('opacity')==0){
				this.show();
		 	} else {
				this.hide();
			}
		} catch(e) { this.show(); }
	 return this;
	}
});
Fx.SmoothShow.implement(new Options);
Fx.SmoothShow.implement(new Events);


/*	Class: Element
		Adds <Fx.SmoothShow> shortcuts to the <Element> class.
	*/
Element.extend({
/*	Property: smoothShow
		Creates a new instance of <Fx.SmoothShow> and calls its *show* method. Returns the instance of <Fx.SmoothShow>.

		Arguments: 
		options	- see <Fx.SmoothShow> options.
	*/
	smoothShow: function(options){
 		if (arguments[1]) { options.heightOverride = arguments[1]; }
		return new Fx.SmoothShow(this, options).show();
	},
/*	Property: smoothHide
		Creates a new instance of <Fx.SmoothShow> and calls its *hide* method. Returns the instance of <Fx.SmoothShow>.

		Arguments: 
		options	- see <Fx.SmoothShow> options.
	*/
	smoothHide: function(options){
 		if (arguments[1]) { options.heightOverride = arguments[1]; }
		return new Fx.SmoothShow(this, options).hide();
	}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Effects/Fx.SmoothShow.js,v $
$Log: Fx.SmoothShow.js,v $
Revision 1.16  2007/10/26 18:41:07  newtona
damned semi-colons

Revision 1.15  2007/10/26 18:39:06  newtona
smoothShow/Hide had issues with Safari < 3; it now degrades to just toggle the style (no transitions)

Revision 1.14  2007/09/07 23:15:14  newtona
fixed a race-condition like issue with Fx.SmoothShow and chaining

Revision 1.13  2007/09/05 18:37:07  newtona
fixing all js warnings in the code base; they weren't breaking anything, but they can create performance issues and it's good practice...

Revision 1.12  2007/08/09 02:06:39  newtona
modified Fx.SmoothShow to prevent it flickering

Revision 1.11  2007/07/20 20:05:51  newtona
Fx.Smoothshow: that last fix didn't quite do it.

Revision 1.10  2007/07/20 17:56:24  newtona
Fixed a bug in Fx.SmoothShow that prevented it from getting the dimensions of hidden elements (to show them)

Revision 1.9  2007/07/17 20:38:44  newtona
Fx.SmoothShow - refactored the exploration of the element dimensions when hidden so that it isn't visible to the user
element.position - refactored to allow for more than just the previous 5 positions, now supports nine: all corners, all mid-points between those corners, and the center
string.cnet.js - fixed up the query string logic to decode values

Revision 1.8  2007/06/28 01:28:08  newtona
adding an option for opacity fading to Fx.SmoothShow

Revision 1.7  2007/06/12 20:46:21  newtona
added tbody to html.table.js
added legacy argument support to Fx.SmoothShow

Revision 1.6  2007/05/31 23:57:49  newtona
slight tweak to last checkin

Revision 1.5  2007/05/31 23:55:30  newtona
chaining now works properly; added logic to handle double-click behavior

Revision 1.4  2007/05/31 21:33:42  newtona
.toggle returns the effect

Revision 1.3  2007/05/30 20:32:33  newtona
doc updates

Revision 1.2  2007/05/29 22:46:19  newtona
syntax fix in Fx.SmoothShow; docs update, too.

Revision 1.1  2007/05/29 22:27:02  newtona
rebuilt cat libs, added Fx.SmoothShow.js


*/


/*	Script: Fx.SmoothMove.js
Moves an element to another location (relative to another element) with a transition.

Dependancies:
	 mootools - <Moo.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>, <Fx.Styles>
	 CNET - <element.position.js>

Author:
	Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
Class: Fx.SmoothMove
Moves an element to another location (relative to another element) with a transition.

Options:
	relativeTo - (element) the element relative to which to position this one; defaults to document.body.
	position - (string) the aspect of the relativeTo element that this element should be positioned. Options are 'upperRight', 'upperLeft', 'bottomLeft', 'bottomRight', and 'center' (the default). With the exception of center, all other options will make the upper right corner of the positioned element = the specified corner of the relativeTo element. 'center' will make the center point of the positioned element = the center point of the relativeTo element.
	edge - (string; optional) the edge of the element to set relative to the relative elements corner; this way you can specify to position this element's upper right corner to the bottom left corner of the relative element. this is optional; the default behavior positions the element's upper left corner to the relative element unless position == center, in which case it positions the center of the element to the center of the relative element. Acceptable values here are the same as those in the 'position' option.
	offset - (object) x/y coordinates for the offset (i.e. {x: 10, y:100} will move it down 100 and to the right 10). Negative values are allowed.

Example:
(start code)
var mover = new Fx.SmoothMove($('myelement'), {
	relativeTo: $('someOtherElement'),
	position: 'upperRight',
	edge: 'upperLeft',
	offset: {x: 10, y: 100}
});
mover.start(); //moves to the new location
mover.start({
	relativeTo: document.body,
	position: 'center',
	edge: false,
	offset: {x:0,y:0}
}); //move it to the center of the window
(end)
	*/
Fx.SmoothMove = Fx.Styles.extend({
	options: {
		relativeTo: document.body,
		position: 'center',
		edge: false,
		offset: {x:0,y:0}
	},
/*	Property: start
		Moves the element to provided destination or the destination specified in the options.
		
		Arguments:
		destination - an object of key/value options specifying a new position for the element; optional. If not provided the values in the options set at initialization will be used.

		Destination:
		See all the arguments defined in the options above for the key/value options in the destination object.
		
		Note:
		If you want to pass in a destination, you must pass in ALL of the options (relativeTo, position, edge, and offset x & y) unless you want to use those already defined in the options at initialization. In other words, if you only pass in one of these options, the rest will be filled in from the options defined at initialization.

		You can always set new defaults using //setOptions//.
	*/
	start: function(destination){
		return this.parent(this.element.setPosition($merge(this.options, destination, {returnPos: true})));
	}
});

/*	Class: Element
		Adds <Fx.SmoothMove> shortcuts to the <Element> class.
	*/
Element.extend({
/*	Property: smoothMove
		Creates a new instance of <Fx.SmoothMove> and calls its *start* method. Returns the instance of <Fx.SmoothMove>.

		Arguments: 
		options	- see <Fx.SmoothMove> options.
	*/
	smoothMove: function(options){
		return new Fx.SmoothMove(this, options).start();
	}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Effects/Fx.SmoothMove.js,v $
$Log: Fx.SmoothMove.js,v $
Revision 1.5  2007/09/05 18:37:07  newtona
fixing all js warnings in the code base; they weren't breaking anything, but they can create performance issues and it's good practice...

Revision 1.4  2007/08/20 18:11:51  newtona
Iframeshim: better handling for methods when the shim isn't ready
stickyWin: fixed a bug for the cssClass option in stickyWinHTML
html.table: push now returns the dom elements it creates
jsonp: fixed a bug; request no longer requires a url argument (my bad)
Fx.SmoothMove: just tidying up some syntax.
element.dimensions: updated getDimensions method for computing size of elements that are hidden; no longer clones element

Revision 1.3  2007/06/27 22:45:23  newtona
docs update to overfiew.js
tabswapper gets some events action
fixed a typo in the docs for smoothmove

Revision 1.2  2007/06/27 21:08:10  newtona
added a missing return statement

Revision 1.1  2007/06/27 18:30:33  newtona
debugger.footer.js: added a delay for IE to avoid the dreaded "operation aborted" bug
element.position.js: deprecated the smoothMove option in Element.setPosition
Fx.SmoothMove: moving this effect out of Element.setPosition and making it a stand alone Fx class
bat file (build file) changes: adding Fx.SmoothMove


*/
/*	Script: Fx.Sort.js
Reorders a group of items with a transition.

Dependancies:
	mootools - <Moo.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>, <Fx.Styles>, <Fx.Elements.js>
	CNET - <element.position.js>

Author:
	Aaron Newton <aaron [dot] newton [at] cnet [dot] com>

Class: Fx.Sort
Reorders a group of items with a transition.

Arguments:
elements - a collection of elements the effects will be applied to.
options - same as <Fx.Base> options, plus the option(s) listed below

Options:
mode - (string; optional) either "vertical" or "horizontal". Defaults to "vertical".

Example:
(start code)
var mysort = new Fx.Sort($$('ul li'), {
	transition: Fx.Transitions.Back.easeInOut,
	duration: 1000
});
mysort.sort([2,0,1]); //a specific order
mysort.forward(); //forward (the original) order
(end)
	*/

Fx.Sort = Fx.Elements.extend({
	options: {
			mode: 'vertical' //or 'horizontal'
	},
	initialize: function(elements, options){
			this.parent(elements, options);
			//set the position of each element to relative
			this.elements.each(function(el){
					if(el.getStyle('position') == 'static') el.setStyle('position', 'relative');
			});
			this.setDefaultOrder();
	},
/*	Property: currentOrder
		An array representing the current sort state.
	*/
	setDefaultOrder: function(){
			this.currentOrder = this.elements.map(function(el, index){
				return index;
			});
	},
/*	Property: sort
		Rearrange the items visually into a new order.
		
		Argument:
		newOrder - (array) the new order for the items.
	
		Example:
		> mySort.sort([2,1,0]); //reverse
	*/
	sort: function(newOrder){
		if($type(newOrder) != 'array') return false;
		var top = 0;
		var left = 0;
		var zero = {};
		var vert = this.options.mode == "vertical";
		//calculate the current location of all the elements
		var current = this.elements.map(function(el, index){
			var size = el.getComputedSize({styles:['border','padding','margin']});
			var val;
			if(vert) {
				val =	{
					top: top,
					margin: size['margin-top'],
					height: size.totalHeight
				};
				top += val.height - size['margin-top'];
			} else {
				val = {
					left: left,
					margin: size['margin-left'],
					width: size.totalWidth
				};
				left += val.width;
			}
			var plain = vert?'top':'left';
			zero[index]={};
			var start = el.getStyle(plain).toInt();
			zero[index][plain] = ($chk(start))?start:0;
			return val;
		}, this);
		this.set(zero);
		//if the array passed in is not the same size as
		//the amount of elements we have, fill it in
		//or cut it short
		newOrder = newOrder.map(function(i){ return i.toInt() });
		if (newOrder.length != this.elements.length){
			this.currentOrder.each(function(index) {
				if(!newOrder.contains(index)) newOrder.push(index);
			});
			if(newOrder.length > this.elements.length) {
				newOrder.splice(this.elements.length-1, newOrder.length-this.elements.length);
			}
		}
		var top = 0;
		var left = 0;
		var margin = 0;
		var next = {};
		//calculate the new location of each item
		newOrder.each(function(item, index){
			var newPos = {};
			if(vert) {
					newPos.top = top - current[item].top - margin;
					top += current[item].height;
			} else {
					newPos.left = left - current[item].left;	
					left += current[item].width;
			}
			margin = margin + current[item].margin;
			next[item]=newPos;
		}, this);
		var mapped = {};
		newOrder.sort().each(function(index){
			mapped[index] = next[index];
		});
		//execute the effect
		this.start(mapped);
		//store the current order
		this.currentOrder = newOrder;
		return this;
	},
/*	Property: rearrangeDOM
		Rearranges the DOM to the current sort order.
		
		Arguments:
		newOrder - (array; optional) the order to arrange the DOM with; defaults to this.currentOrder.
	*/
	rearrangeDOM: function(newOrder){
		newOrder = newOrder || this.currentOrder;
		var parent = this.elements[0].getParent();
		var rearranged = [];
		this.elements.setStyle('opacity', 0);
		//move each element and store the new default order
		newOrder.each(function(index) {
			rearranged.push(this.elements[index].injectInside(parent).setStyles({
				top: 0,
				left: 0
			}));
		}, this);
		this.elements.setStyle('opacity', 1);
		this.elements = rearranged;
		this.setDefaultOrder();
		return this;
	},
	getDefaultOrder: function(){
		return this.elements.map(function(el, index) {
			return index;
		})
	},
/*	Property: forward
		Arrange the items in the original order (0,1,2,3,etc).
	*/
	forward: function(){
		return this.sort(this.getDefaultOrder());
	},
/*	Property: backward
		Arrange the items in the reverse of the original order (3,2,1,0);
	*/
	backward: function(){
		return this.sort(this.getDefaultOrder().reverse());
	},
/*	Property: reverse
		Reverse the current order.
	*/
	reverse: function(){
		return this.sort(this.currentOrder.reverse());
	},
/*	Property: sortByElements
		Sort by the order specified in a collection of elements; elements must be an array (collection) of the elements within the elements specified at instantiation.
	
		Arguments:
		elements - (array/collection) a collection or array of elements in the new order

		Example:
		(start code)
		var mySort = new Fx.Sort($$('ul li'));
		mySort.sortByElements($$('#li3, #li2, #li1, #li0'));
		(end)
		
		Note:
		Again, the elements passed in to sortByElements must be the same ones passed in to the effect when it was created.
	*/
	sortByElements: function(elements){
		return this.sort(elements.map(function(el){
			return this.elements.indexOf(el);
		}));
	},
/*	Property: swap
		Swaps the position of one item with another.
		
		Arguments:
		one - the element or its index to swap
		two - the other element or its index to swap
		
		Example:
		(start code)
		var mySort = new Fx.Sort($$('ul li'));
		mySort.swap($('#li3'), $('#li0'));
		//OR
		mySort.swap(3, 0);
		(end)
	*/
	swap: function(one, two) {
		if($type(one) == 'element') {
			one = this.elements.indexOf(one);
			two = this.elements.indexOf(two);
		}
		var indexOne = this.currentOrder.indexOf(one);
		var indexTwo = this.currentOrder.indexOf(two);
		var newOrder = this.currentOrder.copy();
		newOrder[indexOne] = two;
		newOrder[indexTwo] = one;
		this.sort(newOrder);
	}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Effects/Fx.Sort.js,v $
$Log: Fx.Sort.js,v $
Revision 1.9  2007/11/19 23:23:06  newtona
CNETAPI: added method "getMany" to all the CNETAPI.Utils.* classes so that you can get numerous items in one request.
ObjectBrowser: improved exclusion handling for child elements
jlogger.js, element.position.js: docs update
Fx.Sort: cleaning up tabbing
string.cnet: just reformatting the logic a little.

Revision 1.8  2007/10/09 22:39:28  newtona
documented CNETAPI.Category.Browser, ObjectBrowser
doc tweaks on other files
rebuilding docs to javascript.cnet.com/docs

Revision 1.7  2007/09/05 18:37:07  newtona
fixing all js warnings in the code base; they weren't breaking anything, but they can create performance issues and it's good practice...

Revision 1.6  2007/08/30 17:52:14  newtona
removing some errant dbug lines; added one to jsonp

Revision 1.5  2007/08/30 17:09:29  newtona
stickyWinFx, modalizer: updated syntax a litle
jlogger: updated docs
fixed a bug in Fx.Sort w/ IE6

Revision 1.4  2007/07/19 23:26:00  newtona
a small tweak to fx.sort - hide/unhide elements on dom rearrange...

Revision 1.3  2007/07/19 21:29:36  newtona
moving dom arrangement in Fx.Sort to a stand alone method

Revision 1.2  2007/07/19 18:55:27  newtona
small docs change

Revision 1.1  2007/07/19 18:46:04  newtona
nifty new effect for sorting things


*/