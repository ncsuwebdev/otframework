/*	Script: IconMenu.js
		A simple icon (img) based menu.
		
		Author: 
		Aaron Newton
		
		Dependencies:
		Mootools 1.11 - <Core.js>, <Class.js>, <Class.Extras.js>, <Array.js>, <Element.js>, <String.js>, <Function.js>, <Number.js>, <Element.Event.js>, <Element.Selectors.js>, <Element.Dimensions.js>, <Fx.Base.js>, <Fx.Css.js>, <Fx.Style.js>, <Fx.Styles.js>, <Fx.Elements.js>, <Fx.Slide.js>, <Color.js>
		CNET - <element.pin.js>, <element.dimensions.js>, <fixpng.js>
		
		Class: IconMenu
		A simple icon (img) based menu.
		
		Arguments:
		options - a key/value set of options.
		
		Options:
		container - (DOM element or id) the container of the menu
		images - (string) the class given to all the image in the menu (default ".iconImgs")
		captions - (string) the class given to all the captions (default ".iconCaptions")
		removeLinks - (string or collection) a selector or a collection; all the links to add remove actions to
		clearLinks - (string or collection) a selector or a collection; all the links to add clear actions to
		useAxis - (string) the zooming axis to use; 'x', 'y', or 'both' (default 'x')
		scrollFxOptions - (object) options for the <Fx.Style> that scrolls the icons left and right
		
		Events:
		onFocus - (function) callback executed when the user mouses over an icon (default function(){})
		onFocusDelay - (integer) amount of time to delay the onFocus event; if the user mouses out from 
							the icon before the duration of the delay, the event is not fired; defaults to zero
		initialFocusDelay - (integer) amount of time to delay before the first onFocus event is fired
		onBlur - (function) callback executed when the user mouses over an icon (default function(){})
		onBlurDelay - (integer) amount of time to delay the onBlur event; if the user puts their mouse
							back over the icon before the duration of the delay, the event is not fired; defaults to zero
		onEmpty - (function) callback executed when the toolbar is emptied of icons
		onRemoveItem - (function) callback executed when an item is removed; passed the container 
				of the image and caption just before they are removed
		onRemoveItems - (function) callback executed after items are removed using <IconMenu.removeItems>; passed an array of
				the removed items
		onSelect - (function) callback executed when an icon is selected; passed the index of the icon and
				the image in the icon
		onDeSelect - (function) same as onSelect, only it's fired when something is deselected
		onItemsAdded - (function) callback executed when all the icons are loaded on initialize
		onAdd - (function) callback executed when an individual item is added; passed the image and caption

		Instance Variables:
		container - the element that holds all the icons
		images - an array of all the images
		captions - an array of all captions (associated to each image in imgs)
		selected - an array of icons that are in the selected state
		side - 'left' or 'top' state (where the bar is and its alignment); based on options.useAxis
		scrollerFx - effect used for scrolling left and right
		currentOffset - the currently scrolled-to index
		inFocus - the icon currently hovered over
		prevFocus - the icon previously hovered over
		
		Definitions/Conventions:
		"img" or "image" - generally is a refrence to an actual image in the icon menu
		"caption" - generally is a reference to an optional DOM element that may accompany an image
		"icon" - generally is a reference to the DOM element containing an image and (possibly) a caption
			*/
var IconMenu = new Class({
	options: {
			container: document,
			images: ".iconImgs",
			captions: ".iconCaptions",
			removeLinks: false,
			clearLinks: false,
			useAxis: 'x',
			onFocus: Class.empty, //mouseover of target area
			onFocusDelay: 0,
			initialFocusDelay: 250,
			onBlur: Class.empty, //mouseout of target area
			onEmpty: Class.empty,
			onBlurDelay: 0,
			onRemoveItem: Class.empty,
			onRemoveItems: Class.empty,
			length: 'auto',
			iconPadding: 1,
			scrollFxOptions: {
				duration: 1800,
				transition: Fx.Transitions.Cubic.easeInOut
			},
			onScroll: Class.empty,
			onPageForward: Class.empty,
			onPageBack: Class.empty,
			backScrollButtons: '#scrollLeft',
			forwardScrollButtons: '#scrollRight',
			onSelect: function(index, img){
				//set up the border effect
				if(!this.borderFx[img.getProperty('id')])	this.borderFx[img.getProperty('id')] = img.effects({duration: 800});
				//and fade the border to blue
				this.borderFx[img.getProperty('id')].start({
						'border-top-color': '#00A0C6',
						'border-left-color': '#00A0C6',
						'border-right-color': '#00A0C6',
						'border-bottom-color': '#00A0C6'
				});
			},
			onDeSelect: function(index, img){
				//set up the border effect
				if(!this.borderFx[img.getProperty('id')])	this.borderFx[img.getProperty('id')] = img.effects({duration: 800});
				//and fade the border back to grey
				this.borderFx[img.getProperty('id')].start({
						'border-top-color': '#555',
						'border-left-color': '#555',
						'border-right-color': '#555',
						'border-bottom-color': '#555'
				});
			}
	},

	initialize: function(options) {
		//set the options
		this.setOptions(options);
		//save a reference to the container
		this.container = $(this.options.container);
		//this.setRange(this.options.visibleOffset, this.options.maxVisible);
		//containers for the images, captions, and the selected items
		this.imgs = [];
		this.captions = [];
		this.selected = [];
		//get the captions from the options
		var captions = ($type(this.options.captions) == "string")?
			this.container.getElements(this.options.captions):
			this.options.captions;
		//get the images from the options
		var imgs = ($type(this.options.images) == "string")?
			this.container.getElements(this.options.images):
			this.options.images;
		//loop through each one
		imgs.each(function(img, index) {
			//add it to the menu
			this.addItem(img, captions[index], null);
		}, this);
		//make sure the container and its parent has a position set
		[this.container, this.container.getParent()].each(function(el){
			if(el.getStyle('position') == 'static') el.setStyle('position', 'relative');
		});

		this.fireEvent('onItemsAdded', this.imgs, 50);
		this.side = (this.options.useAxis == 'x')?'left':'top';
		this.container.setStyle(this.side, this.container.getStyle(this.side).toInt()||0);
		this.onFocusDelay = this.options.initialFocusDelay;
		//set up the events
		this.setupEvents();
	},
/*	Section: Public Methods

		Property: scrollTo
		Scrolls the icons in the bar to the specified index.
		
		Arguments:
		index - (integer) the index of the icon you want to scroll to
		useFx - (boolean; optional) use transition or not; defautls to true
		 */
	scrollTo: function(index, useFx){
		//set useFx default to true
		useFx = $pick(useFx, true);
		//get the current range in view
		var currentRange = this.calculateRange();
		//if we're there, exit
		if(index == currentRange.start) return;
		//get the range for the new position
		var newRange = this.calculateRange(index);
		//if this returns no items, exit
		if(!newRange.elements.length) return; //no next page! >> Ajax here
		//create the scroll effects if not present already
		if(!this.scrollerFx) this.scrollerFx = this.container.effect(this.side, $merge(this.options.scrollFxOptions, {wait: false}));
		//scroll to the new location
		if(useFx) {
			this.scrollerFx.start(-newRange.elements[0].offset).chain(function(){
			//set the index to be this new location
				this.fireEvent('onScroll', [index, newRange]);
			}.bind(this));
		} else {
			//we're not using effects, so just jump to the location
			this.scrollerFx.set(-newRange.elements[0].offset);
			this.fireEvent('onScroll', [index, newRange]);
		}
		this.currentOffset = index;
	},
/*	Property: pageForward
		Pages the icon set one visible set forward; a set is defined as the number of icons in range.
		
		Arguments:
		howMany - (integer) optional; you can define a set as a fixed number rather than on the visible amount
	*/
	pageForward: function(howMany){
		var range = this.calculateRange();
		this.scrollTo(($type(howMany) == "number")?range.start+howMany:range.end);
	},
/*	Property: pageBack
		Pages the icon set one visible set backward; a set is defined as the number of icons in range.
		
		Arguments:
		howMany - (integer) optional; you can define a set as a fixed number rather than on the visible amount
	*/
	pageBack: function(howMany) {
		this.scrollTo(($type(howMany) == "number")?this.currentOffset-howMany:this.calculateRange(this.currentOffset, true).start);
	},
/*	Property: addItem
		Adds an item to the icon bar.
		
		Arguments:
		img - DOM element for the icon; typically an image
		caption - DOM element for the caption related to the image, optional
		where - (integer) index where to put it; defaults to the end of the icon set
*/
	addItem: function(img, caption, where) {
		//figure out where to put it
		where = ($defined(where))?where:this.imgs.length;
		//if we've already got this image in there, remove it before putting it in the right place
		if(this.imgs.contains(img)) this.removeItems([img], true);
		//insert the image and caption into the array of these things
		this.imgs.splice(where, 0, $(img));
		this.captions.splice(where, 0, $(caption));

		//if the image doesn't have an id, then lets make one
		var src = img.getProperty('src');
		if(!img.getProperty('id')) img.setProperty('id', src.substring(src.lastIndexOf('/')+1, src.lastIndexOf('.')));

		//fix the image if it's png
		if(img.getProperty("src").test("$png") && window.ie && !img.hasClass('fixPNG')) fixPNG(img);
		//set up the events for the element
		this.setupIconEvents(img, caption);
		this.fireEvent('onAdd', [img, caption]);
	},
/*	Property: removeItems
		Removes a list of items from the icon menu.
		
		Arguments:
		imgs - (array) an array of images to remove
		useFx - (boolean) transition the images away (true; default), or remove them instantly (false)
	*/
	removeItems: function(imgs, useFx){
		var range = this.calculateRange();
		if(!imgs.length) return;
		//create a copy; this is because
		//IconMenu.empty passes *this.selected*
		//which we modify in the process of removing things
		//so we must work on a copy of that so we don't change
		//the list as we iterate over it
		imgs = imgs.copy();
		//set the fx default
		useFx = $pick(useFx, true);
		//placeholder for the items we're removing; the effect will
		//only be applied to the dom element that contains the image and the caption
		var fadeItems = [];
		//the effect we'll use
		var effect = {
				width: 0,
				'border-width':0
		};
		//an object to store all the copies of the effect; one for each item to be passed
		//to Fx.Elements
		var fadeEffects = {};
		//for items that aren't in the current view, we're not going to use a transition
		var itemsToQuietlyRemove = {
			before: [],
			after: []
		};
				
		//a list of all the icons by index
		var indexes = [];
		//for each image in the set to be removed
		imgs.each(function(image){
			var index = this.imgs.indexOf(image);
			//if the image is visible
			if(index >= range.end) {
				itemsToQuietlyRemove.after.push(image.getParent());
			} else if(index < range.start) {
				itemsToQuietlyRemove.before.push(image.getParent());
			} else {
				//store the parent of the image
				fadeItems.push(image.getParent());
				//copy the effect value for this item
				fadeEffects[fadeItems.length-1] = $merge(effect);
			}
			//remove the reference in the selected array
			//because when it's gone, it won't be selected anymore
			this.selected.remove(image);
			//store the index of where this image was in the menu
			indexes.push(index);
		}, this);
		//loop through the captions and remove the captions that match 
		//the images that were removed
		this.captions = this.captions.filter(function(caption, index){
			return !indexes.contains(index);
		});
		//do the same for the list of images in the menu
		//we didn't do this earlier so we could avoid changing
		//the array while we were working on it
		this.imgs = this.imgs.filter(function(img, index){
			return !indexes.contains(index);
		});
		var removed = [];
		//items page left, remove them, but then we have to update the scroll offset to account
		//for their departure
		if(itemsToQuietlyRemove.before.length) {
			var scrollTo = this.imgs.indexOf(range.elements[0].image);
			itemsToQuietlyRemove.before.each(function(el){
				this.fireEvent('onRemoveItem', [el]);
				var img = el.getElement('img');
				removed.push(img.id);
				try {
					el.remove();
					//scroll to the current offset again quickly
				}catch(e){ dbug.log('before: error removing element %o, %o', el, e); }
			}, this);
			this.scrollTo(scrollTo, false);
		}
		//for items page right, just remove them quickly and quietly
		itemsToQuietlyRemove.after.each(function(el){
			this.fireEvent('onRemoveItem', [el]);
			removed.push(el.getElement('img').id);
			try {
				el.remove(); 
			}catch(e){ dbug.log('after: error removing element %o, %o', el, e); }
		});

		//define a function that removes all the items from the dom
		function clean(range, additionalItems){
			var items = [];
			//then fade out the items that are currently visible
			fadeItems.each(function(el){
				this.fireEvent('onRemoveItem', [el]);
				items.push(el.getElement('img').id);
				try {
					el.remove(); 
				}catch(e){ dbug.log('fade: error removing element %o, %o', el, e); }
			}, this);
			items.merge(additionalItems);
			this.fireEvent('onRemoveItems', [items]);
			range = this.calculateRange();
			if(range.elements == 0 && range.start > 0) this.pageBack();
			//if there aren't any items left, fire the onEmpty event
			if(!this.imgs.length) this.fireEvent('onEmpty');
		}
		//if we're using effects, do the transition then call clean()
		if(useFx) new Fx.Elements(fadeItems).start(fadeEffects).chain(clean.bind(this, [range, removed]));
		//else just clean
		else clean.apply(this, [range, removed]);
	},
/*	Property: removeSelected
		Removes the icons that the user selected (see IconMenu.selectItem) from the menu.
	*/
	removeSelected: function(useFx){
		this.removeItems(this.selected, useFx);
	},
/*	Property: empty
		Empties the menu entirely.
		
		Arguments:
		suppressEvent - (boolean) prevents the onEmpty event from firing.
	*/
	empty: function(suppressEvent){
		//placeholder for the effects and items
		var effect = {};
		var items = [];
		//loop through all the images in the icon menu
		this.imgs.each(function(img, index){
			//add the icon container to the list of items to remove
			items.push(img.getParent());
			//create a reference for each one to pass to Fx.Elements
			effect[index] = {opacity: 0};
		});
		//create an instance of Fx.Elements and fade them all out
		new Fx.Elements(items).start(effect).chain(function(){
			//then remove them all instantly
			this.removeItems(this.imgs, false);
			//and fire the onEmpty event
			if(!suppressEvent) this.fireEvent('onEmpty');
		}.bind(this));
	},
/*	Property: selectItem
		Designates an item at the specified index as being selected.
		
		Arguments:
		index - (integer) the location of the icon to select
		select - (boolean) true: select the item; false: deselect; optional. If not specified it toggles
		
	*/
	selectItem: function(index, select){
		//place holder for border effects
		if(!this.borderFx) this.borderFx = {};
		//get the image to select
		var img = this.imgs[index];
		//...and its id
		var imgId = img.getProperty('id');
		//add or remove the "selected" class
		if($defined(select)) {
			if(select) img.addClass('selected');
			else img.removeClass('selected');
		} else {
			img.toggleClass('selected');
		}
		//if it has the class, then fade the border blue
		if(img.hasClass('selected')){
			//store this image in the index of selected images
			this.selected.push(img);
			this.fireEvent('onSelect', [index, img]);
		} else {
			//else we're deselecting; remove the image from the index of selected images
			this.selected.remove(img);
			this.fireEvent('onDeSelect', [index, img]);
		}
	},
/* 	Section: Private Methods

		Property: getDefaultWidth
		Internal; calculates the width of the container; used for scrolling.
 */
	getDefaultWidth: function(){
		//if the user specified a width, just return it
		if($type(this.options.length) == "number") return this.options.length;
		//if, on the other hand, they specified another element than the container
		//to calculate the width, use it
		var container = $(this.options.length);
		//otherwise, use the container
		if(!container) container = this.container.getParent();
		//return the width or height of that element, depending on the axis chosen in the options
		return container.getSize().size[this.options.useAxis];
	},
/*	Property: getIconPositions
		Internal; returns an array for each item containign data used for scrolling.
 */
	getIconPositions: function(){
		var offsets = [];
		var cumulative = 0;
		var prev;
		var axis = this.options.useAxis;
		//loop through all the items
		this.imgs.each(function(img, index){
			//we're measuring the element that contains the image
			var parent = img.getParent();
			//get the width or height of that parent using the appropriate axis
			cumulative += (prev)?img.getPosition()[axis] - prev.getPosition()[axis]:0;
			prev = img;
			//var size = parent.getSize().size[this.options.useAxis]
			//store the data
			offsets.push({
				image: img,
				size: parent.getSize().size[this.options.useAxis],
				offset: cumulative,
				container: parent
			});
		}, this);
		return offsets;
	},
/*	Property: calculateRange
		Internal; calculates the icons that can be viewed in the container starting at a given index.
		
		Arguments:
		index - (integer) the index of icons where to start
		fromEnd - (boolean) if true, will figure out the result in reverse (used for scrolling back)
			and index now correspondes the endpoint, not the offset
 */
	calculateRange: function(index, fromEnd){
		if(!this.imgs.length) return {start: 0, end: 0, elements: []};
		index = $pick(index, this.currentOffset||0);
		if(index < 0) index = 0;
		//dbug.trace();
		//get the width of space that icons are visible
		var length = this.getDefaultWidth();
		//get the positions of all the icons
		var positions = this.getIconPositions();
		var referencePoint;
		//if we're paginating forward the reference point is the left edge 
		//of the range is the left edge of the first icon
		//but if we're going backwards, the referencePoint is the left edge of the first icon currently in range
		//the problem is if the user removes the entire last page of icons, then this
		//item no longer exists, so...
		if(positions[index]) {
			//if the item exists, use it
			referencePoint = positions[index].offset;
		} else {
			//else the right edge of the last icon is the reference point
			//the last icon is the container of the last image
			var lastIcon = this.imgs.getLast().getParent();
			var coords = lastIcon.getCoordinates();
			//and the reference point is that icon's width plus it's left offset minus the offset 
			//of the parent (which gets offset negatively and positively for scrolling
			referencePoint = coords.width + coords.left - lastIcon.getParent().getPosition().x;
		}
		//figure out which ones are in range
		var range = positions.filter(function(position, i){
			//if the index supplied is the endpoint
			//then it's in range if the index of the icon is less than the index,
			//and the left side is less than that of the one at the end point
			//and if the left side is greater than or equal to the end point's position minus the length
			if(fromEnd) return i < index && 
												 position.offset < referencePoint &&
												 position.offset >= referencePoint-length;
			
			//else we go forward...
			//if the item is after the index start and the posision is 
			//less than the max width defined, include it
			else return i >= index && position.offset+position.size < length+positions[index].offset;
		});
		//return the data
		return (fromEnd)?{start: index-range.length, end: index, elements: range}
					 :{start: index, end: range.length+index, elements: range};
	},
/*	Property: inRange
		Internal; given an index of an item, determine if it is in visible range
		
		Arguments:
		index - (integer) the index of the icon to check
 */
	inRange: function(index) {
		//calculate the range
		var range = this.calculateRange();
		//return the result
		return index < range.end && index >= range.start;
	},
/*	Property: setupEvents
		Internal; sets up the mouseleave event for the container to make sure that we 
		remove the focus attribute if the user removes their mouse from the bar; also
		sets up the clear and remove links.
 */
	setupEvents: function(){
		$(this.options.container).addEvents({
			"mouseleave": function() {
				if(this.inFocus) this.inFocus = null;
				this.imgOut(null, true);
			}.bind(this)
		});
		
		$$(this.options.backScrollButtons).each(function(el){
			el.addEvent('click', this.pageBack.bind(this));
		}, this);
		$$(this.options.forwardScrollButtons).each(function(el){
			el.addEvent('click', this.pageForward.bind(this));
		}, this);
		
		$$(this.options.clearLinks).each(function(el){
			el.addEvent('click', this.empty.bind(this));
		}, this);
		$$(this.options.removeLinks).each(function(el){
			el.addEvent('click', this.removeSelected.bind(this));
		}, this);
	},
/*	Property: imgOver
		Internal; function executed when the user mouses over an icon; this is the container
		holding the image and the caption, not the image itself, though the image is what
		is passed in as an argument
		
		
		Arguments:
		img - (DOM element) the image (contained with in the array this.imgs) that has been hovered over
 */
	imgOver: function(img){
		//set the value of what's in focus to be this image
		this.inFocus = img;
		//clear the overTimeout
		$clear(this.overTimeout);
		//delay for the duration of the onFocusDelay option
		this.overTimeout = (function(){
			this.onFocusDelay = this.options.onFocusDelay;
			//if the user is still focused on the image, fire the onFocus event
			if (this.inFocus == img) this.fireEvent("onFocus", [img, this.imgs.indexOf(img)]);
		}).delay(this.onFocusDelay, this);
	},
/*	Property: imgOut
		Internal; similar to <imgOver> above, except it's fired when the mouse leaves.
 */
	imgOut: function(img, force){
		if(!$defined(img) && force) img = this.prevFocus||this.imgs[0];
		//if the focused image is this one
		if(this.inFocus == img && img) {
			//set it to null
			this.inFocus = null;
			//clear the delay timeout
			$clear(this.outTimeout);
			//wait the duration of the onBlurDelay
			this.outTimeout = (function(){
				this.prevFocus = img;
				//if we're not still focused on this image, fire onBlur
				if (this.inFocus != img || (img == null && force)) this.fireEvent("onBlur", [img, this.imgs.indexOf(img)]);
				if (!this.inFocus) this.onFocusDelay = this.options.initialFocusDelay;
			}).delay(this.options.onBlurDelay, this);
		}
	},
/*	Property: setupIconEvents
		Internal; sets up events for an icon
 */
	setupIconEvents: function(img, caption){
		//add the click event
		img.addEvents({
			click: function(e){
				e = new Event(e);
				//if the user is holding down control, select the image
				if(e.control) {
					this.selectItem(this.imgs.indexOf(img));
					e.stop();
				}
			}.bind(this)
		});
		//set up the other events on the container of this image
		img.getParent().addEvents({
			mouseover: this.imgOver.bind(this, img),
			mouseout: this.imgOut.bind(this, img)
		});
	}
});
IconMenu.implement(new Events); 
IconMenu.implement(new Options);

/* do not edit below this line */	 
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/IconMenu.js,v $
$Log: IconMenu.js,v $
Revision 1.10  2007/09/05 18:37:03  newtona
fixing all js warnings in the code base; they weren't breaking anything, but they can create performance issues and it's good practice...

Revision 1.9  2007/09/04 17:35:10  newtona
Fixed a bug in IconMenu where, if removeLinks and clearLinks were not defined, the behavior was applied to all dom elements (doh!)
Rearranged ProductToolbar's references to these options so that, rather than setting up these behaviors itself it passes them on to IconMenu to do it.

Revision 1.8	2007/08/30 23:59:30	newtona
fixed chaining in Fx.Marquee; added to redball.common.full
tweaked docs in IconMenu

Revision 1.7	2007/08/30 21:11:33	newtona
added some new options

Revision 1.6	2007/08/28 23:26:57	newtona
fixing syntax errors - damned semi-colons.

Revision 1.5	2007/08/28 23:16:07	newtona
IconMenu now handles deleteing items off screen more effectively
reverted some logic in element.forms; the new stuff was a little buggy

Revision 1.4	2007/08/28 19:01:50	newtona
added onSelect and onDeselect events and moved the default logic into the options
fixed a doc typo
added wait: false to the scroller, double clicks now don't corrupt the state (you'll just scroll twice as far)
moved the currentOffset instance variable assignation outside of the effect change; this prevents issues with paginating faster than the effect
optimized range calculation calls
now, when deleting items, if you delete everything in the current page set, the bar will scroll one page to the left (if there is one)

Revision 1.3	2007/08/27 19:05:30	mikem
removes stray semicolon

Revision 1.2	2007/08/25 00:52:06	newtona
got lazy with my semi-colons...

Revision 1.1	2007/08/20 21:20:44	newtona
first big check in for RTSS History
*/