/*
Script: dbug.js
Wrapper for the firebug console.log() function.

Dependancies:
	 no dependencies
	
Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>

Class: dbug
		dbug is a wrapper for the firebug console plugin for
		firefox. The syntax for logging is the same as documented
		at http://getfirebug.com, though only the .log() command
		is supported.
		
		You can leave dbug.log() statements in your code and 
		they will not be echoed out to the screen in any way. 

		To display the dbug statements, you have two options:
		include *"jsdebug=true"* in the query string of the page
		and all your dbug statements will be printed as they
		occur OR *type into the firebug console dbug.enable()*
		and the debug statements that have occurred up until
		that point will be echoed, and all others from that
		point will be printed as they occur. You can also
		*put dbug.enable() in your javascript* to turn it on.
		
		dbug.disable() will turn it back off.

Arguments:
	args - collection of things to log to the console.
	
Examples:
	(start code)
	dbug.log("message");
	> message
	dbug.log("my var is %s", myVar)
	> my var is x
	dbug.log($('myelement'));
	> <div id="myelement"></div>
	dbug.log("myelement: %s, some value: %s", $('myelement'), somevalue);
	> myelement: <div id="myelement"></div>, some value: blah
	(end)
	
	more at <http://getfirebug.com>
	*/
var dbug = {
/*	Property: logged

		Array with any messages logged that have not been sent to the console; 
		happens when dbug is not enabled. when you enable it again,
		these messages will be dumped to the console.
	*/
	logged: [],	
	timers: {},
/*	property: debug
		boolean; whether or not the debugger is enabled.
	*/	
	firebug: false, 
	debug: false, 

/*	property: log

		sends a message to the console if dbug is enabled, otherwise
		it stores this info until dbug is enabled.
		
		Parameters:
			message - the message to log, includes various substition options, see <http://www.getFirebug.com>

		Syntax: 
		> dbug.log("message");
		> > message
		> dbug.log("my var is %s", myVar)
		> > my var is x

		for more examples, see <http://www.getFirebug.com>
	*/
	log: function() {
		dbug.logged.push(arguments);
	},
	nolog: function(msg) {
		dbug.logged.push(arguments);
	},
/*	Property: time
		Starts a console timer with the given name if dbug is enabled.
		See <http://www.getFirebug.com> for details.
	*/
	time: function(name){
		dbug.timers[name] = new Date().getTime();
	},
/*	Property: timeEnd
		Ends a console timer with the given name if dbug is enabled.
		See <http://www.getFirebug.com> for details.
	*/
	timeEnd: function(name){
		if (dbug.timers[name]) {
			var end = new Date().getTime() - dbug.timers[name];
			dbug.timers[name] = false;
			dbug.log('%s: %s', name, end);
		} else dbug.log('no such timer: %s', name);
	},
/*	Property: enable

		turns on the dbug functionality so that messages will show up
		in the firebug console. any messages sent to dbug.log() 
		previously will be displayed in the console immediately and
		all future logging statements will echo to the console.

		See also: 
		<dbug.log>, <dbug.disable>
		
		Example:
		>dbug.enable()
		> > enabling dbug
		
	*/	
	enable: function() { 
		if(dbug.firebug) {
			try {
				dbug.debug = true;
				dbug.log = console.debug || console.log;
				dbug.time = console.time;
				dbug.timeEnd = console.timeEnd;
				dbug.log('enabling dbug');
				for(var i=0;i<dbug.logged.length;i++){ dbug.log.apply(console, dbug.logged[i]); }
				dbug.logged=[];
			} catch(e) {
				dbug.enable.delay(400);
			}
		}
	},
/*	Property: disable

		turns the dbug functionality off. all future logging calls
		will be stored in the logged array until dbug is enabled again.
		
		See also: 
		<dbug.log>, <dbug.enable>, <dbug.logged>
		
		Example:
		>dbug.disable()
	*/
	disable: function(){ 
		if(dbug.firebug) dbug.debug = false;
		dbug.log = dbug.nolog;
		dbug.time = function(){};
		dbug.timeEnd = function(){};
	},
/*	Property: cookie
		dbug.cookie turns debugging on for the rest of the day for that domain. This lets you click around and use the debugging version of libraries without having to add jsdebug=true to each new page's url and reload the page.

		Calling dbug.cookie() when the cookie is already present will disable it (toggle).
		
		Arguments:
		set - (boolean; optional); if true sets the cookie even if it's already set (overrides toggle), 
					if false overrides to disable the cookie (same as <dbug.disableCookie>);
	*/
	cookie: function(set){
		var value = document.cookie.match('(?:^|;)\\s*jsdebug=([^;]*)');
		var debugCookie = value ? unescape(value[1]) : false;
		if((debugCookie != 'true' || set) && !set) {
			dbug.enable();
			dbug.log('setting debugging cookie');
			var date = new Date();
			date.setTime(date.getTime()+(24*60*60*1000));
			document.cookie = 'jsdebug=true;expires='+date.toGMTString();
		} else dbug.disableCookie();
	},
/*	Property: disableCookie
		This removes the cookie set by <dbug.cookie> and turns off debugging for subsequent page loads.
	*/
	disableCookie: function(){
		dbug.log('disabling debugging cookie');
		document.cookie = 'jsdebug=false';
	}
};

dbug.setMethods = function(){
	var fb = typeof console != "undefined";
	var debugMethods = ['debug','info','warn','error','assert','dir','dirxml'];
	var otherMethods = ['trace','group','groupEnd','profile','profileEnd','count'];
	function set(methodList, defaultFunction) {
		for(var i = 0; i < methodList.length; i++){
			dbug[methodList[i]] = (fb && console[methodList[i]])?console[methodList[i]]:defaultFunction;
		}
	};
	set(debugMethods, dbug.log);
	set(otherMethods, function(){});
};
dbug.setMethods();
if (typeof console != "undefined" && console.warn){
	dbug.firebug = true;
	var value = document.cookie.match('(?:^|;)\\s*jsdebug=([^;]*)');
	var debugCookie = value ? unescape(value[1]) : false;
	if(window.location.href.indexOf("jsdebug=true")>0 || debugCookie=='true') dbug.enable();
	if(debugCookie=='true')dbug.log('debugging cookie enabled');
	if(window.location.href.indexOf("jsdebugCookie=true")>0){
		dbug.cookie();
		if(!dbug.debug)dbug.enable();
	}
	if(window.location.href.indexOf("jsdebugCookie=false")>0)dbug.disableCookie();
} 
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/utilities/dbug.js,v $
$Log: dbug.js,v $
Revision 1.10  2007/08/28 23:26:58  newtona
fixing syntax errors - damned semi-colons.

Revision 1.9  2007/08/27 23:08:50  newtona
MooScroller: removed periodical for scrollbar resizing; the user can implement this if it's needed for each instance; also, renamed refactor to update
dbug: added support for dbug.dir, profile, stackTrace, etc.
element.dimensions: when getting the size of hidden elements the method now restores the previous inline styles to their original state
element.pin: fixed positioning bug

Revision 1.8  2007/03/28 22:41:20  newtona
dbug.cookie now toggles

Revision 1.7  2007/03/09 23:32:03  newtona
docs update

Revision 1.6  2007/03/08 23:31:22  newtona
strict javascript warnings cleaned up
removed deprecated dbug loadtimers
dbug enables on debug.cookie()

Revision 1.5  2007/02/21 00:30:08  newtona
added loadTime & loadTimeEnd empty functions for legacy support; these should be removed after the next release.

Revision 1.4  2007/02/08 19:18:34  newtona
dbug now uses cookies

Revision 1.3  2007/02/03 01:39:38  newtona
fixed an IE bug

Revision 1.2  2007/01/23 00:12:23  newtona
tweaks to work with Debugger.js

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.6  2006/12/06 20:14:59  newtona
carousel - improved performance, changed some syntax, actually deployed into usage and tested
cnet.nav.accordion - improved css selectors for time
multiple accordion - fixed a typo
dbug.js - added load timers
element.cnet.js - changed syntax to utilize mootools more effectively
function.cnet.js - equated $set to $pick in preparation for mootools v1

Revision 1.5  2006/12/06 17:52:50  newtona
making this file have no dependencies

Revision 1.4  2006/11/22 00:21:01  newtona
docs update

Revision 1.3  2006/11/21 23:56:08  newtona
added dbug.time and debug.timeEnd

Revision 1.2  2006/11/02 21:26:42  newtona
checking in commerce release version of global framework.

notable changes here:
cnet.functions.js is the only file really modified, the rest are just getting cvs footers (again).

cnet.functions adds numerous new classes:

$type.isNumber
$type.isSet
$set

*/


