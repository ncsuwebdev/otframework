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
