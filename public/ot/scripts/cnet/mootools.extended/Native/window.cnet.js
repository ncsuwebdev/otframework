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
