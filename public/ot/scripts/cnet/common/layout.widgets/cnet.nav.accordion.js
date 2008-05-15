/*	Script: cnet.nav.accordion.js
		Makes the default navigation work on CNET.com using the <CNETNavAccordion> class from the global framework.
		
		Dependancies:
			 mootools - 	<Moo.js>, <Function.js>, <Array.js>, <String.js>, <Element.js>, <Fx.Base.js>, <Fx.Elements.js>
			 cnet libraries - <dbug.js>, <multiple.open.accordion.js>
			
		Author:
			Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>

		Class: CNETNavAccordion
		Builds a CNET navigation accordion using <MultipleOpenAccordion>.
		
		Arguments:
		options - an object of key/value options
		
		Options:
		openAll - (boolean) open all the items when it loads; defaults to *false*
		allowMultipleOpen - (boolean) allow more than one item to to be open at a time; 
						defaults to *true*
		defaultOpenIndexes - (array) list of items to open by default (see examples); 
						defaults to *[0]* (the first item)
		defaultOpenClassName - (string) classname given to items in the accordion that you want to
						force open in addition to the indexes specified in defaultOpenIndexes
		allowCookie - (boolean) allow the state of the accordion to be saved in a cookie; 
						defaults to *false*;
		cookieName - (string) the name for the cookie preference; must be unique per page/context
		cookieDuration - (int) number of days to keep the cookie (defaults to 999)
		stretchToggleSelector - (string) the css selector for all the elements that open 
						the accordion elements. defaults to '.xNavGrp .btn'.
		stretcherSelector - (string) the css selector for all the elements that slide open 
						and closed when the toggles are clicked. defualts to '.xNavGrp ul'
		
		Examples:
		(start code)
new CNETNavAccordion({
	defaultOpenIndexes([0, 3]), //show the first and fourth by default
	allowCookie: true, //let's let the user save the state
	cookieName: 'CNETHomePageNav' //the name of the cookie
});
		(end)

		Note:
		The logic for the page loading display is as follows:
		
		If allowCookie is set to true and the cooke name is set, get the cookie and 
		show the values the user previously chose. 
		
		If the cookie is not set or not allowed, and openAll is true, show everything.
		
		If openAll is false, show the indexes specified in defaultOpenIndexes.
	*/

var CNETNavAccordion = new Class({
	initialize: function(options){
		try{
			this.setOptions({
				openAll: false,
				allowMultipleOpen: true,
				defaultOpenIndexes: [0],
				defaultOpenClassName: 'forceOpen',
				allowCookie: false,
				cookieName: false,
				cookieDuration: 999,
				stretchToggleSelector: 'div.xNavGrp div.btn',
				stretcherSelector: 'div.xNavGrp ul'
			}, options);
			var start = (this.options.openAll)?'first-open':'open-first';

			if(this.options.allowCookie && this.options.cookieName && this.getPref()) this.options.defaultOpenIndexes = this.getPref();
			var toggles = $$(this.options.stretchToggleSelector);
			var stretchers = $$(this.options.stretcherSelector);
			if(toggles && stretchers) {
				var forceOpen = toggles.filter(function(t, i){
					return t.hasClass(this.options.defaultOpenClassName);
				}, this);
				forceOpen.extend(this.options.defaultOpenIndexes);
				this.Accordion = new MultipleOpenAccordion(toggles, stretchers, {
					openAll: this.options.openAll,
					allowMultipleOpen: this.options.allowMultipleOpen,
					firstElementsOpen: forceOpen,
					//if we're showing everything, just expose the first item, otherwise slide it open
					start: start
				});
			}
		} catch(e){ 
			dbug.log('nav accordion error: %s', e);
		}
	},
	savePref: function(iToShow){
		Cookie.set(Json.toString(this.options.cookieName), iToShow, {duration:cookieDuration});
	},
	getPref: function(){
		return Json.evaluate(Cookie.get(this.options.cookieName));
	}
});
CNETNavAccordion.implement(new Options);
CNETNavAccordion.implement(new Events);
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/layout.widgets/cnet.nav.accordion.js,v $
$Log: cnet.nav.accordion.js,v $
Revision 1.8  2007/02/21 00:29:04  newtona
implemented Options
fixed a bug with forceOpen
expoded dbug for error handling

Revision 1.7  2007/02/07 20:51:55  newtona
implemented Options class
implemented Events class

Revision 1.6  2007/02/03 01:39:19  newtona
fixed docs typo

Revision 1.5  2007/01/26 05:53:47  newtona
syntax update for mootools 1.0

Revision 1.4  2007/01/22 22:49:43  newtona
updated cookie.set syntax

Revision 1.3  2007/01/22 21:59:36  newtona
updated for mootools 1.0

Revision 1.2  2007/01/22 21:56:18  newtona
updated for mootools version 1.0

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.7  2007/01/09 01:23:42  newtona
changed $S to $$

Revision 1.5  2006/12/06 20:14:59  newtona
carousel - improved performance, changed some syntax, actually deployed into usage and tested
cnet.nav.accordion - improved css selectors for time
multiple accordion - fixed a typo
dbug.js - added load timers
element.cnet.js - changed syntax to utilize mootools more effectively
function.cnet.js - equated $set to $pick in preparation for mootools v1

Revision 1.4  2006/11/22 00:52:20  newtona
docs update

Revision 1.3  2006/11/22 00:49:41  newtona
docs update

Revision 1.2  2006/11/06 19:19:31  newtona
fixed a bug and removed some dbug.log statements

Revision 1.1  2006/11/02 21:28:08  newtona
checking in for the first time.


*/
