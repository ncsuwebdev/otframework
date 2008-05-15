/*	
Script: jlogger.js
	Collected functions and objects for the CNET "Redball" family of sites.
	
Dependancies:
	 mootools - <Moo.js>, <Utility.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>
	 cnet libraries - <dbug.js>
	
Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>

Class: Jlogger
Enables clientside logging for any client side data or events.
You can capture any data the browser knows; resolution, scroll distance (see <JlScroller>), mouseover, etc.

Reports:
Reports for Jlogger events can be found in CNET DW reports at

CNET Reporting > Shared Reports > Redball > Miscellaneous > JLogger Events

Arguments:
	options - optional, an object containing options.

Options:
	tag - (required) the tag for DW
	element - (optional) the id of the element or the DOM element itself or false; use false if you want to call //.ping
						yourself, use a dom element if you want to observe it using the event option below. defaults to false
	event - (optional) the event that the element captures; load, click, etc. (do not include "on")
	ontid - (string or integer) the ontology id, defaults to the nodeId of the page
	pId - (string or integer) the product id, defaults to the pageType of the page
	edId - (string or integer) the edition id, defaults to the editionId of the page
	siteId - (string or integer) the site Id, defaults to the siteId of the page
	useraction - (optional; string or integer) the urs user action id, not used if not included
	fireOnce - (boolean; optional) only fire this event once (true); fire every time (false; default)

Example:
	(start code)
//ping DW when the window loads
new Jlogger({
	ontid: '20', 
	siteId:'4', 
	pId:'2001', 
	tag:'windowLoad', 
	element: window, 
	event: 'load',
	fireOnce: true
});

//ping dw when the user clicks on some element
new Jlogger({
	ontid: '20', 
	siteId:'4', 
	pId:'2001', 
	tag:'somethingClicked', 
	element: 'myElementId', 
	event: 'click',
});

//ping DW NOW
new Jlogger({
	ontid: '20', 
	siteId:'4', 
	pId:'2001', 
	tag:'someAction'
}).ping();
(end)

Note:
See <JlScroller> class for scroll capturing.
	*/
var Jlogger = new Class({
	options: {
		ontid: PageVars.nodeId,
		siteId: PageVars.siteId,
		pId: PageVars.pageType,
		edId: PageVars.editionId,
		ctype: false,
		cval: false,
		tag: false,
		element: false,
		event: false,
		useraction: false,
		fireOnce: false,
		executeNow: false, //deprecated, use new Jlogger().ping();
		onPing: Class.empty
	},
	errors: 0,
	fired: false,
	active: true,
	initialize: function(options) {
		this.setOptions(options);
		if(this.options.element == 'window') this.options.element = window;
		this.setup();
		if(this.options.executeNow) this.ping();//deprecated, use new Jlogger().ping();
	}, 
	setup: function(){
		if(!$(this.options.element)) return;
		var opt = this.options; //saving bytes
		if ($type(opt.tag) && $type(opt.element) && $type(opt.event)){
			//else tag is set, element is set, and event is set, log this info and...
			dbug.log('event observe(element: '+opt.element+', event: '+opt.event+', tag: '+opt.tag+')');
			//if the event == "load" and the observed element is the window, execute the ping immediately
			if(opt.event == 'load' && opt.element == window) opt.executeNow = true;
			//observe the elemnt for the event.
			if(opt.element != window) $(opt.element).addEvent(opt.event, this.ping);
			else if(opt.event != 'load') $(opt.element).addEvent(opt.event, this.ping);
		}
	},
	//generates the url to ping DW and returns it
	makeURL: function(tag) {
		var url = 'http://dw.com.com/redir?';
		var opt = this.options;//saving bytes
		if($type(opt.ontid)) url+= 'ontid='+opt.ontid+'&';
		if($type(opt.siteId)) url+= 'siteid='+opt.siteId+'&';
		if($type(opt.pId)) url+= 'pId='+opt.pId+'&';
		if($type(opt.edId)) url+= 'edId='+opt.edId+'&';
		if($type(opt.ctype)) url+= 'ctype='+opt.ctype+'&';
		if($type(opt.cval)) url+= 'cval='+opt.cval+'&';
		if($type(opt.useraction)) url+= 'useraction='+opt.useraction+'&';
		url+= 'tag='+opt.tag+'&destUrl=/i/b.gif';
		//append a date value so that the browser doesn't cache the request
		url+= '&uniquePingId='+new Date().getTime();
		return url;
	},
/*	Property: ping
		Adds the call to DW with the passed in url; if url is not defined, it will create 
		one with the options that were passed in when it was created. Will not ping if 
		fireOnce = true and this has already been fired.
		
		Arguments:
		url - (string) the url to append to the dom (optional)
		force - (boolean) execute the ping even if fireOnce=true and this has already been fired
			*/
	//creates a clear gif with the url passed in
	ping: function(url, force) {
		//if fireOnce is set and this hasn't yet fired, or fireOnce isn't set or is false, and this observer is active
		//then ping dw
		if (force || (((!this.fired && this.options.fireOnce) || !this.options.fireOnce) && this.active)) {
			//if the url isn't passed in to this function, get it from the makeURL function in this class
			url = $pick(url, this.makeURL());
			//if the doc is loaded, continue with the ping (doing this before the doc is loaded will break IE)
			window.addEvent('domready', function(){
				new Element('img').setProperty('src', url);
				//just creating the img and setting its src will cause the browser to hit the url, you don't
				//need to append it to the DOM
				this.fired = true;
				dbug.log(this.options.tag + ': '+(this.options.event||'')+'\nping: '+url);
				this.fireEvent('onPing');
			}.bind(this));
		}
	},
/*	Property: pingTag
		Use this instance of Jlogger and all its options, but ping DW with a different tag.
		
		Arguments:
		tag - (string) the tag to use in the ping.
		force - (boolean) execute the ping even if fireOnce=true and this has already been fired
		
		Example:
		>var jlWinLoad = new Jlogger({tag: 'myTag', element: document.body, event: 'load', pId: 12345});
		>//later...
		>jlWinLoad.pingTag('someOtherTag'); //pings DW with the new tag 
		>//but doesn't alter the settings of the instance
	*/
	pingTag: function(tag, force){
		this.ping(this.makeURL(tag), force);
	},
/*	Property: stopObserving
		Stops pingging DW; can be turned back on with <startObserving>
	*/
	stopObserving: function(){
		//turns off this logger
		this.active = false;
	},
/*	Property: startObserving
		Starts pinging DW again; see also <stopObserving>.
	*/
	startObserving: function(){
		//turns it back on
		this.active = true;
	}
});
Jlogger.implement(new Events);
Jlogger.implement(new Options);
//legacy namespace
var jlogger = Jlogger;
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/utilities/jlogger.js,v $
$Log: jlogger.js,v $
Revision 1.11  2007/03/10 00:37:10  newtona
docs update

Revision 1.10  2007/03/10 00:31:10  newtona
.pingDW is now just .ping
element and event are no longer required (so jloggers can just get fired inline)
executeNow is deprecated; just use new Jlogger().ping()
added support for cval and ctype

Revision 1.9  2007/03/09 23:57:18  newtona
docs update

Revision 1.8  2007/03/09 23:23:23  newtona
*** empty log message ***

Revision 1.7  2007/03/09 20:15:03  newtona
numerous bug fixes

Revision 1.6  2007/03/09 18:42:27  newtona
options.name is no longer required or used

Revision 1.5  2007/02/21 00:30:18  newtona
switched Class.create to Class.empty

Revision 1.4  2007/02/07 20:52:22  newtona
implemented Options class
implemented Events class

Revision 1.3  2007/01/26 05:56:03  newtona
syntax update for mootools 1.0
docs update

Revision 1.2  2007/01/22 21:54:17  newtona
updated for mootools version 1.0
updated namespaces to capitazlied values

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.4  2006/11/13 23:54:31  newtona
added some error handling

Revision 1.3  2006/11/03 19:42:06  newtona
moved jlscroller into it's own file

Revision 1.2  2006/11/03 19:38:32  newtona
Cleaning up a bit of code and documentation, mainly around the way the class stores options.

Revision 1.1  2006/11/02 21:28:08  newtona
checking in for the first time.


*/
