/*	
Script: jlscroller.js
Extends the <jLogger> class to capture scroll events.

Dependancies:
	 mootools - <Moo.js>, <Utility.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>
	 cnet libraries - <dbug.js>, <jlogger.js>
	
Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>

Class: JlScroller 
Adds Scroll captures to the jlogger class; extends <jlogger>.

Arguments:
	options - optional, an object containing options.

Options:
	scrollTo - {top: #|element, bottom: #|element}
		- top: a number for the top range to observe (i.e. if the user scrolls down that many pixels)
	     or an element, whose top will set the top range of the scroll area to observe
	bottom: (optional) - if left empty, the range will be zero (i.e. top = bottom)
	    - a number for the bottom range to observe
			- or the same element as the top element which will create a range the height of that element,
			- or, no value for top but an element id for the bottom will set the bottom of the element 
			     to a single line across the page,
			- or a different element, the top of which will define the bottom range of the area
 Note:
 a scrollTo observer requires no entry for the 'element' variable to instantiate it.
	      additionally, either the top or the bottom range must be visible (i.e. any portion 
			 of the range) for the ping to fire			

Example:
	(start code)
//capture a scroll to 800 pixels
new jlogger({
	ontid: '20', 
	siteId:'4', 
	pId:'2001', 
	tag:'scrollTo800', 
	fireOnce: false,
	event: 'scrollTo',
	scrollTo: {top:'800'}
});

//capture scroll to the top of an element
new jlogger({
	ontid: '20', 
	siteId:'4', 
	pId:'2001', 
	tag:'scrollBoxTop', 
	fireOnce: false,
	event: 'scrollTo',
	scrollTo: {top:'myElement'}
});
(end)
	*/
var JlScroller = Jlogger.extend({
	setup: function() {
		//if passed in: tag, event, and event == "scrollTo", set up a scroller monitor
 		if ($type(this.options.tag) && $type(this.options.event) && this.options.event == "scrollTo") {
			//log this if debug=true is in the url
			dbug.log('event observe(element: '+this.options.element+', event: '+this.options.event+', tag: '+this.options.tag+', scrollTo: '+this.options.scrollTo+')');
			//set up scrollTo monitor
			this.setUpScrollTo();
		} else this.parent();
	},
	//if we're observing a scroll event, this function handles the setup.
	setUpScrollTo: function() {
		//start with top & bottom set to invalid values (i.e. numbers that the browser can't create on the page)
		var top = -1;
		var bottom = -1;
		try {
			//if scrollTo.top is a number, set that number to the top range
			if ($type(this.options.scrollTo.top) && this.options.scrollTo.top.toInt) top = this.options.scrollTo.top.toInt();
			//else top is not a number, so set top to the top of the element passed in
			else if ($type(this.options.scrollTo.top)) top = $(this.options.scrollTo.top).getTop();
			//save this top value. we're going to need this variable even if it wasn't passed in
			//if all that was passed in was bottom: <elementId>, we'll need to get the top of that
			//element to figure out the bottom of it.
			tmpTop = top;
			//of the bottom is set
			if($type(this.options.scrollTo.bottom)) {
				//if top is still -1 and bottom isn't a number (it's assumed then that it's an element id)
				if (top == -1 && !$chk(parseInt(this.options.scrollTo.bottom)))
					//and save its top
					top = $(this.options.scrollTo.bottom).getTop();
				//if the bottom is the same as the top (in the case of passing in the same element id for both)
				//or the top wasn't set, then set the bottom of the range to the bottom of the element
				//which is calculated by adding the top position of the element to the height of the element
				if (this.options.scrollTo.bottom == this.options.scrollTo.top || !$type(this.options.scrollTo.top))
					bottom = $(this.options.scrollTo.bottom).getStyle('height').toInt() + top;
				else if ($chk(parseInt(this.options.scrollTo.bottom)) && tmpTop >= 0) //else, if the bottom is a number and top is set to something
					bottom = tmpTop + this.options.scrollTo.bottom; //add it to the top value
				else if ($chk(parseInt(this.options.scrollTo.bottom))) //else the top isn't set, so the bottom is just the number passed in
					bottom = this.options.scrollTo.bottom;
				else//else the bottom is an element, so the bottom is equal to the top of it.
					bottom = $(this.options.scrollTo.bottom).getTop();
			} else //else, bottom isn't set at all, so set it's numerical location = to the top
				bottom = top;
			//set top back to our placeholder
			top = tmpTop;
			//if top is still invalid (i.e. -1), make it equal to the bottom value
			if (top < 0) top = bottom;
			dbug.log("new tripwire (%s): top: %s, bottom: %s", this.options.tag, top, bottom);
			if (top >= 0 && bottom >=0) {
				//if the top & the bottom are both valid numbers (>0), set up the scroll observer
				window.addEvent('scroll', this.isOnScreen.bind(this, [top, bottom]));
				//check to see if this range is on screen right NOW
				this.isOnScreen(top, bottom);
			}
		} catch(e) {
			//if there was an error, the DOM might not be ready yet, let's wait a moment and try again
			//let's cap these retries at 10 times.
			if (this.errors < 10) {
				dbug.log('JlScroller error: %o, attempt #: %s', e, this.errors);
				this.errors++;
				this.setUpScrollTo.delay(20);
			} else dbug.log('giving up attempt to set up instance of JlScroller for %s', this.options.tag);
		}
	},
	isOnScreen: function(top, bottom) {
		//is the area defined on the screen
		// top: the top of the area
		// bottom: optional - the bottom of the zone
		// if bottom is not defined, then the area is just a line = top
		var dim = this.getScreenDimensions(); //get the dimensions of the browser screen
		var scroll = this.getScrollOffset(); //get the scroll offset
		try {
			//if the top of our range is between the scroll offset and the scroll offset + the window height (i.e. is visible)
			//or if the bottom is in that range
			if ((top > scroll.y && top < scroll.y+dim.h) || (bottom > scroll.y && bottom < scroll.y+dim.h)) {
				//if this hasn't been marked as having fired...
				//ping dw; note that ping sets this.fired to true
				if(!this.fired) this.ping();

			//if the user scrolled and the range is NOT visible, AND fireOnce is not set to true
			//set fired back to false (so it can fire again, but only once for each time that the
			//range becomes visible)
			} else if (this.fired && !this.options.fireOnce) this.fired = false;
		} catch(e) { dbug.log("isOnScreen error: %s", e) }
	},
	getScreenDimensions: function() {
		return {w: window.getWidth(), h: window.getHeight()};
	},
	getScrollOffset: function() {
		return {x: window.getScrollLeft(), y: window.getScrollTop()};
	}
});
//legacy namespace
var jlScroller = JlScroller;
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/utilities/jlscroller.js,v $
$Log: jlscroller.js,v $
Revision 1.6  2007/03/28 18:09:03  newtona
removing $type.isNumber dependencies

Revision 1.5  2007/03/10 00:31:10  newtona
.pingDW is now just .ping
element and event are no longer required (so jloggers can just get fired inline)
executeNow is deprecated; just use new Jlogger().ping()
added support for cval and ctype

Revision 1.4  2007/03/09 20:15:03  newtona
numerous bug fixes

Revision 1.3  2007/01/26 05:56:03  newtona
syntax update for mootools 1.0
docs update

Revision 1.2  2007/01/22 21:54:17  newtona
updated for mootools version 1.0
updated namespaces to capitazlied values

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.2  2006/11/04 00:52:21  newtona
added docs, fixed a little syntax

Revision 1.1  2006/11/03 19:41:25  newtona
moving jlscroller class into it's own javasacript file


*/
