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


*/