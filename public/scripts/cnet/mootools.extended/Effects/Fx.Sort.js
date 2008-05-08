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